<template>
  <div class="content-page p-4">
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

    <section class="applications-list-header bg-primary text-white p-3 rounded-top d-flex flex-column flex-md-row justify-content-between align-items-center mb-0">
      <h3 class="mb-2 mb-md-0 fs-5 text-white">Mes Candidatures</h3>
      <div class="d-flex align-items-center flex-column flex-md-row">
        <div class="input-group input-group-sm me-0 me-md-3 mb-2 mb-md-0 w-100 w-md-auto search-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-gray-400"></i>
          </span>
          <input type="text" class="form-control border-start-0 search-input" placeholder="Rechercher par offre, entreprise..." v-model="searchQuery" @input="applyFilters">
          <button v-if="searchQuery" class="btn btn-outline-secondary clear-search" type="button" @click="searchQuery = ''">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="dropdown w-100 w-md-auto">
          <button class="btn btn-sm filter-btn dropdown-toggle" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-filter me-1"></i>{{ selectedStatusName || 'Filtrer par statut' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end filter-menu" aria-labelledby="filterStatusDropdown">
            <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus(null, '')">Tous les statuts</a></li>
            <li v-for="status in availableStatuses" :key="status.value">
              <a class="dropdown-item" href="#" @click.prevent="setFilterStatus(status.value, status.text)">
                {{ status.text }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <div v-if="loading" class="d-flex justify-content-center align-items-center bg-white p-5 rounded-bottom shadow-sm">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement de vos candidatures...</span>
      </div>
      <p class="ms-3 text-muted">Chargement de vos candidatures...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger text-center p-4 rounded-bottom shadow-sm">
      Erreur : {{ error }}
    </div>

    <div v-else-if="filteredCandidatures.length > 0" class="applications-grid p-4 bg-white rounded-bottom shadow-sm">
      <div v-for="candidature in paginatedCandidatures" :key="candidature.id" class="card application-card mb-3">
        <div class="card-body">
          <h5 class="card-title text-muted"><span>
            Offre</span> : <router-link :to="{ name: 'etudiant-offre-details', params: { id: candidature.offre_id } }">
  <span class="fw-bold text-primary">{{ candidature.offre.titre || 'Offre inconnue' }}</span>
</router-link></h5>
          <h6 class="card-subtitle mb-2 text-muted">
            Entreprise :
            <span v-if="candidature.offre && candidature.offre.entreprise">
              <router-link :to="{ name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: candidature.offre?.entreprise?.id } }">
  <span class="fw-bold text-primary">{{ candidature.offre.entreprise?.nom_entreprise || 'Nom inconnu' }}</span>
</router-link>
            </span>
            <span v-else>Inconnue</span>
          </h6>
          <ul class="list-unstyled application-details">
            <li>Statut : <span :class="['badge', getStatusBadgeClass(candidature.statut)]">{{ getStatusText(candidature.statut) }}</span></li>
            <li>Date de candidature : {{ new Date(candidature.date_postulat).toLocaleDateString() }}</li>
          </ul>

          <div class="card-actions mt-3">
            <template v-if="candidature.statut === 'acceptee'">
              <p class="alert alert-success d-inline-block p-2 mb-2 w-100">Félicitations ! L'entreprise a accepté votre candidature.</p>
              <button @click="showConfirmationModal(candidature.id)" class="btn btn-outline-success btn-sm me-2">Confirmer le stage</button>
            </template>

            <p v-else-if="candidature.statut === 'confirmee'" class="alert alert-info d-inline-block p-2 mb-2 w-100">
              Vous avez confirmé cette candidature. Un stage a été créé.
            </p>
            <p v-else-if="candidature.statut === 'refusee'" class="alert alert-danger d-inline-block p-2 mb-2 w-100">
              Votre candidature a été refusée par l'entreprise.
              <span v-if="candidature.motif_refus_entreprise"><br>Motif : {{ candidature.motif_refus_entreprise }}</span>
            </p>
            <p v-else-if="candidature.statut === 'envoyee' || candidature.statut === 'traitee' || candidature.statut === 'en_attente'" class="alert alert-secondary d-inline-block p-2 mb-2 w-100">
              Votre candidature est en cours de traitement.
            </p>

            <a v-if="candidature.CV" :href="candidature.CV" target="_blank" class="btn btn-outline-primary btn-sm mt-2">Voir mon CV</a>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info text-center p-4 rounded-bottom shadow-sm">
      Aucune candidature trouvée pour le moment ou ne correspondant à vos critères de recherche.
    </div>

    <!-- Pagination -->
    <nav v-if="filteredCandidatures.length > itemsPerPage" class="d-flex justify-content-center mt-4">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="setPage(currentPage - 1)" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </button>
        </li>
        <li v-for="page in pageNumbers" :key="page" class="page-item" :class="{ active: currentPage === page }">
          <button class="page-link" @click="setPage(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="setPage(currentPage + 1)" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </button>
        </li>
      </ul>
    </nav>

    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="confirmationModalLabel">Confirmer le stage</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Confirmez-vous vouloir prendre ce stage ? Cette action est irréversible et créera le stage. Toutes vos autres candidatures "acceptées" seront refusées automatiquement.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-success" @click="confirmStageAction">Confirmer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale de message générique pour les erreurs critiques -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" :class="messageModal.type === 'error' ? 'bg-danger text-white' : 'bg-primary text-white'">
            <h5 class="modal-title" id="messageModalLabel">{{ messageModal.title }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>{{ messageModal.message }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

// --- Variables Réactives ---
const candidatures = ref([]);
const loading = ref(true);
const error = ref(null);

const searchQuery = ref('');
const selectedStatus = ref(null);
const selectedStatusName = ref('');

const toastMessage = ref('');
const toastType = ref('success');

// Pagination variables
const currentPage = ref(1);
const itemsPerPage = ref(6);

// Variables pour les modales
const currentCandidatureId = ref(null);
let confirmationModalInstance = null;

// Modale de message personnalisée
const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info',
  onConfirm: null,
});

const showMessage = (title, message, type = 'info') => {
  messageModal.title = title;
  messageModal.message = message;
  messageModal.type = type;
  const modalElement = document.getElementById('messageModal');
  const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
  bootstrapModal.show();
};

const hideMessage = () => {
  const modalElement = document.getElementById('messageModal');
  const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
  bootstrapModal.hide();
  messageModal.isVisible = false;
};

// Liste des statuts disponibles pour le filtre
const availableStatuses = ref([
  { value: 'en_attente', text: 'En attente' },
  { value: 'acceptee', text: 'Acceptée par l\'entreprise' },
  { value: 'confirmee', text: 'Confirmée par l\'étudiant' },
  { value: 'refusee', text: 'Refusée par l\'entreprise' },
]);

// --- Pagination Logic ---
const totalPages = computed(() => Math.ceil(filteredCandidatures.value.length / itemsPerPage.value));

const pageNumbers = computed(() => {
  const pages = [];
  for (let i = 1; i <= totalPages.value; i++) {
    pages.push(i);
  }
  return pages;
});

const paginatedCandidatures = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredCandidatures.value.slice(start, end);
});

const setPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// --- Fonctions Utilitaires (Toasts) ---
const displayToast = (message, type = 'success') => {
  console.log(`Affichage du toast: Type=${type}, Message=${message}`);
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

// --- Fonctions de Filtrage ---
const filteredCandidatures = computed(() => {
  let filtered = candidatures.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(candidature =>
      (candidature.offre.titre && candidature.offre.titre.toLowerCase().includes(query)) ||
      (candidature.offre.entreprise && candidature.offre.entreprise.nom_entreprise && candidature.offre.entreprise.nom_entreprise.toLowerCase().includes(query))
    );
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(candidature => candidature.statut === selectedStatus.value);
  }

  return filtered;
});

const setFilterStatus = (value, name) => {
  selectedStatus.value = value;
  selectedStatusName.value = name;
  currentPage.value = 1;
};

const applyFilters = () => {
  currentPage.value = 1;
};

// --- Fonctions de Gestion des Candidatures (avec Modales) ---
const fetchMesCandidatures = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/candidatures');
    candidatures.value = response.data;
  } catch (err) {
    console.error('Erreur lors du chargement des candidatures de l\'étudiant :', err);
    error.value = 'Impossible de charger vos candidatures.';
    displayToast('Échec du chargement des candidatures.', 'danger');
  } finally {
    loading.value = false;
  }
};

// Afficher la modale de confirmation
const showConfirmationModal = (idCandidature) => {
  currentCandidatureId.value = idCandidature;
  confirmationModalInstance.show();
};

// Confirmer l'action de stage (après validation par la modale)
const confirmStageAction = async () => {
  confirmationModalInstance.hide();

  try {
    await axios.post(`/api/candidatures/${currentCandidatureId.value}/confirmer-etudiant`, { choix: 'confirmer' });
    displayToast('Stage confirmé avec succès ! Les autres candidatures ont été gérées.', 'success');
    fetchMesCandidatures();
  } catch (err) {
    console.error('Erreur lors de la confirmation du stage :', err);
    if (err.response && err.response.data && err.response.data.message === 'Vous avez déjà confirmé une autre candidature ou en avez une en attente de validation.') {
      showMessage('Stage Actif Détecté', 'Vous avez déjà un stage actif. Vous ne pouvez pas confirmer une autre offre.', 'error');
    } else {
      displayToast('Une erreur est survenue lors de la confirmation du stage.', 'danger');
    }
  } finally {
    currentCandidatureId.value = null;
  }
};

// --- Fonctions utilitaires pour le style et le texte du statut ---
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'envoyee': return 'bg-info-subtle text-info';
    case 'traitee': return 'bg-primary-subtle text-primary';
    case 'en_attente': return 'bg-secondary-subtle text-secondary';
    case 'acceptee': return 'bg-success-subtle text-success';
    case 'confirmee': return 'bg-info-subtle text-info';
    case 'refusee': return 'bg-danger-subtle text-danger';
    default: return 'bg-light text-muted';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'envoyee': return 'Envoyée';
    case 'traitee': return 'En cours de traitement';
    case 'en_attente': return 'En attente';
    case 'acceptee': return 'Acceptée par l\'entreprise';
    case 'confirmee': return 'Confirmée par l\'étudiant (Stage créé)';
    case 'refusee': return 'Refusée par l\'entreprise';
    default: return status;
  }
};

onMounted(() => {
  fetchMesCandidatures();
  confirmationModalInstance = new Modal(document.getElementById('confirmationModal'));
});
</script>

<style scoped>
/* General Layout */
.content-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
  min-height: calc(100vh - 60px);
  background-color: #f8f9fa;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Toast */
.toast-container {
  z-index: 1100;
}

.toast {
  border-radius: 0.75rem;
  border: none;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.toast.text-bg-success {
  background: linear-gradient(to right, #28a745, #34d058);
}

.toast.text-bg-danger {
  background: linear-gradient(to right, #dc3545, #b02a37);
}

.toast-header {
  border-bottom: none;
  padding: 0.75rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.toast-body {
  padding: 0.75rem;
  font-size: 0.9rem;
}

/* Header Section */
.applications-list-header {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 0.75rem 0.75rem 0 0;
  padding: 1rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.applications-list-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #fff;
}

/* Search Bar */
.search-group {
  position: relative;
  max-width: 300px;
}

.search-input {
  border-radius: 0.75rem;
  border: 1px solid #dee2e6;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  font-size: 0.9rem;
  background-color: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: transparent;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  background: linear-gradient(to right, #fff, #fff) padding-box,
              linear-gradient(to right, #0d6efd, #2563eb) border-box;
  border: 1px solid transparent;
}

.search-input::placeholder {
  color: #6c757d;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.search-input:focus::placeholder {
  opacity: 0.7;
}

.input-group-text {
  border-radius: 0.75rem 0 0 0.75rem;
  background-color: #fff;
  border: 1px solid #dee2e6;
  border-right: none;
  padding: 0.5rem;
}

.input-group-text i {
  font-size: 0.9rem;
  color: #6c757d;
}

.clear-search {
  border-radius: 0 0.75rem 0.75rem 0;
  border: 1px solid #dee2e6;
  border-left: none;
  padding: 0.5rem;
  color: #6c757d;
  background-color: #fff;
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.clear-search:hover {
  background-color: #f8f9fa;
  color: #3b82f6;
  transform: scale(1.05);
}

/* Filter Dropdown */
.filter-btn {
  border-radius: 0.75rem;
  border: 1px solid #dee2e6;
  background-color: #fff;
  color: #0d6efd;
  font-size: 0.9rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.filter-btn:hover {
  background-color: #f8f9fa;
  border-color: #0d6efd;
  color: #3b82f6;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.filter-btn:focus,
.filter-btn:active,
.filter-btn.show {
  border-color: transparent;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  background: linear-gradient(to right, #fff, #fff) padding-box,
              linear-gradient(to right, #0d6efd, #2563eb) border-box;
  border: 1px solid transparent;
  color: #3b82f6;
}

.filter-menu {
  border-radius: 0.5rem;
  border: none;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
  background-color: #fff;
  min-width: 160px;
  animation: slideDown 0.2s ease;
}

.filter-menu .dropdown-item {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
  color: #212529;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.filter-menu .dropdown-item:hover,
.filter-menu .dropdown-item:focus {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  color: #fff;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Applications Grid */
.applications-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  background-color: white;
  border-radius: 0 0 0.75rem 0.75rem;
  padding: 2rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Application Card */
.application-card {
  border: 1px solid #dee2e6;
  border-radius: 0.75rem;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.application-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.application-card .card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.application-card .card-title .text-primary {
  color: #0d6efd !important;
}

.application-card .card-subtitle {
  font-size: 0.95rem;
  color: #6c757d;
}

.application-details {
  font-size: 0.9rem;
  color: #212529;
  margin-bottom: 0.5rem;
}

.application-details li {
  margin-bottom: 0.4rem;
}

.application-details .badge {
  border-radius: 0.5rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 500;
}

/* Card Actions */
.card-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.card-actions .btn {
  border-radius: 0.375rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card-actions .btn-outline-primary {
  border-color: #0d6efd;
  color: #0d6efd;
}

.card-actions .btn-outline-primary:hover {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.card-actions .btn-outline-success {
  border-color: #28a745;
  color: #28a745;
}

.card-actions .btn-outline-success:hover {
  background: linear-gradient(to right, #28a745, #34d058);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(40, 167, 69, 0.2);
}

/* Badges */
.bg-info-subtle { background-color: #e6f3f5 !important; }
.text-info { color: #2e94a4 !important; }
.bg-primary-subtle { background-color: #e7f1ff !important; }
.text-primary { color: #0d6efd !important; }
.bg-secondary-subtle { background-color: #e9ecef !important; }
.text-secondary { color: #6c757d !important; }
.bg-success-subtle { background-color: #e6f4ea !important; }
.text-success { color: #28a745 !important; }
.bg-danger-subtle { background-color: #fce4e6 !important; }
.text-danger { color: #cc2838 !important; }

/* Modals */
.modal-content {
  border-radius: 1rem;
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  background: #fff;
}

.modal-header {
  border-bottom: none;
  padding: 1.25rem;
  border-radius: 1rem 1rem 0 0;
}

.modal-header.bg-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb) !important;
}

.modal-header.bg-danger {
  background: linear-gradient(to right, #dc3545, #b02a37) !important;
}

.modal-header h5 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #fff;
}

.modal-header .btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
  transition: transform 0.2s ease;
}

.modal-header .btn-close-white:hover {
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: none;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 0 0 1rem 1rem;
}

.modal-footer .btn {
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.modal-footer .btn-success {
  background: linear-gradient(to right, #28a745, #34d058);
  border: none;
}

.modal-footer .btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(40, 167, 69, 0.2);
}

.modal-footer .btn-secondary {
  background: linear-gradient(to right, #6c757d, #5c636a);
  border: none;
}

.modal-footer .btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.2);
}

/* Pagination */
.pagination {
  margin-top: 1.5rem;
}

.page-item .page-link {
  cursor: pointer;
  color: #0d6efd;
  border-radius: 0.375rem;
  margin: 0 0.2rem;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.page-item.active .page-link {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-color: transparent;
  color: white;
}

.page-item:not(.active) .page-link:hover {
  background-color: #f8f9fa;
  border-color: #0d6efd;
  color: #3b82f6;
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 1rem;
  }

  .applications-list-header {
    padding: 0.75rem;
  }

  .applications-list-header h3 {
    font-size: 1.1rem;
  }

  .search-group {
    max-width: 100%;
  }

  .search-input {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem 0.4rem 2rem;
  }

  .filter-btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }

  .filter-menu .dropdown-item {
    font-size: 0.85rem;
  }

  .applications-grid {
    grid-template-columns: 1fr;
    padding: 1rem;
  }

  .modal-header h5 {
    font-size: 1.1rem;
  }

  .modal-footer .btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}

@media (max-width: 576px) {
  .applications-list-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .search-group,
  .dropdown {
    width: 100%;
    margin-bottom: 0.5rem;
  }

  .search-input {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem 0.35rem 2rem;
  }

  .filter-btn {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
  }

  .filter-menu .dropdown-item {
    font-size: 0.8rem;
  }

  .modal-dialog {
    margin: 0.5rem;
  }
}
</style>