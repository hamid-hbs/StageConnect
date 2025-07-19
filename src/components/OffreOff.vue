<template>
  <nav class="navbar shadow-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand text-primary fw-bold" href="#">
        <img src="/toge.png" alt="Logo" height="30"> StageConnect
      </a>
    </div>
  </nav>

  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Détails de l'Offre</h1>
      </span>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
    </div>

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

    <section class="offer-details-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="text-center text-secondary py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3 mb-0">Chargement des détails de l'offre...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>

      <div v-else-if="offreDetails">
        <div class="row">
          <div class="col-lg-8 col-md-12">
            <div class="d-flex justify-content-between align-items-baseline mb-3 offer-title-container">
              <h2 class="fs-3 text-dark offer-title">{{ offreDetails.titre }}</h2>
              <span :class="['status-badge badge fs-6', getStatusBadgeClass(offreDetails.statut, offreDetails.date_expiration)]">
                {{ getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) }}
              </span>
            </div>

            <div class="card mb-4 p-3 shadow-sm">
              <h4 class="fs-5 mb-3 text-info"><i class="fas fa-building me-2"></i>Entreprise</h4>
              <p class="mb-0 text-primary text-decoration-none fw-bold">
                  {{ offreDetails.entreprise?.nom_entreprise || 'Entreprise inconnue' }}
             </p>
            </div>

            <div class="card mb-4 p-3 shadow-sm">
              <h4 class="fs-5 mb-3 text-info"><i class="fas fa-industry me-2"></i>Domaine</h4>
              <p class="mb-0">{{ offreDetails.domaine?.libdomaine || 'Non spécifié' }}</p>
            </div>

            <div class="card mb-4 p-3 shadow-sm">
              <h4 class="fs-5 mb-3 text-info"><i class="fas fa-info-circle me-2"></i>Informations générales</h4>
              <div class="row">
                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                      <i class="fas fa-map-marker-alt me-2 text-muted"></i><strong>Adresse :</strong> {{ offreDetails.adresse }}
                    </li>
                    <li class="list-group-item">
                      <i class="fas fa-calendar-alt me-2 text-muted"></i><strong>Date de début du stage :</strong> {{ formatDate(offreDetails.date_debut) }}
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                      <i class="fas fa-calendar-times me-2 text-muted"></i><strong>Date d'expiration de l'offre :</strong> {{ formatDate(offreDetails.date_expiration) }}
                    </li>
                    <li class="list-group-item">
                      <i class="fas fa-clock me-2 text-muted"></i><strong>Durée :</strong> {{ offreDetails.duree_en_semaines ? offreDetails.duree_en_semaines + ' semaines' : 'Non spécifié' }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card mb-4 p-3 shadow-sm">
              <h4 class="fs-5 mb-3 text-info"><i class="fas fa-align-left me-2"></i>Description de l'offre</h4>
              <div class="description-content p-3 border rounded bg-light">
                <p class="mb-0" style="white-space: pre-wrap;">{{ offreDetails.description }}</p>
              </div>
            </div>

          </div>

          <div class="col-lg-4 col-md-12 mt-md-4 mt-lg-0">
            <div class="card p-3 shadow-sm">
              <h4 class="fs-5 mb-3 text-info"><i class="fas fa-chart-bar me-2"></i>Statistiques</h4>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex align-items-center">
                  <i class="fas fa-users me-2 text-muted fs-5"></i>
                  <strong class="me-auto">Candidatures :</strong>
                  <span class="badge bg-primary-subtle text-primary fs-6">{{ nombreCandidatures }}</span>
                </li>
              </ul>
            </div>

            <div class="text-center mt-5">
              <button class="btn btn-primary btn-lg" @click="showAuthRequiredToast">
                <i class="fas fa-paper-plane me-2"></i>
                Postuler
              </button>
            </div>

          </div>
        </div>
      </div>
      <div v-else class="text-center text-muted py-5">
        <p class="fs-5">Aucune offre trouvée pour l'ID spécifié.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';
import { Toast } from 'bootstrap';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

// --- Variables Réactives ---
const offreDetails = ref(null);
const nombreCandidatures = ref(0);
const loading = ref(true);
const error = ref(null);

// Variables pour les toasts de notification spécifiques aux offres
const toastMessageOffers = ref('');
const toastTypeOffers = ref('success');

// Récupération des instances de route et de router de Vue Router
const route = useRoute();
const router = useRouter();



// --- Fonctions de Récupération des Données ---
const fetchOfferDetails = async (offerId) => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/offres-off/${offerId}`);
    offreDetails.value = response.data.offre;
    nombreCandidatures.value = response.data.nombre_candidatures;
  } catch (err) {
    console.error('Erreur lors de la récupération des détails de l\'offre hors ligne :', err.response ? err.response.data : err.message);
    if (err.response) {
      if (err.response.status === 404) {
        error.value = "L'offre demandée n'existe pas ou n'est pas disponible hors ligne.";
      } else {
        error.value = 'Impossible de charger les détails de l\'offre. Veuillez réessayer plus tard.';
      }
    } else {
      error.value = 'Erreur réseau ou serveur inaccessible. Veuillez vérifier votre connexion.';
    }
    offreDetails.value = null;
  } finally {
    loading.value = false;
  }
};

// --- Fonctions Utilitaires d'Affichage et de Notification ---

// Fonction générique pour afficher un toast
const showToast = (message, isError, toastId = 'liveToastOffers') => {
  // Cette partie n'est pas nécessaire en `setup` car `this` n'est pas utilisé directement
  // Vous devez cibler les refs spécifiques.
  if (toastId === 'liveToastOffers') { // Cible le toast des offres
    toastMessageOffers.value = message;
    toastTypeOffers.value = isError ? 'danger' : 'success';
  }
  // Si vous aviez un toast 'authToast' séparé, vous le géreriez ici:
  // else if (toastId === 'authToast') {
  //   toastMessageAuth.value = message; // Supposons que vous ayez toastMessageAuth et toastTypeAuth
  //   toastTypeAuth.value = isError ? 'danger' : 'success';
  // }

  const toastEl = document.getElementById(toastId);
  if (toastEl) {
    const toast = new Toast(toastEl);
    toast.show();
  }
};

// Fonction spécifique pour afficher le toast de connexion requise
const showAuthRequiredToast = () => {
  showToast("Veuillez vous authentifier pour postuler à cette offre.", true, 'liveToastOffers');
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const cleanDateString = dateString.split('T')[0];
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  try {
    return new Date(cleanDateString).toLocaleDateString('fr-FR', options);
  } catch (e) {
    console.error('Erreur de formatage de date:', dateString, e);
    return dateString;
  }
};

const isExpired = (expirationDate) => {
  if (!expirationDate) return true;
  const cleanDateString = expirationDate.split('T')[0];
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return new Date(cleanDateString) < today;
  } catch (e) {
    console.error('Erreur lors de la vérification de l\'expiration de la date:', expirationDate, e);
    return true;
  }
};

const getDisplayStatus = (statut, expirationDate) => {
  if (statut === 'expiree' || isExpired(expirationDate)) {
    return 'Expirée';
  }
  return 'Disponible';
};

const getStatusBadgeClass = (statut, expirationDate) => {
  if (statut === 'expiree' || isExpired(expirationDate)) {
    return 'bg-danger text-white';
  }
  return 'bg-success text-white';
};

const goBack = () => {
  router.go(-1);
};

// --- Hooks de Cycle de Vie ---
onMounted(() => {
  if (props.id) {
    fetchOfferDetails(props.id);
  } else {
    error.value = "Aucun ID d'offre n'a été fourni dans l'URL pour afficher les détails.";
    loading.value = false;
  }
});

watch(() => props.id, (newId) => {
  if (newId) {
    fetchOfferDetails(newId);
  } else {
    error.value = "L'ID de l'offre a été retiré de l'URL.";
    loading.value = false;
  }
});
</script>

<style scoped>
/* Variables de base */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --info-blue: #0dcaf0;
  --danger-color: #dc3545;
}

.content-page {
  padding: 1.5rem;
  padding-top: 70px; /* Ajouté pour décaler le contenu sous la navbar fixe */
  background-color: var(--light-gray-bg);
  min-height: 100vh;
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
  color: var(--dark-text);
}

.offer-details-section {
  background-color: transparent !important;
  padding: 0 !important;
}

/* Nouvel ajout pour le conteneur du titre de l'offre */
.offer-title-container {
  background-color: rgba(13, 110, 253, 0.1);
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Ajustement pour le titre de l'offre à l'intérieur de son conteneur */
.offer-title {
  color: var(--dark-text) !important;
  margin-bottom: 0;
  font-weight: 600;
  flex-grow: 1;
}

.status-badge {
  padding: 0.5em 0.9em;
  font-weight: 600;
  border-radius: 0.375rem;
  white-space: nowrap;
}

.card {
    border: 1px solid var(--border-color);
}

.list-group-item {
  border-color: #e9ecef;
  padding: 0.75rem 0;
  display: flex;
  align-items: center;
}
.list-group-item:last-child {
  border-bottom: none;
}
.list-group-item i {
  min-width: 25px;
}

.text-info {
  color: var(--info-blue) !important;
}

.description-content {
  background-color: #e9ecef;
  border-color: #ced4da;
}

.description-content p {
  line-height: 1.6;
}

.bg-success-subtle { background-color: #d1e7dd !important; }
.text-success { color: #198754 !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
.text-danger { color: #dc3545 !important; }
.bg-primary-subtle { background-color: #cfe2ff !important; }
.text-primary { color: #0d6efd !important; }

.btn-primary {
  transition: all 0.3s ease;
  padding: 0.75rem 1.5rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 0.5rem;
}
.btn-primary:hover {
  background-color: #0056b3;
  border-color: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.btn-primary:disabled {
  background-color: #6c757d;
  border-color: #6c757d;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.spinner-border {
  width: 1rem;
  height: 1rem;
}

/* Styles pour les toasts */
.toast {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}
.toast.showing, .toast.show {
  opacity: 1;
}
.text-bg-success { background-color: #198754 !important; color: white !important; }
.text-bg-danger { background-color: #dc3545 !important; color: white !important; }
.text-bg-info { background-color: #0dcaf0 !important; color: white !important; }
</style>