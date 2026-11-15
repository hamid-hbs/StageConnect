<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row">
        <h1 class="text-dark mb-0 fs-3">Nos offres</h1>
      <button class="btn btn-primary mt-2 mt-md-0" data-bs-toggle="modal" data-bs-target="#addOfferModal">
        <i class="fas fa-plus me-2"></i>Ajouter une offre
      </button>
    </div>

    <section class="offers-summary p-4 rounded shadow-sm mb-4 bg-primary bg-opacity-10">
      <div v-if="loadingStatistics" class="text-center text-secondary">
        Chargement des statistiques...
      </div>
      <div v-else-if="statisticsError" class="text-center text-danger">
        Erreur lors du chargement des statistiques : {{ statisticsError }}
      </div>
      <div v-else class="d-flex justify-content-around flex-wrap gap-3 flex-column flex-md-row">
  <div class="card p-3 rounded shadow-sm text-center bg-white flex-grow-1">
    <div class="d-flex justify-content-center mb-2">
      <i class="fa-regular fa-circle-check text-primary fs-4"></i>
    </div>
    <p class="fs-2 fw-bold mb-0">{{ statistics.total_offres }}</p>
    <p class="text-secondary mb-0">Total</p>
  </div>

  <div class="card p-3 rounded shadow-sm text-center bg-white flex-grow-1">
    <div class="d-flex justify-content-center mb-2">
      <i class="fa-regular fa-circle-xmark text-danger fs-4"></i>
    </div>
    <p class="fs-2 fw-bold mb-0">{{ statistics.expired_offres }}</p>
    <p class="text-secondary mb-0">Expirées</p>
  </div>

  <div class="card p-3 rounded shadow-sm text-center bg-white flex-grow-1">
    <div class="d-flex justify-content-center mb-2">
      <i class="fa-regular fa-clock text-success fs-4"></i>
    </div>
    <p class="fs-2 fw-bold mb-0">{{ statistics.active_offres }}</p>
    <p class="text-secondary mb-0">Disponibles</p>
  </div>
</div>

    </section>

    <section class="offers-list">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center flex-column flex-md-row">
        <h3 class="mb-0 mb-2 mb-md-0 fs-5">Listes des offres</h3>
        <div class="d-flex align-items-center flex-column flex-md-row">
          <div class="input-group input-group-sm me-0 me-md-3 mb-2 mb-md-0 w-100 w-md-auto search-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" class="form-control border-start-0 search-input" placeholder="Rechercher une offre" v-model="searchQuery" @input="filterOffers">
            <button v-if="searchQuery" class="btn btn-outline-secondary clear-search" type="button" @click="searchQuery = ''">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="dropdown w-100 w-md-auto">
            <button class="btn btn-sm filter-btn dropdown-toggle" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-filter me-1"></i>{{ filterStatus === '' ? 'Filtrer par statut' : displayFilterStatus }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end filter-menu" aria-labelledby="filterStatusDropdown">
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('')">Tous les statuts</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('active')">Disponible</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('expiree')">Expirée</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="loadingOffers" class="bg-white p-3 rounded-bottom shadow-sm text-center text-muted border-start border-end border-bottom">
        Chargement des offres...
      </div>
      <div v-else-if="offersError" class="bg-white p-3 rounded-bottom shadow-sm text-center text-danger border-start border-end border-bottom">
        Erreur lors du chargement des offres : {{ offersError }}
      </div>
      <div v-else-if="Array.isArray(filteredOffers) && filteredOffers.length > 0" class="border-start border-end border-bottom rounded-bottom bg-white">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" class="d-none d-md-table-cell">#</th>
              <th scope="col">Titre de l'offre</th>
              <th scope="col" class="d-none d-md-table-cell">Date d'expiration</th>
              <th scope="col" class="d-none d-md-table-cell">Durée</th>
              <th scope="col">Statut</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="offer in filteredOffers" :key="offer.id">
              <td class="d-none d-md-table-cell">{{ offer.id }}</td>
              <td>
                <router-link :to="{ name: 'entreprise-offre-details', params: { id: offer.id } }" class="text-primary fw-bold">
                  {{ offer.titre || 'N/A' }}
                </router-link>
              </td>
              <td class="d-none d-md-table-cell">{{ formatDate(offer.date_expiration) }}</td>
              <td class="d-none d-md-table-cell">{{ offer.duree_en_semaines ? offer.duree_en_semaines + ' semaines' : 'N/A' }}</td>
              <td>
                <span :class="['badge', getStatusBadgeClass(offer.statut, offer.date_expiration)]">
                  {{ getDisplayStatus(offer.statut, offer.date_expiration) }}
                </span>
              </td>
              <td>
                <button
                  v-if="offer.statut === 'active' && !isExpired(offer.date_expiration)"
                  class="btn btn-outline-danger btn-sm"
                  @click="confirmDeactivation(offer.id)"
                >
                  Désactiver
                </button>
                <button v-else class="btn btn-danger btn-sm" disabled>Aucune</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="bg-white p-3 rounded-bottom shadow-sm text-center text-muted border-start border-end border-bottom">
        Aucune offre trouvée pour votre recherche.
      </div>
    </section>

    <div class="modal fade" id="addOfferModal" tabindex="-1" aria-labelledby="addOfferModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
              <h1 class="modal-title fs-5" id="addOfferModalLabel">Ajouter une nouvelle offre</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="createOffer">
            <div class="modal-body">
              <div class="mb-3">
                <label for="offerTitle" class="form-label">Titre de l'offre</label>
                <input type="text" class="form-control" id="offerTitle" v-model="newOffer.titre" required>
              </div>
              <div class="mb-3">
                <label for="offerDescription" class="form-label">Description</label>
                <textarea class="form-control" id="offerDescription" v-model="newOffer.description" rows="5" required></textarea>
              </div>
              <div class="row">
                <div class="col-12 col-md-6 mb-3">
                  <label for="offerDomain" class="form-label">Domaine d'activité</label>
                  <select class="form-select" id="offerDomain" v-model="newOffer.domaine_id" required>
                    <option value="">Sélectionnez un domaine</option>
                    <option v-for="domaine in listeDomaines" :key="domaine.id" :value="domaine.id">
                      {{ domaine.libdomaine }}
                    </option>
                  </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                  <label for="offerAddress" class="form-label">Adresse</label>
                  <input type="text" class="form-control" id="offerAddress" v-model="newOffer.adresse" required>
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-md-6 mb-3">
                  <label for="offerExpirationDate" class="form-label">Date d'expiration de l'offre</label>
                  <input
                    type="date"
                    class="form-control"
                    id="offerExpirationDate"
                    v-model="newOffer.date_expiration"
                    :min="minExpirationDateForOffer"
                    :max="maxExpirationDateForOffer"
                    required
                  >
                </div>
                <div class="col-12 col-md-6 mb-3">
                  <label for="offerStartDate" class="form-label">Date de début du stage</label>
                  <input
                    type="date"
                    class="form-control"
                    id="offerStartDate"
                    v-model="newOffer.date_debut"
                    :min="minStartDateForStage"
                    required
                  >
                </div>
              </div>
              <div class="mb-3">
                <label for="offerDuration" class="form-label">Durée en semaines</label>
                <input type="number" class="form-control" id="offerDuration" v-model="newOffer.duree_en_semaines" min="1">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="creatingOffer">
                {{ creatingOffer ? 'Création...' : 'Créer l\'offre' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

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
import axios from '../axios';
import { Modal } from 'bootstrap';
import { useRouter } from 'vue-router';

const router = useRouter();

const statistics = ref({
  total_offres: 0,
  active_offres: 0,
  expired_offres: 0,
});
const loadingStatistics = ref(true);
const statisticsError = ref(null);
const offers = ref([]);
const loadingOffers = ref(true);
const offersError = ref(null);
const searchQuery = ref('');
const filterStatus = ref('');
const newOffer = reactive({
  titre: '',
  description: '',
  domaine_id: '',
  adresse: '',
  date_expiration: '',
  date_debut: '',
  duree_en_semaines: null,
  statut: 'active',
});
const creatingOffer = ref(false);
const listeDomaines = ref([]);
const messageModal = reactive({
  isVisible: false,
  title: '',
  message: '',
  type: 'info',
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
};

const confirmAction = () => {
  if (messageModal.onConfirm) {
    messageModal.onConfirm();
  }
  hideMessage();
};

const todayDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const minExpirationDateForOffer = computed(() => {
  return todayDate.value;
});

const maxExpirationDateForOffer = computed(() => {
  if (newOffer.date_debut) {
    return newOffer.date_debut;
  }
  const maxDate = new Date();
  maxDate.setFullYear(maxDate.getFullYear() + 1);
  return maxDate.toISOString().split('T')[0];
});

const minStartDateForStage = computed(() => {
  if (newOffer.date_expiration) {
    return newOffer.date_expiration;
  }
  return todayDate.value;
});

const fetchStatistics = async () => {
  loadingStatistics.value = true;
  statisticsError.value = null;
  try {
    const response = await axios.get('/api/get-offer-statistics');
    statistics.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des statistiques :', error);
    statisticsError.value = 'Impossible de charger les statistiques.';
  } finally {
    loadingStatistics.value = false;
  }
};

const fetchOffers = async () => {
  loadingOffers.value = true;
  offersError.value = null;
  try {
    const response = await axios.get(`/api/mes-offres`);
    offers.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des offres :', error);
    offersError.value = 'Impossible de charger les offres.';
    offers.value = [];
  } finally {
    loadingOffers.value = false;
  }
};

const fetchDomaines = async () => {
  try {
    const response = await axios.get('/api/domaines');
    listeDomaines.value = response.data;
  } catch (error) {
    console.error('Erreur lors de la récupération des domaines :', error);
    listeDomaines.value = [];
  }
};

const filteredOffers = computed(() => {
  if (!Array.isArray(offers.value)) {
    return [];
  }
  let filtered = [...offers.value];
  if (filterStatus.value) {
    if (filterStatus.value === 'expiree') {
      filtered = filtered.filter(offer => offer.statut === 'expiree' || isExpired(offer.date_expiration));
    } else if (filterStatus.value === 'active') {
      filtered = filtered.filter(offer => offer.statut === 'active' && !isExpired(offer.date_expiration));
    }
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(offer => offer.titre.toLowerCase().includes(query));
  }
  filtered.sort((a, b) => {
    const dateA = new Date(a.created_at);
    const dateB = new Date(b.created_at);
    return dateB.getTime() - dateA.getTime();
  });
  return filtered;
});

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
    const expires = new Date(cleanDateString);
    const now = new Date();
    expires.setHours(0, 0, 0, 0);
    now.setHours(0, 0, 0, 0);
    return expires < now;
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
    return 'bg-danger-subtle text-danger';
  }
  return 'bg-success-subtle text-success';
};

const displayFilterStatus = computed(() => {
  switch (filterStatus.value) {
    case 'active': return 'Disponible';
    case 'expiree': return 'Expirée';
    default: return 'Filtrer par statut';
  }
});

const setFilterStatus = (status) => {
  filterStatus.value = status;
};

const filterOffers = () => {
  // Handled by computed property filteredOffers
};

const confirmDeactivation = (id) => {
  showMessage(
    'Confirmation de désactivation',
    'Êtes-vous sûr de vouloir désactiver cette offre ? Elle passera en statut "Expirée".',
    'confirm',
    () => deactivateOffer(id)
  );
};

const deactivateOffer = async (id) => {
  try {
    const response = await axios.put(`/api/offres/${id}`, { statut: 'expiree' });
    showMessage('Succès', response.data.message || 'Offre désactivée avec succès !', 'success');
    await fetchOffers();
    await fetchStatistics();
  } catch (error) {
    console.error('Erreur lors de la désactivation de l\'offre :', error);
    let errorMessage = 'Échec de la désactivation de l\'offre.';
    if (error.response && error.response.data) {
      if (typeof error.response.data === 'object' && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (typeof error.response.data === 'object') {
        errorMessage += '\n' + Object.values(error.response.data).map(e => Array.isArray(e) ? e.join(', ') : e).join('\n');
      } else if (typeof error.response.data === 'string') {
        errorMessage += '\n' + error.response.data;
      }
    }
    showMessage('Erreur', errorMessage, 'error');
  }
};

const createOffer = async () => {
  creatingOffer.value = true;
  try {
    newOffer.statut = 'active';
    const expDate = new Date(newOffer.date_expiration);
    const startDate = new Date(newOffer.date_debut);
    const today = new Date(todayDate.value);
    expDate.setHours(0, 0, 0, 0);
    startDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    if (expDate < today) {
      showMessage('Erreur de Date', "La date d'expiration de l'offre ne peut pas être antérieure à aujourd'hui.", 'error');
      creatingOffer.value = false;
      return;
    }
    if (startDate < expDate) {
      showMessage('Erreur de Date', "La date de début du stage ne peut pas être antérieure à la date d'expiration de l'offre.", 'error');
      creatingOffer.value = false;
      return;
    }
    const response = await axios.post('/api/offres', newOffer);
    showMessage('Succès', response.data.message || 'Offre créée avec succès !', 'success');
    Object.assign(newOffer, {
      titre: '',
      description: '',
      domaine_id: '',
      adresse: '',
      date_debut: '',
      date_expiration: '',
      duree_en_semaines: null,
      statut: 'active',
    });
    const modalElement = document.getElementById('addOfferModal');
    const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
    bootstrapModal.hide();
    await fetchOffers();
    await fetchStatistics();
  } catch (error) {
    console.error('Erreur lors de la création de l\'offre :', error.response ? error.response.data : error.message);
    let errorMessage = 'Échec de la création de l\'offre.';
    if (error.response && error.response.data) {
      if (typeof error.response.data === 'object' && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (typeof error.response.data === 'object') {
        errorMessage += '\n' + Object.values(error.response.data).map(e => Array.isArray(e) ? e.join(', ') : e).join('\n');
      } else if (typeof error.response.data === 'string') {
        errorMessage += '\n' + error.response.data;
      }
    }
    showMessage('Erreur', errorMessage, 'error');
  } finally {
    creatingOffer.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
  fetchOffers();
  fetchDomaines();
  const modals = ['addOfferModal', 'messageModal'];
  modals.forEach(modalId => {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        if (modalId === 'messageModal') {
          messageModal.isVisible = false;
          const bootstrapModal = Modal.getInstance(modalElement);
          if (bootstrapModal) {
            bootstrapModal.dispose();
          }
        }
      });
    }
  });
});
</script>

<style scoped>
/* General Layout */
.content-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
  min-height: 100vh;
  background-color: #f8f9fa;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header Section */
.content-page > .d-flex {
  align-items: center;
  margin-bottom: 1.5rem;
}

.content-page h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
  position: relative;
  padding-bottom: 0.5rem;
}

.content-page h1::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 80px;
  height: 3px;
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 1.5px;
}

.content-page .btn-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.content-page .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

/* Offers Summary */
.offers-summary {
  background-color: rgba(13, 110, 253, 0.1);
  border-radius: 0.75rem;
  padding: 1.25rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.offers-summary .card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.5rem;
  padding: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.offers-summary .card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
  border: 1px solid transparent;
  background: linear-gradient(to right, #fff, #fff) padding-box;
}

.offers-summary .card i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.offers-summary .card p.fs-2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
}

.offers-summary .card p.text-secondary {
  font-size: 0.9rem;
  font-weight: 500;
  color: #6c757d;
}

/* Offers List */
.offers-list .list-header {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 0.75rem 0.75rem 0 0;
  padding: 1rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.offers-list .list-header h3 {
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
  color: #0d6efd;
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
.table {
  border-collapse: separate;
  border-spacing: 0;
  background-color: #fff;
  border-radius: 0 0 0.75rem 0.75rem;
  overflow: hidden;
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

.table .btn-danger[disabled] {
  background: linear-gradient(to right, #dc3545, #b02a37);
  border: none;
  opacity: 0.6;
  cursor: not-allowed;
}

/* Badges */
.bg-success-subtle { background-color: #e6f4ea !important; }
.text-success { color: #20a23e !important; }
.bg-danger-subtle { background-color: #fce4e6 !important; }
.text-danger { color: #dc3545 !important; }

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
  background: #f8f9fa;
  border-radius: 1rem 1rem 0 0;
}

.modal-header h5,
.modal-header h1.fs-5 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.modal-header .btn-close {
  filter: invert(0.4);
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

.modal-body .form-control,
.modal-body .form-select {
  border-radius: 0.5rem;
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  font-size: 0.9rem;
  background-color: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
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

.modal-footer .btn-danger {
  background: linear-gradient(to right, #dc3545, #b02a37);
  border: none;
}

.modal-footer .btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.2);
}

.modal-footer .btn-secondary {
  background: #6c757d;
  border: none;
}

.modal-footer .btn-secondary:hover {
  background: #5c636a;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 1rem;
  }

  .content-page h1 {
    font-size: 1.5rem;
  }

  .offers-summary {
    padding: 1rem;
  }

  .offers-summary .card {
    max-width: 100%;
    margin: 0.5rem 0;
    padding: 0.75rem;
  }

  .offers-summary .card p.fs-2 {
    font-size: 1.5rem;
  }

  .offers-summary .card i {
    font-size: 1.75rem;
  }

  .offers-list .list-header {
    padding: 0.75rem;
  }

  .offers-list .list-header h3 {
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

  .table thead th {
    font-size: 0.85rem;
  }

  .table tbody td {
    font-size: 0.85rem;
    padding: 0.5rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-header h5,
  .modal-header h1.fs-5 {
    font-size: 1.1rem;
  }

  .modal-body .form-control,
  .modal-body .form-select {
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

  .content-page .btn-primary {
    width: 100%;
    margin-top: 0.75rem;
  }

  .offers-summary .card {
    max-width: 100%;
  }

  .offers-list .list-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .offers-list .list-header .input-group,
  .offers-list .dropdown {
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

  .modal-body .form-control,
  .modal-body .form-select {
    font-size: 0.85rem;
    padding: 0.4rem;
  }

  .modal-footer .btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}
</style>