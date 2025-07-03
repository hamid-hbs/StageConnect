<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Nos offres</h1>
      </span>
      <button class="btn btn-primary add-offer-page-btn" data-bs-toggle="modal" data-bs-target="#addOfferModal">Ajouter une offre</button>
    </div>

    <section class="offers-summary p-4 rounded shadow-sm mb-4" style="background-color: rgba(91, 168, 245, 0.771);">
      <div v-if="loadingStatistics" class="text-center text-secondary">
        Chargement des statistiques...
      </div>
      <div v-else-if="statisticsError" class="text-center text-danger">
        Erreur lors du chargement des statistiques : {{ statisticsError }}
      </div>
      <div v-else class="summary-cards d-flex justify-content-around flex-wrap gap-3">
        <div class="card total p-3 rounded shadow-sm text-center">
          <i class="fa-regular fa-circle-check" style="color: #0062ff;"></i>
          <p class="fs-2 fw-bold mb-0">{{ statistics.total_offres }}</p>
          <p class="text-secondary mb-0">Total</p>
        </div>
        <div class="card expired p-3 rounded shadow-sm text-center">
          <i class="fa-regular fa-circle-xmark" style="color: #ff1100;"></i>
          <p class="fs-2 fw-bold mb-0">{{ statistics.expired_offres }}</p>
          <p class="text-secondary mb-0">Expirées</p>
        </div>
        <div class="card available p-3 rounded shadow-sm text-center">
          <i class="fa-regular fa-clock" style="color: #11b206;"></i>
          <p class="fs-2 fw-bold mb-0">{{ statistics.active_offres }}</p>
          <p class="text-secondary mb-0">Disponibles</p>
        </div>
      </div>
    </section>

    <section class="offers-list offers-section">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center">
        <h3 class="mb-0 fs-5">Listes des offres</h3>

        <div class="d-flex align-items-center">
          <div class="input-group search-input-group me-3">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher une offre" v-model="searchQuery" @input="filterOffers">
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              {{ filterStatus === '' ? 'Filtrer par statut' : displayFilterStatus }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterStatusDropdown">
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('')">Tous les statuts</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('active')">Disponible</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('expiree')">Expirée</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="loadingOffers" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Chargement des offres...
      </div>
      <div v-else-if="offersError" class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-danger border-start border-end border-bottom">
        Erreur lors du chargement des offres : {{ offersError }}
      </div>
      <div v-else-if="Array.isArray(filteredOffers) && filteredOffers.length > 0" class="offers-table-container border-start border-end border-bottom rounded-bottom">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Titre de l'offre</th>
              <th scope="col">Date d'expiration</th>
              <th scope="col">Durée</th>
              <th scope="col">Statut</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="offer in filteredOffers" :key="offer.id">
              <td>{{ offer.id }}</td>
              <td>
                <router-link :to="{ name: 'entreprise-offre-details', params: { id: offer.id } }" class="text-primary fw-bold">
                  {{ offer.titre || 'N/A'}}
                </router-link>
              </td>
              <td>{{ formatDate(offer.date_expiration) }}</td>
              <td>{{ offer.duree_en_semaines ? offer.duree_en_semaines + ' semaines' : 'N/A' }}</td>
              <td>
                <span :class="['status', 'badge', 'fs-6', getStatusBadgeClass(offer.statut, offer.date_expiration)]">
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
      <div v-else class="bg-white p-3 rounded-bottom shadow-sm mt-0 text-center text-muted border-start border-end border-bottom">
        Aucune offre trouvée pour votre recherche.
      </div>
    </section>

    <div class="modal fade" id="addOfferModal" tabindex="-1" aria-labelledby="addOfferModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
              <h1 class="modal-title fs-5" id="addOfferModalLabel">Ajouter une nouvelle offre</h1>
            </span>
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
                <div class="col-md-6 mb-3">
                  <label for="offerDomain" class="form-label">Domaine d'activité</label>
                  <select class="form-select" id="offerDomain" v-model="newOffer.domaine_id" required>
                    <option value="">Sélectionnez un domaine</option>
                    <option v-for="domaine in listeDomaines" :key="domaine.id" :value="domaine.id">
                      {{ domaine.libdomaine }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="offerAddress" class="form-label">Adresse</label>
                  <input type="text" class="form-control" id="offerAddress" v-model="newOffer.adresse" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="offerStartDate" class="form-label">Date de début du stage</label>
                  <input type="date" class="form-control" id="offerStartDate" v-model="newOffer.date_debut" :min="todayDate" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="offerExpirationDate" class="form-label">Date d'expiration de l'offre</label>
                  <input type="date" class="form-control" id="offerExpirationDate" v-model="newOffer.date_expiration" :min="todayDate" :max="minExpirationDate" required>
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

// --- Données des statistiques ---
const statistics = ref({
  total_offres: 0,
  active_offres: 0,
  expired_offres: 0,
});
const loadingStatistics = ref(true);
const statisticsError = ref(null);

// --- Données des offres ---
const offers = ref([]);
const loadingOffers = ref(true);
const offersError = ref(null);
const searchQuery = ref('');
const filterStatus = ref(''); // 'active', 'expiree', ou '' pour tous

// --- Données pour la nouvelle offre (modale) ---
const newOffer = reactive({
  titre: '',
  description: '',
  domaine_id: '',
  adresse: '',
  date_debut: '',
  date_expiration: '',
  duree_en_semaines: null,
  statut: 'active',
});
const creatingOffer = ref(false);
const listeDomaines = ref([]);

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
  messageModal.onConfirm = onConfirm; // Assigner la fonction de confirmation
  const modalElement = document.getElementById('messageModal');
  // Obtenir l'instance existante ou en créer une nouvelle
  const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
  bootstrapModal.show();
};

const hideMessage = () => {
  const modalElement = document.getElementById('messageModal');
  const bootstrapModal = Modal.getInstance(modalElement) || new Modal(modalElement);
  bootstrapModal.hide();
  // Ne pas réinitialiser isVisible ici, l'événement 'hidden.bs.modal' le fera
};

const confirmAction = () => {
  if (messageModal.onConfirm) {
    messageModal.onConfirm();
  }
  hideMessage();
};


// --- Computed properties pour les dates dans la modale ---
const todayDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const minExpirationDate = computed(() => {
  // La date d'expiration de l'offre (date limite pour postuler)
  // devrait être au plus tard la date de début du stage.
  // Si date_debut est vide, la date d'expiration peut être n'importe quand après aujourd'hui.
  if (newOffer.date_debut) {
    return new Date(newOffer.date_debut).toISOString().split('T')[0];
  }
  return ''; // Pas de limite supérieure si date_debut n'est pas définie
});


// --- Fonctions de récupération des données ---

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
    // L'API mesOffres renvoie directement un tableau, pas un objet avec une propriété 'data'
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

// --- Computed properties pour l'affichage ---

const filteredOffers = computed(() => {
  if (!Array.isArray(offers.value)) {
    return [];
  }

  let filtered = [...offers.value]; // Crée une copie pour ne pas modifier l'original

  if (filterStatus.value) {
    if (filterStatus.value === 'expiree') {
      filtered = filtered.filter(offer => {
        // Une offre est 'expiree' si son statut est 'expiree' OU si sa date d'expiration est passée
        return offer.statut === 'expiree' || isExpired(offer.date_expiration);
      });
    } else if (filterStatus.value === 'active') {
      filtered = filtered.filter(offer => {
        // Une offre est 'active' si son statut est 'active' ET sa date d'expiration n'est pas passée
        return offer.statut === 'active' && !isExpired(offer.date_expiration);
      });
    }
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(offer =>
      offer.titre.toLowerCase().includes(query)
    );
  }

  // Tri par date de création (du plus récent au plus ancien)
  // Assurez-vous que votre objet 'offer' a une propriété 'created_at' ou similaire
  filtered.sort((a, b) => {
    // Supposons que 'created_at' est au format ISO 8601
    const dateA = new Date(a.created_at);
    const dateB = new Date(b.created_at);
    return dateB.getTime() - dateA.getTime(); // Pour un tri descendant (plus récent en premier)
  });

  return filtered;
});

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
    const expires = new Date(cleanDateString);
    const now = new Date();
    expires.setHours(0, 0, 0, 0); // Réinitialiser l'heure pour comparer seulement la date
    now.setHours(0, 0, 0, 0);     // Réinitialiser l'heure pour comparer seulement la date
    return expires < now;
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
    return 'bg-danger-subtle text-danger';
  }
  return 'bg-success-subtle text-success';
};

const displayFilterStatus = computed(() => {
  switch (filterStatus.value) {
    case 'active': return 'Disponible';
    case 'expiree': return 'Expirée'; // Correction: 'Expirée' au lieu de 'Expiré' pour la cohérence
    default: return 'Filtrer par statut';
  }
});

// --- Fonctions d'action ---

const setFilterStatus = (status) => {
  filterStatus.value = status;
};

const filterOffers = () => {
  // La propriété calculée filteredOffers gère déjà le filtrage en temps réel
  // Cette fonction est juste un déclencheur si l'on voulait faire autre chose ici.
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
    const response = await axios.put(`/api/offres/${id}`, {
      statut: 'expiree'
    });
    showMessage('Succès', response.data.message || 'Offre désactivée avec succès !', 'success');
    await fetchOffers(); // Rafraîchir la liste des offres
    await fetchStatistics(); // Rafraîchir les statistiques
  } catch (error) {
    console.error('Erreur lors de la désactivation de l\'offre :', error);
    let errorMessage = 'Échec de la désactivation de l\'offre.';
    if (error.response && error.response.data) {
      if (typeof error.response.data === 'object' && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (typeof error.response.data === 'object') {
        // Concaténer les messages d'erreur de validation si présents
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
    newOffer.statut = 'active'; // Les nouvelles offres sont toujours actives

    const response = await axios.post('/api/offres', newOffer);
    showMessage('Succès', response.data.message || 'Offre créée avec succès !', 'success');

    // Réinitialiser le formulaire
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

    await fetchOffers(); // Rafraîchir la liste des offres
    await fetchStatistics(); // Rafraîchir les statistiques

  } catch (error) {
    console.error('Erreur lors de la création de l\'offre :', error.response ? error.response.data : error.message);
    let errorMessage = 'Échec de la création de l\'offre.';
    if (error.response && error.response.data) {
      if (typeof error.response.data === 'object' && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (typeof error.response.data === 'object') {
        // Concaténer les messages d'erreur de validation si présents
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

  // Gérer le nettoyage des backdrops des modales Bootstrap
  const modals = ['addOfferModal', 'messageModal'];
  modals.forEach(modalId => {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', () => {
        // Supprimer manuellement le backdrop si Bootstrap ne le fait pas correctement
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        // S'assurer que la classe 'modal-open' est retirée du body
        document.body.classList.remove('modal-open');
        // Si c'est la modale de message, réinitialiser son état visible
        if (modalId === 'messageModal') {
          messageModal.isVisible = false;
          // Détruire l'instance de la modale pour éviter les fuites de mémoire
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
/* Vos styles CSS restent inchangés */
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

.offers-list.offers-section {
  margin-top: 2rem;
}

.offers-list .list-header {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  background-color: var(--primary-blue);
  color: white;
}

.offers-list .list-header{
  width: auto;
  flex-shrink: 0;
}

.offers-list .list-header .search-input-group {
  max-width: 300px;
}

.offers-list .list-header {
  background-color: white;
  border: 1px solid var(--border-color);
  color: var(--dark-text);
  padding-left: .75rem;
}
.offers-list .list-header{
  color: var(--secondary-text);
}
.offers-list .list-header{
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.offers-list .list-header {
  border-color: var(--border-color);
  color: var(--dark-text);
  background-color: white;
}

.offers-list .list-header .dropdown-menu {
  background-color: white;
  border: 1px solid rgba(0, 0, 0, 0.15);
}
.offers-list .list-header .dropdown-item {
  color: var(--dark-text);
  background-color:   white;
}
.offers-list .list-header .dropdown-item:hover,
.offers-list .list-header .dropdown-item:focus {
  background-color: #0d6efd;
  color: white;
}

.offers-table-container {
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

.btn-success, .btn-danger,.btn-outline-danger .btn-info {
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
