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
      <div class="d-flex align-items-center">
        <div class="input-group search-input-group me-md-3 mb-2 mb-md-0">
          <input type="text" class="form-control form-control-sm" placeholder="Rechercher par titre, description..." v-model="searchQuery" @input="applyFilters">
        </div>
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" id="filterDomaineDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            {{ selectedDomaineName || 'Filtrer par domaine' }}
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDomaineDropdown">
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
      <div v-for="offer in filteredOffers" :key="offer.id" class="card offer-card mb-3">
        <div class="card-body">
          <h5 class="card-title text-primary">{{ offer.titre }}</h5>
          
          <h6 class="card-subtitle mb-2 text-muted">
            <router-link :to="{ name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: offer?.entreprise?.id } }">
  <span class="fw-bold ">{{ offer.entreprise.nom_entreprise }}</span>
</router-link>   - {{ offer.domaine.libdomaine }}</h6>
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios'; // Assurez-vous que le chemin est correct pour votre instance axios
import {Toast} from 'bootstrap';

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

// --- Propriétés Calculées (Filtres) ---
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
  // Pas besoin d'appeler applyFilters ici car le computed property `filteredOffers` se met à jour automatiquement
};

const applyFilters = () => {
  // Cette fonction est vide car `filteredOffers` est un computed property qui réagit aux changements
  // de `searchQuery` et `selectedDomaineId` automatiquement.
  // Elle est là principalement pour les écouteurs @input si des logiques supplémentaires étaient nécessaires.
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

/* Styles pour l'en-tête de la liste des offres */
.offers-list-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.offers-list-header h3 {
  color: white;
}

.offers-list-header .search-input-group {
  max-width: 300px; /* Limite la largeur de la barre de recherche */
}

.offers-list-header .form-control-sm {
  border-radius: 0.25rem;
}


/* Grille d'offres */
.offers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* 300px minimum, s'adapte */
  gap: 1.5rem; /* Espace entre les cartes */
}

.offer-card {
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.offer-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.offer-card .card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary-blue);
}

.offer-card .card-subtitle {
  font-size: 0.95rem;
}

.offer-card .card-text {
  font-size: 0.9rem;
  color: var(--dark-text);
  margin-bottom: 1rem;
}

.offer-details {
  font-size: 0.85rem;
  color: var(--secondary-text);
  margin-bottom: 1rem;
}

.offer-details li {
  margin-bottom: 0.4rem;
}

.offer-details i {
  color: var(--primary-blue); /* Icônes en couleur primaire */
  width: 1.2em; /* Alignement des icônes */
}

.text-truncate-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 3.6em; /* Approx. 3 lines of text (e.g., 1.2em line-height * 3 lines) */
  -webkit-line-clamp: 3; /* Limite le texte à 3 lignes pour les navigateurs Webkit */
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

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .offers-list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .offers-list-header .search-input-group,
  .offers-list-header {
    width: 100%;
    margin-right: 0 !important;
    margin-bottom: 0.5rem;
  }
}
</style>