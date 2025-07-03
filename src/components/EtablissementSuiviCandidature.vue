<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Suivi des Stages de mes Étudiants</h1>
      </span>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" :class="`text-bg-${toastType}`">
        <div class="toast-header" :class="`bg-${toastType}`">
          <strong class="me-auto text-white">Notification</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>

    <section class="stages-list-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="text-center text-secondary py-5">
        <div class="spinner-border" role="status" :style="{ color: 'var(--primary-blue)' }">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3 mb-0">Chargement des stages de vos étudiants...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else-if="stages.length > 0">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th scope="col">Étudiant</th>
                <th scope="col">Offre (Entreprise)</th>
                <th scope="col">Tuteur de Stage</th>
                <th scope="col">Dates du Stage</th>
                <th scope="col">Statut</th>
                <th scope="col">Note de Stage</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="stage in stages" :key="stage.id">
                <td>
                  <router-link :to="{ name: 'etudiant-profile-view', params: { id: stage.etudiant?.user?.id } }" class="d-flex align-items-center">
                    <img :src="getPhotoUrl(stage.etudiant?.user?.photo)"
                         alt="Photo Étudiant"
                         class="user-logo me-2" />
                    <span class="text-primary fw-bold">
                      {{ stage.etudiant?.user?.name || 'N/A' }}
                    </span>
                  </router-link>
                </td>
                <td>
                  <router-link :to="{ name: 'offre-details', params: { id: stage.offre?.id } }" class="text-primary fw-bold">
                    {{ stage.offre?.titre || 'N/A' }}
                  </router-link>
                  <br>
                  <router-link :to="{ name: 'entreprise-profile-view', params: { id: stage.offre?.entreprise?.id } }" class="text-muted small">
                    ({{ stage.offre?.entreprise?.nom_entreprise || 'N/A' }})
                  </router-link>
                </td>
                <td>
                    <router-link v-if="stage.tuteur_stage" :to="{ name: 'tuteur-stage-profile', params: { id: stage.tuteur_stage.id } }" class="d-flex align-items-center">
                       <img :src="getPhotoUrl(stage.tuteur_stage.user?.photo)"
                         alt="Photo Tuteur"
                         class="user-logo me-2" />
                       <span class="text-primary fw-bold">
                         {{ stage.tuteur_stage.user?.name || 'N/A' }}
                       </span>
                    </router-link>
                    <span v-else class="text-muted">Non assigné</span>
                </td>
                <td>
                  Du {{ formatDate(stage.date_debut) }}<br>
                  Au {{ formatDate(stage.date_fin) }}
                </td>
                <td>
                  <span :class="['badge', getStatusBadgeClass(stage.statut)]">
                    {{ getDisplayStatus(stage.statut) }}
                  </span>
                </td>
                <td>
                    <span v-if="stage.note_stage !== null">{{ stage.note_stage }}/20</span>
                    <span v-else class="text-muted">N/A</span>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <button @click="viewStageDetails(stage.id)"
                            class="btn btn-sm btn-info text-white"
                            title="Voir les détails du stage">
                      <i class="fas fa-eye"></i> Détails
                    </button>
                    <button v-if="stage.rapport_stage" @click="downloadRapport(stage.id)"
                            class="btn btn-sm btn-primary"
                            title="Télécharger le rapport de stage">
                      <i class="fas fa-download"></i> Rapport
                    </button>
                    <span v-else class="text-muted small">Pas de rapport</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-else class="text-center text-muted py-5">
        <p class="fs-5">Aucun stage d'étudiant à suivre pour le moment.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios'; // Assurez-vous que ce chemin est correct
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap'; // Import de Toast uniquement

const stages = ref([]);
const loading = ref(true);
const error = ref(null);
const router = useRouter();

// Pour les toasts
const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null; // Instance du toast

/**
 * Récupère les stages pertinents pour l'établissement authentifié.
 */
const fetchStages = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/stages'); // L'API Laravel filtrera par rôle
    stages.value = response.data;
  } catch (err) {
    console.error('Erreur lors de la récupération des stages :', err.response ? err.response.data : err.message);
    error.value = 'Impossible de charger les stages de vos étudiants. Veuillez réessayer plus tard.';
  } finally {
    loading.value = false;
  }
};

/**
 * Construit l'URL complète de la photo de profil.
 * @param {string} photoPath - Le chemin relatif de la photo (ex: "profile_photos/xyz.jpg").
 * @returns {string} L'URL complète de la photo, ou le chemin d'une image par défaut.
 */
const getPhotoUrl = (photoPath) => {
  const defaultPhoto = '/images/default_user_photo.png'; // Chemin vers votre image par défaut
  if (!photoPath) {
    return defaultPhoto;
  }
  if (photoPath.startsWith('http://') || photoPath.startsWith('https://')) {
    return photoPath;
  }
  const baseUrl = import.meta.env.VITE_API_URL || '';
  return `${baseUrl}${photoPath.startsWith('/') || baseUrl.endsWith('/') ? '' : '/'}${photoPath}`;
};

/**
 * Redirige vers la page de détails du stage.
 * @param {number} stageId - L'ID du stage.
 */
const viewStageDetails = (stageId) => {
  router.push({ name: 'stage-details', params: { id: stageId } }); // Assurez-vous que cette route existe
};

/**
 * Télécharge le rapport de stage.
 * @param {number} stageId - L'ID du stage.
 */
const downloadRapport = async (stageId) => {
  try {
    const response = await axios.get(`/api/stages/${stageId}/rapport/download`, {
      responseType: 'blob', // Important pour télécharger un fichier
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    // Tenter de récupérer le nom du fichier depuis les headers si disponible
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'rapport_stage.pdf'; // Nom par défaut
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1];
      }
    }
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url); // Libérer la mémoire
    displayToast('Rapport de stage téléchargé avec succès.', 'success');
  } catch (err) {
    console.error('Erreur lors du téléchargement du rapport :', err.response ? err.response.data : err.message);
    displayToast(err.response?.data?.message || 'Erreur lors du téléchargement du rapport de stage.', 'danger');
  }
};


// --- Fonctions utilitaires (reprises et adaptées de suivicandidature.vue) ---

/**
 * Formate une chaîne de date dans un format plus lisible.
 * @param {string} dateString - La chaîne de date à formater.
 * @returns {string} La date formatée ou 'N/A'.
 */
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

/**
 * Renvoie le texte d'affichage convivial pour un statut de stage donné.
 * @param {string} statut - Le code de statut (ex: 'en_attente').
 * @returns {string} Le statut affichable.
 */
const getDisplayStatus = (statut) => {
  const statusMap = {
    'en_attente': 'En attente',
    'en_cours': 'En cours',
    'termine': 'Terminé',
    'suspendu': 'Suspendu',
  };
  return statusMap[statut] || statut;
};

/**
 * Renvoie la classe de badge Bootstrap pour un statut de stage donné.
 * @param {string} statut - Le code de statut.
 * @returns {string} La classe CSS pour le badge.
 */
const getStatusBadgeClass = (statut) => {
  switch (statut) {
    case 'termine':
      return 'bg-success text-white';
    case 'en_cours':
      return 'bg-info text-white';
    case 'en_attente':
      return 'bg-secondary text-white';
    case 'suspendu':
      return 'bg-warning text-dark'; // Utiliser text-dark pour un meilleur contraste sur warning
    default:
      return 'bg-light text-dark';
  }
};

/**
 * Affiche une notification toast Bootstrap.
 * @param {string} message - Le message à afficher.
 * @param {string} type - Le type de toast ('success', 'danger', 'info', etc.).
 */
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    if (!liveToast) {
      liveToast = new Toast(toastEl, { delay: 3000 });
    }
    liveToast.show();
  }
};

// --- Hook de cycle de vie ---
onMounted(() => {
  fetchStages();
});
</script>

<style scoped>
/* Styles génériques (identiques à ceux de suivicandidature.vue) */
:root {
  --primary-blue: #0d6efd;
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --info-blue: #0dcaf0;
  --btn-hover-blue: #0056b3;
  --success-color: #198754;
  --danger-color: #dc3545;
  --warning-color: #ffc107;
}

.content-page {
  padding: 1.5rem;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
  color: #212529;
}

.stages-list-section { /* Renommé pour la spécificité */
  background-color: white !important;
  padding: 1.5rem !important;
}

.table thead th {
  background-color: rgba(23, 139, 255, 0.771);
  color: white;
  vertical-align: middle;
}

.table-hover tbody tr:hover {
  background-color: #e9ecef;
}

.badge {
  padding: 0.5em 0.7em;
  font-weight: 600;
  border-radius: 0.375rem;
  white-space: nowrap;
}

/* Couleurs des badges */
.bg-success { background-color: #198754 !important; }
.bg-info { background-color: #0dcaf0 !important; }
.bg-danger { background-color:#dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-secondary { background-color: #6c757d !important; }
.text-white { color: white !important; }
.text-dark { color: #212529 !important; } /* Ajouté pour le badge warning */

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.2rem;
}

/* Styles spécifiques aux modales (si vous en ajoutez plus tard pour les stages) */
.modal-header.bg-success { background-color: #198754 !important; }
.modal-header.bg-danger { background-color: #dc3545 !important; }
.btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

.spinner-border {
  width: 1rem;
  height: 1rem;
}

/* Style pour la photo de l'utilisateur */
.user-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(0, 0, 0, 0.1);
}
</style>
