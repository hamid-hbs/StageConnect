<template>
  <nav class="navbar shadow-lg fixed-top bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand text-primary fw-bold" href="#">
        <img src="/toge.png" alt="Logo" height="30"> StageConnect
      </a>

     <!-- <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="#accueil">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#communautes">Communautés</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#apropos">A propos</a>
          </li>
        </ul>
      </div> -->

      <div class="d-flex align-items-center gap-2">
        <router-link :to="dashboardPath" class="btn btn-outline-primary d-flex align-items-center">
          <img :src="displayedDashboardPhoto" class="user-logo me-2" alt="Votre Photo de Profil" />
          Vous
        </router-link>

        <button class="btn btn-outline-danger" @click.prevent="signOut" title="Déconnexion">
          <i class="fa-solid fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </nav>
      

  <main style="margin-top: 80px;">
    
    <section id="accueil" class="py-6 bg-white">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <h2 class="fw-bold mb-3">Une plateforme, deux communautés</h2>
            <p class="text-muted fs-5">
              Connecte étudiants et entreprises pour une meilleure expérience
            </p>
            <img
              src="/Task.gif"
              alt="Illustration plateforme"
              class="img-fluid mt-3"
              style="max-height: 200px;"
            />
          </div>

          <div class="col-md-6 text-center">
            <img
              src="/Job offers.gif"
              alt="Illustration plateforme"
              class="img-fluid"
              style="max-height: 600px;"
            />
          </div>
        </div>
      </div>
    </section>

    <section id="communautes" class="py-6">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold mb-4 text-center">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Nos Communautés</span>
      </h2>
    </div>
    
    <div class="row g-4 justify-content-center">
      <!-- Étudiants Card -->
      <div class="col-lg-5 col-md-6">
        <div class="card border-0 rounded-4 overflow-hidden shadow-lg h-100 hover-effect">
          <div class="card-header bg-primary text-white py-3">
            <h4 class="text-center mb-0"><i class="fas fa-user-graduate me-2"></i> Étudiants</h4>
          </div>
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <img src="/etudiants.jpg" class="rounded-3 img-fluid" alt="étudiants" style="height: 180px; width: 100%; object-fit: cover;">
            </div>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Trouvez des stages adaptés à votre profil</li>
              <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Gérez facilement vos candidatures</li>
              <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Suivez votre progression</li>
            </ul>
           <!-- <div class="text-center mt-auto">
              <router-link to="/etudiant-communaute" class="btn btn-primary rounded-pill px-4 py-2">
                Explorer <i class="fas fa-arrow-right ms-2"></i>
              </router-link>
            </div>-->
          </div>
        </div>
      </div>

      <!-- Entreprises Card -->
      <div class="col-lg-5 col-md-6">
        <div class="card border-0 rounded-4 overflow-hidden shadow-lg h-100 hover-effect">
          <div class="card-header bg-success text-white py-3">
            <h4 class="text-center mb-0"><i class="fas fa-building me-2"></i> Entreprises</h4>
          </div>
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <img src="/entreprises.jpg" class="rounded-3 img-fluid" alt="entreprises" style="height: 180px; width: 100%; object-fit: cover;">
            </div>
            <ul class="list-unstyled mb-4">
              <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Publiez vos offres de stage</li>
              <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Gérez les candidatures</li>
              <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Trouvez les talents de demain</li>
            </ul>
           <!-- <div class="text-center mt-auto">
              <router-link to="/entreprise-communaute" class="btn btn-success rounded-pill px-4 py-2">
                Explorer <i class="fas fa-arrow-right ms-2"></i>
              </router-link>
            </div>-->
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



    <section id="apropos" class="py-6 bg-white text-center">
      <div class="container text-center text-md-start">
        <h2 class="fw-bold mb-4 text-center">
          <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
            À propos
          </span>
        </h2>
      
        <div class="row">
          <div class="col-md-6 mb-4">
            <h5>Qui sommes-nous ❓</h5>
            <p>
              StageConnect est une plateforme web dédiée à la gestion complète des stages académiques. 
              Notre mission est de créer un lien solide entre le monde de la formation et celui de l'emploi 
              à travers un outil moderne, intuitif et collaboratif.
            </p>
          </div>

          <div class="col-md-6 mb-4">
            <h5>Notre mission ⏳</h5>
            <p>
              Faciliter la mise en relation entre étudiants et entreprises, 
              tout en assurant un suivi rigoureux et centralisé des stages.
            </p>
          </div>

          <div class="col-md-6 mb-4">
            <h5>Ce que nous proposons 📚</h5>
            <ul class="text-start">
              <li>Un espace pour les étudiants pour rechercher et postuler à des offres de stage</li>
              <li>Un tableau de bord pour les entreprises afin de publier des offres et suivre les candidats</li>
              
            </ul>
          </div>

          <div class="col-md-6 mb-4">
            <h5>Notre vision 🌍</h5>
            <p>
              Devenir la référence en matière de gestion de stages au Bénin, 
              en offrant une solution adaptée aux réalités du terrain tout en répondant aux standards nationaux.
            </p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer class="bg-primary text-white mt-6 pt-4 pb-3">
    <div class="container text-center text-md-start">
      <div class="row">
        
        <div class="col-md-4 mb-3">
          <h5 class="text-uppercase">
            <img src="/toge.png" alt="Logo" height="30"> StageConnect
          </h5>
          <p>Connecte étudiants et entreprises.</p>
        </div>

        <div class="col-md-4 mb-3">
          <ul class="list-unstyled">
            <li class="mb-2">
              <i class="fas fa-envelope me-2"></i> stageconnect@gmail.com
            </li>
            <li class="mb-2">
              <i class="fas fa-phone me-2"></i> +229 01 91 86 39 75
            </li>
            <li class="mb-2">
              <i class="fas fa-map-marker-alt me-2"></i> Cotonou, Bénin
            </li>
          </ul>
        </div>

        <div class="col-md-4 mb-3">
          <h6>Suivez-nous</h6>
          <div class="d-flex gap-3 justify-content-center justify-content-md-start">
            <a href="#" class="text-white">
              <i class="fab fa-facebook fa-lg"></i>
            </a>
            <a href="#" class="text-white">
              <i class="fab fa-instagram fa-lg"></i>
            </a>
            <a href="#" class="text-white">
              <i class="fab fa-github fa-lg"></i>
            </a>
          </div>
        </div>

      </div>
    </div>

    <div class="text-center mt-3 border-top pt-3">
      <small>© 2025 StageConnect - Tous droits réservés</small>
    </div>
  </footer>

  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto" :class="{'text-success': !error, 'text-danger': error}">
          {{ error ? 'Erreur' : 'Succès' }}
        </strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        {{ toastMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, computed, onMounted } from 'vue';
import axios from '../axios';
import { Toast } from 'bootstrap';

const router = useRouter();

// --- Variables d'état ---
const userPhoto = ref('');
// userRole n'est plus directement utilisé pour le lien, mais peut rester pour d'autres logiques si nécessaire
const userRole = ref('');
const dashboardPath = ref('/'); // Nouvelle ref pour stocker le chemin du tableau de bord (initialisé à la racine)
const defaultDashboardPhoto = '/photo-default.jpeg';
const toastMessage = ref('');
const error = ref(false);

// --- Propriétés calculées ---

const displayedDashboardPhoto = computed(() => {
  const url = userPhoto.value || defaultDashboardPhoto;
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});

// --- Fonctions ---

const showToast = (message, isError) => {
  toastMessage.value = message;
  error.value = isError;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const bsToast = new Toast(toastEl);
    bsToast.show();
  }
};

const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/user/getProfile');
    const user = response.data;

    if (user) {
      if (user.photo) {
        
        userPhoto.value = user.photo;
      } else {
        userPhoto.value = defaultDashboardPhoto;
      }

      // Mise à jour de userRole pour d'autres usages si besoin
      if (user.role) {
        userRole.value = user.role;
        // Mise à jour directe de dashboardPath en fonction du rôle
        switch (user.role) {
          case 'etudiant':
            dashboardPath.value = '/etudiant-dashboard';
            break;
          case 'entreprise':
            dashboardPath.value = '/entreprise-dashboard';
            break;
          case 'etablissement':
            dashboardPath.value = '/etablissement-dashboard';
            break;
          default:
            dashboardPath.value = '/'; // Chemin par défaut si le rôle n'est pas reconnu
        }
      } else {
        console.warn("Le rôle de l'utilisateur n'a pas été trouvé dans le profil.");
        userRole.value = '';
        dashboardPath.value = '/'; // Définit le chemin par défaut
      }
    } else {
      userPhoto.value = defaultDashboardPhoto;
      userRole.value = '';
      dashboardPath.value = '/'; // Définit le chemin par défaut
    }
  } catch (err) {
    console.error('Erreur lors du chargement du profil utilisateur :', err);
    userPhoto.value = defaultDashboardPhoto;
    userRole.value = '';
    dashboardPath.value = '/'; // Définit le chemin par défaut en cas d'erreur
  }
};

const signOut = () => {
  console.log('Déconnexion en cours...');
  localStorage.removeItem('authToken');
  sessionStorage.removeItem('userData');
  router.push('/');
  showToast('Vous avez été déconnecté avec succès.', false);
};

// --- Cycle de vie du composant ---
onMounted(() => {
  fetchUserProfile();
});
</script>

<style scoped>
/* Conserver les styles existants */

.py-6 {
  padding-top: 4rem !important;
  padding-bottom: 4rem !important;
}

.mt-6 {
  margin-top: 4rem !important;
}

/* Effet de survol personnalisé */
.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.nav-link {
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: #0d6efd;
  transition: width 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}

/* Nouveau style pour la photo de l'utilisateur dans la nav bar */
.user-logo {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(0, 0, 0, 0.1);
}


/* Assurer que les images se chargent correctement */
img {
  max-width: 100%;
  height: auto;
}

/* Styles pour les liens */
a {
  text-decoration: none;
}

/* Smooth scroll pour les ancres */
html {
  scroll-behavior: smooth;
}

/* Styles spécifiques pour le bouton d'authentification */
.btn-outline-primary {
  display: flex;
  align-items: center;
  justify-content: center;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Styles pour le Toast de Bootstrap */
.toast {
  min-width: 250px;
}
.toast-header strong {
  font-weight: bold;
}
.toast-header .text-success {
  color: #198754 !important;
}
.toast-header .text-danger {
  color: #dc3545 !important;
}

/* Styles spécifiques pour la section Communautés */
.hover-effect {
  transition: all 0.3s ease;
  transform: translateY(0);
}

.hover-effect:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
  border-radius: 0 !important;
}

.divider {
  opacity: 0.8;
}

.list-unstyled li {
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.list-unstyled li:last-child {
  border-bottom: none;
}

/* Animation douce au survol */
.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .display-5 {
    font-size: 2rem;
  }
  
  .lead {
    font-size: 1rem;
  }
}
</style>