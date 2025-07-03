<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Profil de l'étudiant : {{ etudiant.user?.name || 'Chargement...' }}</h1>
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
        <p class="mt-3 mb-0">Chargement du profil de l'étudiant...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else class="profile-content">
        <section class="profile-cover-section-view bg-light rounded shadow-sm mb-4 position-relative">
          <img :src="getCoverPhoto(etudiant.user?.couverture)" alt="Image de couverture de l'étudiant" class="profile-cover-image-view w-100 rounded-top" />
        </section>

        <section class="profile-main-card-view bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header-view d-flex align-items-start pb-4 mb-4 border-bottom">
            <div class="profile-avatar-placeholder-view me-4 position-relative">
              <img :src="getProfilePhoto(etudiant.user?.photo)" alt="`Profil de ${etudiant.user?.name}`" class="student-logo-view" />
            </div>
            <div class="profile-info-view flex-grow-1">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ etudiant.user?.name || 'Nom non spécifié' }}
              </h3>
            </div>
          </div>

          <div class="description-field-view pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">À propos de moi :</label>
            <p class="form-control-static" style="white-space: pre-wrap;">{{ etudiant.user?.description || 'L\'étudiant n\'a pas encore ajouté de description.' }}</p>
          </div>

          <div class="d-flex gap-2 mb-4">
            <button v-if="!isOwnProfile" class="btn btn-primary" @click="sendMessage">
              <i class="fas fa-envelope me-2"></i> Envoyer un message
            </button>
          </div>

          <div class="profile-details-view pb-4 mb-4">
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-id-card me-2 text-primary fs-5"></i><span class="fw-bold">Matricule :</span> {{ etudiant.matricule || 'Non spécifié' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span> {{ etudiant.user?.email || 'Non spécifié' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-school me-2 text-primary fs-5"></i><span class="fw-bold">Établissement : 
                {{ etudiant.etablissement?.nom_etablissement || 'Non spécifié' }}
                </span>
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span> {{ etudiant.filiere?.libfil || 'Non spécifiée' }}
            </p>
            <p class="mb-2 text-dark d-flex align-items-center">
              <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span> {{ etudiant.filannee?.libannee || 'Non spécifiée' }}
            </p>
            <p class="mb-0 text-dark d-flex align-items-center mt-2">
              <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
              <a v-if="etudiant.CV" :href="getDownloadUrl(etudiant.CV)" target="_blank" class="ms-1 btn btn-sm btn-outline-primary">Voir le CV</a>
              <span v-else class="ms-1">Non téléchargé</span>
            </p>
          </div>
        </section>

        <section class="bg-white p-4 rounded shadow-sm mb-4">
          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Compétences</span></h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ etudiant.competences || 'L\'étudiant n\'a pas encore listé ses compétences.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Projets</span></h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ etudiant.projets || 'L\'étudiant n\'a pas encore décrit ses projets.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Parcours Académique</span></h2>
          <div class="description-field-view pb-4 mb-4">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ etudiant.parcours || 'L\'étudiant n\'a pas encore décrit son parcours académique.' }}</p>
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

const etudiant = ref({
  user: {}, // Initialiser user comme un objet vide pour éviter 'undefined'
  etablissement: {}, // Idem pour etablissement
  filiere: {},        // Idem pour filiere
  filannee: {},     // Idem pour filannee
  // Initialisez les autres propriétés de niveau supérieur utilisées directement dans le template si nécessaire
  matricule: null,
  competences: null,
  projets: null,
  parcours: null,
  CV: null,
});
const loading = ref(true);
const error = ref(null);
const route = useRoute();
const router = useRouter();

const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null;

const fetchEtudiantProfile = async () => {
  loading.value = true;
  error.value = null;
  const etudiantId = route.params.id; // Récupérer l'ID de l'étudiant depuis l'URL

  if (!etudiantId) {
    error.value = "ID d'étudiant manquant dans l'URL.";
    loading.value = false;
    return;
  }

  try {
    // S'assurer que le backend charge toutes les relations nécessaires
    // user (pour name, email, photo, couverture, description), etablissement, filiere, filannee
    const response = await axios.get(`/api/etudiants/${etudiantId}`);
    etudiant.value = response.data;
  } catch (err) {
    console.error('Erreur lors de la récupération du profil étudiant :', err.response ? err.response.data : err.message);
    error.value = 'Impossible de charger le profil de l\'étudiant. Veuillez réessayer plus tard.';
    displayToast(error.value, 'danger');
  } finally {
    loading.value = false;
  }
};

const getProfilePhoto = (photoUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Nous ajoutons '/storage/' car les chemins dans la DB sont relatifs à storage/app/public
  return photoUrl ? `${baseURL}/storage/${photoUrl}` : '/photo-default.jpeg';
};

const getCoverPhoto = (coverUrl) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Nous ajoutons '/storage/' ici aussi
  return coverUrl ? `${baseURL}/storage/${coverUrl}` : '/cover-default.jpeg';
};

const getDownloadUrl = (filePath) => {
  const baseURL = import.meta.env.VITE_API_URL;
  // Assurez-vous que le chemin est correct pour le téléchargement de fichiers stockés
  return `${baseURL}/storage/${filePath}`;
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
  fetchEtudiantProfile();
});

// --- Fonction pour envoyer un message ---
const sendMessage = () => {
  // Il est important d'envoyer le message à l'ID de l'utilisateur de l'ÉTUDIANT, pas à l'ID de l'entreprise.
  const recipientUserId = etudiant.value.user?.id; // Correction ici : utilisez etudiant.value.user?.id
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
/* Variables de base - cohérentes avec EntrepriseOffre.vue */
:root {
  --primary-blue: #0d6efd; /* Couleur primaire de Bootstrap */
  --light-gray-bg: #f8f9fa;
  --dark-text: #212529;
  --secondary-text: #6c757d;
  --border-color: #dee2e6;
  --success-green: #198754;
  --danger-red: #dc3545;
  --warning-orange: #ffc107;
}

.content-page {
  padding: 1.5rem; /* Ajustement du padding général de la page */
  background-color: var(--light-gray-bg);
  min-height: calc(100vh - 60px); /* Pour s'adapter au dashboard */
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
  color: var(--dark-text);
}

/* Styles pour la section de couverture (lecture seule) */
.profile-cover-section-view {
  height: 250px; /* Hauteur fixe pour la couverture */
  overflow: hidden;
  margin-bottom: -40px !important; /* Pour que le logo de profil chevauche un peu */
  border: 1px solid var(--border-color); /* Bordure légère */
  background-color: #e9ecef; /* Couleur de fond si l'image ne charge pas */
}

.profile-cover-image-view {
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Styles pour la carte principale du profil (lecture seule) */
.profile-main-card-view {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); /* Ombre légère */
  position: relative; /* Pour positionner correctement le contenu par rapport à la couverture */
  padding-top: 60px !important; /* Ajuste le padding pour le chevauchement */
  z-index: 1; /* Assure que la carte est au-dessus de la couverture */
}

.profile-avatar-placeholder-view {
  width: 200px; /* Taille légèrement augmentée pour le logo de profil */
  height: 200px;
  border-radius: 50%;
  background-color: var(--light-gray-bg);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 4px solid white; /* Bordure blanche autour du logo */
  box-shadow: 0 2px 5px rgba(0,0,0,0.2); /* Ombre pour le logo */
  margin-top: -110px; /* Remonte le logo pour chevaucher la couverture */
}

.profile-avatar-placeholder-view .student-logo-view {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info-view h3 {
  margin-bottom: 0.25rem;
}


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

/* Styles pour les sections "informations complémentaires" (Compétences, Projets, Parcours) */
.section-header {
  font-size: 1.25rem;
  color: var(--dark-text);
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--primary-blue);
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
  opacity: 0; /* Hidden by default */
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
  background-color: var(--primary-blue);
  color: black;
}
  
.btn-secondary{
    background-color: #5a6268;
    border-color: #5a6268;
    color: white;
}
</style>