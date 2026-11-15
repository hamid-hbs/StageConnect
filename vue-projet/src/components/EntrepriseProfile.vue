<template>
  <div class="content-page">
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
        <div class="profile-header d-flex align-items-start pb-4 mb-4 border-bottom flex-column flex-md-row">
          <div class="profile-avatar-placeholder me-md-4 position-relative">
            <img :src="displayedPhoto" :alt="`Profil de ${nom_entreprise}`" class="company-logo" />
          </div>
          <div class="profile-info flex-grow-1 mt-3 mt-md-0">
            <h3 class="mb-1 fs-4 text-dark d-flex align-items-center flex-wrap">
              {{ nom_entreprise }}
              <i class="fas fa-pen-to-square ms-2 text-secondary fs-6 cursor-pointer" data-bs-toggle="modal" data-bs-target="#editProfileModal" @click="loadProfileForEditModal"></i>
            </h3>
            <p class="status-online fs-6 text-success d-flex align-items-center">
              <span class="dot bg-success me-1"></span> En ligne
            </p>
            <label for="profile-upload-input" class="btn btn-sm btn-primary align-items-center cursor-pointer" :disabled="isUploadingPhoto">
              <span v-if="isUploadingPhoto" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-camera me-2"></i> {{ isUploadingPhoto ? 'Chargement...' : 'Modifier la photo' }}
              <input type="file" id="profile-upload-input" class="d-none" @change="handleProfileUpload" accept="image/*" :disabled="isUploadingPhoto" />
            </label>
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
          <p class="mb-2 text-dark d-flex align-items-center">
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
            <div>
              <h1 class="modal-title fs-5" id="editProfileModalLabel">Modifier profil entreprise</h1>
              <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
            </div>
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
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" :disabled="isSavingProfile">Annuler</button>
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
const nom_ville = ref('');
const domaine_entreprise = ref('');
const entrepriseId = ref(null);

const editProfileForm = ref({
  nom_entreprise: '',
  email_entreprise: '',
  siteweb: '',
  adresse_entreprise: '',
  id_domaine: '',
  ville_id: '',
});

const hasDescriptionChanged = ref(false);
const listeDomaines = ref([]);
const listeVilles = ref([]);
const isLoadingProfile = ref(true);
const isUploadingPhoto = ref(false);
const isUploadingCover = ref(false);
const isSavingDescription = ref(false);
const isSavingProfile = ref(false);
const isLoadingLists = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

let editProfileModalInstance;

const displayedPhoto = computed(() => {
  const url = photo.value || '/photo-default.jpeg';
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});

const displayedCouverture = computed(() => {
  const url = couverture.value || '/cover-default.jpeg';
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});

const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl && window.bootstrap && window.bootstrap.Toast) {
    const toast = new window.bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

onMounted(async () => {
  try {
    isLoadingProfile.value = true;
    isLoadingLists.value = true;

    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'entreprise' && user.role_details) {
      photo.value = user.photo;
      couverture.value = user.couverture;
      description.value = user.description || 'Décrivez votre entreprise ici...';
      const entrepriseDetails = user.role_details;
      entrepriseId.value = entrepriseDetails.entreprise_id;
      nom_entreprise.value = user.name || entrepriseDetails.nom_entreprise;
      email_entreprise.value = entrepriseDetails.email_entreprise;
      siteweb.value = entrepriseDetails.siteweb;
      adresse_entreprise.value = entrepriseDetails.adresse;
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

const loadProfileForEditModal = () => {
  editProfileForm.value = {
    nom_entreprise: nom_entreprise.value,
    email_entreprise: email_entreprise.value,
    siteweb: siteweb.value,
    adresse_entreprise: adresse_entreprise.value,
    id_domaine: listeDomaines.value.find(d => d.libdomaine === domaine_entreprise.value)?.id || '',
    ville_id: listeVilles.value.find(v => v.nom_ville === nom_ville.value)?.id || '',
  };
};

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
      siteweb: editProfileForm.value.siteweb,
      adresse: editProfileForm.value.adresse_entreprise,
      id_domaine: editProfileForm.value.id_domaine,
      ville_id: editProfileForm.value.ville_id,
    };

    await axios.put(`/api/entreprises/${entrepriseId.value}`, payload);
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

const reloadProfileData = async () => {
  try {
    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'entreprise' && user.role_details) {
      photo.value = user.photo;
      couverture.value = user.couverture;
      description.value = user.description || 'Décrivez votre entreprise ici...';
      const entrepriseDetails = user.role_details;
      nom_entreprise.value = user.name || entrepriseDetails.nom_entreprise;
      email_entreprise.value = entrepriseDetails.email_entreprise;
      siteweb.value = entrepriseDetails.siteweb;
      adresse_entreprise.value = entrepriseDetails.adresse;
      nom_ville.value = entrepriseDetails.ville ? entrepriseDetails.ville.nom_ville : 'Non spécifié';
      domaine_entreprise.value = entrepriseDetails.domaine ? entrepriseDetails.domaine.libdomaine : 'Non spécifié';
    }
  } catch (error) {
    console.error('Erreur lors du rechargement des données du profil :', error);
  }
};
</script>

<style scoped>
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

.cover-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(0,0,0,0.4), rgba(0,0,0,0));
  color: white;
  border-radius: inherit;
  padding: 0.5rem;
}

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

.description-field textarea.form-control {
  resize: vertical;
  min-height: 80px;
  width: 100%;
}

.description-field .btn {
  font-size: 0.85rem;
  padding: 0.4rem 0.8rem;
}

.edit-profile-icon {
  width: 40px;
  height: 40px;
  font-size: 0.8rem;
  right: 5px;
  bottom: 5px;
}

.profile-main-card > div:not(:last-child) {
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.profile-main-card > div:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

/* Modal Styles */
.modal-content {
  border-radius: 1rem;
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  background: #fff;
}

.modal-header {
  border-bottom: none;
  padding: 1.25rem;
  background: #f8f9fa;
  border-radius: 1rem 1rem 0 0;
}

.modal-header h5,
.modal-header h1.fs-5 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.modal-header .btn-close {
  filter: invert(0.4);
  transition: transform 0.2s ease;
}

.modal-header .btn-close:hover {
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-body .form-label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #212529;
  margin-bottom: 0.5rem;
}

.modal-body .form-control,
.modal-body .form-select {
  border-radius: 0.5rem;
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  font-size: 0.9rem;
  background-color: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
  border-color: transparent;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  background: linear-gradient(to right, #fff, #fff) padding-box,
              linear-gradient(to right, #0d6efd, #2563eb) border-box;
  border: 1px solid transparent;
}

.modal-body textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.modal-footer {
  border-top: none;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 0 0 1rem 1rem;
}

.modal-footer .btn {
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.modal-footer .btn-primary {
  background: linear-gradient(to right, #0d6efd, #2563eb);
  border: none;
}

.modal-footer .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.2);
}

.modal-footer .btn-danger {
  background: linear-gradient(to right, #dc3545, #b02a37);
  border: none;
}

.modal-footer .btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.2);
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

  .description-field .btn {
    width: 100%;
    text-align: center;
  }

  .profile-details p {
    font-size: 0.9rem;
  }

  .cover-overlay .btn {
    font-size: 0.75rem;
    padding: 0.3rem 0.6rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-header h5,
  .modal-header h1.fs-5 {
    font-size: 1.1rem;
  }

  .modal-body .form-control,
  .modal-body .form-select {
    font-size: 0.85rem;
    padding: 0.4rem;
  }

  .modal-footer .btn {
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

  .description-field textarea.form-control {
    font-size: 0.9rem;
  }

  .modal-dialog {
    margin: 0.5rem;
  }

  .modal-body .form-control,
  .modal-body .form-select {
    font-size: 0.85rem;
    padding: 0.4rem;
  }

  .modal-footer .btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
}
</style>