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

    <section class="offers-list-header bg-primary text-white p-3 rounded-top d-flex flex-column flex-md-row justify-content-between align-items-center mb-0">
      <h3 class="mb-2 mb-md-0 fs-5 text-white">Offres de Stages Disponibles</h3>
      <div class="d-flex align-items-center flex-column flex-md-row">
        <div class="input-group input-group-sm me-0 me-md-3 mb-2 mb-md-0 w-100 w-md-auto search-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-search text-gray-400"></i>
          </span>
          <input type="text" class="form-control border-start-0 search-input" placeholder="Rechercher par titre, description..." v-model="searchQuery" @input="applyFilters">
          <button v-if="searchQuery" class="btn btn-outline-secondary clear-search" type="button" @click="searchQuery = ''">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="dropdown w-100 w-md-auto">
          <button class="btn btn-sm filter-btn dropdown-toggle" type="button" id="filterDomaineDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-filter me-1"></i>{{ selectedDomaineName || 'Filtrer par domaine' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end filter-menu" aria-labelledby="filterDomaineDropdown">
            <li><a class="dropdown-item" href="#" @click.prevent="setFilterDomaine(null, '')">Tous les domaines</a></li>
            <li v-for="domaine in domainesList" :key="domaine.id">
              <a class="dropdown-item" href="#" @click.prevent="setFilterDomaine(domaine.id, domaine.libdomaine)">
                {{ domaine.libdomaine }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <div v-if="isLoadingOffers" class="d-flex justify-content-center align-items-center bg-white p-5 rounded-bottom shadow-sm">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement des offres...</span>
      </div>
      <p class="ms-3 text-muted">Chargement des offres de stage...</p>
    </div>

    <div v-else-if="errorMessage" class="alert alert-danger text-center p-4 rounded-bottom shadow-sm">
      {{ errorMessage }}
    </div>

    <div v-else-if="filteredOffers.length > 0" class="offers-grid p-4 bg-white rounded-bottom shadow-sm">
      <div v-for="offer in paginatedOffers" :key="offer.id" class="card offer-card mb-3">
        <div class="card-body">
          <h5 class="card-title text-primary">{{ offer.titre }}</h5>
          <h6 class="card-subtitle mb-2 text-muted">
            <router-link :to="{ name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: offer?.entreprise?.id } }">
              <span class="fw-bold">{{ offer.entreprise.nom_entreprise }}</span>
            </router-link> - {{ offer.domaine.libdomaine }}
          </h6>
          <p class="card-text text-truncate-3">{{ offer.description }}</p>
          <ul class="list-unstyled offer-details">
            <li><i class="fas fa-map-marker-alt me-2 text-secondary"></i> {{ offer.ville ? offer.ville.nom_ville : offer.adresse }}</li>
            <li><i class="fas fa-calendar-alt me-2 text-secondary"></i> {{ offer.duree_en_semaines }} semaines</li>
          </ul>
          <router-link :to="{ name: 'etudiant-offre-details', params: { id: offer.id } }" class="btn btn-outline-primary btn-sm">Détails</router-link>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info text-center p-4 rounded-bottom shadow-sm">
      Aucune offre de stage trouvée pour le moment ou ne correspondant à vos critères de recherche.
    </div>

    <!-- Pagination -->
    <nav v-if="filteredOffers.length > itemsPerPage" class="d-flex justify-content-center mt-4">
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios'; // Assurez-vous que le chemin est correct pour votre instance axios
import { Toast } from 'bootstrap';

// --- Variables Réactives ---
const offers = ref([]); // Toutes les offres de stage chargées
const domainesList = ref([]); // Liste des domaines d'activité pour le filtre

const searchQuery = ref(''); // Texte de recherche
const selectedDomaineId = ref(null); // ID du domaine sélectionné pour le filtre
const selectedDomaineName = ref(''); // Nom du domaine sélectionné pour l'affichage

const isLoadingOffers = ref(true); // Indique si les offres sont en cours de chargement
const errorMessage = ref(''); // Message d'erreur à afficher en cas de problème

const toastMessage = ref('');
const toastType = ref('success');

// Pagination variables
const currentPage = ref(1);
const itemsPerPage = ref(6);

// --- Propriétés Calculées (Filtres et Pagination) ---
const filteredOffers = computed(() => {
  let filtered = offers.value;

  // Filtrer par recherche textuelle (titre ou description)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(offer =>
      offer.titre.toLowerCase().includes(query) ||
      offer.description.toLowerCase().includes(query)
    );
  }

  // Filtrer par domaine d'activité
  if (selectedDomaineId.value) {
    filtered = filtered.filter(offer => offer.domaine_id === selectedDomaineId.value);
  }

  return filtered;
});

const totalPages = computed(() => Math.ceil(filteredOffers.value.length / itemsPerPage.value));

const pageNumbers = computed(() => {
  const pages = [];
  for (let i = 1; i <= totalPages.value; i++) {
    pages.push(i);
  }
  return pages;
});

const paginatedOffers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredOffers.value.slice(start, end);
});

const setPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// --- Fonctions Utilitaires (Toasts) ---
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

// --- Fonctions de Filtrage ---
const setFilterDomaine = (id, name) => {
  selectedDomaineId.value = id;
  selectedDomaineName.value = name;
  currentPage.value = 1; // Reset to first page when filter changes
};

const applyFilters = () => {
  currentPage.value = 1; // Reset to first page when search query changes
};

// --- Chargement Initial des Données ---
onMounted(async () => {
  try {
    isLoadingOffers.value = true;
    errorMessage.value = '';

    // 1. Charger les offres de stage
    const offersResponse = await axios.get('/api/offres'); // Assurez-vous que c'est le bon endpoint
    offers.value = offersResponse.data;

    // 2. Charger la liste des domaines pour le filtre
    const domainesResponse = await axios.get('/api/domaines');
    domainesList.value = domainesResponse.data;

  } catch (error) {
    console.error('Erreur lors du chargement des données :', error);
    if (error.response && error.response.status === 403) {
      errorMessage.value = 'Vous n\'êtes pas autorisé à voir ces offres ou votre profil est incomplet. Veuillez vous assurer d\'être connecté avec un rôle approprié.';
    } else {
      errorMessage.value = 'Une erreur est survenue lors du chargement des offres de stage. Veuillez réessayer plus tard.';
    }
    displayToast('Échec du chargement des offres.', 'danger');
  } finally {
    isLoadingOffers.value = false;
  }
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
.offers-list-header {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border-radius: 0.75rem 0.75rem 0 0;
  padding: 1rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.offers-list-header h3 {
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

/* Offers Grid */
.offers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  background-color: white;
  border-radius: 0 0 0.75rem 0.75rem;
  padding: 2rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Offer Card */
.offer-card {
  border: 1px solid #dee2e6;
  border-radius: 0.75rem;
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.offer-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.offer-card .card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #0d6efd;
}

.offer-card .card-subtitle {
  font-size: 0.95rem;
  color: #6c757d;
}

.offer-card .card-subtitle a {
  color: #0d6efd;
  font-weight: 600;
  transition: color 0.2s ease;
}

.offer-card .card-subtitle a:hover {
  color: #3b82f6;
}

.offer-card .card-text {
  font-size: 0.9rem;
  color: #212529;
  margin-bottom: 1rem;
}

.offer-details {
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.offer-details li {
  margin-bottom: 0.4rem;
}

.offer-details i {
  color: #0d6efd;
  width: 1.2em;
}

.offer-card .btn {
  border-radius: 0.375rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.8rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.offer-card .btn-outline-primary {
  border-color: #0d6efd;
  color: #0d6efd;
}

.offer-card .btn-outline-primary:hover {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.text-truncate-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 3.6em;
  -webkit-line-clamp: 3;
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

/* Toast Colors */
.text-bg-success { background: linear-gradient(to right, #28a745, #34d058) !important; color: white !important; }
.text-bg-danger { background: linear-gradient(to right, #dc3545, #b02a37) !important; color: white !important; }
.text-bg-info { background: linear-gradient(to right, #17a2b8, #138496) !important; color: white !important; }

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 1rem;
  }

  .offers-list-header {
    padding: 0.75rem;
  }

  .offers-list-header h3 {
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

  .offers-grid {
    grid-template-columns: 1fr;
    padding: 1rem;
  }

  .page-item .page-link {
    font-size: 0.85rem;
  }
}

@media (max-width: 576px) {
  .offers-list-header {
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

  .page-item .page-link {
    font-size: 0.8rem;
  }
}
</style>