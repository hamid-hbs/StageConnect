<template>
  <div class="content-page">
    <!-- Toast Container for Notifications -->
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

    <!-- Header with Title and Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="page-main-title fs-4 text-dark mb-0">Profil de {{ profileTypeDisplay }} : {{ profileName || 'Chargement...' }}</h1>
        <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
      </div>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
    </div>

    <!-- Profile Content -->
    <section class="profile-section bg-white p-4 rounded shadow-sm">
      <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="ms-3">Chargement du profil {{ profileTypeDisplay.toLowerCase() }}...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else class="profile-content">
        <!-- Cover Image Section -->
        <section class="profile-cover-section bg-light rounded shadow-sm mb-4 position-relative">
          <img :src="getCoverPhoto(profileData.user?.couverture)" :alt="`Image de couverture de ${profileName}`" class="profile-cover-image w-100 rounded-top" />
        </section>

        <!-- Main Profile Card -->
        <section class="profile-main-card bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header d-flex align-items-start pb-4 mb-4 border-bottom flex-column flex-md-row">
            <div class="profile-avatar-placeholder me-md-4 position-relative">
              <img :src="getProfilePhoto(profileData.user?.photo)" :alt="`Profil de ${profileName}`" :class="getLogoClass" />
            </div>
            <div class="profile-info flex-grow-1 mt-3 mt-md-0">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ profileName || 'Nom non spécifié' }}
              </h3>
              <!-- Online Status (Optional, disabled as in original components) -->
              <!-- <p class="status-online fs-6 text-success d-flex align-items-center">
                <span class="dot bg-success me-1"></span> En ligne
              </p> -->
              <button class="btn btn-primary mt-2" @click="sendMessage">
                <i class="fas fa-envelope me-2"></i> Envoyer un message
              </button>
            </div>
          </div>

          <!-- Description Field -->
          <div class="description-field pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">
              {{ profileTypeDisplay === 'étudiant' ? 'À propos de moi' : 'Description du ' + profileTypeDisplay.toLowerCase() }} :
            </label>
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.user?.description || `Cet ${profileTypeDisplay.toLowerCase()} n'a pas encore ajouté de description.` }}
            </p>
          </div>
        
          <!-- Profile Details -->
          <div class="profile-details">
            <!-- Student Details -->
            <template v-if="type === 'etudiants'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-id-card me-2 text-primary fs-5"></i><span class="fw-bold">Matricule :</span>
                {{ profileData.matricule || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.user?.email || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-school me-2 text-primary fs-5"></i><span class="fw-bold">Établissement :</span>
                {{ profileData.etablissement?.nom_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span>
                {{ profileData.filiere?.libfil || 'Non spécifiée' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span>
                {{ profileData.annee?.libannee || 'Non spécifiée' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2 flex-wrap">
                <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
                <a v-if="profileData.CV" :href="getDownloadUrl(profileData.CV)" target="_blank" class="ms-1 btn btn-sm btn-outline-primary">Voir le CV</a>
                <span v-else class="ms-1">Non téléchargé</span>
              </p>
            </template>

            <!-- Company Details -->
            <template v-else-if="type === 'entreprises'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span>
                {{ profileData.domaine?.libdomaine || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.email_entreprise || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <a v-if="profileData.siteweb" :href="profileData.siteweb" target="_blank">{{ profileData.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                {{ profileData.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                {{ profileData.adresse || 'Non spécifié' }}
              </p>
            </template>

            <!-- Establishment Details -->
            <template v-else-if="type === 'etablissements'">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Mail :</span>
                {{ profileData.email_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-phone me-2 text-primary fs-5"></i><span class="fw-bold">Contact :</span>
                {{ profileData.contact || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <a v-if="profileData.siteweb" :href="profileData.siteweb" target="_blank">{{ profileData.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                {{ profileData.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                {{ profileData.adresse || 'Non spécifié' }}
              </p>
            </template>
          </div>
        </section>

        <!-- Additional Sections for Students -->
        <section v-if="type === 'etudiants'" class="bg-white p-4 rounded shadow-sm mb-4">
          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mes Compétences
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.competences || 'L\'étudiant n\'a pas encore listé ses compétences.' }}
            </p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mes Projets
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.projets || 'L\'étudiant n\'a pas encore décrit ses projets.' }}
            </p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mon Parcours Académique
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.parcours || 'L\'étudiant n\'a pas encore décrit son parcours académique.' }}
            </p>
          </div>
        </section>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from '../axios';
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

// Props
const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['etudiants', 'entreprises', 'etablissements'].includes(value)
  },
  id: {
    type: [String, Number],
    required: true
  }
});

// Reactive Data
const profileData = ref({
  user: {
    name: null,
    email: null,
    photo: null,
    couverture: null,
    description: null,
    id: null,
  },
  matricule: null,
  competences: null,
  projets: null,
  parcours: null,
  CV: null,
  etablissement: { nom_etablissement: null },
  filiere: { libfil: null },
  annee: { libannee: null },
  nom_entreprise: null,
  nom_etablissement: null,
  email_entreprise: null,
  email_etablissement: null,
  siteweb: null,
  adresse: null,
  domaine: { libdomaine: null },
  ville: { nom_ville: null },
  contact: null,
});

const loading = ref(true);
const error = ref(null);
const router = useRouter();
const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null;

// Computed Properties
const profileTypeDisplay = computed(() => {
  switch (props.type) {
    case 'etudiants': return 'étudiant';
    case 'entreprises': return 'entreprise';
    case 'etablissements': return 'établissement';
    default: return 'profil';
  }
});

const profileName = computed(() => {
  if (!profileData.value) return 'Chargement...';
  if (props.type === 'etudiants') {
    return profileData.value.user?.name || `${profileData.value.prenom || ''} ${profileData.value.nom || ''}`.trim() || 'Nom non spécifié';
  } else if (props.type === 'entreprises') {
    return profileData.value.nom_entreprise || profileData.value.user?.name || 'Nom non spécifié';
  } else if (props.type === 'etablissements') {
    return profileData.value.nom_etablissement || profileData.value.user?.name || 'Nom non spécifié';
  }
  return 'Nom non spécifié';
});

const getLogoClass = computed(() => {
  switch (props.type) {
    case 'etudiants': return 'student-logo';
    case 'entreprises': return 'company-logo';
    case 'etablissements': return 'school-logo';
    default: return '';
  }
});

// Functions
const fetchProfile = async () => {
  loading.value = true;
  error.value = null;

  if (!props.id) {
    error.value = `ID de ${profileTypeDisplay.value.toLowerCase()} manquant dans l'URL.`;
    loading.value = false;
    return;
  }

  try {
    let endpoint = '';
    switch (props.type) {
      case 'etudiants':
        endpoint = `/api/etudiants/${props.id}`;
        break;
      case 'entreprises':
        endpoint = `/api/entreprises/${props.id}`;
        break;
      case 'etablissements':
        endpoint = `/api/etablissements/${props.id}`;
        break;
      default:
        error.value = "Type de profil inconnu.";
        loading.value = false;
        return;
    }
    
    const response = await axios.get(endpoint);
    profileData.value = response.data;

    if (!profileData.value.user || !profileData.value.user.id) {
      error.value = "Données utilisateur de base manquantes pour ce profil.";
      displayToast(error.value, 'danger');
    }
  } catch (err) {
    console.error(`Erreur lors de la récupération du profil ${props.type} :`, err.response ? err.response.data : err.message);
    error.value = `Impossible de charger le profil de ${profileTypeDisplay.value.toLowerCase()}. Veuillez réessayer plus tard.`;
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

const getProfilePhoto = (photoUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  return photoUrl ? `${baseURL}/storage/${photoUrl}` : '/photo-default.jpeg';
};

const getCoverPhoto = (coverUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  return coverUrl ? `${baseURL}/storage/${coverUrl}` : '/cover-default.jpeg';
};

const getDownloadUrl = (filePath) => {
  const baseURL = import.meta.env.VITE_API_URL;
  return `${baseURL}/storage/${filePath}`;
};

const goBack = () => {
  router.go(-1);
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

const sendMessage = () => {
  const recipientUserId = profileData.value.user?.id;
  if (!recipientUserId) {
    displayToast("Impossible d'envoyer le message : ID de l'utilisateur destinataire manquant.", "danger");
    return;
  }

  let messagerieRouteName = '';
  if (router.currentRoute.value.path.startsWith('/entreprise-dashboard')) {
    messagerieRouteName = 'entreprise-messagerie';
  } else if (router.currentRoute.value.path.startsWith('/etablissement-dashboard')) {
    messagerieRouteName = 'etablissement-messagerie';
  } else if (router.currentRoute.value.path.startsWith('/etudiant-dashboard')) {
    messagerieRouteName = 'etudiant-messagerie';
  }

  if (messagerieRouteName) {
    router.push({ name: messagerieRouteName, params: { userId: recipientUserId } });
    displayToast('Redirection vers la messagerie pour envoyer un message...', 'info');
  } else {
    displayToast("Impossible de déterminer la route de messagerie.", "danger");
  }
};

// Watchers
watch(() => [props.type, props.id], () => {
  fetchProfile();
}, { immediate: true });

// Lifecycle Hooks
onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
/* CSS Variables for Consistency */
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
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Header Styles */
.page-main-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark-text);
}

/* Cover Section */
.profile-cover-section {
  height: 200px;
  overflow: hidden;
  margin-bottom: -30px;
  border: 1px solid var(--border-color);
}

.profile-cover-image {
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Main Profile Card */
.profile-main-card {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  position: relative;
  padding-top: 50px;
  z-index: 1;
}

.profile-avatar-placeholder {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background-color: var(--light-gray-bg);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 3px solid white;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  margin-top: -80px;
}

.profile-avatar-placeholder .company-logo,
.profile-avatar-placeholder .student-logo,
.profile-avatar-placeholder .school-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark-text);
}

.status-online .dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.description-field .form-control-static {
  background-color: var(--light-gray-bg);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  padding: 0.75rem 1rem;
  min-height: 80px;
  font-size: 0.95rem;
  color: var(--dark-text);
}

.profile-details p {
  font-size: 0.95rem;
}

.section-header {
  font-size: 1.1rem;
  color: var(--dark-text);
  margin-bottom: 0.5rem;
  padding-bottom: 0.3rem;
}

/* Toast Styles */
.toast {
  border-radius: 0.5rem;
}

.text-bg-success { background-color: var(--success-green) !important; color: white !important; }
.text-bg-danger { background-color: var(--danger-red) !important; color: white !important; }
.text-bg-warning { background-color: var(--warning-orange) !important; color: black !important; }
.text-bg-info { background-color: #0dcaf7 !important; color: black !important; }

/* Button Styles */
.btn-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.btn-secondary {
  background: linear-gradient(to right, #6c757d, #5a6268);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(108, 117, 125, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-page {
    padding: 0.5rem;
  }

  .profile-cover-section {
    height: 150px;
    margin-bottom: -20px;
  }

  .profile-main-card {
    padding-top: 40px;
  }

  .profile-avatar-placeholder {
    width: 120px;
    height: 120px;
    margin-top: -60px;
  }

  .profile-info h3 {
    font-size: 1.25rem;
  }

  .profile-details p {
    font-size: 0.9rem;
  }

  .section-header {
    font-size: 1rem;
  }

  .page-main-title {
    font-size: 1.25rem;
  }

  .btn-primary,
  .btn-secondary {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}

@media (max-width: 576px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .profile-avatar-placeholder {
    width: 100px;
    height: 100px;
    margin-top: -50px;
  }

  .profile-info {
    margin-top: 1rem;
  }

  .profile-details p {
    font-size: 0.85rem;
  }

  .description-field .form-control-static {
    font-size: 0.9rem;
  }
}
</style>