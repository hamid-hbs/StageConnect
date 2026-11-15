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
          <img :src="getCoverPhoto(profileData.couverture)" :alt="`Image de couverture de ${profileName}`" class="profile-cover-image w-100 rounded-top" />
        </section>

        <!-- Main Profile Card -->
        <section class="profile-main-card bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header d-flex align-items-start pb-4 mb-4 border-bottom flex-column flex-md-row">
            <div class="profile-avatar-placeholder me-md-4 position-relative">
              <img :src="getProfilePhoto(profileData.photo)" :alt="`Profil de ${profileName}`" :class="getLogoClass" />
            </div>
            <div class="profile-info flex-grow-1 mt-3 mt-md-0">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ profileName || 'Nom non spécifié' }}
              </h3>
             <!-- <button class="btn btn-primary mt-2" @click="sendMessage">
                <i class="fas fa-envelope me-2"></i>Envoyer un message
              </button>-->
            </div>
          </div>

          <!-- Description Field -->
          <div class="description-field pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">
              {{ profileTypeDisplay === 'étudiant' ? 'À propos de moi' : 'Description du ' + profileTypeDisplay.toLowerCase() }} :
            </label>
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.description || `Ce ${profileTypeDisplay.toLowerCase()} n'a pas encore ajouté de description.` }}
            </p>
          </div>

          <!-- Profile Details -->
          <div class="profile-details">
            <!-- Student Details -->
            <template v-if="profileData.role === 'etudiant' && profileData.etudiant">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-id-card me-2 text-primary fs-5"></i><span class="fw-bold">Matricule :</span>
                {{ profileData.etudiant.matricule || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.email || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-school me-2 text-primary fs-5"></i><span class="fw-bold">Établissement :</span>
                {{ profileData.etudiant.etablissement?.nom_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span>
                {{ profileData.etudiant.filiere?.libfil || 'Non spécifiée' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span>
                {{ profileData.etudiant.annee?.libannee || 'Non spécifiée' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2 flex-wrap">
                <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
                <a v-if="profileData.etudiant.CV" :href="getDownloadUrl(profileData.etudiant.CV)" target="_blank" class="ms-1 btn btn-sm btn-outline-primary">Voir le CV</a>
                <span v-else class="ms-1">Non téléchargé</span>
              </p>
            </template>

            <!-- Company Details -->
            <template v-else-if="profileData.role === 'entreprise' && profileData.entreprise">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-building me-2 text-primary fs-5"></i><span class="fw-bold">RCCM :</span>
                {{ profileData.entreprise.RCCM || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span>
                {{ profileData.entreprise.domaine?.libdomaine || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.entreprise.email_entreprise || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <a v-if="profileData.entreprise.siteweb" :href="profileData.entreprise.siteweb" target="_blank">{{ profileData.entreprise.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                {{ profileData.entreprise.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                {{ profileData.entreprise.adresse || 'Non spécifié' }}
              </p>
            </template>

            <!-- Establishment and Admin Details -->
            <template v-else>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.email || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-user-tag me-2 text-primary fs-5"></i><span class="fw-bold">Rôle :</span>
                {{ profileData.role || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-calendar-alt me-2 text-primary fs-5"></i><span class="fw-bold">Membre depuis :</span>
                {{ new Date(profileData.created_at).toLocaleDateString() || 'Non spécifié' }}
              </p>
            </template>
          </div>
        </section>

        <!-- Additional Sections for Students -->
        <section v-if="profileData.role === 'etudiant' && profileData.etudiant" class="bg-white p-4 rounded shadow-sm mb-4">
          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mes Compétences
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.etudiant.competences || 'L\'étudiant n\'a pas encore listé ses compétences.' }}
            </p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mes Projets
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.etudiant.projets || 'L\'étudiant n\'a pas encore décrit ses projets.' }}
            </p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            Mon Parcours Académique
            <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
          </h2>
          <div class="description-field pb-4 mb-4">
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.etudiant.parcours || 'L\'étudiant n\'a pas encore décrit son parcours académique.' }}
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
  id: {
    type: [String, Number],
    required: true
  }
});

// Reactive Data
const profileData = ref(null);
const loading = ref(true);
const error = ref(null);
const router = useRouter();
const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null;

// Computed Properties
const profileTypeDisplay = computed(() => {
  if (!profileData.value) return 'profil';
  switch (profileData.value.role) {
    case 'etudiant': return 'étudiant';
    case 'entreprise': return 'entreprise';
    case 'etablissement': return 'établissement';
    case 'admin': return 'administrateur';
    default: return 'profil';
  }
});

const profileName = computed(() => {
  if (!profileData.value) return 'Chargement...';
  if (profileData.value.role === 'etudiant' && profileData.value.etudiant) {
    return profileData.value.etudiant.nom_etudiant || profileData.value.name || 'Nom non spécifié';
  } else if (profileData.value.role === 'entreprise' && profileData.value.entreprise) {
    return profileData.value.entreprise.nom_entreprise || profileData.value.name || 'Nom non spécifié';
  } else {
    return profileData.value.name || 'Nom non spécifié';
  }
});

const getLogoClass = computed(() => {
  if (!profileData.value) return '';
  switch (profileData.value.role) {
    case 'etudiant': return 'student-logo';
    case 'entreprise': return 'company-logo';
    case 'etablissement': return 'school-logo';
    default: return '';
  }
});

// Functions
const fetchProfile = async () => {
  loading.value = true;
  error.value = null;

  if (!props.id) {
    error.value = `ID utilisateur manquant dans l'URL.`;
    displayToast(error.value, 'danger');
    loading.value = false;
    return;
  }

  try {
    const response = await axios.get(`/api/admin/users/${props.id}`);
    profileData.value = response.data;

    if (!profileData.value || !profileData.value.id) {
      error.value = "Données utilisateur de base manquantes pour ce profil.";
      displayToast(error.value, 'danger');
    }
  } catch (err) {
    console.error(`Erreur lors de la récupération du profil utilisateur (ID: ${props.id}) :`, err.response ? err.response.data : err.message);
    error.value = `Impossible de charger le profil. Veuillez réessayer plus tard.`;
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

const getProfilePhoto = (photoPath) => {
  if (photoPath && photoPath.startsWith('http')) {
    return photoPath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return photoPath ? `${baseUrl}/storage/${photoPath}` : '/photo-default.jpeg';
};

const getCoverPhoto = (coverPath) => {
  if (coverPath && coverPath.startsWith('http')) {
    return coverPath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return coverPath ? `${baseUrl}/storage/${coverPath}` : '/cover-default.jpeg';
};

const getDownloadUrl = (filePath) => {
  if (filePath && filePath.startsWith('http')) {
    return filePath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return `${baseUrl}/storage/${filePath}`;
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
  const recipientUserId = profileData.value?.id;
  if (!recipientUserId) {
    displayToast("Impossible d'envoyer le message : ID de l'utilisateur destinataire manquant.", "danger");
    return;
  }

  let messagerieRouteName = 'admin-messagerie'; // Assuming an admin messaging route
  router.push({ name: messagerieRouteName, params: { userId: recipientUserId } });
  displayToast('Redirection vers la messagerie pour envoyer un message...', 'info');
};

// Watcher
watch(() => props.id, () => {
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