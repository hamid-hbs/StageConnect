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
      <h3 class="mb-2 mb-md-0 fs-5 text-white">Mes Partenariats</h3>
      <div class="search-bar mt-2 mt-md-0">
        <input
          type="text"
          v-model="searchQuery"
          class="form-control form-control-sm"
          placeholder="Rechercher un partenariat..."
        />
      </div>
    </section>

    <div class="p-4 bg-white rounded-bottom shadow-sm">
      <div v-if="isLoadingPartenariats" class="d-flex justify-content-center align-items-center p-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement des partenariats...</span>
        </div>
        <p class="ms-3 text-muted">Chargement de vos partenariats et demandes...</p>
      </div>

      <div v-else-if="errorMessage" class="alert alert-danger text-center p-4">
        {{ errorMessage }}
      </div>

      <div v-else-if="filteredPartenariats.length > 0" class="offers-grid">
        <div v-for="partenariat in filteredPartenariats" :key="partenariat.id" class="card offer-card mb-3">
          <div class="card-body">
            <h5 class="card-title text-primary">
              Partenariat avec :<router-link :to="{ name: 'entreprise-generic-profile-view', params: { type: 'etablissements', id: partenariat?.etablissement?.id } }">
  <span class="fw-bold ">{{ partenariat.etablissement ? partenariat.etablissement.nom_etablissement : 'Établissement inconnu' }}</span>
</router-link> 
            </h5>
            <h6 class="card-subtitle mb-2">
              <span :class="['badge', getStatutClass(partenariat.statut)]">
                <strong>{{ formatStatut(partenariat.statut) }}</strong>
              </span>
            </h6>
            <ul class="list-unstyled offer-details">
              <li><i class="fas fa-calendar-alt me-2 text-secondary"></i> Date de début : <strong>{{ formatDate(partenariat.date_debut) }}</strong></li>
              <li v-if="partenariat.date_fin"><i class="fas fa-calendar-check me-2 text-secondary"></i> Date de fin : <strong>{{ formatDate(partenariat.date_fin) }}</strong></li>
              <li v-if="partenariat.type_partenariat"><i class="fas fa-tag me-2 text-secondary"></i> Type : <strong>{{ partenariat.type_partenariat }}</strong></li>
              <li v-if="partenariat.etablissement && partenariat.etablissement.user">
                <i class="fas fa-envelope me-2 text-secondary"></i>E-mail :
                <a :href="'mailto:' + partenariat.etablissement.user.email" class="text-primary">{{ partenariat.etablissement.user.email }}</a>
              </li>
            </ul>

            <div class="d-flex gap-2 flex-wrap mt-3">
              <div v-if="partenariat.statut === 'en_attente'">
                <button @click="updatePartenariatStatut(partenariat.id, 'actif')" class="btn btn-success btn-sm me-2">
                  <i class="fas fa-check-circle"></i> Accepter
                </button>
                <button @click="updatePartenariatStatut(partenariat.id, 'inactif')" class="btn btn-danger btn-sm">
                  <i class="fas fa-times-circle"></i> Décliner
                </button>
              </div>
              <div v-else-if="partenariat.statut === 'actif'">
                <button @click="updatePartenariatStatut(partenariat.id, 'termine')" class="btn btn-secondary btn-sm me-2">
                  <i class="fas fa-flag-checkered"></i> Terminer
                </button>
                <!--<button @click="updatePartenariatStatut(partenariat.id, 'suspendu')" class="btn btn-warning btn-sm">
                  <i class="fas fa-pause-circle"></i> Suspendre
                </button>-->
              </div>
            </div>
            <div v-if="updateError[partenariat.id]" class="alert alert-danger mt-3 mb-0 py-2 px-3 fs-6">
              <i class="fas fa-exclamation-circle me-2"></i> {{ updateError[partenariat.id] }}
            </div>
          </div>
        </div>
      </div>

      <div v-else class="alert alert-info text-center p-4">
        Aucun partenariat ou demande en attente trouvé pour le moment, ou aucun résultat pour votre recherche.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios'; // Assurez-vous que le chemin est correct pour votre instance axios
import { Toast } from 'bootstrap'; // Importez explicitement Toast de bootstrap

// --- Variables Réactives ---
const partenariats = ref([]);
const isLoadingPartenariats = ref(true);
const errorMessage = ref('');
const updateError = ref({}); // Pour stocker les erreurs de mise à jour par ID de partenariat

const toastMessage = ref('');
const toastType = ref('success');

const searchQuery = ref(''); // Nouvelle variable pour la recherche

// --- Propriétés Calculées ---
const filteredPartenariats = computed(() => {
  if (!searchQuery.value) {
    return partenariats.value;
  }
  const query = searchQuery.value.toLowerCase();
  return partenariats.value.filter(partenariat =>
    (partenariat.etablissement && partenariat.etablissement.nom_etablissement.toLowerCase().includes(query)) ||
    formatStatut(partenariat.statut).toLowerCase().includes(query) ||
    (partenariat.type_partenariat && partenariat.type_partenariat.toLowerCase().includes(query))
  );
});

// --- Fonctions Utilitaires (Toasts) ---
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) { // Vérifie si l'élément existe avant d'essayer de créer un Toast
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
    'inactif': 'Décliné'
  };
  return statuses[statut] || statut;
};

const getStatutClass = (statut) => {
  return {
    'bg-warning text-dark': statut === 'en_attente',
    'bg-success': statut === 'actif',
    'bg-secondary': statut === 'termine',
    'bg-danger': statut === 'inactif', 
    'bg-warning': statut === 'suspendu',
    'bg-info': statut === 'annule',
  };
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  // Créer un objet Date à partir de la chaîne, en prenant en compte le fuseau horaire si nécessaire
  // Pour éviter les décalages d'un jour, on peut créer la date comme UTC puis afficher localement
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
    partenariats.value = response.data.data || response.data; // Assurez-vous que la structure de la réponse correspond (Laravel retourne souvent .data.data)
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

const updatePartenariatStatut = async (id, statut) => {
  updateError.value = { ...updateError.value, [id]: null }; // Réinitialise l'erreur pour ce partenariat
  try {
    const token = localStorage.getItem('authToken');
    const response = await axios.put(`/api/partenariats/${id}`, { statut }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    // Mettre à jour l'objet partenariat directement dans la liste
    const index = partenariats.value.findIndex(p => p.id === id);
    if (index !== -1) {
      // Si l'API renvoie l'objet mis à jour, utilisez-le
      Object.assign(partenariats.value[index], response.data.data || response.data);
    }
    displayToast('Statut du partenariat mis à jour avec succès.', 'success');
  } catch (err) {
    console.error(`Erreur API mise à jour partenariat ${id}:`, err);
    if (err.response && err.response.data && err.response.data.message) {
      updateError.value = { ...updateError.value, [id]: err.response.data.message };
    } else if (err.response && err.response.data && err.response.data.errors) {
      // Pour les erreurs de validation de Laravel
      const errorMessages = Object.values(err.response.data.errors).flat().join(', ');
      updateError.value = { ...updateError.value, [id]: 'Erreur de validation : ' + errorMessages };
    } else {
      updateError.value = { ...updateError.value, [id]: 'Une erreur est survenue lors de la mise à jour.' };
    }
    displayToast('Échec de la mise à jour du statut.', 'danger');
  }
};

// --- Cycle de Vie ---
onMounted(() => {
  fetchPartenariats();
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

/* Styles pour l'en-tête de la liste */
.offers-list-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.offers-list-header h3 {
  color: white;
  font-weight: 600;
}

.search-bar {
  flex-grow: 1; /* Permet à la barre de recherche de prendre l'espace disponible */
  max-width: 300px; /* Limite la largeur de la barre de recherche */
  margin-left: 1rem; /* Espace à gauche du titre */
}

.search-bar .form-control-sm {
  border-radius: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.5); /* Bordure plus douce */
  background-color: rgba(255, 255, 255, 0.2); /* Fond semi-transparent */
  color: white;
  padding-left: 0.75rem;
}

.search-bar .form-control-sm::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.search-bar .form-control-sm:focus {
  background-color: white; /* Fond blanc au focus */
  color: var(--dark-text); /* Texte noir au focus */
  border-color: white; /* Bordure blanche au focus */
  box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
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
  font-size: 1.35rem; /* Légèrement plus grand pour le titre de partenariat */
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
}

/* Couleurs des badges de statut (utilisant les classes Bootstrap) */
.badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; } /* En attente */
.badge.bg-success { background-color: #198754 !important; color: white !important; }    /* Actif */
.badge.bg-secondary { background-color: #6c757d !important; color: white !important; }  /* Terminé */
.badge.bg-danger { background-color: #dc3545 !important; color: white !important; }      /* Suspendu / Inactif */
.badge.bg-dark { background-color: #343a40 !important; color: white !important; }        /* Refusé (ancien annule) */
.badge.bg-info { background-color: #0dcaf0 !important; color: white !important; } /* Annulé (si utilisé) */


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
  flex-shrink: 0; /* Empêche l'icône de se rétrécir */
  width: 1.2em; /* Alignement */
}

.offer-details strong {
  font-weight: 500;
  color: var(--dark-text);
}

.text-primary {
  color: var(--primary-blue) !important;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 0.25rem;
}

/* Styles pour les toasts */
.toast-container {
  z-index: 1100; /* Assurez-vous qu'il soit au-dessus de tout */
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

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .offers-list-header {
    flex-direction: column;
    align-items: stretch;
  }
  .search-bar {
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
  .d-flex.gap-2.flex-wrap {
    flex-direction: column; /* Les boutons d'action s'empilent */
  }
  .btn-sm {
    width: 100%; /* Les boutons prennent toute la largeur disponible */
    margin-bottom: 0.5rem !important; /* Espacement entre les boutons empilés */
  }
}
</style>