<template>
  <div class="content-page p-4">
    <!-- Conteneur pour les notifications rapides (toasts) -->
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
      <p class="ms-3">Chargement du profil établissement...</p>
    </div>
    
    <div v-else>
    <!-- Section de l'image de couverture de l'entreprise -->
    <section class="profile-cover-section bg-light rounded shadow-sm mb-4 position-relative">
      <!-- L'image de couverture affichée. 'displayedCouverture' ajoute un code spécial pour forcer le rafraîchissement. -->
      <img :src="displayedCouverture" alt="Image de couverture de l'entreprise" class="profile-cover-image w-100 rounded-top" />
      <div class="cover-overlay p-3 d-flex justify-content-end align-items-center">
        <!-- Bouton pour modifier la couverture. Il est désactivé si une image est en cours d'upload. -->
        <label for="cover-upload-input" class="btn btn-sm btn-light d-flex align-items-center cursor-pointer" :disabled="isUploadingCover">
          <!-- Affiche un petit cercle animé (spinner) si l'upload est en cours -->
          <span v-if="isUploadingCover" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <i v-else class="fas fa-camera me-2"></i> {{ isUploadingCover ? 'Chargement...' : 'Modifier la couverture' }}
          <!-- Champ de fichier caché qui déclenche la fonction 'handleCoverUpload' quand un fichier est choisi -->
          <input type="file" id="cover-upload-input" class="d-none" @change="handleCoverUpload" accept="image/*" :disabled="isUploadingCover" />
        </label>
      </div>
    </section>

    <!-- Carte principale du profil de l'entreprise (nom, logo, description, détails) -->
    <section class="profile-main-card bg-white p-4 rounded shadow-sm mb-4">
      <div class="profile-header d-flex align-items-start pb-4 mb-4 border-bottom">
        <div class="profile-avatar-placeholder me-4 position-relative">
          <!-- L'avatar (logo) de l'entreprise. 'displayedPhoto' fait aussi le rafraîchissement. -->
          <img :src="displayedPhoto" :alt="`Profil de ${nom_etablissement}`" class="school-logo" />
          <!-- Bouton/icône pour modifier le logo, désactivé pendant l'upload. -->
          <label for="profile-upload-input" class="edit-profile-icon position-absolute bg-primary text-white rounded-circle p-1 d-flex justify-content-center align-items-center cursor-pointer" :disabled="isUploadingPhoto">
            <span v-if="isUploadingPhoto" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-camera"></i>
            <!-- Champ de fichier caché pour l'upload du logo -->
            <input type="file" id="profile-upload-input" class="d-none" @change="handleProfileUpload" accept="image/*" :disabled="isUploadingPhoto" />
          </label>
        </div>
        <div class="profile-info flex-grow-1">
          <!-- Nom de l'entreprise avec une icône d'édition. Cliquer ouvre le modal de modification. -->
          <h3 class="mb-1 fs-4 text-dark d-flex align-items-center">
            {{ nom_etablissement }}
            <i class="fas fa-pen-to-square ms-2 text-secondary fs-6 cursor-pointer" data-bs-toggle="modal" data-bs-target="#editProfileModal" @click="loadProfileForEditModal"></i>
          </h3>
          <!-- Indicateur de statut "En ligne" -->
          <p class="status-online fs-6 text-success d-flex align-items-center">
            <span class="dot bg-success me-1"></span> En ligne
          </p>
        </div>
      </div>

      <!-- Champ de description de l'entreprise -->
      <div class="description-field pb-4 mb-4 border-bottom">
        <label for="school-description-profile" class="form-label fw-bold text-dark mb-2">Description de l'établissement :</label>
        <textarea
          id="school-description-profile"
          class="form-control"
          rows="4"
          placeholder="Décrivez votre établissement ici..."
          v-model="description"
          @input="hasDescriptionChanged = true"
          :disabled="isSavingDescription || isLoadingProfile"
        ></textarea>
        <!-- Bouton pour enregistrer la description. Désactivé si rien n'a changé ou si l'enregistrement est en cours. -->
        <button
          class="btn btn-primary mt-2"
          :disabled="!hasDescriptionChanged || isSavingDescription || isLoadingProfile"
          @click="saveDescription"
        >
          <span v-if="isSavingDescription" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          {{ isSavingDescription ? 'Enregistrement...' : 'Enregistrer la description' }}
        </button>
      </div>

      <!-- Détails affichables du profil de l'établissement -->
      <div class="profile-details">
        <p class="mb-2 text-dark d-flex align-items-center">
          <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Mail :</span> {{ email_etablissement || 'Non spécifié' }}
        </p>
        <p class="mb-2 text-dark d-flex align-items-center">
          <i class="fas fa-phone me-2 text-primary fs-5"></i><span class="fw-bold">Contact :</span> {{ contact_etablissement || 'Non spécifié' }}
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
          <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i><span class="fw-bold">Adresse :</span> {{ adresse_etablissement || 'Non spécifié' }}
        </p>
      </div>
    </section>

    </div>

    <!-- Modal (fenêtre pop-up) pour l'édition du profil complet de l'établissement -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <span style="text-decoration: underline; text-decoration-color: #0d6efd;"><h1 class="modal-title fs-5" id="editProfileModalLabel">Modifiez profil établissement</h1></span>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Formulaire d'édition lié à 'editProfileForm'. @submit.prevent empêche le rechargement de la page. -->
            <form @submit.prevent="editProfile">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nomEtablissement" class="form-label">Nom de l'établissement</label>
                  <input type="text" class="form-control" id="nomEtablissement" v-model="editProfileForm.nom_etablissement"/>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="contactEtablissement" class="form-label">Contact</label>
                  <input type="text" class="form-control" id="contactEtablissement" v-model="editProfileForm.contact_etablissement" />
                </div>
              </div>

              <div class="mb-3">
                <label for="emailEtablissement" class="form-label">Email</label>
                <input type="email" class="form-control" id="emailEtablissement" v-model="editProfileForm.email_etablissement"/>             
              </div>

              <div class="mb-3">
                <label for="siteWebEtablissement" class="form-label">Site web</label>
                <input
                  type="url"
                  class="form-control"
                  id="siteWebEtablissement"
                  v-model="editProfileForm.siteweb"
                  placeholder="https://www.exemple.com"
                />
              </div>

              <div class="mb-3">
                <label for="adresseEtablissement" class="form-label">Adresse complète</label>
                <textarea
                  class="form-control"
                  id="adresseEtablissement"
                  v-model="editProfileForm.adresse_etablissement"
                  rows="3"
                  placeholder="Adresse complète de l'établissement"
                ></textarea>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="villeEtablissement" class="form-label">Ville</label>
                  <!-- Liste déroulante des villes, remplie par 'listeVilles' -->
                  <select class="form-select" id="villeEtablissement" v-model="editProfileForm.ville_id">
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
            <!-- Boutons du modal. "Enregistrer" est désactivé pendant l'enregistrement. -->
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
const description = ref('Décrivez votre établissement ici...');

const nom_etablissement = ref('Nom de l\'établissement');
const email_etablissement = ref('');
const siteweb = ref('');
const adresse_etablissement = ref('');
const contact_etablissement = ref('');

const nom_ville = ref('');

const etablissementId = ref(null);

// Formulaire de modification (initialisé vide pour ne pas pré-remplir)
const editProfileForm = ref({
  nom_etablissement: '',
  email_etablissement: '',
  contact_etablissement: '',
  siteweb: '',
  adresse_etablissement: '',
  ville_id: '',
});

const hasDescriptionChanged = ref(false);

// --- Listes pour les sélections dans le modal ---
const listeVilles = ref([]);

// --- Données et Variables pour la Section Partenariat ---
const partnerships = ref([]);

const searchQuery = ref('');
const filterStatus = ref('');

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
  const url = couverture.value;
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
    const baseURL = import.meta.env.VITE_API_URL;

    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'etablissement' && user.role_details) {
      photo.value = user.photo ? `${baseURL}${user.photo}` : '';
      couverture.value = user.couverture ? `${baseURL}${user.couverture}` : '';
      description.value = user.description || 'Décrivez votre établissement ici...';

      const etablissementDetails = user.role_details;
      etablissementId.value = etablissementDetails.etablissement_id;
      nom_etablissement.value = user.name || etablissementDetails.nom_etablissement;
      email_etablissement.value = etablissementDetails.email_etablissement || 'Non spécifié';
      siteweb.value = etablissementDetails.siteweb;
      adresse_etablissement.value = etablissementDetails.adresse;
      contact_etablissement.value = etablissementDetails.contact || 'Non spécifié';

      nom_ville.value = etablissementDetails.ville ? etablissementDetails.ville.nom_ville : 'Non spécifié';

      try {
        const partnershipsResponse = await axios.get('/api/partenariats');
        partnerships.value = partnershipsResponse.data;
      } catch (partnershipError) {
        console.error('Erreur lors de la récupération des partenariats :', partnershipError);
        displayToast("Erreur lors du chargement des partenariats.", "danger");
      }

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
    const [villesResponse] = await Promise.all([
      axios.get('/api/villes')
    ]);
    listeVilles.value = villesResponse.data;
  } catch (error) {
    console.error('Erreur lors du chargement des listes de villes :', error);
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
 * Réinitialise le formulaire du modal d'édition pour que tous les champs soient vides.
 * Cette fonction est appelée lorsqu'on ouvre le modal.
 */
const loadProfileForEditModal = () => {
  editProfileForm.value = {
    nom_etablissement: nom_etablissement.value,
    email_etablissement: email_etablissement.value,
    contact_etablissement: contact_etablissement.value,
    siteweb: siteweb.value,
    adresse_etablissement: adresse_etablissement.value,
  
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
      const baseURL = import.meta.env.VITE_API_URL;
      const newPhotoPath = response.data.photo_url || response.data;
      photo.value = `${baseURL}${newPhotoPath}?t=${Date.now()}`;
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
      const baseURL = import.meta.env.VITE_API_URL;
      const newCoverPath = response.data.couverture_url || response.data;
      couverture.value = `${baseURL}${newCoverPath}?t=${Date.now()}`;
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
    if (!etablissementId.value) {
      displayToast("ID de l'entreprise manquant pour la mise à jour.", "danger");
      isSavingProfile.value = false;
      return;
    }

    const payload = {
      nom_etablissement: editProfileForm.value.nom_etablissement,
      email_etablissement: editProfileForm.value.email_etablissement,
      contact: editProfileForm.value.contact_etablissement,
      siteweb: editProfileForm.value.siteweb,
      adresse: editProfileForm.value.adresse_etablissement,
      ville_id: editProfileForm.value.ville_id,
    };

    await axios.put(`/api/etablissements/${etablissementId.value}`, payload);

    // Recharger toutes les données du profil pour s'assurer que l'affichage est à jour
    await reloadProfileData();
    
    displayToast('Profil de l\'établissement mis à jour avec succès !', 'success');
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
    const baseURL = import.meta.env.VITE_API_URL;
    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'etablissement' && user.role_details) {
      photo.value = user.photo ? `${baseURL}${user.photo}` : '';
      couverture.value = user.couverture ? `${baseURL}${user.couverture}` : '';
      description.value = user.description || 'Décrivez votre etablissement  ici...';

      const etablissementDetails = user.role_details;
      nom_etablissement.value = user.name;
      email_etablissement.value = etablissementDetails.email_etablissement;
      siteweb.value = etablissementDetails.siteweb;
      adresse_etablissement.value = etablissementDetails.adresse;
      contact_etablissement.value = etablissementDetails.contact || 'Non spécifié';

      nom_ville.value = etablissementDetails.ville ? etablissementDetails.ville.nom_ville : 'Non spécifié';
    }
  } catch (error) {
    console.error('Erreur lors du rechargement des données du profil :', error);
  }
};


// --- Logique de filtrage des partenariats (inchangée) ---
const filteredPartnerships = computed(() => {
  let filtered = partnerships.value;

  if (filterStatus.value) {
    filtered = filtered.filter(p => p.status === filterStatus.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(query)
    );
  }
  return filtered;
});

const setFilterStatus = (status) => {
  filterStatus.value = status;
};

const getPartnershipStatusBadgeClass = (status) => {
  switch (status) {
    case 'Partenaire': return 'bg-success-subtle text-success';
    case 'En attente de validation': return 'bg-warning-subtle text-warning';
    default: return 'bg-secondary';
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

.profile-avatar-placeholder .school-logo {
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


/* Section Partenariat - Réutilisation des styles de offers-list */
.partnership-section {
  margin-top: 2rem;
}

.partnership-section .list-header {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.partnership-section .list-header .search-input-group {
    max-width: 300px;
}

.partnership-section .list-header {
    border-color: rgba(255, 255, 255, 0.4);
    color: white;
}
.partnership-section .list-header {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}
.partnership-section .list-header ,
.partnership-section .list-header {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    box-shadow: none;
}

.partnership-table-container {
    border-top: 1px solid var(--border-color);
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
