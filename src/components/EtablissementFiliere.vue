<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Gestion des Filières</h1>
      </span>
      <button class="btn btn-primary add-filiere-page-btn" data-bs-toggle="modal" data-bs-target="#addFiliereModal">Ajouter une Filière</button>
    </div>

    <section class="filieres-summary p-4 rounded shadow-sm mb-4" style="background-color: rgba(91, 168, 245, 0.771);">
      <div v-if="loadingStatistics" class="text-center text-secondary">
        Chargement des statistiques...
      </div>
      <div v-else-if="statisticsError" class="text-center text-danger">
        Erreur lors du chargement des statistiques : {{ statisticsError }}
      </div>
      <div v-else class="summary-cards d-flex justify-content-around flex-wrap gap-3">
        <div class="card total p-3 rounded shadow-sm text-center">
          <i class="fa-solid fa-book" style="color: #0062ff;"></i>
          <p class="fs-2 fw-bold mb-0">{{ statistics.total_filieres }}</p>
          <p class="text-secondary mb-0">Total Filières</p>
        </div>
        <!-- Vous pouvez ajouter d'autres statistiques ici si votre backend les fournit, par exemple "Filières avec années", "Filières sans années" -->
      </div>
    </section>

    <section class="filieres-list filieres-section">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center">
        <h3 class="mb-0 fs-5">Liste des Filières</h3>
        <div class="d-flex align-items-center">
          <div class="input-group search-input-group me-3">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher une filière" v-model="searchQuery" @input="filterFilieres">
          </div>
          <!-- Le filtre par statut n'est pas applicable ici pour les filières, je l'ai retiré. -->
        </div>
      </div>

      <div v-if="loadingFilieres" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Chargement des filières...
      </div>
      <div v-else-if="filieresError" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-danger border-start border-end border-bottom">
        Erreur lors du chargement des filières : {{ filieresError }}
      </div>
      <div v-else-if="Array.isArray(filteredFilieres) && filteredFilieres.length > 0" class="filieres-table-container border-start border-end border-bottom rounded-bottom">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Nom de la Filière</th>
              <th scope="col">Années Associées</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="filiere in filteredFilieres" :key="filiere.id">
              <td>{{ filiere.id }}</td>
              <td>{{ filiere.libfil }}</td>
              <td>
                <span v-if="filiere.filannee && filiere.filannee.length > 0">
                  {{ filiere.filannee.map(annee => annee.libannee).join(', ') }}
                </span>
                <span v-else class="text-muted">Aucune année</span>
              </td>
              <td>
                <button
                  class="btn btn-info btn-sm text-white"
                  @click="openManageYearsModal(filiere)"
                >
                  Gérer les Années
                </button>
                <!-- Ajoutez d'autres actions ici si nécessaire (ex: modifier, supprimer) -->
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Aucune filière trouvée pour votre recherche.
      </div>
    </section>

    <!-- Modale d'ajout de filière -->
    <div class="modal fade" id="addFiliereModal" tabindex="-1" aria-labelledby="addFiliereModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
              <h1 class="modal-title fs-5" id="addFiliereModalLabel">Ajouter une nouvelle Filière</h1>
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="createFiliere">
            <div class="modal-body">
              <div class="mb-3">
                <label for="filiereName" class="form-label">Nom de la Filière</label>
                <input type="text" class="form-control" id="filiereName" v-model="newFiliere.libfil" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="creatingFiliere">
                {{ creatingFiliere ? 'Création...' : 'Créer la filière' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modale de gestion des années pour une filière -->
    <div class="modal fade" id="manageYearsModal" tabindex="-1" aria-labelledby="manageYearsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
              <h1 class="modal-title fs-5" id="manageYearsModalLabel">Gérer les Années pour "{{ selectedFiliere ? selectedFiliere.libfil : '' }}"</h1>
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="syncFiliereYears">
            <div class="modal-body">
              <div v-if="loadingAnnees" class="text-center text-muted">Chargement des années...</div>
              <div v-else-if="anneesError" class="text-center text-danger">Erreur lors du chargement des années : {{ anneesError }}</div>
              <div v-else-if="listeAnnees.length > 0">
                <p>Sélectionnez les années à associer à cette filière :</p>
                <div class="form-check" v-for="annee in listeAnnees" :key="annee.id">
                  <input class="form-check-input" type="checkbox" :value="annee.id" v-model="selectedAnneeIds" :id="'annee-' + annee.id">
                  <label class="form-check-label" :for="'annee-' + annee.id">
                    {{ annee.libannee }}
                  </label>
                </div>
              </div>
              <div v-else class="text-center text-muted">
                Aucune année disponible.
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="syncingYears">
                {{ syncingYears ? 'Synchronisation...' : 'Synchroniser les années' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modale de message/alerte personnalisée -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">{{ messageModal.title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>{{ messageModal.message }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="messageModal.isVisible = false">Fermer</button>
            <button v-if="messageModal.type === 'confirm'" type="button" class="btn btn-primary" @click="confirmAction">Confirmer</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import axios from '../axios'; // Assurez-vous que ce chemin est correct pour votre configuration axios
import { Modal } from 'bootstrap'; // Importez Modal de Bootstrap

// --- Données des statistiques des filières ---
const statistics = ref({
  total_filieres: 0,
});
const loadingStatistics = ref(true);
const statisticsError = ref(null);

// --- Données des filières ---
const filieres = ref([]);
const loadingFilieres = ref(true);
const filieresError = ref(null);
const searchQuery = ref('');

// --- Données pour la nouvelle filière (modale d'ajout) ---
const newFiliere = reactive({
  libfil: '',
});
const creatingFiliere = ref(false);

// --- Données pour la gestion des années (modale de gestion) ---
const selectedFiliere = ref(null); // La filière dont les années sont en cours de gestion
const listeAnnees = ref([]); // Toutes les années disponibles
const loadingAnnees = ref(true);
const anneesError = ref(null);
const selectedAnneeIds = ref([]); // IDs des années sélectionnées pour la filière courante
const syncingYears = ref(false); // État de chargement pour la synchronisation des années

// --- Modale de message personnalisée ---
const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info', // 'info', 'success', 'error', 'confirm'
  onConfirm: null,
});

const showMessage = (title, message, type = 'info', onConfirm = null) => {
  messageModal.title = title;
  messageModal.message = message;
  messageModal.type = type;
  messageModal.onConfirm = onConfirm;
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

const confirmAction = () => {
  if (messageModal.onConfirm) {
    messageModal.onConfirm();
  }
  hideMessage();
};

// --- Fonctions de récupération des données ---

const fetchStatistics = async () => {
  loadingStatistics.value = true;
  statisticsError.value = null;
  try {
    // Adapter cette API si votre backend fournit des statistiques plus détaillées pour les filières
    // Pour l'instant, on se contente du compte total des filières via l'API index
    const response = await axios.get('/api/filieres');
    statistics.value.total_filieres = response.data.length;
  } catch (error) {
    console.error('Erreur lors de la récupération des statistiques des filières :', error);
    statisticsError.value = 'Impossible de charger les statistiques des filières.';
  } finally {
    loadingStatistics.value = false;
  }
};

const fetchFilieres = async () => {
  loadingFilieres.value = true;
  filieresError.value = null;

  try {
    const response = await axios.get(`/api/filieres`);
    filieres.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des filières :', error);
    filieresError.value = 'Impossible de charger les filières.';
    filieres.value = [];
  } finally {
    loadingFilieres.value = false;
  }
};

const fetchAnnees = async () => {
  loadingAnnees.value = true;
  anneesError.value = null;
  try {
    const response = await axios.get('/api/annees');
    listeAnnees.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des années :', error);
    anneesError.value = 'Impossible de charger les années.';
    listeAnnees.value = [];
  } finally {
    loadingAnnees.value = false;
  }
};

// --- Computed properties pour l'affichage ---
const filteredFilieres = computed(() => {
  if (!Array.isArray(filieres.value)) {
    return [];
  }

  let filtered = filieres.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(filiere =>
      filiere.libfil.toLowerCase().includes(query)
    );
  }
  return filtered;
});

// --- Fonctions d'action ---

const filterFilieres = () => {
  // La propriété calculée `filteredFilieres` se mettra à jour automatiquement
};

const createFiliere = async () => {
  creatingFiliere.value = true;
  try {
    const response = await axios.post('/api/filieres', newFiliere);
    console.log('Filière créée avec succès:', response.data);
    showMessage('Succès', 'Filière créée avec succès !', 'success');

    // Réinitialiser le formulaire
    Object.assign(newFiliere, {
      libfil: '',
    });

    const modalElement = document.getElementById('addFiliereModal');
    const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
    bootstrapModal.hide();

    await fetchFilieres(); // Rafraîchir la liste des filières
    await fetchStatistics(); // Rafraîchir les statistiques

  } catch (error) {
    console.error('Erreur lors de la création de la filière :', error.response ? error.response.data : error.message);
    let errorMessage = 'Échec de la création de la filière.';
    if (error.response && error.response.data && typeof error.response.data === 'object') {
      errorMessage += '\n' + Object.values(error.response.data).map(e => Array.isArray(e) ? e.join(', ') : e).join('\n');
    } else if (error.response && error.response.data && typeof error.response.data === 'string') {
      errorMessage += '\n' + error.response.data;
    }
    showMessage('Erreur', errorMessage, 'error');
  } finally {
    creatingFiliere.value = false;
  }
};

const openManageYearsModal = (filiere) => {
  selectedFiliere.value = filiere;
  // Initialise les années sélectionnées avec celles déjà associées à cette filière
  selectedAnneeIds.value = filiere.filannee ? filiere.filannee.map(annee => annee.id) : [];

  const modalElement = document.getElementById('manageYearsModal');
  const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
  bootstrapModal.show();
};

const syncFiliereYears = async () => {
  if (!selectedFiliere.value) return;

  syncingYears.value = true;
  try {
    const response = await axios.post(`/api/filieres/${selectedFiliere.value.id}/sync-annees`, {
      annee_ids: selectedAnneeIds.value,
    });
    console.log('Années synchronisées avec succès:', response.data);
    showMessage('Succès', 'Années de la filière synchronisées avec succès !', 'success');

    const modalElement = document.getElementById('manageYearsModal');
    const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
    bootstrapModal.hide();

    await fetchFilieres(); // Rafraîchir la liste des filières pour mettre à jour les années affichées

  } catch (error) {
    console.error('Erreur lors de la synchronisation des années :', error.response ? error.response.data : error.message);
    let errorMessage = 'Échec de la synchronisation des années.';
    if (error.response && error.response.data && typeof error.response.data === 'object') {
      errorMessage += '\n' + Object.values(error.response.data).map(e => Array.isArray(e) ? e.join(', ') : e).join('\n');
    } else if (error.response && error.response.data && typeof error.response.data === 'string') {
      errorMessage += '\n' + error.response.data;
    }
    showMessage('Erreur', errorMessage, 'error');
  } finally {
    syncingYears.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
  fetchFilieres();
  fetchAnnees(); // Charge les années une fois au montage pour la modale de gestion

  // Assurez un nettoyage robuste des arrière-plans de modales lors de leur fermeture.
  // Cela gère les cas où Bootstrap pourrait ne pas nettoyer complètement le DOM.
  const modals = ['addFiliereModal', 'manageYearsModal', 'messageModal'];
  modals.forEach(modalId => {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', () => {
        // Supprime tous les éléments .modal-backdrop qui pourraient persister
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        // S'assure que la classe 'modal-open' est bien retirée du body
        // La classe peut rester si plusieurs modales sont ouvertes successivement
        document.body.classList.remove('modal-open');
      });
    }
  });
});
</script>

<style scoped>
/* Vos styles CSS restent inchangés et sont adaptés ici */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --success-green: #198754;
  --danger-red: #dc3545;
  --warning-orange: #ffc107;
}

.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
  min-height: 100vh;
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
}

.summary-cards {
  align-items: stretch;
}

.summary-cards .card {
  width: 200px;
  height: 150px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  border: 1px solid var(--border-color);
  background-color: white;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.summary-cards .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.15) !important;
}

.summary-cards .card i {
  font-size: 1.8em;
  margin-bottom: 0.5rem;
}

.summary-cards .card.total i {
  color: #0062ff;
}

.summary-cards .card.expired i {
  color: #ff1100;
}

.summary-cards .card.available i {
  color: #11b206;
}

.summary-cards .card p.fs-2 {
  font-size: 1.8em !important;
  line-height: 1.2;
}

.summary-cards .card p.text-secondary {
  font-size: 1.10em;
}

.filieres-list.filieres-section {
  margin-top: 2rem;
}

.filieres-list .list-header {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  background-color: var(--primary-blue);
  color: white;
}

.filieres-list .list-header {
  width: auto;
  flex-shrink: 0;
}

.filieres-list .list-header .search-input-group {
  max-width: 300px;
}

.filieres-list .list-header {
  background-color: white;
  border: 1px solid var(--border-color);
  color: var(--dark-text);
  padding-left: .75rem;
}
.filieres-list .list-header {
  color: var(--secondary-text);
}
.filieres-list .list-header {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.filieres-list .list-header {
  border-color: var(--border-color);
  color: var(--dark-text);
  background-color: white;
}

.filieres-table-container {
  border-top: 1px solid var(--border-color);
  background-color: white;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead th {
  background-color: var(--light-gray-bg);
  color: var(--dark-text);
  font-weight: bold;
  border-top: none;
  border-bottom: 1px solid var(--border-color);
}

.table tbody tr {
  background-color: white;
}

.table tbody tr td {
  border-bottom: 1px solid var(--border-color);
  color: var(--dark-text);
  vertical-align: middle;
  padding: 0.75rem 1rem;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.status.badge {
  padding: 0.4em 0.8em;
  font-size: 0.95em;
  font-weight: 600;
  display: inline-block;
}

.btn-success, .btn-danger, .btn-info {
  font-size: 0.85rem;
  padding: 0.45rem 0.9rem;
  font-weight: 500;
}

.btn-info {
  background-color: #0dcaf0 !important;
  border-color: #0dcaf0 !important;
}
.btn-info:hover {
  background-color: #31d2f2 !important;
  border-color: #25cff2 !important;
}

.bg-success-subtle { background-color: #d1e7dd !important; }
.text-success { color: var(--success-green) !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
.text-danger { color: var(--danger-red) !important; }
.bg-warning-subtle { background-color: #fff3cd !important; }
.text-warning { color: var(--warning-orange) !important; }
.bg-secondary-subtle { background-color: #e2e3e5 !important; }
.text-secondary { color: var(--secondary-text) !important; }
</style>
