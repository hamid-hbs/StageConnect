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
      <h3 class="mb-2 mb-md-0 fs-5 text-white">Demander un nouveau partenariat</h3>
    </section>

    <div class="p-4 bg-white rounded-bottom shadow-sm mb-4">
      <form @submit.prevent="submitPartenariatRequest">
        <div class="mb-3">
          <label for="entreprise-search-input" class="form-label">Choisir ou rechercher une entreprise :</label>
          <input
            type="text"
            id="entreprise-search-input"
            v-model="selectedEntrepriseName"
            list="entreprises-list"
            class="form-control"
            placeholder="Tapez le nom de l'entreprise ou sélectionnez..."
            required
          />
          <datalist id="entreprises-list">
            <option v-for="entreprise in entreprises" :key="entreprise.id" :value="entreprise.nom_entreprise"></option>
          </datalist>
          <div v-if="formErrors.entreprise_id" class="text-danger mt-1">{{ formErrors.entreprise_id[0] }}</div>
          <div v-if="!isValidEntrepriseSelected && selectedEntrepriseName && !formErrors.entreprise_id" class="text-danger mt-1">
            Veuillez sélectionner une entreprise valide dans la liste ou vérifier l'orthographe.
          </div>
        </div>

        <div class="mb-3">
          <label for="date_debut" class="form-label">Date de début :</label>
          <input type="date" id="date_debut" v-model="newPartenariat.date_debut" class="form-control" required>
          <div v-if="formErrors.date_debut" class="text-danger mt-1">{{ formErrors.date_debut[0] }}</div>
        </div>

        <div class="mb-3">
          <label for="date_fin" class="form-label">Date de fin (optionnel) :</label>
          <input type="date" id="date_fin" v-model="newPartenariat.date_fin" class="form-control">
          <div v-if="formErrors.date_fin" class="text-danger mt-1">{{ formErrors.date_fin[0] }}</div>
        </div>

        <button type="submit" :disabled="submitting || !isValidEntrepriseSelected" class="btn btn-primary w-100">
          <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          {{ submitting ? 'Envoi en cours...' : 'Envoyer la demande' }}
        </button>
        <div v-if="formError" class="alert alert-danger mt-3">{{ formError }}</div>
      </form>
    </div>


    <section class="offers-list-header bg-primary text-white p-3 rounded-top d-flex flex-column flex-md-row justify-content-between align-items-center mb-0">
      <h3 class="mb-2 mb-md-0 fs-5 text-white">Mes Partenariats</h3>
      <div class="d-flex flex-column flex-md-row gap-2"> <input
          type="text"
          v-model="searchQuery"
          class="form-control form-control-sm"
          placeholder="Rechercher un partenariat..."
        />
        <select v-model="statusFilter" class="form-select form-select-sm status-filter">
          <option value="">Tous les statuts</option>
          <option value="actif">Actif</option>
          <option value="termine">Terminé</option>
          <option value="inactif">Décliné</option>
          </select>
      </div>
    </section>
    

    <div class="p-4 bg-white rounded-bottom shadow-sm">
      <div v-if="isLoadingPartenariats" class="d-flex justify-content-center align-items-center p-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement des partenariats...</span>
        </div>
        <p class="ms-3 text-muted">Chargement de vos partenariats...</p>
      </div>

      <div v-else-if="errorMessage" class="alert alert-danger text-center p-4">
        {{ errorMessage }}
      </div>

      <div v-else-if="filteredPartenariats.length > 0" class="offers-grid">
        <div v-for="partenariat in filteredPartenariats" :key="partenariat.id" class="card offer-card mb-3">
          <div class="card-body">
            <h5 class="card-title text-primary">
              Partenariat avec : <router-link :to="{ name: 'etablissement-generic-profile-view', params: { type: 'entreprises', id: partenariat?.entreprise?.id } }">
  <span class="fw-bold ">{{ partenariat.entreprise ? partenariat.entreprise.nom_entreprise : 'Entreprise inconnue' }}</span>
</router-link>
            </h5>
            <h6 class="card-subtitle mb-2">
              <span :class="['badge', getStatusBadgeClass(partenariat.statut)]">
                <strong>{{ formatStatut(partenariat.statut) }}</strong>
              </span>
            </h6>
            <ul class="list-unstyled offer-details">
              <li><i class="fas fa-calendar-alt me-2 text-secondary"></i> Date de début : <strong>{{ formatDate(partenariat.date_debut) }}</strong></li>
              <li v-if="partenariat.date_fin"><i class="fas fa-calendar-check me-2 text-secondary"></i> Date de fin : <strong>{{ formatDate(partenariat.date_fin) }}</strong></li>
              <li v-if="partenariat.type_partenariat"><i class="fas fa-tag me-2 text-secondary"></i> Type : <strong>{{ partenariat.type_partenariat }}</strong></li>
              <li v-if="partenariat.entreprise && partenariat.entreprise.user">
                <i class="fas fa-envelope me-2 text-secondary"></i> E-mail :
                <a :href="'mailto:' + partenariat.entreprise.email_entreprise" class="text-primary">{{ partenariat.entreprise.email_entreprise }}</a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-else class="alert alert-info text-center p-4">
        Aucun partenariat trouvé pour votre recherche ou aucun partenariat établi pour le moment.
      </div>
    </div>

    
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios';
import { Toast } from 'bootstrap';

// --- Variables Réactives ---
const partenariats = ref([]);
const entreprises = ref([]); // Liste de toutes les entreprises pour la datalist

const isLoadingPartenariats = ref(true);
const errorMessage = ref(''); // Message d'erreur général pour le chargement des partenariats

const submitting = ref(false); // État de soumission du formulaire
const formError = ref(null); // Message d'erreur général du formulaire
const formErrors = ref({}); // Erreurs de validation spécifiques des champs du formulaire
const formSuccess = ref(null); // Message de succès du formulaire

const newPartenariat = ref({
  entreprise_id: '',
  date_debut: '',
  date_fin: '',
});
const selectedEntrepriseName = ref(''); // Nom saisi/sélectionné dans l'input de l'entreprise

const toastMessage = ref('');
const toastType = ref('success');

const searchQuery = ref(''); // Nouvelle variable pour la recherche de partenariats
const statusFilter = ref(''); // Nouvelle variable pour le filtre de statut

// --- Propriétés Calculées ---
const isValidEntrepriseSelected = computed(() => {
  // Vérifie si le nom saisi correspond à une entreprise existante dans la liste
  return entreprises.value.some(
    (entreprise) => entreprise.nom_entreprise.toLowerCase() === selectedEntrepriseName.value.toLowerCase()
  );
});

const filteredPartenariats = computed(() => {
  let filtered = partenariats.value;

  // Filtrage par recherche textuelle
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(partenariat =>
      (partenariat.entreprise && partenariat.entreprise.nom_entreprise.toLowerCase().includes(query)) ||
      formatStatut(partenariat.statut).toLowerCase().includes(query) ||
      (partenariat.type_partenariat && partenariat.type_partenariat.toLowerCase().includes(query))
    );
  }

  // Filtrage par statut
  if (statusFilter.value) {
    filtered = filtered.filter(partenariat => partenariat.statut === statusFilter.value);
  }

  return filtered;
});

// --- Fonctions Utilitaires (Toasts) ---
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const toast = new Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

// --- Fonctions de Formatage ---
const formatStatut = (statut) => {
  const statuses = {
    'en_attente': 'En attente',
    'actif': 'Actif',
    'termine': 'Terminé',
    'suspendu': 'Suspendu',
    'annule': 'Annulé',
    'refuse': 'Refusé',
    'inactif': 'Décliné'
  };
  return statuses[statut] || statut;
};

const getStatusBadgeClass = (statut) => {
  switch (statut) {
    case 'en_attente': return 'bg-warning text-dark';
    case 'actif': return 'bg-success';
    case 'termine': return 'bg-secondary';
    case 'suspendu': return 'bg-warning';
    case 'annule': return 'bg-dark';
    case 'refuse': return 'bg-danger';
    case 'inactif': return 'bg-danger';
    default: return 'bg-light text-dark';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString.split('T')[0] + 'T00:00:00Z');
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return date.toLocaleDateString('fr-FR', options);
};

// --- Logique d'API ---
const fetchPartenariats = async () => {
  isLoadingPartenariats.value = true;
  errorMessage.value = '';
  try {
    const token = localStorage.getItem('authToken');
    if (!token) {
      errorMessage.value = 'Vous n\'êtes pas connecté. Veuillez vous connecter pour voir vos partenariats.';
      displayToast('Veuillez vous connecter.', 'danger');
      return;
    }

    const response = await axios.get('/api/partenariats', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    partenariats.value = response.data.data || response.data;
  } catch (err) {
    console.error("Erreur API partenariats:", err);
    if (err.response && err.response.status === 401) {
      errorMessage.value = 'Votre session a expiré ou vous n\'êtes pas autorisé. Veuillez vous reconnecter.';
    } else {
      errorMessage.value = 'Une erreur est survenue lors du chargement des partenariats. Veuillez réessayer plus tard.';
    }
    displayToast('Échec du chargement des partenariats.', 'danger');
  } finally {
    isLoadingPartenariats.value = false;
  }
};

const fetchEntreprises = async () => {
  try {
    const token = localStorage.getItem('authToken');
    if (!token) {
      displayToast('Vous devez être connecté pour charger la liste des entreprises.', 'danger');
      return;
    }
    const response = await axios.get('/api/entreprises', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    entreprises.value = response.data.data || response.data;
  } catch (err) {
    console.error("Erreur lors du chargement des entreprises:", err);
    displayToast('Échec du chargement des entreprises disponibles.', 'danger');
  }
};

const submitPartenariatRequest = async () => {
  submitting.value = true;
  formError.value = null;
  formSuccess.value = null;
  formErrors.value = {};

  const selectedEntreprise = entreprises.value.find(
    (entreprise) => entreprise.nom_entreprise.toLowerCase() === selectedEntrepriseName.value.toLowerCase()
  );

  if (!selectedEntreprise) {
    formError.value = 'Veuillez sélectionner une entreprise valide dans la liste.';
    formErrors.value.entreprise_id = ['L\'entreprise sélectionnée est invalide.'];
    displayToast(formError.value, 'danger');
    submitting.value = false;
    return;
  }

  newPartenariat.value.entreprise_id = selectedEntreprise.id;

  try {
    const token = localStorage.getItem('authToken');
    const response = await axios.post('/api/partenariats', newPartenariat.value, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    formSuccess.value = response.data.message || 'Demande de partenariat envoyée avec succès !';
    partenariats.value.unshift(response.data.data);
    displayToast(formSuccess.value, 'success');

    // Réinitialiser le formulaire
    newPartenariat.value = {
      entreprise_id: '',
      date_debut: '',
      date_fin: '',
    };
    selectedEntrepriseName.value = '';
  } catch (err) {
    console.error("Erreur API soumission partenariat:", err);
    if (err.response && err.response.data) {
      if (err.response.data.errors) {
        formErrors.value = err.response.data.errors;
        formError.value = 'Veuillez corriger les erreurs dans le formulaire.';
      } else if (err.response.data.message) {
        formError.value = err.response.data.message;
      } else {
        formError.value = 'Une erreur est survenue lors de l\'envoi de la demande.';
      }
    } else {
      formError.value = 'Une erreur réseau est survenue. Veuillez réessayer.';
    }
    displayToast(formError.value, 'danger');
  } finally {
    submitting.value = false;
  }
};

// --- Cycle de Vie ---
onMounted(() => {
  fetchPartenariats();
  fetchEntreprises();
});
</script>

<style scoped>
/* Importation de Font Awesome et Roboto */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

/* Variables de base (si non déjà définies globalement) */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
}

/* Styles généraux du conteneur principal */
.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
  min-height: calc(100vh - 60px); /* Ajustez selon la hauteur de votre navbar/footer */
  font-family: 'Roboto', sans-serif;
  color: var(--dark-text);
}

/* Styles pour l'en-tête de section */
.offers-list-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.offers-list-header h3 {
  color: white;
  font-weight: 600;
}

/* Conteneur pour la barre de recherche et le filtre de statut */
.offers-list-header > div { /* Cibler le div direct sous l'en-tête qui contient les inputs */
  justify-content: flex-end; /* Aligne les éléments à droite */
}

.search-bar, .status-filter {
  width: 170px; /* Limite la largeur des inputs */
  margin-left: 1rem; /* Espace entre le titre et les inputs, et entre les inputs */
}

.search-bar .form-control-sm, .status-filter {
  border-radius: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.5); /* Bordure plus douce */
  background-color: rgba(255, 255, 255, 0.2); /* Fond semi-transparent */
  color: white;
  padding-left: 0.75rem;
}

.search-bar .form-control-sm::placeholder, .status-filter::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.search-bar .form-control-sm:focus, .status-filter:focus {
  background-color: white; /* Fond blanc au focus */
  color: var(--dark-text); /* Texte noir au focus */
  border-color: white; /* Bordure blanche au focus */
  box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

.status-filter option {
  color: var(--dark-text); /* Texte noir pour les options du select */
  background-color: white; /* Fond blanc pour les options du select */
}


/* Grille des cartes (partenariats) */
.offers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); /* Adapté pour les cartes de partenariat */
  gap: 1.5rem; /* Espace entre les cartes */
}

.card.offer-card {
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  display: flex;
  flex-direction: column;
}

.card.offer-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.card-body {
  padding: 1.5rem;
  flex-grow: 1; /* Permet au corps de la carte de prendre l'espace restant */
}

.card-title {
  font-size: 1.35rem;
  font-weight: 600;
  color: var(--primary-blue);
  margin-bottom: 0.5rem;
}

.card-subtitle {
  font-size: 0.95rem;
  color: var(--secondary-text);
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
}

.badge {
  padding: 0.4em 0.8em;
  border-radius: 0.25rem;
  font-size: 0.8em;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: white; /* Par défaut pour les badges colorés */
}

/* Couleurs des badges de statut (utilisant les classes Bootstrap) */
.badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; } /* En attente */
.badge.bg-success { background-color: #198754 !important; }      /* Actif */
.badge.bg-secondary { background-color: #6c757d !important; }   /* Terminé */
.badge.bg-danger { background-color: #dc3545 !important; }       /* Suspendu / Refusé / Inactif */
.badge.bg-dark { background-color: #343a40 !important; }         /* Annulé */
.badge.bg-info { background-color: #0dcaf0 !important; } /* Peut être utilisé pour d'autres statuts */

.offer-details {
  font-size: 0.9rem;
  color: var(--dark-text);
  margin-bottom: 1rem;
}

.offer-details li {
  margin-bottom: 0.5rem;
  display: flex;
  align-items: flex-start;
}

.offer-details i {
  color: var(--primary-blue); /* Icônes en couleur primaire */
  margin-right: 0.6rem;
  font-size: 1.1em;
  flex-shrink: 0;
  width: 1.2em;
}

.offer-details strong {
  font-weight: 500;
  color: var(--dark-text);
}

.text-primary {
  color: var(--primary-blue) !important;
}

.form-label {
  font-weight: 600;
  color: var(--dark-text);
  margin-bottom: 0.5rem;
}

.form-control {
  border-radius: 0.25rem;
  border-color: var(--border-color);
}

.form-control:focus {
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); /* bs-blue with alpha */
}

.btn-primary {
  background-color:#0d6efd ;
  border-color: #0d6efd;
  font-weight: 500;
}
.btn-primary:hover {
  background-color: #0b5ed7; /* Darker blue on hover */
  border-color: #0a58ca;
}

.btn:disabled {
  background-color: var(--secondary-text);
  border-color: var(--secondary-text);
  opacity: 0.7;
}

.text-danger {
  color: #dc3545 !important; /* Bootstrap danger red */
  font-size: 0.875em;
}

/* Styles pour les toasts */
.toast-container {
  z-index: 1100;
}

.toast {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}

.toast.showing, .toast.show {
  opacity: 1;
}

.text-bg-success { background-color: #198754 !important; color: white !important; }
.text-bg-danger { background-color: #dc3545 !important; color: white !important; }
.alert-danger.mt-3 { /* Spécifique pour les alertes de formulaire */
  border-radius: 0.25rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.9em;
}

/* Séparateur revisité pour s'adapter au style */
.border-dashed {
  border-top: 1px dashed var(--border-color);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .offers-list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .offers-list-header > div {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }
  .search-bar, .status-filter {
    width: 100%; /* La barre de recherche prend toute la largeur sur mobile */
    margin-left: 0;
  }
  .offers-grid {
    grid-template-columns: 1fr; /* Une seule colonne sur les petits écrans */
  }
  .card-body {
    padding: 1rem;
  }
  .card-title {
    font-size: 1.2rem;
  }
  .offer-details {
    font-size: 0.85rem;
  }
  .form-control, .btn {
    width: 100%;
  }
}
</style>