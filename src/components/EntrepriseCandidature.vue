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
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Candidatures Reçues</h1>
      </span>
      </div>


    <section class="offers-list offers-section">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center">
        <h3 class="mb-0 fs-5">Listes des candidatures</h3>

        <div class="d-flex align-items-center">
          <div class="input-group search-input-group me-3">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher par étudiant ou offre" v-model="searchQuery" @input="filterCandidatures">
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              {{ filterStatus === '' ? 'Filtrer par statut' : getStatusText(filterStatus) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterStatusDropdown">
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
                          <router-link :to="{ name: 'entreprise-generic-profile-view', params: { type: 'etudiants',id: candidature.etudiant.id  } }">
  <span class="fw-bold ">{{ candidature.etudiant && candidature.etudiant.user ? candidature.etudiant.user.name : 'Étudiant inconnu' }}</span>
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
                <span :class="['status', 'badge', 'fs-6', getStatusBadgeClass(candidature.statut)]">
                  {{ getStatusText(candidature.statut) }}
                </span>
              </td>
              <td>
                <template v-if="candidature.statut === 'envoyee' || candidature.statut === 'traitee' || candidature.statut === 'en_attente'">
                  <button @click="showAcceptModal(candidature.id)" class="btn btn-outline-success btn-sm me-2">Accepter</button>
                  <button @click="showRefuseModal(candidature.id)" class="btn btn-outline-danger btn-sm">Refuser</button>
                </template>
                <p v-else-if="candidature.statut === 'acceptee'" class="text-success mt-2 mb-0 fw-bold">
                  En attente de l'étudiant.
                </p>
                <p v-else-if="candidature.statut === 'confirmee'" class="text-info mt-2 mb-0 fw-bold">
                  Stage confirmé.
                </p>
                <p v-else-if="candidature.statut === 'desistement_demande'" class="text-warning mt-2 mb-0 fw-bold">
                  Désistement.
                </p>
                  <p v-else-if="candidature.statut === 'refusee'" class="text-danger mt-2 mb-0 fw-bold">
                  Refusée par vous.
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
          <div class="modal-body bg-light p-4 rounded">
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
import { ref, onMounted, computed, reactive,nextTick } from 'vue';
import axios from 'axios';
import { Toast, Modal } from 'bootstrap';
import { useRouter } from 'vue-router'; // Si vous utilisez vue-router pour les liens

const router = useRouter();

// --- Données réactives ---
const candidatures = ref([]);
const loading = ref(true);
const error = ref(null);

const searchQuery = ref('');
const filterStatus = ref('');

const toastMessage = ref('');
const toastType = ref('success');
const currentMotivationLetter = ref('');

let motivationLetterViewModalInstance = null;

// Configuration de la modale générique
const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info', // 'info', 'success', 'error', 'confirm', 'warning', 'danger'
  headerClass: 'bg-primary text-white',
  closeButtonClass: 'btn-close-white',
  confirmButtonText: 'Confirmer',
  confirmButtonClass: 'btn-primary',
  showInput: false,
  inputLabel: '',
  inputValue: '',
  inputError: '',
  onConfirm: null, // Callback à exécuter en cas de confirmation
  currentCandidatureId: null, // ID de la candidature concernée
  desistementActionType: null, // 'accepter' ou 'refuser' pour la validation du désistement
});

let messageModalInstance = null; // Instance de la modale Bootstrap

// Liste des statuts disponibles pour le filtre et l'affichage
const availableStatuses = ref([
  //{ value: 'envoyee', text: 'Envoyée' },
  //{ value: 'traitee', text: 'En cours de traitement' },
  { value: 'en_attente', text: 'En attente' },
  { value: 'acceptee', text: 'Acceptée par l\'entreprise' }, // Nouveau statut pour clarifier
  { value: 'confirmee', text: 'Confirmée par l\'étudiant' },
 // { value: 'desistement_demande', text: 'Désistement demandé' },
  { value: 'refusee', text: 'Refusée par l\'entreprise' },
]);

// --- Fonctions utilitaires (Toasts et Modale générique) ---
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
  messageModal.inputValue = ''; // Réinitialiser l'input à chaque affichage
  messageModal.inputError = ''; // Réinitialiser l'erreur d'input
  messageModal.confirmButtonText = confirmButtonText;

  // Définir les classes Bootstrap en fonction du type de message
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
  } else if (type === 'warning') { // Pour le refus, par exemple
      messageModal.headerClass = 'bg-warning text-dark';
      messageModal.closeButtonClass = ''; // Bouton par défaut sombre
      messageModal.confirmButtonClass = 'btn-warning';
  } else if (type === 'danger') { // Pour le refus, par exemple
      messageModal.headerClass = 'bg-danger text-white';
      messageModal.closeButtonClass = 'btn-close-white'; // Bouton blanc pour fond sombre
      messageModal.confirmButtonClass = 'btn-danger';
  }

  messageModalInstance.show();
};

const hideMessage = () => {
  messageModalInstance.hide();
  messageModal.onConfirm = null; // Nettoyer le callback
  messageModal.currentCandidatureId = null; // Nettoyer l'ID
  messageModal.desistementActionType = null; // Nettoyer l'action de désistement
};

const confirmAction = () => {
  if (messageModal.onConfirm) {
    messageModal.onConfirm();
  }
};

// --- Fonctions de récupération des données ---
const fetchCandidatures = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/candidatures'); // Assurez-vous que l'endpoint est correct
    candidatures.value = response.data;
  } catch (err) {
    console.error('Erreur lors du chargement des candidatures reçues :', err);
    error.value = 'Impossible de charger les candidatures reçues.';
    displayToast('Échec du chargement des candidatures.', 'danger');
  } finally {
    loading.value = false;
  }
};


// --- Fonctions de Filtrage ---
const filteredCandidatures = computed(() => {
  let filtered = candidatures.value;

  // Filtrer par recherche textuelle (titre de l'offre ou nom de l'étudiant)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(candidature =>
      (candidature.offre && candidature.offre.titre && candidature.offre.titre.toLowerCase().includes(query)) ||
      (candidature.etudiant && candidature.etudiant.user && candidature.etudiant.user.name && candidature.etudiant.user.name.toLowerCase().includes(query))
    );
  }

  // Filtrer par statut
  if (filterStatus.value) {
    filtered = filtered.filter(candidature => candidature.statut === filterStatus.value);
  }

  return filtered;
});

const setFilterStatus = (status) => {
  filterStatus.value = status;
};

const filterCandidatures = () => {
  // `filteredCandidatures` se met à jour automatiquement
};

// --- Fonctions d'action sur les candidatures (avec modales) ---

const showAcceptModal = (idCandidature) => {
  messageModal.currentCandidatureId = idCandidature;
  showMessage(
    'Accepter cette candidature',
    'Voulez-vous vraiment accepter cette candidature ? L\'étudiant sera notifié.',
    'success',
    accepterCandidature, // La fonction callback sans arguments immédiats
    false, // Pas d'input
    '',
    'Oui, Accepter'
  );
};

const accepterCandidature = async () => {
  try {
    await axios.post(`/api/candidatures/${messageModal.currentCandidatureId}/accepter-entreprise`);
    displayToast('Candidature acceptée avec succès ! L\'étudiant sera informé.', 'success');
    fetchCandidatures(); // Rafraîchir
  } catch (err) {
    console.error('Erreur lors de l\'acceptation de la candidature :', err);
    let errorMessage = 'Une erreur est survenue lors de l\'acceptation de la candidature.';
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }
    showMessage('Erreur', errorMessage, 'error'); // Afficher l'erreur dans la modale
  } finally {
    hideMessage();
  }
};

const showRefuseModal = (idCandidature) => {
  messageModal.currentCandidatureId = idCandidature;
  showMessage(
    'Refuser cette candidature',
    'Veuillez indiquer un motif de refus (facultatif) :',
    'danger', // Utilisation de 'warning' pour le style
    refuserCandidature, // La fonction callback
    true, // Afficher un champ d'input
    'Motif de refus',
    'Refuser'
  );
};

const refuserCandidature = async () => {
  messageModal.inputError = '';
  const motif = messageModal.inputValue.trim();

  try {
    await axios.post(`/api/candidatures/${messageModal.currentCandidatureId}/refuser-entreprise`, { motif_refus: motif });
    displayToast('Candidature refusée avec succès.', 'success');
    fetchCandidatures(); // Rafraîchir
  } catch (err) {
    console.error('Erreur lors du refus de la candidature :', err);
    let errorMessage = 'Une erreur est survenue lors du refus de la candidature.';
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage = err.response.data.message;
    } else if (err.message) {
      errorMessage = err.message;
    }
    showMessage('Erreur', errorMessage, 'error'); // Afficher l'erreur dans la modale
  } finally {
    hideMessage();
  }
};


// --- Fonctions utilitaires pour le style et le texte du statut ---
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'envoyee': return 'bg-info-subtle text-info';
    case 'traitee': return 'bg-primary-subtle text-primary';
    case 'en_attente': return 'bg-secondary-subtle text-secondary';
    case 'acceptee': return 'bg-success-subtle text-success';
    case 'confirmee': return 'bg-info-subtle text-info'; // Statut final confirmé
    case 'desistement_demande': return 'bg-warning-subtle text-warning';
    case 'refusee': return 'bg-danger-subtle text-danger'; // Refus final
    default: return 'bg-light text-muted';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'envoyee': return 'Envoyée';
    case 'traitee': return 'En cours de traitement';
    case 'en_attente': return 'En attente';
    case 'acceptee': return 'Acceptée par vous';
    case 'confirmee': return 'Confirmée par étudiant';
    case 'desistement_demande': return 'Désistement demandé';
    case 'refusee': return 'Refusée par vous';
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

// --- Cycle de vie ---
onMounted(() => {
  fetchCandidatures();
  messageModalInstance = new Modal(document.getElementById('messageModal'));
});
</script>

<style scoped>
/* Votre style CSS existant de la page des offres, avec quelques ajustements */

.content-page {
  padding: 1.5rem;
  background-color: #f8f9fa; /* light-gray-bg */
  min-height: calc(100vh - 60px);
}

.page-main-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #212529; /* dark-text */
}

.add-offer-page-btn {
  background-color: #0d6efd; /* primary-blue */
  border-color: #0d6efd;
  font-weight: 500;
  transition: all 0.3s ease;
}
.add-offer-page-btn:hover {
  background-color: #0056b3;
  border-color: #0056b3;
  transform: translateY(-1px);
}

/* Section de résumé (statistiques) */
.offers-summary {
  background-color: rgba(91, 168, 245, 0.771); /* Couleur spécifiée */
}

.summary-cards .card {
  flex: 1;
  min-width: 150px;
  max-width: 220px;
  border: none;
  background-color: rgba(255, 255, 255, 0.9); /* Fond légèrement transparent pour se fondre */
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.summary-cards .card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.summary-cards .card i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

/* Liste des candidatures (style similaire à la liste des offres) */
.offers-section {
  margin-top: 2rem;
}

.list-header {
  background-color: #0d6efd; /* primary-blue */
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.list-header h3 {
  color: white;
}

.search-input-group {
  max-width: 250px;
}

.search-input-group .form-control-sm {
  border-radius: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.5);
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
}
.search-input-group .form-control-sm::placeholder {
  color: rgba(255, 255, 255, 0.7);
}
.search-input-group .form-control-sm:focus {
  background-color: white;
  color: #212529;
  box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

.dropdown .btn-outline-light {
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
}
.dropdown .btn-outline-light:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}
.dropdown-menu {
  border-radius: 0.25rem;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.offers-table-container {
  background-color: white;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th, .table td {
  padding: 1rem;
  vertical-align: middle;
  border-top: 1px solid #dee2e6;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
}

.table tbody tr:hover {
  background-color: #f2f2f2;
}

/* Badges de statut */
.status.badge {
  padding: 0.5em 0.7em;
  border-radius: 0.25rem;
  font-weight: 600;
  font-size: 0.85em; /* Ajusté pour la lisibilité */
}

/* Couleurs des badges (adaptées aux candidatures) */
.bg-info-subtle { background-color: #d1ecf1 !important; } /* Envoyée */
.text-info { color: #17a2b8 !important; }

.bg-primary-subtle { background-color: #cfe2ff !important; } /* En cours de traitement */
.text-primary { color: #0d6efd !important; }

.bg-secondary-subtle { background-color: #e2e3e5 !important; } /* En attente */
.text-secondary { color: #6c757d !important; }

.bg-success-subtle { background-color: #d4edda !important; } /* Acceptée par entreprise */
.text-success { color: #28a745 !important; }

.bg-success.text-white { background-color: #28a745 !important; color: white !important; } /* Confirmée par étudiant */

.bg-warning-subtle { background-color: #fff3cd !important; } /* Désistement demandé */
.text-warning { color: #ffc107 !important; }

.bg-dark-subtle { background-color: #d6d8d9 !important; } /* Désistement validé */
.text-dark { color: #343a40 !important; }

.bg-danger-subtle { background-color: #f8d7da !important; } /* Désistement refusé */
.text-danger { color: #dc3545 !important; }

.bg-danger.text-white { background-color: #dc3545 !important; color: white !important; } /* Refusée par entreprise */


/* Boutons dans le tableau */
.table .btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

/* Styles spécifiques aux modales (réutilisés/adaptés de la page des offres) */
.modal-header.bg-primary { background-color: #0d6efd !important; }
.modal-header.bg-success { background-color: #28a745 !important; }
.modal-header.bg-danger { background-color: #dc3545 !important; }
.modal-header.bg-warning { background-color: #ffc107 !important; }

.modal-header .btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .offers-summary .summary-cards {
    flex-direction: column;
    align-items: center;
  }
  .summary-cards .card {
    width: 100%;
    max-width: 300px;
  }
  .list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .list-header .search-input-group,
  .list-header .dropdown {
    width: 100%;
    margin-right: 0 !important;
    margin-bottom: 0.5rem;
  }
  .offers-table-container table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>
