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

    <div v-if="isLoadingProfile" class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="ms-3">Chargement du profil entreprise...</p>
    </div>
    
    <div v-else>
    <section class="profile-cover-section bg-light rounded shadow-sm mb-4 position-relative">
      <img :src="displayedCouverture" alt="Image de couverture de l'entreprise" class="profile-cover-image w-100 rounded-top" />
      <div class="cover-overlay p-3 d-flex justify-content-end align-items-center">
        <label for="cover-upload-input" class="btn btn-sm btn-light d-flex align-items-center cursor-pointer" :disabled="isUploadingCover">
          <span v-if="isUploadingCover" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <i v-else class="fas fa-camera me-2"></i> {{ isUploadingCover ? 'Chargement...' : 'Modifier la couverture' }}
          <input type="file" id="cover-upload-input" class="d-none" @change="handleCoverUpload" accept="image/*" :disabled="isUploadingCover" />
        </label>
      </div>
    </section>

    <section class="profile-main-card bg-white p-4 rounded shadow-sm mb-4">
      <div class="profile-header d-flex align-items-start pb-4 mb-4 border-bottom">
        <div class="profile-avatar-placeholder me-4 position-relative">
          <img :src="displayedPhoto" :alt="`Profil de ${nom_entreprise}`" class="company-logo" />
          <label for="profile-upload-input" class="edit-profile-icon position-absolute bg-primary text-white rounded-circle p-1 d-flex justify-content-center align-items-center cursor-pointer" :disabled="isUploadingPhoto">
            <span v-if="isUploadingPhoto" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-camera"></i>
            <input type="file" id="profile-upload-input" class="d-none" @change="handleProfileUpload" accept="image/*" :disabled="isUploadingPhoto" />
          </label>
        </div>
        <div class="profile-info flex-grow-1">
          <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
            {{ nom_entreprise }}
            <i class="fas fa-pen-to-square ms-2 text-secondary fs-6 cursor-pointer" data-bs-toggle="modal" data-bs-target="#editProfileModal" @click="loadProfileForEditModal"></i>
          </h3>
          <p class="status-online fs-6 text-success d-flex align-items-center">
            <span class="dot bg-success me-1"></span> En ligne
          </p>
        </div>
      </div>

      <div class="description-field pb-4 mb-4 border-bottom">
        <label for="company-description-profile" class="form-label fw-bold text-dark mb-2">Description de l'entreprise :</label>
        <textarea
          id="company-description-profile"
          class="form-control"
          rows="4"
          placeholder="Décrivez votre entreprise ici..."
          v-model="description"
          @input="hasDescriptionChanged = true"
          :disabled="isSavingDescription || isLoadingProfile"
        ></textarea>
        <button
          class="btn btn-primary mt-2"
          :disabled="!hasDescriptionChanged || isSavingDescription || isLoadingProfile"
          @click="saveDescription"
        >
          <span v-if="isSavingDescription" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          {{ isSavingDescription ? 'Enregistrement...' : 'Enregistrer la description' }}
        </button>
      </div>

      <div class="profile-details">
        <p class="mb-2 text-dark d-flex align-items-center">
          <i class="fas fa-industry me-2 text-primary fs-5"></i><span class="fw-bold">Domaine d'activité/Secteur :</span> {{ domaine_entreprise || 'Non spécifié' }}
        </p>
        <p class="mb-2 text-dark d-flex align-items-center">
          <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Mail :</span> {{ email_entreprise || 'Non spécifié' }}
        </p>
        <p class="mb-2 text-dark d-flex align-items-center">
          <i class="fas fa-globe me-2 text-primary fs-5"></i><span class="fw-bold">Site web :</span>
          <a v-if="siteweb" :href="siteweb" target="_blank">{{ siteweb }}</a>
          <span v-else>Non spécifié</span>
        </p>
        <p class="mb-0 text-dark d-flex align-items-center">
          <i class="fa-solid fa-city me-2 text-primary fs-5"></i><span class="fw-bold">Ville :</span> {{ nom_ville || 'Non spécifié' }}
        </p>
        <p class="mb-0 text-dark d-flex align-items-center">
          <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span> {{ adresse_entreprise || 'Non spécifié' }}
        </p>
      </div>
    </section>

    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;"><h1 class="modal-title fs-5" id="editProfileModalLabel">Modifiez profil entreprise</h1></span>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="editProfile">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nomEntreprise" class="form-label">Nom de l'entreprise</label>
                  <input type="text" class="form-control" id="nomEntreprise" v-model="editProfileForm.nom_entreprise"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="emailEntreprise" class="form-label">Email de contact</label>
                  <input type="email" class="form-control" id="emailEntreprise" v-model="editProfileForm.email_entreprise"/>
                </div>
              </div>

              <div class="mb-3">
                <label for="siteWebEntreprise" class="form-label">Site web</label>
                <input
                  type="url"
                  class="form-control"
                  id="siteWebEntreprise"
                  v-model="editProfileForm.siteweb"
                  placeholder="https://www.exemple.com"
                />
              </div>

              <div class="mb-3">
                <label for="adresseEntreprise" class="form-label">Adresse complète</label>
                <textarea
                  class="form-control"
                  id="adresseEntreprise"
                  v-model="editProfileForm.adresse_entreprise"
                  rows="3"
                  placeholder="Adresse complète de l'entreprise"
                ></textarea>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="domaineActivite" class="form-label">Domaine d'activité</label>
                  <select class="form-select" id="domaineActivite" v-model="editProfileForm.id_domaine">
                    <option value="">Sélectionnez un domaine</option>
                    <option
                      v-for="domaine in listeDomaines"
                      :key="domaine.id"
                      :value="domaine.id"
                    >
                      {{ domaine.libdomaine }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="villeEntreprise" class="form-label">Ville</label>
                  <select class="form-select" id="villeEntreprise" v-model="editProfileForm.ville_id">
                    <option value="">Sélectionnez une ville</option>
                    <option v-for="ville in listeVilles" :key="ville.id" :value="ville.id">
                      {{ ville.nom_ville }}
                    </option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" :disabled="isSavingProfile">Fermer</button>
            <button type="submit" class="btn btn-primary" @click="editProfile" :disabled="isSavingProfile">
              <span v-if="isSavingProfile" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isSavingProfile ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../axios'; 
import { Modal, Toast } from 'bootstrap';

// --- Variables Réactives du Profil de l'Entreprise ---
const photo = ref('');
const couverture = ref('');
const description = ref('Décrivez votre entreprise ici...');

const nom_entreprise = ref('Nom de l\'entreprise');
const email_entreprise = ref('');
const siteweb = ref('');
const adresse_entreprise = ref('');
// const contact_entreprise = ref(''); // <--- SUPPRIMÉ

const nom_ville = ref('');
const domaine_entreprise = ref('');

const entrepriseId = ref(null);

// Formulaire de modification (initialisé vide pour ne pas pré-remplir)
const editProfileForm = ref({
  nom_entreprise: '',
  email_entreprise: '',
  // contact_entreprise: '', // <--- SUPPRIMÉ
  siteweb: '',
  adresse_entreprise: '',
  id_domaine: '',
  ville_id: '',
});

const hasDescriptionChanged = ref(false);

// --- Listes pour les sélections dans le modal ---
const listeDomaines = ref([]);
const listeVilles = ref([]);


// --- Variables d'État (pour les indicateurs de chargement et d'action) ---
const isLoadingProfile = ref(true);
const isUploadingPhoto = ref(false);
const isUploadingCover = ref(false);
const isSavingDescription = ref(false);
const isSavingProfile = ref(false);
const isLoadingLists = ref(false);

const toastMessage = ref('');
const toastType = ref('success');

// Instance du modal Bootstrap
let editProfileModalInstance;

// --- Propriétés Calculées pour l'Affichage des Images avec Cache Busting ---
const displayedPhoto = computed(() => {
  // REMPLACEZ '/path/to/default-avatar.png' par le chemin réel de votre image par défaut
  const url = photo.value || '/photo-default.jpeg';
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});
const displayedCouverture = computed(() => {
  // REMPLACEZ '/path/to/default-cover.jpg' par le chemin réel de votre image par défaut
  const url = couverture.value || ''; // La couverture peut être vide au début
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
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

// --- Fonctions de Gestion du Profil ---

/**
 * Cette fonction est appelée automatiquement quand le composant est créé (monté).
 * Elle charge toutes les données initiales du profil et des listes depuis votre API.
 */
onMounted(async () => {
  try {
    isLoadingProfile.value = true;
    isLoadingLists.value = true;
    

    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'entreprise' && user.role_details) {
      photo.value = user.photo ;
      couverture.value = user.couverture ;
      description.value = user.description || 'Décrivez votre entreprise ici...';

      const entrepriseDetails = user.role_details;
      entrepriseId.value = entrepriseDetails.entreprise_id;
      nom_entreprise.value = user.name || entrepriseDetails.nom_entreprise;
      email_entreprise.value = entrepriseDetails.email_entreprise;
      siteweb.value = entrepriseDetails.siteweb;
      adresse_entreprise.value = entrepriseDetails.adresse;
      // contact_entreprise.value = entrepriseDetails.contact || 'Non spécifié'; // <--- SUPPRIMÉ

      nom_ville.value = entrepriseDetails.ville ? entrepriseDetails.ville.nom_ville : 'Non spécifié';
      domaine_entreprise.value = entrepriseDetails.domaine ? entrepriseDetails.domaine.libdomaine : 'Non spécifié';


    } else {
      console.warn("Le profil récupéré n'est pas celui d'une entreprise ou est incomplet.");
      displayToast("Impossible de charger le profil de l'entreprise.", "danger");
    }
  } catch (error) {
    console.error('Erreur générale lors de la récupération du profil :', error);
    displayToast("Erreur lors du chargement du profil.", "danger");
  } finally {
    isLoadingProfile.value = false;
  }

  try {
    const [domainesResponse, villesResponse] = await Promise.all([
      axios.get('/api/domaines'),
      axios.get('/api/villes')
    ]);
    listeDomaines.value = domainesResponse.data;
    listeVilles.value = villesResponse.data;
  } catch (error) {
    console.error('Erreur lors du chargement des listes de domaines et villes :', error);
    displayToast("Erreur lors du chargement des options de formulaire.", "danger");
  } finally {
    isLoadingLists.value = false;
    const modalElement = document.getElementById('editProfileModal');
    if (modalElement && window.bootstrap && window.bootstrap.Modal) {
        editProfileModalInstance = new window.bootstrap.Modal(modalElement);
    }
  }
});

/**
 * Réinitialise le formulaire du modal d'édition pour que tous les champs soient pré-remplies.
 * Cette fonction est appelée lorsqu'on ouvre le modal.
 */
const loadProfileForEditModal = () => {
  editProfileForm.value = {
    nom_entreprise: nom_entreprise.value,
    email_entreprise: email_entreprise.value,
    // contact_entreprise: contact_entreprise.value === 'Non spécifié' ? '' : contact_entreprise.value, // <--- SUPPRIMÉ
    siteweb: siteweb.value,
    adresse_entreprise: adresse_entreprise.value,
    // Pour les IDs, vous devez trouver la correspondance avec les listes chargées
    // Si vos API renvoient les IDs directement, utilisez-les.
    // Sinon, vous devrez faire une recherche dans listeDomaines et listeVilles.
    id_domaine: listeDomaines.value.find(d => d.libdomaine === domaine_entreprise.value)?.id || '',
    ville_id: listeVilles.value.find(v => v.nom_ville === nom_ville.value)?.id || '',
  };
};

/**
 * Gère le téléchargement d'une nouvelle photo de profil.
 * Lit le fichier, l'affiche en aperçu, puis l'envoie à l'API.
 * @param {Event} event - L'événement de changement de fichier.
 */
const handleProfileUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    isUploadingPhoto.value = true;
    const reader = new FileReader();
    reader.onload = (e) => {
      photo.value = e.target.result;
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('photo', file);

    try {
      const response = await axios.post('/api/photo-update', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      const newPhotoPath = response.data.photo_url || response.data;
      photo.value = `${newPhotoPath}?t=${Date.now()}`;
      displayToast('Photo de profil mise à jour avec succès !', 'success');
    } catch (error) {
      console.error('Erreur lors de l\'upload de la photo de profil :', error);
      displayToast('Échec de la mise à jour de la photo de profil.', 'danger');
    } finally {
      isUploadingPhoto.value = false;
    }
  }
};

/**
 * Gère le téléchargement d'une nouvelle image de couverture.
 * Fonctionne de manière similaire à 'handleProfileUpload'.
 * @param {Event} event - L'événement de changement de fichier.
 */
const handleCoverUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    isUploadingCover.value = true;
    const reader = new FileReader();
    reader.onload = (e) => {
      couverture.value = e.target.result;
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('couverture', file);

    try {
      const response = await axios.post('/api/couverture-update', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      const newCoverPath = response.data.couverture_url || response.data;
      couverture.value = `${newCoverPath}?t=${Date.now()}`;
      displayToast('Image de couverture mise à jour avec succès !', 'success');
    } catch (error) {
      console.error('Erreur lors de l\'upload de l\'image de couverture :', error);
      displayToast('Échec de la mise à jour de l\'image de couverture.', 'danger');
    } finally {
      isUploadingCover.value = false;
    }
  }
};

/**
 * Sauvegarde la description de l'entreprise.
 * Envoie le texte de la description à l'API.
 */
const saveDescription = async () => {
  isSavingDescription.value = true;
  try {
    const response = await axios.post('/api/description-update', {
      description: description.value
    });
    description.value = response.data.description;
    displayToast('Description enregistrée avec succès !', 'success');
    hasDescriptionChanged.value = false;
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement de la description :', error);
    displayToast('Échec de l\'enregistrement de la description.', 'danger');
  } finally {
    isSavingDescription.value = false;
  }
};

/**
 * Gère l'enregistrement du formulaire d'édition complet du profil (dans le modal).
 * Envoie toutes les données modifiées à l'API.
 */
const editProfile = async () => {
  isSavingProfile.value = true;
  try {
    if (!entrepriseId.value) {
      displayToast("ID de l'entreprise manquant pour la mise à jour.", "danger");
      isSavingProfile.value = false;
      return;
    }

    const payload = {
      nom_entreprise: editProfileForm.value.nom_entreprise,
      email_entreprise: editProfileForm.value.email_entreprise,
      // contact: editProfileForm.value.contact_entreprise, // <--- SUPPRIMÉ
      siteweb: editProfileForm.value.siteweb,
      adresse: editProfileForm.value.adresse_entreprise,
      id_domaine: editProfileForm.value.id_domaine,
      ville_id: editProfileForm.value.ville_id,
    };

    await axios.put(`/api/entreprises/${entrepriseId.value}`, payload);

    // Recharger toutes les données du profil pour s'assurer que l'affichage est à jour
    await reloadProfileData();
    
    displayToast('Profil de l\'entreprise mis à jour avec succès !', 'success');
    editProfileModalInstance.hide();
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil :', error);
    displayToast('Échec de la mise à jour du profil.', 'danger');
  } finally {
    isSavingProfile.value = false;
  }
};

/**
 * Recharge les données complètes du profil depuis l'API pour s'assurer que
 * l'affichage est à jour après une modification.
 */
const reloadProfileData = async () => {
  try {
    
    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'entreprise' && user.role_details) {
      photo.value = user.photo ;
      couverture.value = user.couverture ;
      description.value = user.description || 'Décrivez votre entreprise ici...';

      const entrepriseDetails = user.role_details;
      nom_entreprise.value = user.name || entrepriseDetails.nom_entreprise;
      email_entreprise.value = entrepriseDetails.email_entreprise;
      siteweb.value = entrepriseDetails.siteweb;
      adresse_entreprise.value = entrepriseDetails.adresse;
      // contact_entreprise.value = entrepriseDetails.contact || 'Non spécifié'; // <--- SUPPRIMÉ

      nom_ville.value = entrepriseDetails.ville ? entrepriseDetails.ville.nom_ville : 'Non spécifié';
      domaine_entreprise.value = entrepriseDetails.domaine ? entrepriseDetails.domaine.libdomaine : 'Non spécifié';
    }
  } catch (error) {
    console.error('Erreur lors du rechargement des données du profil :', error);
  }
};

</script>

<style scoped>
/* Vos styles CSS restent inchangés */
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
}

/* Styles pour la section de couverture */
.profile-cover-section {
  height: 250px; /* Hauteur fixe pour la couverture */
  overflow: hidden;
  margin-bottom: -40px !important; /* Pour que le logo de profil chevauche un peu */
  border: 1px solid var(--border-color); /* Bordure légère */
}

.profile-cover-image {
  height: 100%;
  object-fit: cover;
  display: block;
}

.cover-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(0,0,0,0.4), rgba(0,0,0,0)); /* Dégradé pour que le texte soit lisible */
  color: white;
  border-radius: inherit; /* Hérite le border-radius du parent */
}

/* Styles pour la carte principale du profil */
.profile-main-card {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); /* Ombre légère */
  position: relative; /* Pour positionner correctement le contenu par rapport à la couverture */
  padding-top: 60px !important; /* Ajuste le padding pour le chevauchement */
  z-index: 1; /* Assure que la carte est au-dessus de la couverture */
}

.profile-avatar-placeholder {
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

.profile-avatar-placeholder .company-logo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.profile-info h3 i {
  cursor: pointer;
}

.status-online .dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.description-field textarea {
  resize: vertical;
  min-height: 80px;
}

/* Icône de modification du profil */
.edit-profile-icon {
  width: 50px;
  height: 50px;
  font-size: 0.9rem;
  right: 8px; /* Positionnement par rapport au coin inférieur droit de l'avatar */
  bottom: 5px;
}

/* Styles pour le bouton "Enregistrer la description" */
.description-field .btn {
  font-size: 0.9rem; /* Taille de police légèrement plus petite */
  padding: 0.5rem 1rem; /* Padding pour un bouton plus compact */
}


.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead th {
  background-color: var(--light-gray-bg);
  color: var(--dark-text);
  font-weight: bold;
  border-top: none;
  border-bottom: 1px solid var(--border-color);
}

.table tbody tr {
  background-color: white;
}

.table tbody tr td {
  border-bottom: 1px solid var(--border-color);
  color: var(--dark-text);
  vertical-align: middle;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.status.badge {
  padding: 0.4em 0.8em;
  font-size: 0.95em;
  font-weight: 600;
  display: inline-block;
}

.bg-success-subtle { background-color: #d1e7dd !important; }
.text-success { color: var(--success-green) !important; }
.bg-warning-subtle { background-color: #fff3cd !important; }
.text-warning { color: var(--warning-orange) !important; }

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
</style>