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
      <p class="ms-3">Chargement du profil étudiant...</p>
    </div>

    <div v-else>
      <section class="profile-cover-section bg-light rounded shadow-sm mb-4 position-relative">
        <img :src="displayedCouverture" alt="Image de couverture de l'étudiant" class="profile-cover-image w-100 rounded-top" />
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
            <img :src="displayedPhoto" :alt="`Profil de ${name}`" class="student-logo" />
          </div>
          <div class="profile-info flex-grow-1 mt-3 mt-md-0">
            <h3 class="mb-1 fs-4 text-dark d-flex align-items-center flex-wrap">
              {{ name }}
              <i class="fas fa-pen-to-square ms-2 text-secondary fs-6 cursor-pointer" data-bs-toggle="modal" data-bs-target="#editProfileModal" @click="loadProfileForEditModal"></i>
            </h3>
            <p class="status-online fs-6 text-success d-flex align-items-center">
              <span class="dot bg-success me-1"></span> En ligne
            </p>
            <label for="profile-upload-input" class="btn btn-sm btn-primary align-items-center cursor-pointer" :disabled="isUploadingPhoto">
              <span v-if="isUploadingPhoto" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-camera me-2"></i> {{ isUploadingCover ? 'Chargement...' : 'Modifier la photo' }}
              <input type="file" id="profile-upload-input" class="d-none" @change="handleProfileUpload" accept="image/*" :disabled="isUploadingPhoto" />
            </label>
          </div>
        </div>

        <div class="description-field pb-4 mb-4 border-bottom">
          <label for="student-description-profile" class="form-label fw-bold text-dark mb-2">À propos de moi :</label>
          <textarea
            id="student-description-profile"
            class="form-control"
            rows="4"
            placeholder="Décrivez vous ici..."
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

        <div class="profile-details pb-4 mb-4">
          <p class="mb-2 text-dark d-flex align-items-center">
            <i class="fas fa-id-card me-2 text-primary fs-5"></i><span class="fw-bold">Matricule :</span> {{ matricule || 'Non spécifié' }}
          </p>
          <p class="mb-2 text-dark d-flex align-items-center">
            <i class="fas fa-envelope me-2 text-primary fs-5"></i><span class="fw-bold">Email :</span> {{ email || 'Non spécifié' }}
          </p>
          <p class="mb-2 text-dark d-flex align-items-center">
            <i class="fas fa-school me-2 text-primary fs-5"></i><span class="fw-bold">Établissement :</span> {{ nom_etablissement || 'Non spécifié' }}
          </p>
          <p class="mb-2 text-dark d-flex align-items-center">
            <i class="fas fa-graduation-cap me-2 text-primary fs-5"></i><span class="fw-bold">Filière :</span> {{ libfil || 'Non spécifiée' }}
          </p>
          <p class="mb-2 text-dark d-flex align-items-center">
            <i class="fas fa-book me-2 text-primary fs-5"></i><span class="fw-bold">Année d'étude :</span> {{ libannee || 'Non spécifiée' }}
          </p>
          <p class="mb-0 text-dark d-flex align-items-center mt-2 flex-wrap">
            <i class="fas fa-file-alt me-2 text-primary fs-5"></i><span class="fw-bold">CV :</span>
            <a v-if="CV" :href="CV_url" target="_blank" class="ms-1 btn btn-sm btn-outline-primary me-2">Voir mon CV</a>
            <span v-else class="ms-1">Non téléchargé</span>
            <label for="cv-upload-input" class="btn btn-sm btn-primary ms-2" :disabled="isUploadingCV">
              <span v-if="isUploadingCV" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="fas fa-download me-2"></i> {{ isUploadingCV ? 'Chargement...' : (CV ? 'Modifier le CV' : 'Télécharger un CV') }}
              <input type="file" id="cv-upload-input" class="d-none" @change="handleCVUpload" accept="application/pdf" :disabled="isUploadingCV" />
            </label>
          </p>
        </div>
      </section>

      <section class="bg-white p-4 rounded shadow-sm mb-4">
        <h2 class="form-label fw-bold text-dark mb-3 section-header">
         <!-- <span style="text-decoration: underline; text-decoration-color: #0d6efd;">Mes Compétences</span>-->
         Mes Compétences
        <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
         
        </h2>
        <div class="description-field pb-4 mb-4 border-bottom">
          <textarea
            class="form-control"
            rows="4"
            placeholder="Décrivez vos compétences techniques, soft skills, langages de programmation, outils maîtrisés..."
            v-model="competences"
            @input="hasCompetencesChanged = true"
            :disabled="isSavingCompetences || isLoadingProfile"
          ></textarea>
          <button
            class="btn btn-primary mt-2"
            :disabled="!hasCompetencesChanged || isSavingCompetences || isLoadingProfile"
            @click="saveCompetences"
          >
            <span v-if="isSavingCompetences" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ isSavingCompetences ? 'Enregistrement...' : 'Enregistrer les compétences' }}
          </button>
        </div>

        <h2 class="form-label fw-bold text-dark mb-3 section-header">
          Mes Projets
          <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
        </h2>
        <div class="description-field pb-4 mb-4 border-bottom">
          <textarea
            class="form-control"
            rows="4"
            placeholder="Présentez vos projets réalisés, en cours ou à venir. Décrivez les technologies utilisées, les défis relevés..."
            v-model="projets"
            @input="hasProjetsChanged = true"
            :disabled="isSavingProjets || isLoadingProfile"
          ></textarea>
          <button
            class="btn btn-primary mt-2"
            :disabled="!hasProjetsChanged || isSavingProjets || isLoadingProfile"
            @click="saveProjets"
          >
            <span v-if="isSavingProjets" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ isSavingProjets ? 'Enregistrement...' : 'Enregistrer les projets' }}
          </button>
        </div>

        <h2 class="form-label fw-bold text-dark mb-3 section-header">
          Mon Parcours Académique
          <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
        </h2>
        <div class="description-field pb-4 mb-4">
          <textarea
            class="form-control"
            rows="4"
            placeholder="Décrivez votre parcours académique, formations, expériences marquantes, évolution..."
            v-model="parcours"
            @input="hasParcoursChanged = true"
            :disabled="isSavingParcours || isLoadingProfile"
          ></textarea>
          <button
            class="btn btn-primary mt-2"
            :disabled="!hasParcoursChanged || isSavingParcours || isLoadingProfile"
            @click="saveParcours"
          >
            <span v-if="isSavingParcours" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ isSavingParcours ? 'Enregistrement...' : 'Enregistrer le parcours' }}
          </button>
        </div>
      </section>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h1 class="modal-title fs-5" id="editProfileModalLabel">Modifier mon profil étudiant</h1>
              <div style="width: 80px; height: 3px; background: linear-gradient(to right, #0d6efd, #2563eb); border-radius: 1.5px;"></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="editProfile">
              <div class="mb-3">
                <label for="annee" class="form-label">Année d'étude</label>
                <select class="form-select" id="annee" v-model="editProfileForm.annee_id">
                  <option value="">Sélectionnez une année</option>
                  <option v-for="annee in listeAnnees" :key="annee.id" :value="annee.id">
                    {{ annee.libannee }}
                  </option>
                </select>
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

// --- Variables Réactives du Profil de l'Étudiant ---
const photo = ref('');
const couverture = ref('');
const description = ref('Décrivez-vous ici...');

const name = ref('Nom de l\'étudiant');
const email = ref('');
const matricule = ref('');
const projets = ref('');
const competences = ref('');
const CV = ref(null);
const parcours = ref('');

const nom_etablissement = ref('');
const libfil = ref('');
const libannee = ref('');

const etudiantId = ref(null);

// Ajout des listes pour les sélecteurs du modal
const listeEtablissements = ref([]);
const listeFilieres = ref([]);
const listeAnnees = ref([]);

// Formulaire de modification (simplifié)
const editProfileForm = ref({
  annee_id: '',
});

// Variables pour détecter les changements et gérer l'état de sauvegarde
const hasDescriptionChanged = ref(false);
const hasCompetencesChanged = ref(false);
const hasProjetsChanged = ref(false);
const hasParcoursChanged = ref(false);

// Variables pour l'état de sauvegarde
const isLoadingProfile = ref(true);
const isUploadingPhoto = ref(false);
const isUploadingCover = ref(false);
const isUploadingCV = ref(false);
const isSavingDescription = ref(false);
const isSavingCompetences = ref(false);
const isSavingProjets = ref(false);
const isSavingParcours = ref(false);
const isSavingProfile = ref(false);
const isLoadingLists = ref(false);

const toastMessage = ref('');
const toastType = ref('success');

// Instance du modal Bootstrap
let editProfileModalInstance;

// --- Propriétés Calculées pour l'Affichage des Images et du CV avec Cache Busting ---
const displayedPhoto = computed(() => {
  const url = photo.value || '/photo-default.jpeg';
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});
const displayedCouverture = computed(() => {
  const url = couverture.value || '/cover-default.jpeg';
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});
const CV_url = computed(() => {
  const url = CV.value;
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
onMounted(async () => {
  try {
    isLoadingProfile.value = true;

    // Chargement des listes pour le modal d'édition AVANT de charger le profil
    try {
      isLoadingLists.value = true;
      const [etablissementsResponse, filieresResponse, anneesResponse] = await Promise.all([
        axios.get('/api/etablissements'),
        axios.get('/api/filieres'),
        axios.get('/api/annees')
      ]);
      listeEtablissements.value = etablissementsResponse.data;
      listeFilieres.value = filieresResponse.data;
      listeAnnees.value = anneesResponse.data;
    } catch (error) {
      console.error('Erreur lors du chargement des listes (établissements, filières, années) :', error);
      displayToast("Erreur lors du chargement des options de formulaire.", "danger");
    } finally {
      isLoadingLists.value = false;
    }

    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'etudiant' && user.role_details) {
      photo.value = user.photo;
      couverture.value = user.couverture;
      description.value = user.description || 'Décrivez-vous ici...';

      const etudiantDetails = user.role_details;
      etudiantId.value = etudiantDetails.etudiant_id;
      name.value = user.name;
      email.value = user.email;
      matricule.value = etudiantDetails.matricule || 'Non spécifié';
      projets.value = etudiantDetails.projets || '';
      competences.value = etudiantDetails.competences || '';
      CV.value = etudiantDetails.CV;
      parcours.value = etudiantDetails.parcours || '';

      nom_etablissement.value = etudiantDetails.id_etablissement
        ? (listeEtablissements.value.find(e => e.id === etudiantDetails.id_etablissement)?.nom_etablissement || 'Non spécifié')
        : 'Non spécifié';

      libfil.value = etudiantDetails.filiere ? etudiantDetails.filiere.libfil : 'Non spécifiée';
      libannee.value = etudiantDetails.annee_libelle ? etudiantDetails.annee_libelle : 'Non spécifiée';
    } else {
      console.warn("Le profil récupéré n'est pas celui d'un étudiant ou est incomplet.");
      displayToast("Impossible de charger le profil de l'étudiant.", "danger");
    }
  } catch (error) {
    console.error('Erreur générale lors de la récupération du profil :', error);
    displayToast("Erreur lors du chargement du profil.", "danger");
  } finally {
    isLoadingProfile.value = false;
    const modalElement = document.getElementById('editProfileModal');
    if (modalElement && window.bootstrap && window.bootstrap.Modal) {
      editProfileModalInstance = new window.bootstrap.Modal(modalElement);
    }
  }
});

const loadProfileForEditModal = () => {
  editProfileForm.value = {
    annee_id: listeAnnees.value.find(a => a.libannee === libannee.value)?.id || '',
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

const handleCVUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.type !== 'application/pdf') {
      displayToast('Veuillez télécharger un fichier PDF.', 'danger');
      event.target.value = '';
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      displayToast('Le fichier ne doit pas dépasser 2MB.', 'danger');
      event.target.value = '';
      return;
    }
    isUploadingCV.value = true;
    const formData = new FormData();
    formData.append('CV', file);

    try {
      const response = await axios.post(`/api/etudiants/${etudiantId.value}?_method=PUT`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      CV.value = response.data.CV;
      displayToast('CV mis à jour avec succès !', 'success');
    } catch (error) {
      console.error('Erreur lors de l\'upload du CV :', error);
      displayToast('Échec de la mise à jour du CV.', 'danger');
    } finally {
      isUploadingCV.value = false;
      event.target.value = '';
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

const saveCompetences = async () => {
  isSavingCompetences.value = true;
  try {
    if (!etudiantId.value) {
      displayToast("ID de l'étudiant manquant pour la mise à jour des compétences.", "danger");
      return;
    }
    await axios.put(`/api/etudiants/${etudiantId.value}`, {
      competences: competences.value
    });
    displayToast('Compétences enregistrées avec succès !', 'success');
    hasCompetencesChanged.value = false;
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement des compétences :', error);
    displayToast('Échec de l\'enregistrement des compétences.', 'danger');
  } finally {
    isSavingCompetences.value = false;
  }
};

const saveProjets = async () => {
  isSavingProjets.value = true;
  try {
    if (!etudiantId.value) {
      displayToast("ID de l'étudiant manquant pour la mise à jour des projets.", "danger");
      return;
    }
    await axios.put(`/api/etudiants/${etudiantId.value}`, {
      projets: projets.value
    });
    displayToast('Projets enregistrés avec succès !', 'success');
    hasProjetsChanged.value = false;
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement des projets :', error);
    displayToast('Échec de l\'enregistrement des projets.', 'danger');
  } finally {
    isSavingProjets.value = false;
  }
};

const saveParcours = async () => {
  isSavingParcours.value = true;
  try {
    if (!etudiantId.value) {
      displayToast("ID de l'étudiant manquant pour la mise à jour du parcours.", "danger");
      return;
    }
    await axios.put(`/api/etudiants/${etudiantId.value}`, {
      parcours: parcours.value
    });
    displayToast('Parcours enregistré avec succès !', 'success');
    hasParcoursChanged.value = false;
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement du parcours :', error);
    displayToast('Échec de l\'enregistrement du parcours.', 'danger');
  } finally {
    isSavingParcours.value = false;
  }
};

const editProfile = async () => {
  isSavingProfile.value = true;
  try {
    if (!etudiantId.value) {
      displayToast("ID de l'étudiant manquant pour la mise à jour.", "danger");
      isSavingProfile.value = false;
      return;
    }

    const etudiantDetailsPayload = {
      annee_id: editProfileForm.value.annee_id,
    };

    await axios.put(`/api/etudiants/${etudiantId.value}`, etudiantDetailsPayload);
    await reloadProfileData();
    displayToast('Profil étudiant mis à jour avec succès !', 'success');
    editProfileModalInstance.hide();
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil étudiant :', error);
    displayToast('Échec de la mise à jour du profil.', 'danger');
  } finally {
    isSavingProfile.value = false;
  }
};

const reloadProfileData = async () => {
  try {
    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;

    if (user.role === 'etudiant' && user.role_details) {
      photo.value = user.photo;
      couverture.value = user.couverture;
      description.value = user.description || 'Décrivez-vous ici...';

      const etudiantDetails = user.role_details;
      name.value = user.name;
      email.value = user.email;
      matricule.value = etudiantDetails.matricule || 'Non spécifié';
      projets.value = etudiantDetails.projets || '';
      competences.value = etudiantDetails.competences || '';
      CV.value = etudiantDetails.CV;
      parcours.value = etudiantDetails.parcours || '';

      nom_etablissement.value = etudiantDetails.id_etablissement
        ? (listeEtablissements.value.find(e => e.id === etudiantDetails.id_etablissement)?.nom_etablissement || 'Non spécifié')
        : 'Non spécifié';
      libfil.value = etudiantDetails.filiere ? etudiantDetails.filiere.libfil : 'Non spécifiée';
      libannee.value = etudiantDetails.annee_libelle ? etudiantDetails.annee_libelle : 'Non spécifiée';
    }
  } catch (error) {
    console.error('Erreur lors du rechargement des données du profil :', error);
  }
};
</script>

<style scoped>
/* Variables de base - cohérentes avec EntrepriseOffre.vue */
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
  padding: 1rem; /* Réduit pour petits écrans */
  max-width: 1200px; /* Limite la largeur max */
  margin: 0 auto; /* Centre le contenu */
}

/* Styles pour la section de couverture */
.profile-cover-section {
  height: 200px; /* Hauteur réduite pour petits écrans */
  overflow: hidden;
  margin-bottom: -30px; /* Réduit le chevauchement */
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
  padding: 0.5rem; /* Réduit le padding */
}

/* Styles pour la carte principale du profil */
.profile-main-card {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  position: relative;
  padding-top: 50px; /* Réduit pour petits écrans */
  z-index: 1;
}

.profile-avatar-placeholder {
  width: 150px; /* Taille réduite pour petits écrans */
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
  margin-top: -80px; /* Réduit le chevauchement */
}

.profile-avatar-placeholder .student-logo {
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
  width: 100%; /* Assure que le textarea prend toute la largeur disponible */
}

.description-field .btn {
  font-size: 0.85rem; /* Taille de police réduite */
  padding: 0.4rem 0.8rem; /* Padding réduit */
}

/* Icône de modification du profil */
.edit-profile-icon {
  width: 40px;
  height: 40px;
  font-size: 0.8rem;
  right: 5px;
  bottom: 5px;
}

/* Styles pour les sections "informations complémentaires" */
.section-header {
  font-size: 1.1rem; /* Taille réduite pour petits écrans */
  color: var(--dark-text);
  margin-bottom: 0.5rem;
  padding-bottom: 0.3rem;
  border-bottom: 2px solid var(--primary-blue);
}

/* S'assurer que les blocs sont bien séparés */
.profile-main-card > div:not(:last-child),
section.bg-white > div:not(:last-child) {
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.profile-main-card > div:last-child,
section.bg-white > div:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

/* Media Queries pour la responsivité */
@media (max-width: 768px) {
  .content-page {
    padding: 0.5rem;
  }

  .profile-cover-section {
    height: 150px; /* Hauteur encore réduite */
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
    font-size: 1.25rem; /* Taille de titre réduite */
  }

  .description-field .btn {
    width: 100%; /* Boutons pleine largeur sur petits écrans */
    text-align: center;
  }

  .profile-details p {
    font-size: 0.9rem; /* Réduit la taille du texte */
  }

  .section-header {
    font-size: 1rem;
  }

  .cover-overlay .btn {
    font-size: 0.75rem; /* Bouton plus petit */
    padding: 0.3rem 0.6rem;
  }
}

@media (max-width: 576px) {
  .profile-header {
    flex-direction: column; /* Empile les éléments verticalement */
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
}

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
