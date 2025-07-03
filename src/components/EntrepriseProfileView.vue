<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Profil de l'entreprise : {{ entreprise.nom_entreprise || 'Chargement...' }}</h1>
      </span>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
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

    <section class="profile-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="text-center text-secondary py-5">
        <div class="spinner-border" role="status" style="color: #0d6efd">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3 mb-0">Chargement du profil de l'entreprise...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else class="profile-content">
        <section class="profile-cover-section-view bg-light rounded shadow-sm mb-4 position-relative">
          <img :src="getCoverPhoto(entreprise.user?.couverture)" alt="Image de couverture de l'entreprise" class="profile-cover-image-view w-100 rounded-top" />
        </section>

        <section class="profile-main-card-view bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header-view d-flex align-items-start pb-4 mb-4 border-bottom">
            <div class="profile-avatar-placeholder-view me-4 position-relative">
              <img :src="getProfilePhoto(entreprise.user?.photo)" :alt="`Profil de ${entreprise.nom_entreprise}`" class="company-logo-view" />
            </div>
            <div class="profile-info-view flex-grow-1">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ entreprise.nom_entreprise || 'Nom non spécifié' }}
              </h3>
             <!-- <p class="status-online fs-6 text-success d-flex align-items-center">
                <span class="dot bg-success me-1"></span> En ligne </p>-->
            </div>
          </div>

          <div class="description-field-view pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">Description de l'entreprise :</label>
            <p class="form-control-static" style="white-space: pre-wrap;">{{ entreprise.user?.description || 'L\'entreprise n\'a pas encore ajouté de description.' }}</p>
          </div>

          <div class="d-flex gap-2 mb-4">
            <button class="btn btn-primary" @click="sendMessage">
              <i class="fas fa-envelope me-2"></i> Envoyer un message
            </button>
          </div>

          <div class="profile-details-view pb-4 mb-4">
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span> {{ entreprise.domaine?.libdomaine || 'Non spécifié' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span> {{ entreprise.email_entreprise || 'Non spécifié' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-phone me-2 text-primary fs-5"></i><span class="fw-bold">Contact :</span> {{ entreprise.contact || 'Non spécifié' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
              <a v-if="entreprise.siteweb" :href="entreprise.siteweb" target="_blank">{{ entreprise.siteweb }}</a>
              <span v-else>Non spécifié</span>
            </p>
            <p class="mb-0 text-dark d-flex align-items-center mt-2">
              <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span> {{ entreprise.ville?.nom_ville || 'Non spécifié' }}
            </p>
            <p class="mb-0 text-dark d-flex align-items-center">
              <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span> {{ entreprise.adresse || 'Non spécifié' }}
            </p>
          </div>
        </section>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios'; // Assurez-vous que le chemin est correct pour votre instance Axios
import { useRoute, useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

const entreprise = ref({
  user: {}, // Initialiser user comme un objet vide
  domaine: {}, // Initialiser domaine comme un objet vide
  ville: {}, // Initialiser ville comme un objet vide
  // Initialisez les autres propriétés de niveau supérieur utilisées directement dans le template
  email_entreprise: null,
  contact: null,
  siteweb: null,
  adresse: null,
});
const loading = ref(true);
const error = ref(null);
const route = useRoute();
const router = useRouter();

const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null;

const fetchEntrepriseProfile = async () => {
  loading.value = true;
  error.value = null;
  const entrepriseId = route.params.id; // Récupérer l'ID de l'entreprise depuis l'URL

  if (!entrepriseId) {
    error.value = "ID d'entreprise manquant dans l'URL.";
    loading.value = false;
    return;
  }

  try {
    // S'assurer que le backend charge toutes les relations nécessaires : user, domaine, ville
    const response = await axios.get(`/api/entreprises/${entrepriseId}`);
    entreprise.value = response.data;
  } catch (err) {
    console.error('Erreur lors de la récupération du profil entreprise :', err.response ? err.response.data : err.message);
    error.value = 'Impossible de charger le profil de l\'entreprise. Veuillez réessayer plus tard.';
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

const getProfilePhoto = (photoUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Assurez-vous que '/storage/' est ajouté si le chemin dans la DB ne l'inclut pas
  return photoUrl ? `${baseURL}/storage/${photoUrl}` : '/photo-default.jpeg'; // Chemin par défaut pour l'avatar
};

const getCoverPhoto = (coverUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Assurez-vous que '/storage/' est ajouté si le chemin dans la DB ne l'inclut pas
  return coverUrl ? `${baseURL}/storage/${coverUrl}` : '/cover-default.jpeg'; // Chemin par défaut pour la couverture
};

const goBack = () => {
  router.go(-1); // Retourne à la page précédente dans l'historique de navigation
};

const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    if (!liveToast) {
      liveToast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    }
    liveToast.show();
  }
};

onMounted(() => {
  fetchEntrepriseProfile();
});

// --- Fonction pour envoyer un message ---
const sendMessage = () => {
  // Envoyer le message à l'utilisateur associé à cette entreprise
  const recipientUserId = entreprise.value.user?.id;
  if (!recipientUserId) {
    displayToast("Impossible d'envoyer le message : ID de l'utilisateur destinataire manquant.", "danger");
    return;
  }
  
  // Redirection vers une page de messagerie ou affichage d'une modale
  // Exemple de redirection (assurez-vous d'avoir une route nommée 'send-message' ou similaire)
  router.push({ name: 'send-message', params: { userId: recipientUserId } });
  
  displayToast('Redirection vers la messagerie pour envoyer un message...', 'info');
};
</script>

<style scoped>
/* Vos styles CSS restent inchangés - réutilise les styles de EtudiantProfileView.vue */
/* Variables de base */
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
  min-height: calc(100vh - 60px);
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
  color: var(--dark-text);
}

/* Styles pour la section de couverture (lecture seule) */
.profile-cover-section-view {
  height: 250px;
  overflow: hidden;
  margin-bottom: -40px !important;
  border: 1px solid var(--border-color);
  background-color: #e9ecef;
}

.profile-cover-image-view {
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Styles pour la carte principale du profil (lecture seule) */
.profile-main-card-view {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  position: relative;
  padding-top: 60px !important;
  z-index: 1;
}

.profile-avatar-placeholder-view {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background-color: var(--light-gray-bg);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 4px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  margin-top: -110px;
}

.profile-avatar-placeholder-view .company-logo-view {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info-view h3 {
  margin-bottom: 0.25rem;
}

/*Staut en ligne 
.status-online .dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}*/

.description-field-view .form-control-static {
  background-color: var(--light-gray-bg);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  padding: 0.75rem 1rem;
  min-height: 80px;
  font-size: 0.95rem;
  color: var(--dark-text);
}

.profile-details-view p {
  font-size: 0.95rem;
}

/* S'assurer que les blocs sont bien séparés */
.profile-main-card-view > div:not(:last-child),
section.bg-white > div:not(:last-child) {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

/* Pas de bordure inférieure pour le dernier élément de la section principale, ni pour le dernier élément des nouvelles sections */
.profile-main-card-view > div:last-child,
section.bg-white > div:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

/* Styles pour les toasts */
.toast {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}

.toast.showing, .toast.show {
  opacity: 1;
}

.text-bg-success { background-color: var(--success-green) !important; color: white !important; }
.text-bg-danger { background-color: var(--danger-red) !important; color: white !important; }
.text-bg-warning { background-color: var(--warning-orange) !important; color: black !important; }
.text-bg-info { background-color: #0dcaf7 !important; color: black !important; }

/* Boutons */
.btn-outline-primary {
  color: var(--primary-blue);
  border-color: var(--primary-blue);
}
.btn-outline-primary:hover {
  background-color: var(--primary-blue);
  color: white;
}
  
.btn-secondary{
    background-color: #5a6268;
    border-color: #5a6268;
    color: white;
}
</style>