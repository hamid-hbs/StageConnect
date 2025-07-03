<template>
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
              <button v-if="userIsStudent && getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) === 'Disponible'"
                      @click="handleApply"
                      :disabled="applying || hasApplied" 
                      class="btn btn-primary btn-lg"
                      data-bs-toggle="modal"
                      data-bs-target="#motivationLetterModal">
                <span v-if="applying" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                <i v-else class="fas fa-paper-plane me-2"></i>
                {{ applying ? 'Envoi de la candidature...' : (hasApplied ? 'Déjà postulé' : 'Envoyer candidature') }} <!-- Le texte change si hasApplied est true -->
              </button>
              <button v-else-if="userIsStudent && getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) === 'Expirée'"
                      class="btn btn-secondary btn-lg" disabled>
                Offre expirée
              </button>
              <button v-else-if="!userIsStudent" class="btn btn-primary btn-lg" disabled>
                Seuls les étudiants peuvent postuler
              </button>
              <button v-else class="btn btn-secondary btn-lg" disabled>
                Offre non disponible pour postuler
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

  <div class="modal fade" id="motivationLetterModal" tabindex="-1" aria-labelledby="motivationLetterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="motivationLetterModalLabel"><i class="fas fa-edit me-2"></i>Lettre de Motivation</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="motivationTextArea" class="form-label">Rédigez votre lettre de motivation pour cette offre :</label>
            <textarea class="form-control" id="motivationTextArea" rows="15" v-model="lettreMotivation"
                      :class="{'is-invalid': motivationLetterError}"
                      placeholder="Expliquez pourquoi vous êtes le candidat idéal pour cette offre..."></textarea>
            <div class="invalid-feedback" v-if="motivationLetterError">
              {{ motivationLetterError }}
            </div>
            <small class="form-text text-muted">Minimum 50 caractères.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary" @click="confirmApply" :disabled="applying || !lettreMotivation || lettreMotivation.length < 50">
            <span v-if="applying" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Envoyer la Candidature
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';
import {Modal, Toast} from 'bootstrap';

// --- Variables Réactives ---
const offreDetails = ref(null);
const nombreCandidatures = ref(0);
const loading = ref(true);
const error = ref(null);
const applying = ref(false);
const hasApplied = ref(false); // <-- Déclarée ici

// Variables pour les toasts de notification
const toastMessage = ref('');
const toastType = ref('success');

// Rôle de l'utilisateur connecté (sera mis à jour depuis l'API)
const userRole = ref(null);

// Récupération des instances de route et de router de Vue Router
const route = useRoute();
const router = useRouter();

// --- Nouvelles Variables Réactives pour la Modale ---
const lettreMotivation = ref('');
const motivationLetterError = ref('');
let motivationModal = null; // Pour stocker l'instance de la modale Bootstrap

// --- Propriété calculée pour vérifier si l'utilisateur est un étudiant ---
const userIsStudent = computed(() => {
  return userRole.value === 'etudiant';
});

// --- Propriété calculée pour déterminer le nom de la route générique du profil ---
const dynamicGenericProfileRouteName = computed(() => {
  if (route.path.startsWith('/entreprise-dashboard')) {
    return 'entreprise-generic-profile-view';
  } else if (route.path.startsWith('/etablissement-dashboard')) {
    return 'etablissement-generic-profile-view';
  } else if (route.path.startsWith('/etudiant-dashboard')) {
    return 'etudiant-generic-profile-view';
  }
  return ''; // Fallback, gérer le cas où la route n'est pas dans un dashboard connu
});


// --- Fonctions de Récupération des Données ---
const fetchOfferDetails = async (offerId) => {
  loading.value = true;
  error.value = null;
  hasApplied.value = false; // Réinitialisé avant chaque fetch pour éviter des états résiduels

  try {
    const userResponse = await axios.get('/api/user/getProfile');
    userRole.value = userResponse.data.role;

    const response = await axios.get(`/api/offres/${offerId}`);
    offreDetails.value = response.data.offre;
    nombreCandidatures.value = response.data.nombre_candidatures;
    hasApplied.value = response.data.has_applied || false; // <-- La valeur du backend est assignée ici

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

// --- Fonctions de Soumission de Candidature ---
const handleApply = () => {
    // Cette vérification est redondante avec le v-if sur le bouton, mais ajoute une couche de sécurité
    if (!userIsStudent.value) {
        displayToast("Seuls les étudiants peuvent postuler à cette offre.", 'danger');
        return;
    }
    if (hasApplied.value) {
        displayToast("Vous avez déjà postulé à cette offre.", 'info');
        return;
    }
    // Réinitialiser l'erreur et afficher la modale
    lettreMotivation.value = '';
    motivationLetterError.value = '';
    // Initialiser la modale Bootstrap si elle n'existe pas déjà
    const modalElement = document.getElementById('motivationLetterModal');
    if (modalElement && window.bootstrap && window.bootstrap.Modal) {
        motivationModal = new window.bootstrap.Modal(modalElement);
        motivationModal.show();
    }
};

const confirmApply = async () => {
    // Vérification finale du rôle avant l'envoi de l'API (sécurité supplémentaire)
    if (!userIsStudent.value) {
        displayToast("Vous n'êtes pas autorisé à envoyer une candidature.", 'danger');
        if (motivationModal) motivationModal.hide();
        return;
    }

    if (lettreMotivation.value.length < 50) {
        motivationLetterError.value = 'La lettre de motivation doit contenir au moins 50 caractères.';
        return;
    }
    motivationLetterError.value = ''; // Réinitialiser l'erreur s'il y en avait une

    if (!offreDetails.value || !offreDetails.value.id) {
        displayToast("Impossible de postuler : ID de l'offre manquant.", 'danger');
        return;
    }

    if (applying.value) return;
    applying.value = true;

    try {
        const response = await axios.post('/api/candidatures', {
            offre_id: offreDetails.value.id,
            lettre_motivation: lettreMotivation.value
        });

        displayToast(response.data.message || "Candidature envoyée avec succès !", 'success');
        nombreCandidatures.value++;
        hasApplied.value = true; // <-- Mis à jour à true immédiatement après succès de la candidature
        if (motivationModal) motivationModal.hide();

    } catch (err) {
        console.error('Erreur lors de l\'envoi de la candidature :', err.response ? err.response.data : err.message);
        if (err.response && err.response.data) {
            if (err.response.data.message) {
                displayToast(err.response.data.message, 'danger');
            } else if (err.response.data.errors && err.response.data.errors.lettre_motivation) { // Utilisez err.response.data.errors
                motivationLetterError.value = err.response.data.errors.lettre_motivation[0];
                displayToast("Erreur de validation: " + err.response.data.errors.lettre_motivation[0], 'danger');
            } else {
                displayToast("Une erreur est survenue lors de l'envoi de votre candidature.", 'danger');
            }
        } else {
            displayToast("Une erreur réseau est survenue lors de l'envoi de votre candidature.", 'danger');
        }
    } finally {
        applying.value = false;
    }
};

// --- Fonctions Utilitaires d'Affichage et de Notification ---
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  // Assurez-vous que la date est au format YYYY-MM-DD pour une création correcte de Date
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
  // Assurez-vous que la date est au format YYYY-MM-DD pour une création correcte de Date
  const cleanDateString = expirationDate.split('T')[0];
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Réinitialiser l'heure pour comparer seulement la date
    return new Date(cleanDateString) < today;
  } catch (e) {
    console.error('Erreur lors de la vérification de l\'expiration de la date:', expirationDate, e);
    return true; // En cas d'erreur de format, considérer comme expiré
  }
};

const getDisplayStatus = (statut, expirationDate) => {
  // Le statut 'expiree' du backend prime, sinon on vérifie la date d'expiration
  if (statut === 'expiree' || isExpired(expirationDate)) {
    return 'Expirée';
  }
  return 'Disponible'; // Si statut est 'active' et non expirée
};

const getStatusBadgeClass = (statut, expirationDate) => {
  if (statut === 'expiree' || isExpired(expirationDate)) {
    return 'bg-danger text-white';
  }
  return 'bg-success text-white'; // Si statut est 'active' et non expirée
};

const goBack = () => {
  router.go(-1);
};

// --- Hooks de Cycle de Vie ---
onMounted(() => {
  if (route.params.id) {
    fetchOfferDetails(route.params.id);
  } else {
    error.value = "Aucun ID d'offre n'a été fourni dans l'URL pour afficher les détails.";
    loading.value = false;
  }
  // S'assurer que Bootstrap est chargé pour initialiser la modale
  const modalElement = document.getElementById('motivationLetterModal');
  if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', () => {
          motivationLetterError.value = ''; // Réinitialiser l'erreur quand la modale est fermée
          // Si motivationModal est une instance, la détruire pour éviter les fuites de mémoire
          if (motivationModal) {
              motivationModal.dispose();
              motivationModal = null;
          }
      });
  }
});

watch(() => route.params.id, (newId) => {
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
  --danger-color: #dc3545; /* Ajout de la variable danger-color */
}

.content-page {
  padding: 1.5rem;
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
  background-color: rgba(13, 110, 253, 0.1); /* Bleu transparent */
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px; /* Ajoute un peu d'espace en dessous */
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Ajustement pour le titre de l'offre à l'intérieur de son conteneur */
.offer-title {
  color: var(--dark-text) !important; /* Couleur du texte noir */
  margin-bottom: 0; /* Supprime la marge par défaut si elle existe */
  font-weight: 600; /* Peut-être un peu plus gras pour se démarquer */
  flex-grow: 1; /* Permet au titre de prendre l'espace disponible */
}


.status-badge {
  padding: 0.5em 0.9em;
  font-weight: 600;
  border-radius: 0.375rem;
  white-space: nowrap;
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
  transition: all 0.3s ease; /* Transition douce pour le survol */
  padding: 0.75rem 1.5rem; /* Augmente la taille du bouton */
  font-size: 1.1rem; /* Texte un peu plus grand */
  font-weight: 600;
  border-radius: 0.5rem; /* Bords plus arrondis */
}
.btn-primary:hover {
  background-color: #0056b3; /* Bleu plus foncé au survol */
  border-color: #0056b3;
  transform: translateY(-2px); /* Léger soulèvement */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre au survol */
}
.btn-primary:disabled {
  background-color: #6c757d; /* Gris pour désactivé */
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

/* Styles pour les modals */
.modal-header.bg-primary {
    background-color: var(--primary-blue) ;
}
.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.is-invalid {
    border-color: var(--danger-color);
}
.invalid-feedback {
    color: var(--danger-color);
    font-size: 0.875em;
}
</style>
