<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <!-- Titre dynamique basé sur le type de profil et le nom de l'entité -->
        <h1 class="text-dark page-main-title mb-0">Profil de {{ profileTypeDisplay }} : {{ profileName || 'Chargement...' }}</h1>
      </span>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
      </button>
    </div>

    <!-- Conteneur pour les notifications rapides (toasts) -->
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
        <p class="mt-3 mb-0">Chargement du profil {{ profileTypeDisplay.toLowerCase() }}...</p>
      </div>
      <div v-else-if="error" class="text-center text-danger py-5">
        <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
        <p>{{ error }}</p>
      </div>
      <div v-else class="profile-content">
        <!-- Section de l'image de couverture du profil -->
        <section class="profile-cover-section-view bg-light rounded shadow-sm mb-4 position-relative">
          <!-- Accès direct à profileData.couverture -->
          <img :src="getCoverPhoto(profileData.couverture)" :alt="`Image de couverture de ${profileName}`" class="profile-cover-image-view w-100 rounded-top" />
        </section>

        <!-- Carte principale du profil (nom, logo, description, détails) -->
        <section class="profile-main-card-view bg-white p-4 rounded shadow-sm mb-4">
          <div class="profile-header-view d-flex align-items-start pb-4 mb-4 border-bottom">
            <div class="profile-avatar-placeholder-view me-4 position-relative">
              <!-- Accès direct à profileData.photo -->
              <img :src="getProfilePhoto(profileData.photo)" :alt="`Profil de ${profileName}`" :class="getLogoClass" />
            </div>
            <div class="profile-info-view flex-grow-1">
              <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
                {{ profileName || 'Nom non spécifié' }}
              </h3>
            </div>
          </div>

          <!-- Champ de description du profil -->
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <label class="form-label fw-bold text-dark mb-2">
              Description du {{ profileTypeDisplay.toLowerCase() }} :
            </label>
            <!-- Accès direct à profileData.description -->
            <p class="form-control-static" style="white-space: pre-wrap;">
              {{ profileData.description || `${profileTypeDisplay.toLowerCase()} n'a pas encore ajouté de description.` }}
            </p>
          </div>

         

          <!-- Détails affichables du profil (séparés par type d'utilisateur) -->
          <div class="profile-details-view pb-4 mb-4">

            <!-- Détails pour les ÉTUDIANTS -->
            <!-- Condition basée sur profileData.role et présence de profileData.etudiant -->
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
                <!-- Accès via la relation imbriquée sur l'objet etudiant -->
                {{ profileData.etudiant.etablissement?.nom_etablissement || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span>
                <!-- Accès via la relation imbriquée sur l'objet etudiant -->
                {{ profileData.etudiant.filiere?.libfil || 'Non spécifiée' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span>
                <!-- Accès via la relation imbriquée sur l'objet etudiant -->
                {{ profileData.etudiant.annee?.libannee || 'Non spécifiée' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2">
                <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
                <!-- Accès via la relation imbriquée sur l'objet etudiant -->
                <a v-if="profileData.etudiant.CV" :href="getDownloadUrl(profileData.etudiant.CV)" target="_blank" class="ms-1 btn btn-sm btn-outline-primary">Voir le CV</a>
                <span v-else class="ms-1">Non téléchargé</span>
              </p>
            </template>

            <!-- Détails pour les ENTREPRISES -->
            <!-- Condition basée sur profileData.role et présence de profileData.entreprise -->
            <template v-else-if="profileData.role === 'entreprise' && profileData.entreprise">
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span>
                <!-- Accès via la relation imbriquée sur l'objet entreprise -->
                {{ profileData.entreprise.domaine?.libdomaine || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span>
                {{ profileData.entreprise.email_entreprise || 'Non spécifié' }}
              </p>
              <p class="mb-2 text-dark d-flex align-items-center">
                <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
                <!-- Accès via la relation imbriquée sur l'objet entreprise -->
                <a v-if="profileData.entreprise.siteweb" :href="profileData.entreprise.siteweb" target="_blank">{{ profileData.entreprise.siteweb }}</a>
                <span v-else>Non spécifié</span>
              </p>
              <p class="mb-0 text-dark d-flex align-items-center mt-2">
                <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span>
                <!-- Accès via la relation imbriquée sur l'objet entreprise -->
                {{ profileData.entreprise.ville?.nom_ville || 'Non spécifié' }}
              </p>
              <p class="mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span>
                <!-- Accès via la relation imbriquée sur l'objet entreprise -->
                {{ profileData.entreprise.adresse || 'Non spécifié' }}
              </p>
            </template>
            
            <!-- Détails pour les ÉTABLISSEMENTS et ADMINISTRATEURS (ou autres rôles sans profil spécifique imbriqué) -->
            <!-- Ces rôles n'ont pas de relation 'etablissement' ou 'admin' chargée dans le JSON fourni.
                 Leurs détails sont donc tirés directement des propriétés de l'objet User principal. -->
            <template v-else-if="profileData.role === 'etablissement' || profileData.role === 'admin'">
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
            
            <!-- Fallback générique pour tout autre rôle non spécifié ou données manquantes -->
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

        <!-- Sections additionnelles spécifiques à l'étudiant (Compétences, Projets, Parcours Académique) -->
        <!-- Condition basée sur profileData.role et présence de profileData.etudiant -->
        <section v-if="profileData.role === 'etudiant' && profileData.etudiant" class="bg-white p-4 rounded shadow-sm mb-4">
          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Compétences</span>
          </h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.etudiant.competences || 'L\'étudiant n\'a pas encore listé ses compétences.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Projets</span>
          </h2>
          <div class="description-field-view pb-4 mb-4 border-bottom">
           
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.etudiant.projets || 'L\'étudiant n\'a pas encore décrit ses projets.' }}</p>
          </div>

          <h2 class="form-label fw-bold text-dark mb-3 section-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Parcours Académique</span>
          </h2>
          <div class="description-field-view pb-4 mb-4">
       
            <p class="form-control-static" style="white-space: pre-wrap;">{{ profileData.etudiant.parcours || 'L\'étudiant n\'a pas encore décrit son parcours académique.' }}</p>
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

// Le composant reçoit l'ID de l'utilisateur via les props de la route
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

// profileData stockera l'objet User complet renvoyé par l'API, avec ses relations (etudiant, entreprise)
const profileData = ref(null); 

const loading = ref(true);
const error = ref(null);
const router = useRouter();

const toastMessage = ref('');
const toastType = ref('success');
let liveToast = null; // Pour stocker l'instance du toast

// Propriétés calculées pour l'affichage dynamique des titres et noms
const profileTypeDisplay = computed(() => {
  if (!profileData.value) return 'le profil';
  switch (profileData.value.role) {
    case 'etudiant': return 'l\'étudiant';
    case 'entreprise': return 'l\'entreprise';
    case 'etablissement': return 'l\'établissement';
    case 'admin': return 'l\'administrateur';
    default: return 'le profil';
  }
});

const profileName = computed(() => {
  if (!profileData.value) return 'Chargement...';

  // Logique pour le nom de l'entité basée sur le rôle et les relations
  if (profileData.value.role === 'etudiant' && profileData.value.etudiant) {
    return profileData.value.etudiant.nom_etudiant || profileData.value.name;
  } else if (profileData.value.role === 'entreprise' && profileData.value.entreprise) {
    return profileData.value.entreprise.nom_entreprise || profileData.value.name;
  } else if (profileData.value.role === 'etablissement') {
    // Pour les utilisateurs de rôle 'etablissement', on prend le 'name' de l'objet User principal.
    // Si une relation 'etablissement' est chargée par AdminController::showUser,
    // vous pouvez la prioriser ici: profileData.value.etablissement?.nom_etablissement || profileData.value.name;
    return profileData.value.name || 'Nom non spécifié';
  }
  // Pour les admins ou autres rôles
  return profileData.value.name || 'Nom non spécifié';
});

// Classe CSS dynamique pour le logo de profil
const getLogoClass = computed(() => {
  if (!profileData.value) return '';
  switch (profileData.value.role) {
    case 'etudiant': return 'student-logo-view';
    case 'entreprise': return 'company-logo-view';
    case 'etablissement': return 'school-logo-view';
    default: return '';
  }
});

/**
 * Récupère les données du profil en fonction de l'ID de l'utilisateur.
 * Utilise la route /api/admin/users/{id}
 */
const fetchProfile = async () => {
  loading.value = true;
  error.value = null;

  if (!props.id) {
    error.value = `ID utilisateur manquant dans l'URL.`;
    loading.value = false;
    return;
  }

  try {
    // Appel à la route AdminController::showUser
    // Cette route doit renvoyer l'objet User avec les relations 'etudiant' et 'entreprise' chargées
    const response = await axios.get(`/api/admin/users/${props.id}`);
    profileData.value = response.data; // Stocke directement l'objet User complet

    // Vérification supplémentaire si l'objet user est manquant ou incomplet après le fetch
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

/**
 * Construit l'URL complète pour l'image de profil.
 * @param {string} photoPath - Le chemin relatif de l'image (ex: 'profile_photos/avatar.jpg').
 * @returns {string} L'URL complète de l'image ou une image par défaut.
 */
const getProfilePhoto = (photoPath) => {
  if (photoPath && photoPath.startsWith('http')) {
    return photoPath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return photoPath ? `${baseUrl}/storage/${photoPath}` : '/photo-default.jpeg';
};

/**
 * Construit l'URL complète pour l'image de couverture.
 * @param {string} coverPath - Le chemin relatif de l'image (ex: 'couverture/cover.jpg').
 * @returns {string} L'URL complète de l'image ou une image par défaut.
 */
const getCoverPhoto = (coverPath) => {
  if (coverPath && coverPath.startsWith('http')) {
    return coverPath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return coverPath ? `${baseUrl}/storage/${coverPath}` : '/cover-default.jpeg';
};

/**
 * Construit l'URL complète pour le téléchargement d'un fichier (ex: CV).
 * @param {string} filePath - Le chemin relatif du fichier (ex: 'cv/mon_cv.pdf').
 * @returns {string} L'URL complète du fichier pour le téléchargement.
 */
const getDownloadUrl = (filePath) => {
  if (filePath && filePath.startsWith('http')) {
    return filePath;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return `${baseUrl}/storage/${filePath}`;
};

/**
 * Retourne à la page précédente dans l'historique du navigateur.
 */
const goBack = () => {
  router.go(-1);
};

/**
 * Affiche une notification de type "toast".
 * @param {string} message - Le message à afficher.
 * @param {string} type - Le type de toast (ex: 'success', 'danger', 'warning', 'info').
 */
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    if (!liveToast) { // Crée l'instance de toast une seule fois
      liveToast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    }
    liveToast.show();
  }
};


// --- Watcher pour recharger le profil si 'id' change ---
watch(() => props.id, () => {
  fetchProfile();
}, { immediate: true }); // immediate: true exécute le watcher au montage initial

</script>

<style scoped>
/* Les styles CSS sont repris des précédents composants pour une cohérence visuelle */
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

/* Classes dynamiques pour le logo de profil */
.profile-avatar-placeholder-view .company-logo-view, /* Pour entreprise */
.profile-avatar-placeholder-view .school-logo-view, /* Pour établissement */
.profile-avatar-placeholder-view .student-logo-view { /* Pour étudiant - renommée de student-avatar-view */
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
 
.btn-secondary{
  background-color: #5a6268;
  border-color: #5a6268;
  color: white;
}
</style>