import axios from 'axios';
import { getAuth, signOut } from 'firebase/auth'; // Importe les fonctions Firebase nécessaires
import { auth } from './firebase'; // Assurez-vous que votre instance 'auth' est exportée ici

// --- Configuration de base d'Axios ---

// 1. Permet d'envoyer et de recevoir des cookies avec nos requêtes.
// C'est ESSENTIEL pour que Laravel Sanctum (qui gère la sécurité des sessions)
// puisse envoyer et recevoir le cookie de session et le cookie de protection CSRF.
axios.defaults.withCredentials = true;

// 2. Définit l'URL de base pour toutes nos requêtes API.
// Au lieu de taper "http://localhost:8000/api/users", on pourra taper "/api/users".
// La variable d'environnement VITE_API_URL doit être configurée (par exemple, dans un fichier .env).
axios.defaults.baseURL = import.meta.env.VITE_API_URL;

// --- Intercepteur de REQUÊTES : Avant d'envoyer une requête ---
// Cet "intercepteur" est une fonction qui s'exécute automatiquement avant chaque requête Axios.
// Son but est d'ajouter le bon jeton d'authentification (Firebase ID Token ou Sanctum Auth Token).
axios.interceptors.request.use(async (config) => {
  // Récupère l'utilisateur actuellement connecté via Firebase.
  const firebaseUser = getAuth().currentUser;
  // Récupère le jeton d'authentification de Laravel Sanctum s'il a été stocké.
  const sanctumAuthToken = localStorage.getItem('authToken');

  // On définit une liste des routes pour lesquelles le backend attend le Firebase ID Token dans le CORPS de la requête.
  // Ces routes sont généralement celles qui gèrent la connexion initiale ou la définition du rôle.
  const routesRequiringFirebaseIdTokenInBody = [
    '/api/auth/google/firebase-callback', // Exemple: La première connexion Google
  ];
  const isTargetRouteForBodyToken = routesRequiringFirebaseIdTokenInBody.some(route => config.url.includes(route));

  // --- Logique de choix du Jeton ---

  // PRIORITÉ 1: Si l'utilisateur est connecté via Firebase ET que c'est une de nos routes spéciales (POST avec token dans le corps).
  // C'est souvent le cas pour la première interaction avec le backend après une connexion Firebase.
  if (firebaseUser && config.method === 'post' && isTargetRouteForBodyToken) {
    try {
      // On récupère le Firebase ID Token. `true` peut forcer un rafraîchissement si nécessaire.
      const idToken = await firebaseUser.getIdToken(true); 
      
      // On s'assure que le corps de la requête est un objet pour y ajouter le 'id_token'.
      if (typeof config.data === 'string') {
        try { config.data = JSON.parse(config.data); } 
        catch (e) { console.warn("Axios Interceptor (Request): Impossible de parser le corps de la requête JSON pour ajouter le token Firebase."); }
      } else if (!config.data) {
        config.data = {};
      }
      config.data.id_token = idToken; // Ajout du Firebase ID Token au corps.
      
      // Très important : Pour ces routes spécifiques, on supprime l'en-tête Authorization
      // car le backend attend le token dans le corps, pas dans l'en-tête.
      delete config.headers.Authorization;
      // console.log('Axios Interceptor (Request): Firebase ID Token ajouté au corps pour une route spécifique.');
    } catch (error) {
      console.error('Axios Interceptor (Request): Erreur lors de l\'obtention du Firebase ID Token (corps):', error);
      // La requête peut continuer sans le token, le backend gérera l'absence ou l'invalidité.
    }
  } 
  // PRIORITÉ 2: Si un jeton Laravel Sanctum est déjà disponible dans le local storage.
  // Cela signifie que l'utilisateur a déjà une session active avec notre backend Laravel.
  else if (sanctumAuthToken) {
    config.headers.Authorization = `Bearer ${sanctumAuthToken}`;
    // console.log('Axios Interceptor (Request): Jeton Sanctum ajouté à l\'en-tête Authorization.');
  }
  // PRIORITÉ 3: Si aucun jeton Sanctum n'est disponible, mais qu'un utilisateur Firebase est connecté.
  // Cela agit comme un "plan de secours" pour les requêtes authentifiées, ou pour les premières requêtes
  // qui ne nécessitent pas le token dans le corps (comme la récupération de listes).
  else if (firebaseUser) {
    try {
      const idToken = await firebaseUser.getIdToken(true);
      config.headers.Authorization = `Bearer ${idToken}`;
      // console.log('Axios Interceptor (Request): Firebase ID Token ajouté à l\'en-tête Authorization (par défaut).');
    } catch (error) {
      console.error('Axios Interceptor (Request): Erreur lors de l\'obtention du Firebase ID Token (en-tête):', error);
    }
  }

  // On retourne la configuration modifiée de la requête pour qu'elle puisse être envoyée.
  return config;
}, (error) => {
  // En cas d'erreur pendant la préparation de la requête, on la rejette.
  return Promise.reject(error);
});

// --- Intercepteur de RÉPONSES : Après avoir reçu une réponse du serveur ---
// Cet "intercepteur" s'exécute automatiquement après que le serveur ait répondu à une requête.
// Il gère deux choses : stocker un nouveau jeton Sanctum et déconnecter l'utilisateur en cas d'erreur d'authentification.
axios.interceptors.response.use(
  (response) => {
    // Si la réponse du serveur contient un nouveau 'token' (qui est notre jeton Laravel Sanctum),
    // on le stocke dans le "local storage" pour les futures requêtes.
    if (response.data && response.data.token) {
      localStorage.setItem('authToken', response.data.token);
      console.log('Axios Interceptor (Response): Nouveau jeton Sanctum enregistré.');
    }
    // On retourne la réponse telle quelle.
    return response;
  },
  async (error) => {
    // Si le serveur renvoie une erreur de statut 401 (Non autorisé) ou 419 (Jeton CSRF expiré/manquant),
    // cela signifie que notre session n'est plus valide.
    if (error.response && (error.response.status === 401 || error.response.status === 419)) {
      console.warn('Axios Interceptor (Response): Session invalide détectée (401 ou 419). Nettoyage.');
      
      // On supprime le jeton Sanctum du "local storage".
      localStorage.removeItem('authToken');
      
      // On déconnecte aussi l'utilisateur de Firebase (si une instance 'auth' et un utilisateur existent).
      // C'est important pour assurer une déconnexion complète.
      try {
        if (auth && auth.currentUser) {
          await signOut(auth);
          console.log('Axios Interceptor (Response): Déconnexion de Firebase effectuée.');
        }
      } catch (firebaseSignOutError) {
        console.error('Axios Interceptor (Response): Erreur lors de la déconnexion Firebase:', firebaseSignOutError);
      }
      // Note : La redirection vers la page de connexion/accueil est généralement gérée par les composants Vue
      // qui observent l'état de l'authentification ou gèrent l'erreur renvoyée par ce catch.
    }
    // On rejette l'erreur pour qu'elle puisse être traitée par le code qui a fait la requête.
    return Promise.reject(error);
  }
);

// --- Gestion du CSRF (Cross-Site Request Forgery) ---
// La protection CSRF est une mesure de sécurité essentielle pour éviter que des sites malveillants
// n'effectuent des actions indésirables sur votre site à l'insu de l'utilisateur.
// Laravel Sanctum gère cela via un cookie appelé "XSRF-TOKEN" pour les applications SPA.

// Cette fonction demande au serveur de nous envoyer le cookie CSRF.
// Elle est appelée une fois au démarrage de l'application.
async function getCsrfCookie() {
  try {
    // Axios enverra cette requête et, grâce à `axios.defaults.withCredentials = true;`,
    // le navigateur enregistrera le cookie "XSRF-TOKEN" envoyé par Laravel.
    await axios.get('/sanctum/csrf-cookie');
    console.log('CSRF cookie obtenu avec succès.');
  } catch (error) {
    console.error('Échec de l\'obtention du cookie CSRF:', error);
  }
}

// On appelle la fonction pour obtenir le cookie CSRF dès que ce script est chargé.
// C'est important pour que toutes les requêtes qui modifient des données (POST, PUT, DELETE) soient protégées.
getCsrfCookie();

// Ce bloc est une approche supplémentaire pour le CSRF. Avec Laravel Sanctum en mode SPA
// et `withCredentials`, le cookie seul est souvent suffisant. Cependant, certains environnements
// ou configurations peuvent bénéficier d'avoir le jeton CSRF également dans l'en-tête HTTP 'X-CSRF-TOKEN'.
// Ce code essaie de le récupérer d'une balise meta dans votre HTML (si vous en avez une)
// et de l'ajouter à toutes les requêtes Axios par défaut.
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
  console.log('Jeton CSRF de la balise meta défini globalement pour Axios.');
} else {
  console.warn('Balise meta "csrf-token" introuvable. La protection CSRF pourrait ne pas fonctionner comme prévu si elle dépend de cela.');
}

// On exporte notre instance Axios configurée pour qu'elle puisse être importée
// et utilisée dans d'autres parties de notre application (ex: dans nos composants Vue).
export default axios;