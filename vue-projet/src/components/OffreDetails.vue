<template>
  <div class="content-page">
    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" :class="`text-bg-${toastType}`">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>

    <!-- Header with Title and Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="page-main-title fs-4 text-dark mb-0">Détails de l'Offre</h1>
        <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
      </div>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
    </div>

    <!-- Offer Details Section -->
    <section class="offer-details-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="ms-3">Chargement des détails de l'offre...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else-if="offreDetails" class="row">
        <div class="col-lg-8 col-md-12">
          <!-- Offer Title and Status -->
          <div class="offer-title-container mb-4 p-3 bg-light rounded shadow-sm d-flex justify-content-between align-items-center">
            <h2 class="fs-4 text-dark mb-0">{{ offreDetails.titre }}</h2>
            <span :class="['status-badge badge fs-6', getStatusBadgeClass(offreDetails.statut, offreDetails.date_expiration)]">
              {{ getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) }}
            </span>
          </div>

          <!-- Company Card -->
          <div class="card mb-4 p-4 shadow-sm">
            <h4 class="section-header fs-5 mb-3 text-dark">
              <i class="fas fa-building me-2 text-primary"></i>
              <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Entreprise</span>
            </h4>
            <p class="mb-0">
              <router-link
                v-if="offreDetails.entreprise?.id && dynamicGenericProfileRouteName"
                :to="{
                  name: dynamicGenericProfileRouteName,
                  params: { type: 'entreprises', id: offreDetails.entreprise.id }
                }"
                class="text-primary text-decoration-none fw-bold"
              >
                {{ offreDetails.entreprise?.nom_entreprise || 'Entreprise inconnue' }}
              </router-link>
              <span v-else>{{ offreDetails.entreprise?.nom_entreprise || 'Entreprise inconnue' }}</span>
            </p>
          </div>

          <!-- Domain Card -->
          <div class="card mb-4 p-4 shadow-sm">
            <h4 class="section-header fs-5 mb-3 text-dark">
              <i class="fas fa-industry me-2 text-primary"></i>
              <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Domaine</span>
            </h4>
            <p class="mb-0">{{ offreDetails.domaine?.libdomaine || 'Non spécifié' }}</p>
          </div>

          <!-- General Information Card -->
          <div class="card mb-4 p-4 shadow-sm">
            <h4 class="section-header fs-5 mb-3 text-dark">
              <i class="fas fa-info-circle me-2 text-primary"></i>
              <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Informations générales</span>
            </h4>
            <div class="row">
              <div class="col-md-6">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i>
                    <span class="fw-bold">Adresse :</span>
                    <span class="ms-2">{{ offreDetails.adresse || 'Non spécifié' }}</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-calendar-alt me-2 text-primary fs-5"></i>
                    <span class="fw-bold">Date de début :</span>
                    <span class="ms-2">{{ formatDate(offreDetails.date_debut) }}</span>
                  </li>
                </ul>
              </div>
              <div class="col-md-6">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-calendar-times me-2 text-primary fs-5"></i>
                    <span class="fw-bold">Date d'expiration :</span>
                    <span class="ms-2">{{ formatDate(offreDetails.date_expiration) }}</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center">
                    <i class="fas fa-clock me-2 text-primary fs-5"></i>
                    <span class="fw-bold">Durée :</span>
                    <span class="ms-2">{{ offreDetails.duree_en_semaines ? offreDetails.duree_en_semaines + ' semaines' : 'Non spécifié' }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Description Card -->
          <div class="card mb-4 p-4 shadow-sm">
            <h4 class="section-header fs-5 mb-3 text-dark">
              <i class="fas fa-align-left me-2 text-primary"></i>
              <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Description de l'offre</span>
               </h4>
            <div class="description-content p-3 border rounded bg-light">
              <p class="mb-0" style="white-space: pre-wrap;">{{ offreDetails.description || 'Aucune description disponible.' }}</p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-12 mt-md-4 mt-lg-0">
          <!-- Statistics Card -->
          <div class="card p-4 shadow-sm mb-4">
            <h4 class="section-header fs-5 mb-3 text-dark inline-block relative">
  <i class="fas fa-chart-bar me-2 text-primary"></i>
  <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Statistiques</span>
</h4>

            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-users me-2 text-primary fs-5"></i>
                <span class="fw-bold me-auto">Candidatures :</span>
                <span class="badge bg-primary-subtle text-primary fs-6">{{ nombreCandidatures }}</span>
              </li>
            </ul>
          </div>

          <!-- Apply Button -->
          <div class="text-center mt-5">
            <button
              v-if="userIsStudent && getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) === 'Disponible'"
              @click="handleApply"
              :disabled="applying || hasApplied"
              class="btn btn-primary btn-lg"
              data-bs-toggle="modal"
              data-bs-target="#motivationLetterModal"
            >
              <span v-if="applying" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-paper-plane me-2"></i>
              {{ applying ? 'Envoi de la candidature...' : (hasApplied ? 'Déjà postulé' : 'Postuler') }}
            </button>
            <button
              v-else-if="userIsStudent && getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) === 'Expirée'"
              class="btn btn-secondary btn-lg"
              disabled
            >
              Offre expirée
            </button>
            <button
              v-else-if="!userIsStudent"
              class="btn btn-secondary btn-lg"
              disabled
            >
              Seuls les étudiants peuvent postuler
            </button>
            <button
              v-else
              class="btn btn-secondary btn-lg"
              disabled
            >
              Offre non disponible pour postuler
            </button>
          </div>
        </div>
      </div>
      <div v-else class="text-center text-muted py-5">
        <p class="fs-5">Aucune offre trouvée pour l'ID spécifié.</p>
      </div>
    </section>

    <!-- Motivation Letter Modal -->
    <div class="modal fade" id="motivationLetterModal" tabindex="-1" aria-labelledby="motivationLetterModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="motivationLetterModalLabel"><i class="fas fa-edit me-2"></i>Lettre de Motivation</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="motivationTextArea" class="form-label fw-bold text-dark">Rédigez votre lettre de motivation pour cette offre :</label>
              <textarea
                class="form-control"
                id="motivationTextArea"
                rows="15"
                v-model="lettreMotivation"
                :class="{ 'is-invalid': motivationLetterError }"
                placeholder="Expliquez pourquoi vous êtes le candidat idéal pour cette offre..."
              ></textarea>
              <div class="invalid-feedback" v-if="motivationLetterError">
                {{ motivationLetterError }}
              </div>
              <small class="form-text text-muted">Minimum 50 caractères.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
            <button
              type="button"
              class="btn btn-primary"
              @click="confirmApply"
              :disabled="applying || !lettreMotivation || lettreMotivation.length < 50"
            >
              <span v-if="applying" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Envoyer la Candidature
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';
import { Modal, Toast } from 'bootstrap';

// Props
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

// Reactive Variables
const offreDetails = ref(null);
const nombreCandidatures = ref(0);
const loading = ref(true);
const error = ref(null);
const applying = ref(false);
const hasApplied = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
const pendingToast = ref(null);
const userRole = ref(null);
const lettreMotivation = ref('');
const motivationLetterError = ref('');
let motivationModalInstance = null;

// Router Instances
const route = useRoute();
const router = useRouter();

// Computed Properties
const userIsStudent = computed(() => {
  return userRole.value === 'etudiant';
});

const dynamicGenericProfileRouteName = computed(() => {
  if (route.path.startsWith('/entreprise-dashboard')) {
    return 'entreprise-generic-profile-view';
  } else if (route.path.startsWith('/etablissement-dashboard')) {
    return 'etablissement-generic-profile-view';
  } else if (route.path.startsWith('/etudiant-dashboard')) {
    return 'etudiant-generic-profile-view';
  }
  return '';
});

// Data Fetching
const fetchOfferDetails = async (offerId) => {
  loading.value = true;
  error.value = null;
  hasApplied.value = false;

  try {
    const userResponse = await axios.get('/api/user/getProfile');
    userRole.value = userResponse.data.role;

    const response = await axios.get(`/api/offres/${offerId}`);
    offreDetails.value = response.data.offre;
    nombreCandidatures.value = response.data.nombre_candidatures;
    hasApplied.value = response.data.has_applied || false;
  } catch (err) {
    console.error('Erreur lors de la récupération des détails de l\'offre ou du profil utilisateur :', err.response ? err.response.data : err.message);
    if (err.response) {
      if (err.response.status === 404) {
        error.value = "L'offre demandée n'existe pas.";
      } else if (err.response.status === 403) {
        error.value = "Vous n'êtes pas autorisé à voir les détails de cette offre. " + (err.response.data.message || "");
      } else if (err.response.status === 401) {
        error.value = "Veuillez vous connecter pour voir les détails de cette offre. " + (err.response.data.message || "");
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

// Apply Handlers
const handleApply = () => {
  if (!userIsStudent.value) {
    pendingToast.value = { message: "Seuls les étudiants peuvent postuler à cette offre.", type: 'danger' };
    return;
  }
  if (hasApplied.value) {
    pendingToast.value = { message: "Vous avez déjà postulé à cette offre.", type: 'info' };
    return;
  }
  lettreMotivation.value = '';
  motivationLetterError.value = '';
  const modalElement = document.getElementById('motivationLetterModal');
  if (modalElement) {
    if (!motivationModalInstance) {
      motivationModalInstance = new Modal(modalElement);
    }
    motivationModalInstance.show();
  }
};

const confirmApply = async () => {
  if (!userIsStudent.value) {
    pendingToast.value = { message: "Vous n'êtes pas autorisé à envoyer une candidature.", type: 'danger' };
    if (motivationModalInstance) motivationModalInstance.hide();
    return;
  }

  if (lettreMotivation.value.length < 50) {
    motivationLetterError.value = 'La lettre de motivation doit contenir au moins 50 caractères.';
    return;
  }
  motivationLetterError.value = '';

  if (!offreDetails.value || !offreDetails.value.id) {
    pendingToast.value = { message: "Impossible de postuler : ID de l'offre manquant.", type: 'danger' };
    if (motivationModalInstance) motivationModalInstance.hide();
    return;
  }

  if (applying.value) return;
  applying.value = true;

  try {
    const response = await axios.post('/api/candidatures', {
      offre_id: offreDetails.value.id,
      lettre_motivation: lettreMotivation.value
    });

    nombreCandidatures.value++;
    hasApplied.value = true;
    pendingToast.value = { message: response.data.message || "Candidature envoyée avec succès !", type: 'success' };
    if (motivationModalInstance) {
      motivationModalInstance.hide();
    }
  } catch (err) {
    console.error('Erreur lors de l\'envoi de la candidature :', err.response ? err.response.data : err.message);
    if (err.response && err.response.data) {
      if (err.response.data.message) {
        pendingToast.value = { message: err.response.data.message, type: 'danger' };
      } else if (err.response.data.errors && err.response.data.errors.lettre_motivation) {
        motivationLetterError.value = err.response.data.errors.lettre_motivation[0];
        pendingToast.value = { message: "Erreur de validation: " + err.response.data.errors.lettre_motivation[0], type: 'danger' };
      } else {
        pendingToast.value = { message: "Une erreur est survenue lors de l'envoi de votre candidature.", type: 'danger' };
      }
    } else {
      pendingToast.value = { message: "Une erreur réseau est survenue lors de l'envoi de votre candidature.", type: 'danger' };
    }
    if (motivationModalInstance && !motivationLetterError.value) {
      motivationModalInstance.hide();
    } else if (motivationModalInstance) {
      pendingToast.value = null;
    }
  } finally {
    applying.value = false;
  }
};

// Utility Functions
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const toast = new Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'Non spécifié';
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

// Lifecycle Hooks
onMounted(() => {
  if (props.id) {
    fetchOfferDetails(props.id);
  } else {
    error.value = "Aucun ID d'offre n'a été fourni dans l'URL pour afficher les détails.";
    loading.value = false;
  }

  const modalElement = document.getElementById('motivationLetterModal');
  if (modalElement) {
    modalElement.addEventListener('hidden.bs.modal', () => {
      motivationLetterError.value = '';
      if (motivationModalInstance) {
        motivationModalInstance.dispose();
        motivationModalInstance = null;
      }
      if (pendingToast.value) {
        displayToast(pendingToast.value.message, pendingToast.value.type);
        pendingToast.value = null;
      }
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    });
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
/* CSS Variables for Consistency */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --success-green: #198754;
  --danger-red: #dc3545;
  --warning-orange: #ffc107;
  --info-blue: #0dcaf0;
}

.content-page {
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header Styles */
.page-main-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark-text);
}

/* Offer Details Section */
.offer-details-section {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Offer Title Container */
.offer-title-container {
  border: 1px solid var(--border-color);
}

/* Status Badge */
.status-badge {
  padding: 0.5em 0.9em;
  font-weight: 600;
  border-radius: 0.375rem;
  white-space: nowrap;
}

/* Cards */
.card {
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
}

/* Section Headers */
.section-header {
  font-size: 1.1rem;
  color: var(--dark-text);
  margin-bottom: 0.5rem;
  padding-bottom: 0.3rem;
}

/* List Group Items */
.list-group-item {
  border: none;
  padding: 0.5rem 0;
  font-size: 0.95rem;
}

.list-group-item i {
  min-width: 25px;
}

/* Description Content */
.description-content {
  background-color: var(--light-gray-bg);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  padding: 0.75rem 1rem;
  min-height: 80px;
  font-size: 0.95rem;
  color: var(--dark-text);
}

.description-content p {
  line-height: 1.6;
}

/* Button Styles */
.btn-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border: none;
  border-radius: 0.5rem;
  padding: 0.75rem 1.5rem;
  font-size: 1.1rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.btn-primary:disabled {
  background: #6c757d;
  border-color: #6c757d;
  transform: none;
  box-shadow: none;
}

.btn-secondary {
  background: linear-gradient(to right, #6c757d, #5a6268);
  border: none;
  border-radius: 0.5rem;
  padding: 0.75rem 1.5rem;
  font-size: 1.1rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.2);
}

.btn-danger {
  background: linear-gradient(to right, #dc3545, #c82333);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.2);
}

/* Toast Styles */
.toast {
  border-radius: 0.5rem;
}

.text-bg-success {
  background-color: var(--success-green) !important;
  color: white !important;
}

.text-bg-danger {
  background-color: var(--danger-red) !important;
  color: white !important;
}

.text-bg-info {
  background-color: var(--info-blue) !important;
  color: white !important;
}

/* Modal Styles */
.modal-header.bg-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
}

.btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}

.is-invalid {
  border-color: var(--danger-red);
}

.invalid-feedback {
  color: var(--danger-red);
  font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 0.5rem;
  }

  .page-main-title {
    font-size: 1.25rem;
  }

  .offer-title-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .offer-title-container h2 {
    font-size: 1.25rem;
  }

  .section-header {
    font-size: 1rem;
  }

  .list-group-item {
    font-size: 0.9rem;
  }

  .description-content {
    font-size: 0.9rem;
  }

  .btn-primary,
  .btn-secondary,
  .btn-danger {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
  }
}

@media (max-width: 576px) {
  .offer-title-container {
    padding: 0.75rem;
  }

  .list-group-item {
    font-size: 0.85rem;
  }

  .btn-primary,
  .btn-secondary,
  .btn-danger {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}
</style>