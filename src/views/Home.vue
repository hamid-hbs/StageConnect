<template>
  <nav class="navbar shadow-lg fixed-top bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand text-primary fw-bold" href="#">
        <img src="/toge.png" alt="Logo" height="30"> StageConnect
      </a>
        
      <div class="row">   
        <ul class="nav justify-content-center">
          <li class="nav-item">
            <a class="nav-link" href="#accueil">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#communautes">Communautés</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#offres-stages">Offres de Stages</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#apropos">A propos</a>
          </li>
        </ul>
      </div>

      <div class="d-grid gap-2">
        <button
          @click="handleAuthClick"
          class="btn btn-outline-primary"
          type="button"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <img src="/google-icon.png" style="height: 20px; width: 20px; margin-right: 5px;"/>
          {{ isLoading ? 'Connexion...' : 'S\'authentifier' }}
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
               </div>
            </div>
          </div>

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
                </div>
            </div>
          </div>
        </div>
      </div>
    </section> 


    <section id="offres-stages" class="py-6 content-page">
      <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="liveToastOffers" class="toast" role="alert" aria-live="assertive" aria-atomic="true" :class="`text-bg-${toastTypeOffers}`">
          <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            {{ toastMessageOffers }}
          </div>
        </div>
      </div>

      <section class="offers-list-header bg-primary text-white p-3 rounded-top d-flex flex-column flex-md-row justify-content-between align-items-center mb-0">
        <h3 class="mb-2 mb-md-0 fs-5 text-white">Offres de Stages Disponibles</h3>
        <div class="d-flex align-items-center">
          <div class="input-group search-input-group me-md-3 mb-2 mb-md-0">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher par titre, description..." v-model="searchQuery" @input="applyFilters">
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" id="filterDomaineDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              {{ selectedDomaineName || 'Filtrer par domaine' }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDomaineDropdown">
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterDomaine(null, '')">Tous les domaines</a></li>
              <li v-for="domaine in domainesList" :key="domaine.id">
                <a class="dropdown-item" href="#" @click.prevent="setFilterDomaine(domaine.id, domaine.libdomaine)">
                  {{ domaine.libdomaine }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <div v-if="isLoadingOffers" class="d-flex justify-content-center align-items-center bg-white p-5 rounded-bottom shadow-sm">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement des offres...</span>
        </div>
        <p class="ms-3 text-muted">Chargement des offres de stage...</p>
      </div>

      <div v-else-if="errorMessageOffers" class="alert alert-danger text-center p-4 rounded-bottom shadow-sm">
        {{ errorMessageOffers }}
      </div>

      <div v-else-if="filteredOffers.length > 0" class="offers-container p-4 bg-white rounded-bottom shadow-sm">
        <div class="offers-carousel">
          <div v-for="offer in filteredOffers" :key="offer.id" class="card offer-card-carousel me-3">
            <div class="card-body">
              <h5 class="card-title text-primary">{{ offer.titre }}</h5>
              <h6 class="card-subtitle mb-2 text-muted">
                  <span class="fw-bold text-primary">{{ offer.entreprise.nom_entreprise }}</span> - {{ offer.domaine.libdomaine }}
              </h6>
              <p 
                class="card-text" 
                :class="{ 'text-truncate-3': !offer.showFullDescription }"
              >
                {{ offer.description }}
              </p>
              <button 
                v-if="offer.description.length > 150" 
                @click="toggleDescription(offer)" 
                class="btn btn-link p-0 text-primary mb-2 text-decoration-none"
              >
                {{ offer.showFullDescription ? 'Voir moins' : 'Voir plus' }}
              </button>
              <ul class="list-unstyled offer-details">
                <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> {{ offer.ville ? offer.ville.nom_ville : offer.adresse }}</li>
                <li><i class="fas fa-calendar-alt me-2 text-secondary"></i> {{ offer.duree_en_semaines }} semaines</li>
              </ul>
              <button v-if="!isAuthenticated" @click="showAuthRequiredToast" class="btn btn-outline-primary btn-sm">Postuler</button>
              <router-link v-else :to="{ name: 'etudiant-offre-details', params: { id: offer.id } }" class="btn btn-outline-primary btn-sm">Postuler</router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="alert alert-info text-center p-4 rounded-bottom shadow-sm">
        Aucune offre de stage trouvée pour le moment ou ne correspondant à vos critères de recherche.
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
    <div id="authToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
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

<script>
import { ref, onMounted, computed } from 'vue';
import { signInWithPopup } from 'firebase/auth';
import { auth, googleAuthProvider } from '../firebase';
import axios from '../axios'; 
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

export default {
  name: 'Home',
  setup() {
    const router = useRouter();
    const offers = ref([]);
    const domainesList = ref([]);
    const searchQuery = ref('');
    const selectedDomaineId = ref(null);
    const selectedDomaineName = ref('');
    const isLoadingOffers = ref(true);
    const errorMessageOffers = ref('');
    const toastMessageOffers = ref('');
    const toastTypeOffers = ref('success');

    const filteredOffers = computed(() => {
      let filtered = offers.value;

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(offer =>
          offer.titre.toLowerCase().includes(query) ||
          offer.description.toLowerCase().includes(query)
        );
      }

      if (selectedDomaineId.value) {
        filtered = filtered.filter(offer => offer.domaine_id === selectedDomaineId.value);
      }
      return filtered;
    });

    const displayToastOffers = (message, type = 'success') => {
      toastMessageOffers.value = message;
      toastTypeOffers.value = type;
      const toastEl = document.getElementById('liveToastOffers');
      if (toastEl && window.bootstrap && window.bootstrap.Toast) {
        const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
      }
    };

    const setFilterDomaine = (id, name) => {
      selectedDomaineId.value = id;
      selectedDomaineName.value = name;
    };

    const applyFilters = () => {
      // Cette fonction est là principalement pour les écouteurs @input
    };

    // Nouvelle fonction pour basculer l'affichage complet de la description
    const toggleDescription = (offer) => {
      offer.showFullDescription = !offer.showFullDescription;
    };

    onMounted(async () => {
      try {
        isLoadingOffers.value = true;
        errorMessageOffers.value = '';

        const offersResponse = await axios.get('/api/offres-off');
        // Initialiser showFullDescription pour chaque offre
        offers.value = offersResponse.data.map(offer => ({
          ...offer,
          showFullDescription: false // Par défaut, la description est tronquée
        }));

        const domainesResponse = await axios.get('/api/domaines');
        domainesList.value = domainesResponse.data;

      } catch (error) {
        console.error('Erreur lors du chargement des données des offres :', error);
        if (error.response && error.response.status === 403) {
          errorMessageOffers.value = 'Vous n\'êtes pas autorisé à voir ces offres ou votre profil est incomplet. Veuillez vous assurer d\'être connecté avec un rôle approprié.';
        } else {
          errorMessageOffers.value = 'Une erreur est survenue lors du chargement des offres de stage. Veuillez réessayer plus tard.';
        }
        displayToastOffers('Échec du chargement des offres.', 'danger');
      } finally {
        isLoadingOffers.value = false;
      }
    });

    return {
      router,
      offers,
      domainesList,
      searchQuery,
      selectedDomaineId,
      selectedDomaineName,
      isLoadingOffers,
      errorMessageOffers,
      toastMessageOffers,
      toastTypeOffers,
      filteredOffers,
      setFilterDomaine,
      applyFilters,
      displayToastOffers,
      toggleDescription // Exposer la fonction au template
    };
  },
  data() {
    return {
      isLoading: false,
      error: false,
      toastMessage: '',
    };
  },
  computed: {
    isAuthenticated() {
      return localStorage.getItem('authToken') !== null;
    }
  },
  methods: {
    async handleAuthClick() {
      this.isLoading = true;
      this.error = false;
      this.toastMessage = '';

      try {
        const result = await signInWithPopup(auth, googleAuthProvider);
        const user = result.user;
        const idToken = await user.getIdToken();
        console.log("Firebase ID Token:", idToken);

        let formData = new FormData();
        formData.append('id_token',idToken);
        
        axios.defaults.withCredentials=true;
        const response = await axios.post('/api/auth/google/firebase-callback', formData);
        const { token, user: userData, next_step } = response.data;

        if (token) {
          localStorage.setItem('authToken', token);
        }
        if (userData) {
          localStorage.setItem('currentUser', JSON.stringify(userData));
        }

        this.showToast("Authentification réussie ! Redirection...", false, 'authToast');

        setTimeout(() => {
          this.router.push(next_step);
        }, 1500);

      } catch (err) {
        console.error("Erreur d'authentification ou communication Laravel:", err);
        this.error = true;
        let displayMessage = "Une erreur est survenue lors de l'authentification.";

        if (err.code) {
          displayMessage = {
            'auth/popup-closed-by-user': 'Connexion annulée par l\'utilisateur.',
            'auth/popup-blocked': 'La fenêtre pop-up a été bloquée par votre navigateur.',
            'auth/cancelled-popup-request': 'La demande de pop-up a été annulée.',
            'auth/account-exists-with-different-credential': 'Un compte existe déjà avec cette adresse email mais avec un autre fournisseur d\'authentification.',
            'auth/network-request-failed': 'Problème de connexion réseau. Veuillez vérifier votre internet.',
            'auth/internal-error': 'Une erreur interne est survenue dans Firebase. Cela peut être dû à un problème réseau ou une configuration.',
          }[err.code] || displayMessage;
        }
        else if (axios.isAxiosError(err) && err.response && err.response.data && err.response.data.message) {
          displayMessage = err.response.data.message;
        } else if (err.message) {
            displayMessage = err.message;
        }

        this.showToast(displayMessage, true, 'authToast');

        if (auth.currentUser) {
            auth.signOut().catch(signOutErr => console.error("Erreur de déconnexion Firebase:", signOutErr));
        }

      } finally {
        this.isLoading = false;
      }
    },
    showToast(message, isError, toastId = 'liveToastOffers') { // Par défaut, cible liveToastOffers
      if (toastId === 'authToast') {
        this.toastMessage = message;
        this.error = isError;
      } else {
        this.toastMessageOffers = message;
        this.toastTypeOffers = isError ? 'danger' : 'success';
      }
      const toastEl = document.getElementById(toastId);
      if (toastEl) {
        const toast = new Toast(toastEl);
        toast.show();
      }
    },
    showAuthRequiredToast() {
      this.showToast("Veuillez vous authentifier pour postuler à cette offre.", true, 'liveToastOffers');
    }
  },
};
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

/* Styles spécifiques pour la section des offres */
.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
}

.offers-list-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.offers-list-header h3 {
  color: white;
}

.offers-list-header .search-input-group {
  max-width: 300px;
}

.offers-list-header .form-control-sm {
  border-radius: 0.25rem;
}

/* Conteneur pour le carrousel horizontal des offres restantes */
.offers-carousel {
  display: flex;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 15px;
  scrollbar-width: thin;
  scrollbar-color: var(--primary-blue) rgba(0,0,0,0.1);
}

/* Cache la barre de défilement pour Webkit (Chrome, Safari) */
.offers-carousel::-webkit-scrollbar {
  height: 8px;
}

.offers-carousel::-webkit-scrollbar-track {
  background: rgba(0,0,0,0.1);
  border-radius: 10px;
}

.offers-carousel::-webkit-scrollbar-thumb {
  background-color: var(--primary-blue);
  border-radius: 10px;
  border: 2px solid rgba(255,255,255,0.8);
}

.offer-card-carousel {
  flex: 0 0 300px;
  max-width: 300px;
  margin-right: 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  white-space: normal;
  min-height: 250px;
  display: flex;
  flex-direction: column;
}

.offer-card-carousel .card-body {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}


.offer-card:hover, .offer-card-carousel:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.offer-card .card-title, .offer-card-carousel .card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary-blue);
}

.offer-card .card-subtitle, .offer-card-carousel .card-subtitle {
  font-size: 0.95rem;
}

.offer-card .card-text, .offer-card-carousel .card-text {
  font-size: 0.9rem;
  color: var(--dark-text);
  margin-bottom: 0.5rem; /* Réduit l'espace avant le bouton Voir plus */
}

.offer-details {
  font-size: 0.85rem;
  color: var(--secondary-text);
  margin-top: 0.5rem; /* Ajoute un peu d'espace après le bouton Voir plus */
  margin-bottom: 1rem;
}

.offer-details li {
  margin-bottom: 0.4rem;
}

.offer-details i {
  color: var(--primary-blue);
  width: 1.2em;
}

.text-truncate-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 3.6em; /* Assure une hauteur minimale pour 3 lignes (approximativement 1.2em * 3) */
  -webkit-line-clamp: 3; /* Limite le texte à 3 lignes pour Webkit */
}

/* Responsive adjustments for offers section */
@media (max-width: 767.98px) {
  .offers-list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .offers-list-header .search-input-group,
  .offers-list-header {
    width: 100%;
    margin-right: 0 !important;
    margin-bottom: 0.5rem;
  }
  .offer-card-carousel {
    flex: 0 0 280px;
    max-width: 280px;
  }
}
</style>