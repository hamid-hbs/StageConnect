<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <!-- Titre dynamique basé sur le type de profil et le nom de l'entité -->
        <h1 class="text-dark page-main-title mb-0">Profil de {{ profileTypeDisplay }} : {{ profileName || 'Chargement...' }}</h1>
      </span>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
    </div>

    <!-- Conteneur pour les notifications rapides (toasts) -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" :class="`text-bg-${toastType}`">
        <div class="toast-header" :class="`bg-${toastType}`">
          <strong class="me-auto text-white">Notification</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>

    <section class="profile-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="text-center text-secondary py-5">
        <div class="spinner-border" role="status" style="color: #0d6efd">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3 mb-0">Chargement du profil {{ profileTypeDisplay.toLowerCase() }}...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else class="profile-content">
        <!-- Section de l'image de couverture du profil -->
        <section class="profile-cover-section-view bg-light rounded shadow-sm mb-4 position-relative">
          <img :src="getCoverPhoto(profileData.user?.couverture)" :alt="`Image de couverture de ${profileName}`" class="profile-cover-image-view w-100 rounded-top" />
        </section>

        <!-- Carte principale du profil (nom, logo, description, détails) -->
        <section class="profile-main-card-view bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header-view d-flex align-items-start pb-4 mb-4 border-bottom">
            <div class="profile-avatar-placeholder-view me-4 position-relative">
              <!-- La classe de l'image sera dynamique pour des styles spécifiques si besoin -->
              <img :src="getProfilePhoto(profileData.user?.photo)" :alt="`Profil de ${profileName}`" :class="getLogoClass" />
            </div>
            <div class="profile-info-view flex-grow-1">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ profileName || 'Nom non spécifié' }}
              </h3>
              <!-- Le statut "En ligne" est désactivé ici car il n'était pas utilisé dans tous vos codes -->
              <!-- <p class="status-online fs-6 text-success d-flex align-items-center">
                <span class="dot bg-success me-1"></span> En ligne
              </p> -->
            </div>
          </div>

          <!-- Champ de description du profil -->
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">
              Description du {{ profileTypeDisplay.toLowerCase() }} :
            </label>
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.user?.description || `Ce ${profileTypeDisplay.toLowerCase()} n'a pas encore ajouté de description.` }}
            </p>
          </div>

          <!-- Bouton pour envoyer un message -->
          <div class="d-flex gap-2 mb-4">
            <button class="btn btn-primary" @click="sendMessage">
              <i class="fas fa-envelope me-2"></i> Envoyer un message
            </button>
          </div>

          <!-- Détails affichables du profil (séparés par type d'utilisateur) -->
          <div class="profile-details-view pb-4 mb-4">

            <!-- Détails pour les ÉTUDIANTS -->
            <template v-if="type === 'etudiants'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-id-card me-2 text-primary fs-5"></i><span class="fw-bold">Matricule :</span>
                {{ profileData.matricule || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.user?.email || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-school me-2 text-primary fs-5"></i><span class="fw-bold">Établissement :</span>
                {{ profileData.etablissement?.nom_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span>
                {{ profileData.filiere?.libfil || 'Non spécifiée' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span>
                {{ profileData.annee?.libannee || 'Non spécifiée' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2">
                <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
                <a v-if="profileData.CV" :href="getDownloadUrl(profileData.CV)" target="_blank" class="ms-1 btn btn-sm btn-outline-primary">Voir le CV</a>
                <span v-else class="ms-1">Non téléchargé</span>
              </p>
            </template>

            <!-- Détails pour les ENTREPRISES -->
            <template v-else-if="type === 'entreprises'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span>
                {{ profileData.domaine?.libdomaine || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.email_entreprise || 'Non spécifié' }}
              </p>
              <!--<p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-phone me-2 text-primary fs-5"></i><span class="fw-bold">Contact :</span>
                {{ profileData.contact || 'Non spécifié' }}
              </p>-->
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <a v-if="profileData.siteweb" :href="profileData.siteweb" target="_blank">{{ profileData.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                {{ profileData.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                {{ profileData.adresse || 'Non spécifié' }}
              </p>
            </template>

            <!-- Détails pour les ÉTABLISSEMENTS -->
            <template v-else-if="type === 'etablissements'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Mail :</span>
                {{ profileData.email_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-phone me-2 text-primary fs-5"></i><span class="fw-bold">Contact :</span>
                {{ profileData.contact || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <a v-if="profileData.siteweb" :href="profileData.siteweb" target="_blank">{{ profileData.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                {{ profileData.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                {{ profileData.adresse || 'Non spécifié' }}
              </p>
            </template>
            
          </div>
        </section>

        <!-- Sections additionnelles spécifiques à l'étudiant (Compétences, Projets, Parcours Académique) -->
        <section v-if="type === 'etudiants'" class="bg-white p-4 rounded shadow-sm mb-4">
          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Compétences</span>
          </h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.competences || 'L\'étudiant n\'a pas encore listé ses compétences.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Projets</span>
          </h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.projets || 'L\'étudiant n\'a pas encore décrit ses projets.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Parcours Académique</span>
          </h2>
          <div class="description-field-view pb-4 mb-4">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.parcours || 'L\'étudiant n\'a pas encore décrit son parcours académique.' }}</p>
          </div>
        </section>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from '../axios';
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

// Les props 'type' et 'id' sont automatiquement injectées grâce à `props: true` dans la route
const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['etudiants', 'entreprises', 'etablissements'].includes(value)
  },
  id: {
    type: [String, Number],
    required: true
  }
});

// Initialisation de profileData avec toutes les structures potentielles pour éviter les erreurs d'accès à des propriétés de 'undefined'
const profileData = ref({
  user: {
    name: null,
    email: null,
    photo: null,
    couverture: null,
    description: null,
    id: null, // L'ID de l'utilisateur principal est important pour le message
  },
  // Propriétés spécifiques aux étudiants
  matricule: null,
  competences: null,
  projets: null,
  parcours: null,
  CV: null,
  etablissement: { nom_etablissement: null },
  filiere: { libfil: null },
  filannee: { libannee: null },
  // Propriétés spécifiques aux entreprises/établissements
  nom_entreprise: null,
  nom_etablissement: null,
  email_entreprise: null,
  email_etablissement: null,
  //contact: null, // Commun à entreprise/établissement/etudiant
  siteweb: null, // Commun à entreprise/établissement
  adresse: null, // Commun à entreprise/établissement/etudiant
  domaine: { libdomaine: null }, // Spécifique à l'entreprise
  ville: { nom_ville: null }, // Commun à tous les détails de rôle
});

const loading = ref(true);
const error = ref(null);
const router = useRouter();

const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null; // Pour stocker l'instance du toast et éviter les duplications

// Propriétés calculées pour l'affichage dynamique des titres et noms
const profileTypeDisplay = computed(() => {
  switch (props.type) {
    case 'etudiants': return 'l\'étudiant';
    case 'entreprises': return 'l\'entreprise';
    case 'etablissements': return 'l\'établissement';
    default: return 'le profil';
  }
});

const profileName = computed(() => {
  if (!profileData.value) return 'Chargement...';

  // Logique pour le nom de l'entité
  if (props.type === 'etudiants') {
    // Si l'étudiant a un nom d'utilisateur, l'utiliser, sinon combiner prénom/nom du profil étudiant
    return profileData.value.user?.name || `${profileData.value.prenom || ''} ${profileData.value.nom || ''}`.trim();
  } else if (props.type === 'entreprises') {
    return profileData.value.nom_entreprise || profileData.value.user?.name || 'Nom non spécifié';
  } else if (props.type === 'etablissements') {
    return profileData.value.nom_etablissement || profileData.value.user?.name || 'Nom non spécifié';
  }
  return 'Nom non spécifié'; // Fallback
});

// Classe CSS dynamique pour le logo de profil (si besoin de styles différents par type)
const getLogoClass = computed(() => {
  switch (props.type) {
    case 'etudiants': return 'student-logo-view';
    case 'entreprises': return 'company-logo-view';
    case 'etablissements': return 'school-logo-view';
    default: return '';
  }
});

/**
 * Récupère les données du profil en fonction du type et de l'ID.
 */
const fetchProfile = async () => {
  loading.value = true;
  error.value = null;

  if (!props.id) {
    error.value = `ID de ${profileTypeDisplay.value.toLowerCase()} manquant dans l'URL.`;
    loading.value = false;
    return;
  }

  try {
    let endpoint = '';
    switch (props.type) {
      case 'etudiants':
        endpoint = `/api/etudiants/${props.id}`; // Assurez-vous que cette route existe et renvoie les relations user, etablissement, filiere, filannee
        break;
      case 'entreprises':
        endpoint = `/api/entreprises/${props.id}`; // Assurez-vous que cette route existe et renvoie les relations user, domaine, ville
        break;
      case 'etablissements':
        endpoint = `/api/etablissements/${props.id}`; // Assurez-vous que cette route existe et renvoie les relations user, ville
        break;
      default:
        error.value = "Type de profil inconnu.";
        loading.value = false;
        return;
    }
    
    const response = await axios.get(endpoint);
    // Assigner directement la réponse car le backend devrait renvoyer l'objet racine du rôle
    // et les informations 'user' seront imbriquées à l'intérieur sous la clé 'user'.
    profileData.value = response.data;

    // Vérification supplémentaire si l'objet user est manquant ou incomplet après le fetch
    if (!profileData.value.user || !profileData.value.user.id) {
        error.value = "Données utilisateur de base manquantes pour ce profil.";
        displayToast(error.value, 'danger');
    }
    
  } catch (err) {
    console.error(`Erreur lors de la récupération du profil ${props.type} :`, err.response ? err.response.data : err.message);
    error.value = `Impossible de charger le profil de ${profileTypeDisplay.value.toLowerCase()}. Veuillez réessayer plus tard.`;
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

/**
 * Construit l'URL complète pour l'image de profil.
 * @param {string} photoUrl - Le chemin relatif de l'image (ex: 'photos/avatar.jpg').
 * @returns {string} L'URL complète de l'image ou une image par défaut.
 */
const getProfilePhoto = (photoUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Assurez-vous que '/storage/' est ajouté car les chemins dans la DB sont relatifs à storage/app/public
  return photoUrl ? `${baseURL}/storage/${photoUrl}` : '/photo-default.jpeg';
};

/**
 * Construit l'URL complète pour l'image de couverture.
 * @param {string} coverUrl - Le chemin relatif de l'image (ex: 'couvertures/cover.jpg').
 * @returns {string} L'URL complète de l'image ou une image par défaut.
 */
const getCoverPhoto = (coverUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  return coverUrl ? `${baseURL}/storage/${coverUrl}` : '/cover-default.jpeg';
};

/**
 * Construit l'URL complète pour le téléchargement d'un fichier (ex: CV).
 * @param {string} filePath - Le chemin relatif du fichier (ex: 'cv/mon_cv.pdf').
 * @returns {string} L'URL complète du fichier pour le téléchargement.
 */
const getDownloadUrl = (filePath) => {
  const baseURL = import.meta.env.VITE_API_URL;
  return `${baseURL}/storage/${filePath}`;
};

/**
 * Retourne à la page précédente dans l'historique du navigateur.
 */
const goBack = () => {
  router.go(-1);
};

/**
 * Affiche une notification de type "toast".
 * @param {string} message - Le message à afficher.
 * @param {string} type - Le type de toast (ex: 'success', 'danger', 'warning', 'info').
 */
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    if (!liveToast) { // Crée l'instance de toast une seule fois
      liveToast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    }
    liveToast.show();
  }
};

/**
 * Fonction pour envoyer un message à l'utilisateur associé à ce profil.
 * Redirige vers une page de messagerie.
 */
const sendMessage = () => {
  const recipientUserId = profileData.value.user?.id;
  if (!recipientUserId) {
    displayToast("Impossible d'envoyer le message : ID de l'utilisateur destinataire manquant.", "danger");
    return;
  }
  
  // Ancien: router.push({ name: 'send-message', params: { userId: recipientUserId } });
  // NOUVEAU: Rediriger vers la page de messagerie du dashboard actuel, en passant l'ID.
  // Déterminez le nom de la route messagerie en fonction du dashboard actuel.
  let messagerieRouteName = '';
  if (router.currentRoute.value.path.startsWith('/entreprise-dashboard')) {
    messagerieRouteName = 'entreprise-messagerie';
  } else if (router.currentRoute.value.path.startsWith('/etablissement-dashboard')) {
    messagerieRouteName = 'etablissement-messagerie';
  } else if (router.currentRoute.value.path.startsWith('/etudiant-dashboard')) {
    messagerieRouteName = 'etudiant-messagerie';
  }

  if (messagerieRouteName) {
    router.push({ name: messagerieRouteName, params: { userId: recipientUserId } });
  } else {
    displayToast("Impossible de déterminer la route de messagerie.", "danger");
  }

  displayToast('Redirection vers la messagerie pour envoyer un message...', 'info');
};
// --- Watcher pour recharger le profil si 'type' ou 'id' changent ---
// C'est important si un utilisateur navigue de /etablissements/1/profile à /etudiants/2/profile
watch(() => [props.type, props.id], () => {
  fetchProfile();
}, { immediate: true }); // immediate: true exécute le watcher au montage initial

</script>

<style scoped>
/* Les styles CSS sont repris des précédents composants pour une cohérence visuelle */
/* Variables de base */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --success-green: #198754;
  --danger-red: #dc3545;
  --warning-orange: #ffc107;
}

.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
  min-height: calc(100vh - 60px);
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
  color: var(--dark-text);
}

/* Styles pour la section de couverture (lecture seule) */
.profile-cover-section-view {
  height: 250px;
  overflow: hidden;
  margin-bottom: -40px !important;
  border: 1px solid var(--border-color);
  background-color: #e9ecef;
}

.profile-cover-image-view {
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Styles pour la carte principale du profil (lecture seule) */
.profile-main-card-view {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  position: relative;
  padding-top: 60px !important;
  z-index: 1;
}

.profile-avatar-placeholder-view {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background-color: var(--light-gray-bg);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 4px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  margin-top: -110px;
}

/* Classes dynamiques pour le logo de profil */
.profile-avatar-placeholder-view .company-logo-view, /* Pour entreprise */
.profile-avatar-placeholder-view .school-logo-view, /* Pour établissement */
.profile-avatar-placeholder-view .student-logo-view { /* Pour étudiant - renommée de student-avatar-view */
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info-view h3 {
  margin-bottom: 0.25rem;
}

.description-field-view .form-control-static {
  background-color: var(--light-gray-bg);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  padding: 0.75rem 1rem;
  min-height: 80px;
  font-size: 0.95rem;
  color: var(--dark-text);
}

.profile-details-view p {
  font-size: 0.95rem;
}

/* Styles pour les sections "informations complémentaires" (Compétences, Projets, Parcours) */
.section-header {
  font-size: 1.25rem;
  color: var(--dark-text);
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--primary-blue);
}

/* S'assurer que les blocs sont bien séparés */
.profile-main-card-view > div:not(:last-child),
section.bg-white > div:not(:last-child) {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

/* Pas de bordure inférieure pour le dernier élément de la section principale, ni pour le dernier élément des nouvelles sections */
.profile-main-card-view > div:last-child,
section.bg-white > div:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

/* Styles pour les toasts */
.toast {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}

.toast.showing, .toast.show {
  opacity: 1;
}

.text-bg-success { background-color: var(--success-green) !important; color: white !important; }
.text-bg-danger { background-color: var(--danger-red) !important; color: white !important; }
.text-bg-warning { background-color: var(--warning-orange) !important; color: black !important; }
.text-bg-info { background-color: #0dcaf7 !important; color: black !important; }

/* Boutons */
 
.btn-secondary{
  background-color: #5a6268;
  border-color: #5a6268;
  color: white;
}
</style>
