<template>
  <div>
    <nav class="navbar">
      <div class="navbar-brand">
        <a class="navbar-brand text-primary fw-bold" href="#">
          <img src="/toge.png" alt="StageConnect Logo" />
          StageConnect
        </a>
      </div>
      
      <div class="navbar-links">
        <router-link to="/accueil-user" class="nav-link">
          <i class="fas fa-home icon-spacing-navbar"></i> Accueil
        </router-link>
        <router-link to="/etudiant-dashboard" class="nav-link">
          <img :src="displayedDashboardPhoto" class="user-logo"/> Vous
        </router-link>
        
          <button class="btn btn-outline-danger" @click.prevent="confirmSignOut" title="Déconnexion">
          <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        
      </div>
    </nav>

    <aside class="sidebar">
      <ul class="sidebar-menu">
        <router-link to="/etudiant-profile" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-user-graduate icon-spacing-sidebar"></i>Etudiant</a>
            </li>
          </router-link>
        <router-link to="/etudiant-candidature" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-briefcase icon-spacing-sidebar"></i>Mes candidatures</a>
            </li>
          </router-link>
          <router-link to="/etudiant-recherchestage" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-search icon-spacing-sidebar"></i>Recherche de stage</a>
            </li>
          </router-link>
          <router-link :to="{ name: 'etudiant-messagerie' }" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-comments icon-spacing-sidebar"></i>Messagerie</a>
            </li>
          </router-link>
          <router-link to="/etudiant-notification" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }" class="notification-item-sidebar">
              <a :href="href" @click="navigate">
                <i class="fas fa-bell icon-spacing-sidebar"></i>Notifications
                <span v-if="unreadNotificationsCount > 0" class="badge bg-danger rounded-pill notification-badge">
                  {{ unreadNotificationsCount }}
                </span>
              </a>
            </li>
          </router-link>
        </ul>
    </aside>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, computed, onMounted, onUnmounted, provide } from 'vue';
import axios from '../axios'; 

const router = useRouter();

// --- Variables Réactives ---
const photo = ref('');
const isLoadingDashboard = ref(true);
const unreadNotificationsCount = ref(0); 
let refreshIntervalId = null;

// --- Propriété Calculée pour la Photo (avec cache busting) ---
const displayedDashboardPhoto = computed(() => {
  const url = photo.value;
  const defaultPhoto = '/images/default-user.png'; // <--- VÉRIFIEZ OU REMPLACEZ VOTRE CHEMIN PAR DÉFAUT
  const finalUrl = url || defaultPhoto;
  return finalUrl.includes('?') ? finalUrl : `${finalUrl}?t=${Date.now()}`;
});

// --- Fonction pour demander confirmation avant la déconnexion ---
const confirmSignOut = () => {
  if (window.confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    signOut();
  }
};

// --- Fonction pour gérer la déconnexion (appelée après confirmation) ---
const signOut = () => {
  console.log('Déconnexion en cours...');
  localStorage.removeItem('authToken');
  sessionStorage.removeItem('userData');
  router.push('/');
};

// --- Fonction pour récupérer le nombre de notifications non lues ---
const fetchUnreadNotificationsCount = async () => {
  try {
    const response = await axios.get('/api/notifications/unread/count');
    unreadNotificationsCount.value = response.data.count;
    console.log('Notifications non lues mises à jour (Etudiant):', unreadNotificationsCount.value);
  } catch (error) {
    console.error('Erreur lors du chargement du nombre de notifications non lues (Etudiant):', error.response ? error.response.data : error.message);
    unreadNotificationsCount.value = 0;
  }
};

// --- Fonction pour récupérer les données du profil (y compris la photo) ---
const fetchProfileData = async () => {
    try {
        const userResponse = await axios.get('/api/user/getProfile');
        const user = userResponse.data;
        if (user) {
            photo.value = user.photo;
            console.log('Photo de profil mise à jour (Etudiant):', photo.value);
        }
    } catch (error) {
        console.error('Erreur lors du chargement du profil utilisateur (Etudiant):', error.response ? error.response.data : error.message);
        photo.value = ''; // Réinitialiser la photo en cas d'erreur
    }
};


// --- Cycle de vie du composant ---
onMounted(async () => {
  try {
    isLoadingDashboard.value = true;
      
    // Appel initial pour la photo et les notifications
    await fetchProfileData();
    await fetchUnreadNotificationsCount();

  } catch (error) {
    console.error('Erreur lors du chargement initial (Etudiant):', error);
  } finally {
    isLoadingDashboard.value = false;
  }

  // Mettre en place l'intervalle de rafraîchissement (3 secondes = 3000 ms)
  refreshIntervalId = setInterval(async () => {
    await fetchProfileData(); // Pour la photo
    await fetchUnreadNotificationsCount(); // Pour les notifications
  }, 3000); // 3000 ms = 3 secondes
});

// --- Nettoyage de l'intervalle au démontage du composant ---
onUnmounted(() => {
  if (refreshIntervalId) {
    clearInterval(refreshIntervalId);
    console.log('Intervalle de rafraîchissement nettoyé (Etudiant).');
  }
});

// Exposer la fonction pour les composants enfants (notamment la page Notifications)
provide('refreshUnreadNotifications', fetchUnreadNotificationsCount);

</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Ajout d'un espacement pour les icônes de la sidebar (si elle est incluse dans le même fichier) */
.icon-spacing {
  margin-right: 10px; /* Ajoute un espace de 10px à droite de l'icône */
  width: 20px; /* Donne une largeur fixe pour aider à l'alignement des textes */
  text-align: center; /* Centre l'icône dans sa largeur fixe */
}

/* Style pour les icônes dans la navbar */
.icon-spacing-navbar {
  margin-right: 5px; /* Un espacement un peu plus petit pour la navbar */
}

.user-logo{
  width: 30px; /* Taille légèrement augmentée pour le logo de profil */
  height: 30px;
  border-radius: 50%;
  background-color: var(--light-gray-bg);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 4px;
  margin-right: 5px;
  object-fit: cover; /* Assure que l'image couvre bien la zone sans être déformée */
}

/* Navbar fixe */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 70px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  display: flex;
  align-items: center;
  text-decoration: none;
}

.navbar-brand img {
  height: 40px;
  margin-right: 10px;
}

/* Navigation links horizontaux */
.navbar-links {
  display: flex;
  gap: 20px;
  align-items: center;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  color: #333;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-link:hover {
  background-color: #f0f0f0;
  color: #007bff;
}

.nav-link.router-link-active {
  background-color: #007bff;
  color: white;
}

/* Ajustement pour le bouton de déconnexion */
.btn-outline-danger {
    color: var(--bs-danger);
    border-color: var(--bs-danger);
}
.btn-outline-danger:hover {
    color: #fff;
    background-color: var(--bs-danger);
    border-color: var(--bs-danger);
}

/* Sidebar fixe */
.sidebar {
  position: fixed;
  left: 0;
  top: 70px;
  width: 250px;
  height: calc(100vh - 70px);
  background-color: #ffffff;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
  z-index: 999;
  overflow-y: auto;
  padding: 20px;
}

.sidebar-menu {
  list-style: none;
}

.sidebar-menu li {
  margin-bottom: 10px;
}

.sidebar-menu a {
  display: block;
  padding: 12px 15px;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.sidebar-menu a:hover {
  background-color: #f0f0f0;
}

/* Updated active state for the sidebar link */
.sidebar-menu li.active-button a {
  background-color: #007bff; /* A distinct background color */
  color: white; /* White text for contrast */
  font-weight: bold; /* Make the text bold */
}

.icon-spacing-sidebar {
  margin-right: 10px; /* Ajoute un espace de 10px à droite de l'icône */
  /* Les propriétés suivantes peuvent aider à l'alignement, selon les icônes */
  width: 20px; /* Donne une largeur fixe pour aider à l'alignement des textes */
  text-align: center; /* Centre l'icône dans sa largeur fixe */
}

/* Styles spécifiques pour le badge de notification */
.notification-item-sidebar {
  position: relative; /* Nécessaire pour positionner le badge */
}

.notification-badge {
  position: absolute;
  top: 50%; /* Positionne le centre vertical du badge au milieu du lien */
  right: 10px; /* Distance du bord droit de l'élément parent (le lien 'a') */
  transform: translateY(-50%); /* Ajuste le badge pour qu'il soit réellement centré verticalement */
  font-size: 0.75em; /* Taille de police plus petite pour le badge */
  padding: 0.2em 0.5em; /* Remplissage du badge */
  line-height: 1; /* Assure que la hauteur de ligne n'ajoute pas d'espace indésirable */
  border-radius: 999px; /* Rend le badge parfaitement rond */
  background-color: #dc3545; /* Couleur rouge de Bootstrap pour les badges d'alerte */
  color: white;
  min-width: 20px; /* Largeur minimale pour les chiffres simples */
  text-align: center; /* Centre le texte dans le badge */
}

/* Ajustement pour le padding du lien afin de ne pas masquer le badge */
.sidebar-menu li.notification-item-sidebar a {
  padding-right: 40px; /* Augmente le padding à droite pour laisser de la place au badge */
}


/* Zone de contenu principal */
.main-content {
  margin-left: 250px;
  margin-top: 70px;
  padding: 0; /* Suppression du padding fixe */
  min-height: calc(100vh - 70px);
  background-color: #f8f9fa;
  overflow-x: auto; /* Défilement horizontal si nécessaire */
  overflow-y: auto; /* Défilement vertical si nécessaire */
}

/* Container flexible pour le contenu des routes */
.main-content > * {
  padding: 20px; /* Padding par défaut pour le contenu */
  width: 100%;
  box-sizing: border-box;
}

/* Classes utilitaires pour différents types de contenu */
.content-fullwidth {
  padding: 0 !important;
  width: 100% !important;
}

.content-compact {
  padding: 10px !important;
}

.content-spacious {
  padding: 40px !important;
}

.content-no-bg {
  background: none !important;
}

.content-centered {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 70px - 40px); /* Centrage vertical */
}

/* Responsive */
@media (max-width: 768px) {
  .navbar-links {
    gap: 10px;
  }
  
  .nav-link {
    padding: 6px 12px;
    font-size: 14px;
  }
  
  .sidebar {
    width: 200px;
  }
  
  .main-content {
    margin-left: 200px;
  }
}

@media (max-width: 576px) {
  .navbar {
    padding: 0 10px;
  }
  
  .navbar-brand {
    font-size: 1.2rem;
  }
  
  .navbar-brand img {
    height: 30px;
  }
  
  .navbar-links {
    gap: 5px;
  }
  
  .nav-link {
    padding: 4px 8px;
    font-size: 12px;
  }
  
  .icon-spacing-navbar {
    margin-right: 3px;
  }
}
</style>