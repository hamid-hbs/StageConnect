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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-dark page-main-title mb-0">Candidatures Reçues</h1>
    </div>

    <section class="offers-list offers-section">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center flex-column flex-md-row">
        <h3 class="mb-0 mb-2 mb-md-0 fs-5">Listes des candidatures</h3>
        <div class="d-flex align-items-center flex-column flex-md-row">
          <div class="input-group input-group-sm me-0 me-md-3 mb-2 mb-md-0 w-100 w-md-auto search-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" class="form-control border-start-0 search-input" placeholder="Rechercher par étudiant ou offre" v-model="searchQuery" @input="filterCandidatures">
            <button v-if="searchQuery" class="btn btn-outline-secondary clear-search" type="button" @click="searchQuery = ''">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="dropdown w-100 w-md-auto">
            <button class="btn btn-sm filter-btn dropdown-toggle" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-filter me-1"></i>{{ filterStatus === '' ? 'Filtrer par statut' : getStatusText(filterStatus) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end filter-menu" aria-labelledby="filterStatusDropdown">
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('')">Tous les statuts</a></li>
              <li v-for="status in availableStatuses" :key="status.value">
                <a class="dropdown-item" href="#" @click.prevent="setFilterStatus(status.value)">
                  {{ status.text }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="loading" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Chargement des candidatures...
      </div>
      <div v-else-if="error" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-danger border-start border-end border-bottom">
        Erreur lors du chargement des candidatures : {{ error }}
      </div>
      <div v-else-if="filteredCandidatures.length > 0" class="offers-table-container border-start border-end border-bottom rounded-bottom">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Offre</th>
              <th scope="col">Étudiant</th>
              <th scope="col">Lettre de motivation</th>
              <th scope="col">Date Postulat</th>
              <th scope="col">Statut</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="candidature in filteredCandidatures" :key="candidature.id">
              <td>{{ candidature.id }}</td>
              <td>
                <router-link :to="{ name: 'entreprise-offre-details', params: { id: candidature.offre.id } }" class="text-primary fw-bold">
                  {{ candidature.offre ? candidature.offre.titre : 'Offre inconnue' }}
                </router-link>
              </td>
              <td>
                <router-link :to="{ name: 'entreprise-generic-profile-view', params: { type: 'etudiants', id: candidature.etudiant.id } }">
                  <span class="fw-bold">{{ candidature.etudiant && candidature.etudiant.user ? candidature.etudiant.user.name : 'Étudiant inconnu' }}</span>
                </router-link>
                <br>
              </td>
              <td>
                <button @click="showMotivationLetter(candidature.lettre_motivation)" class="btn btn-sm btn-info">
                  Voir la lettre
                </button>
              </td>
              <td>{{ new Date(candidature.date_postulat).toLocaleDateString() }}</td>
              <td>
                <span :class="['status', 'badge', 'fs-8', getStatusBadgeClass(candidature.statut)]">
                  {{ getStatusText(candidature.statut) }}
                </span>
              </td>
              <td>
                <template v-if="candidature.statut === 'envoyee' || candidature.statut === 'traitee' || candidature.statut === 'en_attente'">
                  <button @click="showAcceptModal(candidature.id)" class="btn btn-outline-success btn-sm me-2">Accepter</button>
                  <button @click="showRefuseModal(candidature.id)" class="btn btn-outline-danger btn-sm">Refuser</button>
                </template>
                <p v-else-if="candidature.statut === 'acceptee'" class="text-success mt-2 mb-0">
                  En attente de l'étudiant.
                </p>
                <p v-else-if="candidature.statut === 'confirmee'" class="text-info mt-2 mb-0">
                  Stage confirmé.
                </p>
                <p v-else-if="candidature.statut === 'desistement_demande'" class="text-warning mt-2 mb-0">
                  Désistement.
                </p>
                <p v-else-if="candidature.statut === 'refusee'" class="text-danger mt-2 mb-0">
                  Refusée par l'entreprise.
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Aucune candidature trouvée pour le moment ou ne correspondant à vos critères de recherche.
      </div>
    </section>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" :class="messageModal.headerClass">
            <h5 class="modal-title" id="messageModalLabel">{{ messageModal.title }}</h5>
            <button type="button" class="btn-close" :class="messageModal.closeButtonClass" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>{{ messageModal.message }}</p>
            <div v-if="messageModal.showInput" class="mt-3">
              <label for="modalInput" class="form-label">{{ messageModal.inputLabel }}</label>
              <textarea class="form-control" id="modalInput" v-model="messageModal.inputValue" rows="3"></textarea>
              <p v-if="messageModal.inputError" class="text-danger mt-1">{{ messageModal.inputError }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="hideMessage">Annuler</button>
            <button type="button" class="btn" :class="messageModal.confirmButtonClass" @click="confirmAction">
              {{ messageModal.confirmButtonText }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="motivationLetterViewModal" tabindex="-1" aria-labelledby="motivationLetterViewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="motivationLetterViewModalLabel"><i class="fas fa-file-alt me-2"></i>Lettre de Motivation</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body bg-light p-4 rounded" style="max-height: 70vh; overflow-y: auto;">
            <p style="white-space: pre-wrap;">{{ currentMotivationLetter }}</p>
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
import { ref, onMounted, computed, reactive, nextTick } from 'vue';
import axios from 'axios';
import { Toast, Modal } from 'bootstrap';
import { useRouter } from 'vue-router';

const router = useRouter();

const candidatures = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const filterStatus = ref('');
const toastMessage = ref('');
const toastType = ref('success');
const currentMotivationLetter = ref('');
let motivationLetterViewModalInstance = null;

const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info',
  headerClass: 'bg-primary text-white',
  closeButtonClass: 'btn-close-white',
  confirmButtonText: 'Confirmer',
  confirmButtonClass: 'btn-primary',
  showInput: false,
  inputLabel: '',
  inputValue: '',
  inputError: '',
  onConfirm: null,
  currentCandidatureId: null,
  desistementActionType: null,
});

let messageModalInstance = null;

const availableStatuses = ref([
  { value: 'en_attente', text: 'En attente' },
  { value: 'acceptee', text: 'Acceptée par l\'entreprise' },
  { value: 'confirmee', text: 'Confirmée par l\'étudiant' },
  { value: 'refusee', text: 'Refusée par l\'entreprise' },
]);

const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

const showMessage = (title, message, type = 'info', onConfirm = null, showInput = false, inputLabel = '', confirmButtonText = 'Confirmer') => {
  messageModal.title = title;
  messageModal.message = message;
  messageModal.type = type;
  messageModal.onConfirm = onConfirm;
  messageModal.showInput = showInput;
  messageModal.inputLabel = inputLabel;
  messageModal.inputValue = '';
  messageModal.inputError = '';
  messageModal.confirmButtonText = confirmButtonText;

  if (type === 'confirm' || type === 'info') {
    messageModal.headerClass = 'bg-primary text-white';
    messageModal.closeButtonClass = 'btn-close-white';
    messageModal.confirmButtonClass = 'btn-primary';
  } else if (type === 'error') {
    messageModal.headerClass = 'bg-danger text-white';
    messageModal.closeButtonClass = 'btn-close-white';
    messageModal.confirmButtonClass = 'btn-danger';
  } else if (type === 'success') {
    messageModal.headerClass = 'bg-success text-white';
    messageModal.closeButtonClass = 'btn-close-white';
    messageModal.confirmButtonClass = 'btn-success';
  } else if (type === 'warning' || type === 'danger') {
    messageModal.headerClass = 'bg-danger text-white';
    messageModal.closeButtonClass = 'btn-close-white';
    messageModal.confirmButtonClass = 'btn-danger';
  }

  messageModalInstance.show();
};

const hideMessage = () => {
  messageModalInstance.hide();
  messageModal.onConfirm = null;
  messageModal.currentCandidatureId = null;
  messageModal.desistementActionType = null;
};

const confirmAction = () => {
  if (messageModal.onConfirm) {
    messageModal.onConfirm();
  }
};

const fetchCandidatures = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/candidatures');
    candidatures.value = response.data;
  } catch (err) {
    console.error('Erreur lors du chargement des candidatures reçues :', err);
    error.value = 'Impossible de charger les candidatures reçues.';
    displayToast('Échec du chargement des candidatures.', 'danger');
  } finally {
    loading.value = false;
  }
};

const filteredCandidatures = computed(() => {
  let filtered = candidatures.value;
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(candidature =>
      (candidature.offre && candidature.offre.titre && candidature.offre.titre.toLowerCase().includes(query)) ||
      (candidature.etudiant && candidature.etudiant.user && candidature.etudiant.user.name && candidature.etudiant.user.name.toLowerCase().includes(query))
    );
  }
  if (filterStatus.value) {
    filtered = filtered.filter(candidature => candidature.statut === filterStatus.value);
  }
  return filtered;
});

const setFilterStatus = (status) => {
  filterStatus.value = status;
};

const filterCandidatures = () => {
  // Handled by computed property filteredCandidatures
};

const showAcceptModal = (idCandidature) => {
  messageModal.currentCandidatureId = idCandidature;
  showMessage(
    'Accepter cette candidature',
    'Voulez-vous vraiment accepter cette candidature ? L\'étudiant sera notifié.',
    'success',
    accepterCandidature,
    false,
    '',
    'Oui, Accepter'
  );
};

const accepterCandidature = async () => {
  try {
    await axios.post(`/api/candidatures/${messageModal.currentCandidatureId}/accepter-entreprise`);
    displayToast('Candidature acceptée avec succès ! L\'étudiant sera informé.', 'success');
    fetchCandidatures();
  } catch (err) {
    console.error('Erreur lors de l\'acceptation de la candidature :', err);
    let errorMessage = 'Une erreur est survenue lors de l\'acceptation de la candidature.';
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }
    showMessage('Erreur', errorMessage, 'error');
  } finally {
    hideMessage();
  }
};

const showRefuseModal = (idCandidature) => {
  messageModal.currentCandidatureId = idCandidature;
  showMessage(
    'Refuser cette candidature',
    'Voulez-vous vraiment refuser cette candidature ? L\'étudiant sera notifié.',
    'danger',
    refuserCandidature,
    false,
    '',
    'Refuser'
  );
};

const refuserCandidature = async () => {
  messageModal.inputError = '';
  const motif = messageModal.inputValue.trim();
  try {
    await axios.post(`/api/candidatures/${messageModal.currentCandidatureId}/refuser-entreprise`, { motif_refus: motif });
    displayToast('Candidature refusée avec succès.', 'success');
    fetchCandidatures();
  } catch (err) {
    console.error('Erreur lors du refus de la candidature :', err);
    let errorMessage = 'Une erreur est survenue lors du refus de la candidature.';
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }
    showMessage('Erreur', errorMessage, 'error');
  } finally {
    hideMessage();
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'envoyee': return 'bg-info-subtle text-info';
    case 'traitee': return 'bg-primary-subtle text-primary';
    case 'en_attente': return 'bg-secondary-subtle text-secondary';
    case 'acceptee': return 'bg-success-subtle text-success';
    case 'confirmee': return 'bg-info-subtle text-info';
    case 'desistement_demande': return 'bg-warning-subtle text-warning';
    case 'refusee': return 'bg-danger-subtle text-danger';
    default: return 'bg-light text-muted';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'envoyee': return 'Envoyée';
    case 'traitee': return 'En cours de traitement';
    case 'en_attente': return 'En attente';
    case 'acceptee': return 'Accepté';
    case 'confirmee': return 'Confirmé';
    case 'desistement_demande': return 'Désistement demandé';
    case 'refusee': return 'Refusé';
    default: return status;
  }
};

const showMotivationLetter = (letter) => {
  currentMotivationLetter.value = letter;
  nextTick(() => {
    const modalElement = document.getElementById('motivationLetterViewModal');
    if (modalElement) {
      motivationLetterViewModalInstance = new Modal(modalElement);
      motivationLetterViewModalInstance.show();
    }
  });
};

onMounted(() => {
  fetchCandidatures();
  messageModalInstance = new Modal(document.getElementById('messageModal'));
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
.content-page > .d-flex {
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-main-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
  position: relative;
  padding-bottom: 0.5rem;
}

.page-main-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 80px;
  height: 3px;
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 1.5px;
}

/* Candidatures List */
.offers-section {
  margin-top: 1.5rem;
}

.list-header {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 0.75rem 0.75rem 0 0;
  padding: 1rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.list-header h3 {
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

/* Table */
.offers-table-container {
  background-color: #fff;
  border-radius: 0 0 0.75rem 0.75rem;
  overflow: hidden;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table {
  border-collapse: separate;
  border-spacing: 0;
}

.table thead th {
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  padding: 0.75rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #212529;
  text-transform: uppercase;
}

.table tbody tr {
  transition: background-color 0.2s ease;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.table tbody td {
  padding: 0.75rem;
  font-size: 0.9rem;
  color: #212529;
  border-bottom: 1px solid #dee2e6;
}

.table tbody td a.text-primary {
  font-weight: 600;
  transition: color 0.2s ease;
}

.table tbody td a.text-primary:hover {
  color: #3b82f6;
}

.table .badge {
  border-radius: 0.5rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 500;
}

.table .btn-sm {
  border-radius: 0.375rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table .btn-info {
  background: linear-gradient(to right, #17a2b8, #138496);
  border: none;
  color: #fff;
}

.table .btn-info:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(23, 162, 184, 0.2);
}

.table .btn-secondary {
  background: linear-gradient(to right, #6c757d, #5c636a);
  border: none;
  color: #fff;
}

.table .btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.2);
}

.table .btn-outline-success {
  border-color: #28a745;
  color: #28a745;
}

.table .btn-outline-success:hover {
  background: linear-gradient(to right, #28a745, #34d058);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(40, 167, 69, 0.2);
}

.table .btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
}

.table .btn-outline-danger:hover {
  background: linear-gradient(to right, #dc3545, #b02a37);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.2);
}

.table .text-success,
.table .text-info,
.table .text-warning,
.table .text-danger {
  font-weight: 600;
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
.bg-warning-subtle { background-color: #fff3e0 !important; }
.text-warning { color: #ffc107 !important; }
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

.modal-header.bg-success {
  background: linear-gradient(to right, #28a745, #34d058) !important;
}

.modal-header.bg-danger {
  background: linear-gradient(to right, #dc3545, #b02a37) !important;
}

.modal-header.bg-info {
  background: linear-gradient(to right, #17a2b8, #138496) !important;
}

.modal-header h5 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #fff;
}

.modal-header .btn-close {
  filter: invert(1) grayscale(100%) brightness(200%);
  transition: transform 0.2s ease;
}

.modal-header .btn-close:hover {
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-body .form-label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #212529;
  margin-bottom: 0.5rem;
}

.modal-body .form-control {
  border-radius: 0.5rem;
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  font-size: 0.9rem;
  background-color: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.modal-body .form-control:focus {
  border-color: transparent;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  background: linear-gradient(to right, #fff, #fff) padding-box,
              linear-gradient(to right, #0d6efd, #2563eb) border-box;
  border: 1px solid transparent;
}

.modal-body textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.modal-body.bg-light {
  background-color: #f8f9fa !important;
  border-radius: 0.75rem;
  max-height: 70vh;
  overflow-y: auto;
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

.modal-footer .btn-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border: none;
}

.modal-footer .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.modal-footer .btn-success {
  background: linear-gradient(to right, #28a745, #34d058);
  border: none;
}

.modal-footer .btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(40, 167, 69, 0.2);
}

.modal-footer .btn-danger {
  background: linear-gradient(to right, #dc3545, #b02a37);
  border: none;
}

.modal-footer .btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.2);
}

.modal-footer .btn-secondary {
  background: linear-gradient(to right, #6c757d, #5c636a);
  border: none;
}

.modal-footer .btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 1rem;
  }

  .page-main-title {
    font-size: 1.5rem;
  }

  .list-header {
    padding: 0.75rem;
  }

  .list-header h3 {
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

  .offers-table-container {
    overflow-x: auto;
  }

  .table thead th {
    font-size: 0.85rem;
  }

  .table tbody td {
    font-size: 0.85rem;
    padding: 0.5rem;
  }

  .modal-body.bg-light {
    max-height: 60vh;
  }

  .modal-header h5 {
    font-size: 1.1rem;
  }

  .modal-body .form-control {
    font-size: 0.85rem;
    padding: 0.4rem;
  }

  .modal-footer .btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}

@media (max-width: 576px) {
  .content-page > .d-flex {
    flex-direction: column;
    align-items: flex-start;
  }

  .list-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .list-header .input-group,
  .list-header .dropdown {
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

  .table tbody td {
    font-size: 0.8rem;
    padding: 0.4rem;
  }

  .modal-dialog {
    margin: 0.5rem;
  }

  .modal-body.bg-light {
    max-height: 50vh;
  }
}
</style>