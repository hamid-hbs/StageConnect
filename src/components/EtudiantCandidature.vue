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
        <div class="input-group search-input-group me-md-3 mb-2 mb-md-0">
          <input type="text" class="form-control form-control-sm" placeholder="Rechercher par offre, entreprise..." v-model="searchQuery" @input="applyFilters">
        </div>
        <div class="dropdown me-md-3 mb-2 mb-md-0">
          <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            {{ selectedStatusName || 'Filtrer par statut' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterStatusDropdown">
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
      <div v-for="candidature in filteredCandidatures" :key="candidature.id" class="card application-card mb-3">
        <div class="card-body">
          <h5 class="card-title text-muted"><span>
            Offre</span> : <router-link :to="{ name: 'etudiant-offre-details', params: { id: candidature.offre_id } }">
  <span class="fw-bold ">{{ candidature.offre.titre || 'Offre inconnue' }}</span>
</router-link></h5>
          <h6 class="card-subtitle mb-2 text-muted">
            Entreprise :
            <span v-if="candidature.offre && candidature.offre.entreprise">
              <router-link :to="{ name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: candidature.offre?.entreprise?.id } }">
  <span class="fw-bold ">{{ candidature.offre.entreprise?.nom_entreprise || 'Nom inconnu' }}</span>
</router-link>
              
            </span>
            <span v-else>Inconnue</span>
          </h6>
          <ul class="list-unstyled application-details">
            <li>Statut : <strong :class="getStatusClass(candidature.statut)">{{ getStatusText(candidature.statut) }}</strong></li>
            <li>Date de candidature : {{ new Date(candidature.date_postulat).toLocaleDateString() }}</li>
          </ul>

          <div class="card-actions mt-3">
            <template v-if="candidature.statut === 'acceptee'">
              <p class="alert alert-success d-inline-block p-2 mb-2 w-100">Félicitations ! L'entreprise a accepté votre candidature.</p>
              <button @click="showConfirmationModal(candidature.id)" class="btn btn-success btn-sm me-2">Confirmer le stage</button>
              <!-- Le bouton "Me désister" a été retiré -->
            </template>

            <p v-else-if="candidature.statut === 'confirmee'" class="alert alert-info d-inline-block p-2 mb-2 w-100">
              Vous avez confirmé cette candidature. Un stage a été créé.
            </p>
            <!-- Le paragraphe pour 'desistement_demande' a été retiré -->
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
            <!-- Le bouton de confirmation n'est pas nécessaire pour les messages d'erreur simples -->
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'; // Ajout de 'reactive'
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

// Variables pour les modales
const currentCandidatureId = ref(null);
let confirmationModalInstance = null;

// Modale de message personnalisée (déjà existante, mais je m'assure qu'elle est bien utilisée)
const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info', // 'info', 'success', 'error'
  onConfirm: null, // Pas utilisé pour les messages d'erreur simples
});

const showMessage = (title, message, type = 'info') => { // Retrait de onConfirm car non utilisé pour les erreurs
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
  //{ value: 'envoyee', text: 'Envoyée' },
  //{ value: 'traitee', text: 'En cours de traitement' },
  { value: 'en_attente', text: 'En attente' },
  { value: 'acceptee', text: 'Acceptée par l\'entreprise' },
  { value: 'confirmee', text: 'Confirmée par l\'étudiant' },
  { value: 'refusee', text: 'Refusée par l\'entreprise' },
]);

// --- Fonctions Utilitaires (Toasts) ---
const displayToast = (message, type = 'success') => {
  console.log(`Affichage du toast: Type=${type}, Message=${message}`); // Log de débogage
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 5000 }); // Délai augmenté à 5 secondes
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
};

const applyFilters = () => {
  // `filteredCandidatures` se met à jour automatiquement
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
  confirmationModalInstance.hide(); // Cacher la modale

  try {
    await axios.post(`/api/candidatures/${currentCandidatureId.value}/confirmer-etudiant`, { choix: 'confirmer' });
    displayToast('Stage confirmé avec succès ! Les autres candidatures ont été gérées.', 'success');
    fetchMesCandidatures(); // Rafraîchir pour voir le nouveau statut
  } catch (err) {
    console.error('Erreur lors de la confirmation du stage :', err);
    // Vérifier si l'erreur est due à un stage déjà actif
    if (err.response && err.response.data && err.response.data.message === 'Vous avez déjà confirmé une autre candidature ou en avez une en attente de validation.') {
      // Afficher le message dans la modale générique
      showMessage('Stage Actif Détecté', 'Vous avez déjà un stage actif. Vous ne pouvez pas confirmer une autre offre.', 'error');
    } else {
      displayToast('Une erreur est survenue lors de la confirmation du stage.', 'danger');
    }
  } finally {
    currentCandidatureId.value = null; // Réinitialiser l'ID
  }
};


// --- Fonctions utilitaires pour le style et le texte du statut ---
const getStatusClass = (status) => {
  switch (status) {
    case 'envoyee': return 'text-secondary';
    case 'traitee': return 'text-info';
    case 'en_attente': return 'text-muted';
    case 'acceptee': return 'text-success';
    case 'confirmee': return 'text-primary';
    case 'refusee': return 'text-danger';
    default: return '';
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
  // Initialiser les instances de modales après le montage du composant
  confirmationModalInstance = new Modal(document.getElementById('confirmationModal'));
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
}

.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
  min-height: calc(100vh - 60px); /* Ajustez selon la hauteur de votre navbar/footer */
}

/* Styles pour l'en-tête de la liste des candidatures */
.applications-list-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.applications-list-header h3 {
  color: white;
}

.applications-list-header .search-input-group {
  max-width: 300px; /* Limite la largeur de la barre de recherche */
}

.applications-list-header .form-control-sm {
  border-radius: 0.25rem;
}

/* Grille des candidatures */
.applications-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* 300px minimum, s'adapte */
  gap: 1.5rem; /* Espace entre les cartes */
}

.application-card {
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.application-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.application-card .card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary-blue);
}

.application-card .card-subtitle {
  font-size: 0.95rem;
}

.application-details {
  font-size: 0.9rem;
  color: var(--dark-text);
  margin-bottom: 0.5rem;
}

.application-details li {
  margin-bottom: 0.4rem;
}

.card-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem; /* Espace entre les boutons */
}

/* Styles pour les toasts */
.toast {
  opacity: 0; /* Hidden by default */
  transition: opacity 0.3s ease-in-out;
}

.toast.showing, .toast.show {
  opacity: 1;
}

.text-bg-success { background-color: #198754 !important; color: white !important; }
.text-bg-danger { background-color: #dc3545 !important; color: white !important; }
.text-bg-info { background-color: #0dcaf0 !important; color: white !important; }
.text-bg-warning { background-color: #ffc107 !important; color: #212529 !important; } /* Texte sombre pour le fond jaune */
.text-bg-secondary { background-color: #6c757d !important; color: white !important; }

/* Styles spécifiques aux modales */
.modal-header.bg-primary {
  background-color: var(--primary-blue) !important;
}
.modal-header.bg-warning {
  background-color: #ffc107 !important;
}
.btn-close-white {
  filter: invert(1) grayscale(100%) brightness(200%);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .applications-list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .applications-list-header .search-input-group,
  .applications-list-header .dropdown {
    width: 100%;
    margin-right: 0 !important;
    margin-bottom: 0.5rem;
  }
}
</style>
