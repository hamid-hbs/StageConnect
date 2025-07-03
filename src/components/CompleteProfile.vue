<template>
  <div class="container mt-5">
    <div class="main-content">
      <h2 class="fw-bold mb-4 text-center">Compléter votre profil</h2>
      <p class="text-center mb-4">Veuillez remplir les informations spécifiques à votre rôle de {{ role }} pour continuer.</p>

      <div v-if="isLoading" class="status-message">
        <div class="spinner"></div>
        <p>Chargement des informations...</p>
      </div>
      <div v-if="successMessage" class="success-message">
        <p>{{ successMessage }}</p>
      </div>
      <div v-if="errorMessage" class="error mt-3 text-center">
        <p>{{ errorMessage }}</p>
      </div>

      <div v-if="role === 'etudiant' && !isLoading" class="form-container">
        <h3>Profil Étudiant</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Matricule <span class="required">*</span></label>
            <input type="text" v-model="etudiantForm.matricule" required />
          </div>
          <div class="form-group">
            <label>Établissement <span class="required">*</span></label>
            <select v-model="etudiantForm.id_etablissement" required>
              <option value="">Sélectionnez votre établissement</option>
              <option v-for="etablissement in etablissements" :key="etablissement.id" :value="etablissement.id">
                {{ etablissement.nom_etablissement }}
              </option>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Filière <span class="required">*</span></label>
            <select v-model="etudiantForm.id_filiere" required>
              <option value="">Sélectionnez votre filière</option>
              <option v-for="filiere in filieres" :key="filiere.id" :value="filiere.id">
                {{ filiere.libfil}}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Année <span class="required">*</span></label>
            <select v-model="etudiantForm.annee_id" :disabled="anneesLoading || !annees.length" required>
              <option value="">
                {{ anneesLoading ? 'Chargement...' : 'Sélectionnez votre année' }}
              </option>
              <option v-for="annee in annees" :key="annee.id" :value="annee.id">
                {{ annee.libannee }} <!-- Accès direct à libannee -->
              </option>
            </select>
            <p v-if="!anneesLoading && !annees.length" class="text-danger mt-1">
                Aucune année disponible.
            </p>
          </div>
        </div>

        <!-- Les champs Projets, Compétences, Parcours et CV ont été retirés -->

        <div class="d-grid gap-2 mt-4">
          <button
            @click="submitProfile"
            class="btn btn-primary"
            type="button"
            :disabled="isSubmitting || !isEtudiantFormValid"
          >
            {{ isSubmitting ? 'Envoi...' : 'Enregistrer mon profil' }}
          </button>
        </div>
      </div>

      <div v-if="role === 'entreprise' && !isLoading" class="form-container">
        <h3>Profil Entreprise</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Nom de l'entreprise <span class="required">*</span></label>
            <input type="text" v-model="entrepriseForm.nom_entreprise" required />
          </div>
          <div class="form-group">
            <label>Email de contact <span class="required">*</span></label>
            <input type="email" v-model="entrepriseForm.email_entreprise" required />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Site web</label>
            <input
              type="url"
              v-model="entrepriseForm.siteweb"
              placeholder="https://www.exemple.com"
            />
          </div>
          <div class="form-group">
            <label>Numéro RCCM <span class="required">*</span></label>
            <input type="text" v-model="entrepriseForm.RCCM" required />
          </div>
        </div>
        <div class="form-group">
          <label>Adresse complète <span class="required">*</span></label>
          <textarea
            v-model="entrepriseForm.adresse"
            rows="3"
            required
            placeholder="Adresse complète de l'entreprise"
          ></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Domaine d'activité <span class="required">*</span></label>
            <select v-model="entrepriseForm.id_domaine" required>
              <option value="">Sélectionnez un domaine</option>
              <option
                v-for="domaine in domaines"
                :key="domaine.id"
                :value="domaine.id"
              >
                {{ domaine.libdomaine }} </option>
            </select>
          </div>
          <div class="form-group">
            <label>Ville <span class="required">*</span></label>
            <select v-model="entrepriseForm.ville_id" required>
              <option value="">Sélectionnez une ville</option>
              <option v-for="ville in villes" :key="ville.id" :value="ville.id">
                {{ ville.nom_ville }} </option>
            </select>
          </div>
        </div>
          <div class="d-grid gap-2 mt-4">
          <button
            @click="submitProfile"
            class="btn btn-primary"
            type="button"
            :disabled="isSubmitting || !isEnterpriseFormValid"
          >
            {{ isSubmitting ? 'Envoi...' : 'Enregistrer mon profil' }}
          </button>
          </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';

export default {
  name: 'CompleteProfile',
  setup() {
    const route = useRoute();
    const router = useRouter();

    const role = ref('');
    const isLoading = ref(true);
    const isSubmitting = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    // fileError et selectedFile ne sont plus nécessaires
    // const fileError = ref('');
    // const selectedFile = ref(null);
    // const cvFile = ref(null); // Ref pour l'input de fichier CV

    const filieres = ref([]);
    const etablissements = ref([]);
    const domaines = ref([]);
    const villes = ref([]);
    const annees = ref([]); // Stocke les années (Annee directement)
    const anneesLoading = ref(false); // État de chargement pour les années

    // --- FORMULAIRES RÉACTIFS ---
    const etudiantForm = reactive({
      matricule: '',
      id_etablissement: '',
      id_filiere: '',
      annee_id: '', // ID de l'année (qui est l'ID de la table 'annees')
      // projets, competences, parcours, CV ne sont plus dans le formulaire
      // projets: '',
      // competences: '',
      // parcours: '',
      // CV: null,
    });

    const entrepriseForm = reactive({
      nom_entreprise: '',
      email_entreprise: '',
      siteweb: '',
      RCCM: '',
      adresse: '',
      id_domaine: '',
      ville_id: '',
    });

    // --- VALIDATION D'EMAIL ---
    const isValidEmail = (email) => {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return regex.test(email);
    };

    // --- PROPRIÉTÉS CALCULÉES POUR LA VALIDATION DES FORMULAIRES ---
    const isEtudiantFormValid = computed(() => {
      return (
        etudiantForm.matricule &&
        etudiantForm.id_etablissement &&
        etudiantForm.id_filiere &&
        etudiantForm.annee_id
        // selectedFile.value n'est plus nécessaire pour la validation
      );
    });

    const isEnterpriseFormValid = computed(() => {
      return (
        entrepriseForm.nom_entreprise &&
        entrepriseForm.email_entreprise && isValidEmail(entrepriseForm.email_entreprise) &&
        entrepriseForm.RCCM &&
        entrepriseForm.id_domaine &&
        entrepriseForm.ville_id &&
        entrepriseForm.adresse
      );
    });

    // --- GESTION DE L'UPLOAD DE FICHIER ---
    // La fonction handleFileUpload n'est plus nécessaire
    // const handleFileUpload = (event) => {
    //   fileError.value = '';
    //   const file = event.target.files[0];
    //   if (file) {
    //     if (file.type !== 'application/pdf') {
    //       fileError.value = 'Le fichier doit être un PDF.';
    //       selectedFile.value = null;
    //       etudiantForm.CV = null;
    //       if (cvFile.value) cvFile.value.value = '';
    //       return;
    //     }
    //     if (file.size > 2 * 1024 * 1024) {
    //       fileError.value = 'Le fichier ne doit pas dépasser 2MB.';
    //       selectedFile.value = null;
    //       etudiantForm.CV = null;
    //       if (cvFile.value) cvFile.value.value = '';
    //       return;
    //     }
    //     selectedFile.value = file;
    //     etudiantForm.CV = file;
    //   } else {
    //     selectedFile.value = null;
    //     etudiantForm.CV = null;
    //   }
    // };

    // --- RÉCUPÉRATION DE TOUTES LES ANNÉES ---
    const fetchAllAnnees = async () => {
        annees.value = [];
        etudiantForm.annee_id = '';
        
        anneesLoading.value = true;
        errorMessage.value = ''; 

        try {
            const response = await axios.get(`/api/annees`);
            annees.value = response.data;
        } catch (error) {
            console.error('Erreur lors du chargement des années:', error);
            errorMessage.value = "Impossible de charger les années disponibles.";
            annees.value = []; 
        } finally {
            anneesLoading.value = false;
        }
    };

    // --- RÉCUPÉRATION DES LISTES DYNAMIQUES (filières, établissements, etc.) ---
    const fetchDynamicLists = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const [filieresRes, etablissementsRes, domainesRes, villesRes] = await Promise.all([
          axios.get('/api/filieres'),
          axios.get('/api/etablissements'),
          axios.get('/api/domaines'),
          axios.get('/api/villes'),
        ]);

        filieres.value = filieresRes.data;
        etablissements.value = etablissementsRes.data;
        domaines.value = domainesRes.data;
        villes.value = villesRes.data;

        await fetchAllAnnees();

      } catch (error) {
        console.error('Erreur lors du chargement des listes dynamiques:', error);
        errorMessage.value = "Impossible de charger les listes nécessaires. Veuillez réessayer.";
      } finally {
        isLoading.value = false;
      }
    };

    // --- SOUMISSION DU PROFIL ---
    const submitProfile = async () => {
      isSubmitting.value = true;
      successMessage.value = '';
      errorMessage.value = '';
      // fileError n'est plus nécessaire
      // fileError.value = '';

      let formData = new FormData();
      let apiUrl = `/api/user/complete-profile/${role.value}`;

      if (role.value === 'etudiant') {
        if (!isEtudiantFormValid.value) {
          errorMessage.value = "Veuillez remplir tous les champs obligatoires du profil étudiant."; // Message mis à jour
          isSubmitting.value = false;
          return;
        }
        formData.append('matricule', etudiantForm.matricule);
        formData.append('id_etablissement', etudiantForm.id_etablissement);
        formData.append('id_filiere', etudiantForm.id_filiere);
        formData.append('annee_id', etudiantForm.annee_id); 
        // Les champs projets, competences, parcours, CV ne sont plus ajoutés
        // formData.append('projets', etudiantForm.projets);
        // formData.append('competences', etudiantForm.competences);
        // formData.append('parcours', etudiantForm.parcours);
        // if (etudiantForm.CV) {
        //   formData.append('CV', etudiantForm.CV);
        // }

      } else if (role.value === 'entreprise') {
        if (!isEnterpriseFormValid.value) {
          errorMessage.value = "Veuillez remplir tous les champs obligatoires du profil entreprise et vérifier l'email.";
          isSubmitting.value = false;
          return;
        }
        formData.append('nom_entreprise', entrepriseForm.nom_entreprise);
        formData.append('email_entreprise', entrepriseForm.email_entreprise);
        formData.append('RCCM', entrepriseForm.RCCM);
        formData.append('id_domaine', entrepriseForm.id_domaine);
        formData.append('ville_id', entrepriseForm.ville_id);
        formData.append('adresse', entrepriseForm.adresse); 

        if (entrepriseForm.siteweb) formData.append('siteweb', entrepriseForm.siteweb);

      } else {
        errorMessage.value = 'Rôle inconnu pour la complétion de profil.';
        isSubmitting.value = false;
        return;
      }

      try {
        const response = await axios.post(apiUrl, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        successMessage.value = response.data.message || 'Profil enregistré avec succès ! Redirection...';

        const redirectPath = `/${role.value}-dashboard`;

        setTimeout(() => {
          router.push(redirectPath);
        }, 2000);

      } catch (error) {
        console.error('Erreur lors de la soumission du profil:', error);
        if (axios.isAxiosError(error) && error.response && error.response.data) {
          errorMessage.value = error.response.data.message || "Une erreur est survenue lors de l'enregistrement de votre profil.";
          if (error.response.data.errors) {
            for (const field in error.response.data.errors) {
                errorMessage.value += ` ${field}: ${error.response.data.errors[field].join(', ')}`;
            }
          }
        } else {
          errorMessage.value = 'Erreur réseau ou serveur indisponible.';
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(async () => {
      role.value = route.params.role;

      if (!role.value || !['etudiant', 'entreprise'].includes(role.value)) {
        errorMessage.value = "Rôle invalide spécifié dans l'URL. Redirection vers la page d'accueil...";
        setTimeout(() => router.push('/'), 1500);
        return;
      }
      await fetchDynamicLists();
    });

    return {
      role,
      isLoading,
      isSubmitting,
      successMessage,
      errorMessage,
      // fileError, selectedFile, cvFile ne sont plus exposés
      // fileError,
      // selectedFile,
      // cvFile,
      etudiantForm,
      entrepriseForm,
      filieres,
      etablissements,
      domaines,
      villes,
      annees,
      anneesLoading,
      isEtudiantFormValid,
      isEnterpriseFormValid,
      // handleFileUpload n'est plus exposé
      // handleFileUpload,
      submitProfile,
    };
  },
};
</script>

<style scoped>
/* Votre CSS existant reste inchangé */
.container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 80vh;
}
.main-content {
  max-width: 700px;
  width: 100%;
  padding: 30px;
  border-radius: 8px;
  background: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
}

.error,
.error-message {
  color: #dc3545;
  font-size: 14px;
  text-align: center;
  margin-top: 10px;
}

.success-message,
.status-message {
  text-align: center;
  padding: 20px;
  color: #198754;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #0d6efd;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Styles pour les formulaires spécifiques */
.form-container {
  margin-top: 20px;
  padding: 50px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fcfcfc;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.form-container h3 {
  text-align: center;
  margin-bottom: 25px;
  color: #333;
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
}

.form-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 8px;
  color: #555;
  font-size: 14px;
}

.form-group input[type='text'],
.form-group input[type='email'],
.form-group input[type='url'],
.form-group textarea,
.form-group select {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-group input[type='text']:focus,
.form-group input[type='email']:focus,
.form-group input[type='url']:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #0d6efd;
  outline: none;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.required {
  color: red;
  margin-left: 2px;
}

.file-input-wrapper {
  position: relative;
  overflow: hidden;
  display: inline-block;
  width: 100%;
}

.file-input-wrapper input[type=file] {
  position: absolute;
  left: -9999px;
  opacity: 0;
  pointer-events: none;
}

.file-input-label {
  display: block;
  padding: 15px 20px;
  background: linear-gradient(145deg, #d8e0e3, #c0c7cb);
  border: 2px dashed #5fabcb;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #2c3e50;
  font-weight: 600;
}

.file-input-label:hover {
  background: linear-gradient(145deg, #c0c7cb, #aab1b6);
  border-color: #8fa1a8;
}

@media (max-width: 768px) {
  .main-content {
    padding: 20px;
    margin: 0 15px;
  }
  .form-row {
    flex-direction: column;
    gap: 0;
  }
  .form-group {
    margin-bottom: 10px;
  }
}
</style>
