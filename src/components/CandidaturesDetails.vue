<template>
  <div class="p-4 bg-gray-100 min-h-screen font-inter">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-semibold text-blue-700 underline underline-offset-4 decoration-blue-500">
        Détails de la Candidature
      </h1>
      <button @click="goBack" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 transition-colors flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Retour
      </button>
    </div>

    <!-- Notification Toast -->
    <div id="toast-container-details" class="fixed top-4 right-4 z-[1100] space-y-2"></div>

    <section v-if="loading" class="bg-white p-6 rounded-lg shadow-md text-center py-8 text-gray-500">
      <div class="spinner-border w-8 h-8 mx-auto mb-3" role="status">
        <span class="sr-only">Chargement...</span>
      </div>
      <p>Chargement des détails de la candidature...</p>
    </section>

    <section v-else-if="error" class="bg-white p-6 rounded-lg shadow-md text-center py-8 text-red-600">
      <p class="text-lg mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Erreur</p>
      <p>{{ error }}</p>
    </section>

    <section v-else class="bg-white p-6 rounded-lg shadow-md">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations sur la Candidature -->
        <div>
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Informations sur la Candidature</h2>
          <div class="space-y-3 text-gray-700">
            <p><strong>Offre :</strong> <span class="text-blue-600 font-medium">{{ candidature.offre?.titre || 'N/A' }}</span></p>
            <p><strong>Étudiant :</strong>
              <router-link :to="{ name: 'etudiant-profile', params: { id: candidature.etudiant?.user?.id } }" class="text-blue-600 hover:underline font-medium">
                {{ candidature.etudiant?.user?.name || 'N/A' }}
              </router-link>
            </p>
            <p><strong>Date de Postulat :</strong> {{ formatDate(candidature.date_postulat) }}</p>
            <p><strong>Statut Actuel :</strong>
              <span :class="['px-3 py-1 rounded-full text-sm font-semibold', getStatusBadgeClass(candidature.statut)]">
                {{ getDisplayStatus(candidature.statut) }}
              </span>
            </p>
            <p v-if="candidature.cv_path">
              <strong>CV :</strong>
              <a :href="fullCvUrl" target="_blank" class="text-blue-500 hover:underline ml-2">Télécharger le CV</a>
            </p>
          </div>
        </div>

        <!-- Lettre de Motivation -->
        <div>
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Lettre de Motivation</h2>
          <div class="bg-gray-50 p-4 rounded-md border border-gray-200 h-64 overflow-y-auto whitespace-pre-wrap text-gray-800 shadow-inner">
            {{ candidature.lettre_motivation || 'Pas de lettre de motivation fournie.' }}
          </div>
        </div>
      </div>

      <!-- Section Actions -->
      <div class="mt-8 pt-6 border-t border-gray-200 flex flex-wrap gap-4 justify-end">
        <h2 class="w-full text-xl font-semibold text-gray-800 mb-4">Actions</h2>

        <template v-if="candidature.statut === 'en_attente'">
          <button @click="confirmAcceptCandidature" class="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center">
            <i class="fas fa-check mr-2"></i> Accepter la Candidature
          </button>
          <button @click="confirmRefuseCandidature" class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center">
            <i class="fas fa-times mr-2"></i> Refuser la Candidature
          </button>
        </template>
        <button v-else-if="candidature.statut === 'acceptee' || candidature.statut === 'validee_etablissement'"
                class="px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors flex items-center"
                @click="displayToast('La candidature est déjà acceptée ou validée.', 'info')">
          <i class="fas fa-info-circle mr-2"></i> Statut : Acceptée
        </button>
        <button v-else-if="candidature.statut === 'refusee' || candidature.statut === 'refusee_etablissement' || candidature.statut === 'desistement_etudiant'"
                class="px-5 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center"
                @click="displayToast('La candidature est déjà refusée ou désistée.', 'info')">
          <i class="fas fa-info-circle mr-2"></i> Statut : Refusée
        </button>

        <!-- Placeholder for more actions like "Schedule Interview" -->
        <button @click="displayToast('Fonctionnalité d\'entretien à venir !', 'info')"
                class="px-5 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors flex items-center">
          <i class="fas fa-calendar-alt mr-2"></i> Planifier un Entretien
        </button>
      </div>
    </section>

    <!-- Custom Confirmation Modal -->
    <div id="confirmation-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-[1200]" style="display: none;">
      <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm">
        <h3 class="text-lg font-bold mb-4 text-gray-900">{{ confirmModal.title }}</h3>
        <p class="text-gray-700 mb-6">{{ confirmModal.message }}</p>
        <div class="flex justify-end gap-3">
          <button @click="hideConfirmModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Annuler</button>
          <button @click="executeConfirmAction" :class="`px-4 py-2 text-white rounded-lg transition-colors ${confirmModal.type === 'accept' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'}`">Confirmer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios';
import { useRoute, useRouter } from 'vue-router';
import { Toast } from 'bootstrap'; // Assuming Bootstrap is loaded for Toast

const route = useRoute();
const router = useRouter();

const candidature = ref(null);
const loading = ref(true);
const error = ref(null);

// Custom Confirmation Modal state
const confirmModal = ref({
  isVisible: false,
  title: '',
  message: '',
  type: '', // 'accept' or 'refuse'
  actionId: null,
  onConfirm: null,
});

// Toast notification state
let toastInstance = null; // Stores the Bootstrap Toast instance

const fetchCandidatureDetails = async () => {
  loading.value = true;
  error.value = null;
  const candidatureId = route.params.id;

  if (!candidatureId) {
    error.value = "ID de candidature manquant dans l'URL.";
    loading.value = false;
    return;
  }

  try {
    // API endpoint to get details of a single candidature
    const response = await axios.get(`/api/candidatures/${candidatureId}`);
    candidature.value = response.data; // Assuming API returns the full candidature object
  } catch (err) {
    console.error('Erreur lors de la récupération des détails de la candidature :', err.response ? err.response.data : err.message);
    error.value = 'Impossible de charger les détails de la candidature. Veuillez réessayer plus tard.';
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

const fullCvUrl = computed(() => {
  // Assuming your backend serves static files from a base URL like /storage/cvs/
  // Adjust `import.meta.env.VITE_API_BASE_URL` if your file server is different from your API server
  if (candidature.value?.cv_path) {
    const baseURL = import.meta.env.VITE_API_URL || ''; // Ensure this is defined in your .env
    return `${baseURL}${candidature.value.cv_path}`;
  }
  return '#';
});

// --- Actions (Accept/Refuse) ---
const confirmAcceptCandidature = () => {
  showConfirmModal(
    'Confirmer l\'Acceptation',
    'Voulez-vous vraiment accepter cette candidature ?',
    'accept',
    candidature.value.id,
    acceptCandidature
  );
};

const acceptCandidature = async (id) => {
  try {
    const response = await axios.post(`/api/candidatures/${id}/accepter-entreprise`);
    displayToast(response.data.message || 'Candidature acceptée avec succès !', 'success');
    fetchCandidatureDetails(); // Refresh data
  } catch (err) {
    console.error('Erreur lors de l\'acceptation :', err.response ? err.response.data : err.message);
    displayToast(err.response?.data?.message || 'Erreur lors de l\'acceptation de la candidature.', 'danger');
  }
};

const confirmRefuseCandidature = () => {
  showConfirmModal(
    'Confirmer le Refus',
    'Voulez-vous vraiment refuser cette candidature ?',
    'refuse',
    candidature.value.id,
    refuseCandidature
  );
};

const refuseCandidature = async (id) => {
  try {
    const response = await axios.post(`/api/candidatures/${id}/refuser-entreprise`);
    displayToast(response.data.message || 'Candidature refusée.', 'success');
    fetchCandidatureDetails(); // Refresh data
  } catch (err) {
    console.error('Erreur lors du refus :', err.response ? err.response.data : err.message);
    displayToast(err.response?.data?.message || 'Erreur lors du refus de la candidature.', 'danger');
  }
};

// --- Custom Confirmation Modal functions ---
const showConfirmModal = (title, message, type, actionId, onConfirm) => {
  confirmModal.value = {
    isVisible: true,
    title,
    message,
    type,
    actionId,
    onConfirm,
  };
  document.getElementById('confirmation-modal').style.display = 'flex';
};

const hideConfirmModal = () => {
  confirmModal.value.isVisible = false;
  document.getElementById('confirmation-modal').style.display = 'none';
};

const executeConfirmAction = () => {
  if (confirmModal.value.onConfirm) {
    confirmModal.value.onConfirm(confirmModal.value.actionId);
  }
  hideConfirmModal();
};

// --- Helper functions for display ---
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

const getDisplayStatus = (statut) => {
  const statusMap = {
    'en_attente': 'En attente',
    'acceptee': 'Acceptée par Entreprise',
    'refusee': 'Refusée par Entreprise',
    'en_attente_validation_etablissement': 'En attente validation Établissement',
    'validee_etablissement': 'Validée par Établissement (Stage créé)',
    'desistement_etudiant': 'Désistée (par Étudiant)',
    'refusee_etablissement': 'Refusée par Établissement',
  };
  return statusMap[statut] || statut;
};

const getStatusBadgeClass = (statut) => {
  switch (statut) {
    case 'acceptee':
    case 'validee_etablissement':
      return 'bg-green-100 text-green-800'; // Tailwind equivalent for success
    case 'en_attente':
    case 'en_attente_validation_etablissement':
      return 'bg-blue-100 text-blue-800'; // Tailwind equivalent for info
    case 'refusee':
    case 'refusee_etablissement':
    case 'desistement_etudiant':
      return 'bg-red-100 text-red-800'; // Tailwind equivalent for danger
    default:
      return 'bg-gray-100 text-gray-800'; // Tailwind equivalent for secondary
  }
};

// --- Toast notification function ---
const displayToast = (message, type = 'success') => {
  const toastEl = document.getElementById('toast-container-details');
  if (!toastEl) return;

  const newToastDiv = document.createElement('div');
  newToastDiv.className = `toast align-items-center text-white bg-${type} border-0 show`;
  newToastDiv.setAttribute('role', 'alert');
  newToastDiv.setAttribute('aria-live', 'assertive');
  newToastDiv.setAttribute('aria-atomic', 'true');
  newToastDiv.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">
        ${message}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  `;

  toastEl.appendChild(newToastDiv);

  const bsToast = new Toast(newToastDiv, { delay: 3000 });
  bsToast.show();

  newToastDiv.addEventListener('hidden.bs.toast', () => {
    newToastDiv.remove();
  });
};

const goBack = () => {
  router.go(-1); // Revenir à la page précédente (liste des candidatures pour l'offre ou vue d'ensemble)
};

onMounted(() => {
  fetchCandidatureDetails();
});
</script>

<style scoped>
/* Tailwind CSS utilities are used directly in the template.
   Adding specific Bootstrap-like spinner and toast styles for functionality.
*/
.spinner-border {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  vertical-align: -0.125em;
  border: 0.25em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: .75s linear infinite spinner-border;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}

.font-inter {
  font-family: 'Inter', sans-serif;
}

/* Custom Bootstrap-like toast for Tailwind context */
.toast {
  --bs-toast-zindex: 1090;
  --bs-toast-padding-x: 0.75rem;
  --bs-toast-padding-y: 0.5rem;
  --bs-toast-spacing: 1rem;
  --bs-toast-background-color: rgba(var(--bs-body-bg-rgb), .85);
  --bs-toast-border-color: rgba(0, 0, 0, .1);
  --bs-toast-border-radius: 0.375rem;
  --bs-toast-box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
  --bs-toast-header-color: #6c757d;
  --bs-toast-header-background-color: rgba(var(--bs-body-bg-rgb), .85);
  --bs-toast-header-border-color: rgba(0, 0, 0, .05);
  width: 350px; /* Fixed width for consistent look */
  max-width: 100%;
  font-size: 0.875rem;
  pointer-events: auto;
  background-clip: padding-box;
  border: 1px solid var(--bs-toast-border-color);
  box-shadow: var(--bs-toast-box-shadow);
  opacity: 0;
  border-radius: var(--bs-toast-border-radius);
}

.toast.show {
  display: block;
  opacity: 1;
}

.toast.showing {
  opacity: 0;
}

.toast:not(.show) {
  display: none;
}

.toast-body {
  padding: var(--bs-toast-padding-x);
  word-wrap: break-word;
}

.toast-header {
  display: flex;
  align-items: center;
  padding: var(--bs-toast-padding-y) var(--bs-toast-padding-x);
  color: var(--bs-toast-header-color);
  background-color: var(--bs-toast-header-background-color);
  background-clip: padding-box;
  border-bottom: 1px solid var(--bs-toast-header-border-color);
  border-top-left-radius: calc(var(--bs-toast-border-radius) - 1px);
  border-top-right-radius: calc(var(--bs-toast-border-radius) - 1px);
}

.btn-close {
  box-sizing: content-box;
  width: 1em;
  height: 1em;
  padding: .25em .25em;
  color: #000;
  background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
  border: 0;
  border-radius: .25rem;
  opacity: .5;
}
.btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}

/* Specific toast types */
.bg-success { background-color: #198754 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-info { background-color: #0dcaf0 !important; }
.bg-yellow-500 { background-color: #f59e0b; }
.hover\:bg-yellow-600:hover { background-color: #d97706; }
.bg-purple-500 { background-color: #a855f7; }
.hover\:bg-purple-600:hover { background-color: #9333ea; }
.bg-gray-500 { background-color: #6b7280; }
.hover\:bg-gray-600:hover { background-color: #4b5563; }
</style>