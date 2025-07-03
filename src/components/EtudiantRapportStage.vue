<template>
  <div class="etudiant-rapport-stage p-6 bg-blue-50 min-h-screen">
    <h1 class="text-3xl font-bold text-center text-blue-800 mb-8 rounded-lg p-3 bg-white shadow-md border-b-4 border-blue-600">
      Mon Rapport de Stage
    </h1>

    <div v-if="loading" class="text-center text-blue-600 text-lg py-4">Chargement de votre stage...</div>
    <div v-if="error" class="text-center text-red-600 text-lg py-4">
      Erreur : {{ error }}
      <p v-if="error === 'Aucun stage trouvé pour cet étudiant.'">
        Vérifiez que votre compte étudiant est bien lié à un stage dans le système.
      </p>
    </div>

    <div v-if="stage" class="bg-white rounded-lg shadow-xl p-6 mb-8 border border-blue-200">
      <h2 class="text-2xl font-semibold text-blue-700 mb-4 pb-2 border-b border-blue-100">Détails de mon stage</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
        <p><strong>Titre de l'offre :</strong> {{ stage.offre.titre }}</p>
        <p><strong>Entreprise :</strong> {{ stage.offre.entreprise.user.name }}</p>
        <p><strong>Date de début :</strong> {{ formatDate(stage.date_debut) }}</p>
        <p><strong>Date de fin :</strong> {{ formatDate(stage.date_fin) }}</p>
        <p><strong>Statut du stage :</strong>
          <span :class="getStatusClass(stage.statut)">{{ formatStatus(stage.statut) }}</span>
        </p>
        <p v-if="stage.note_stage !== null">
          <strong>Note obtenue :</strong> <span class="font-bold text-blue-800">{{ stage.note_stage }}/20</span>
        </p>
        <p v-if="stage.commentaire_note">
          <strong>Commentaire de l'entreprise :</strong> <span class="italic">{{ stage.commentaire_note }}</span>
        </p>
      </div>

      <div class="mt-8 border-t-2 pt-6 border-blue-100">
        <h3 class="text-xl font-semibold text-blue-700 mb-4">Gestion du Rapport</h3>
        <div v-if="stage.rapport_stage" class="mb-6 flex items-center justify-between bg-blue-100 border border-blue-300 rounded-lg p-4 shadow-sm">
          <p class="text-blue-800 font-medium flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0113 3.414L16.586 7A2 2 0 0118 8.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0h4v3a1 1 0 001 1h3v7a1 1 0 01-1 1H6a1 1 0 01-1-1V5a1 1 0 011-1zm3 10a1 1 0 00-1 1v1a1 1 0 102 0v-1a1 1 0 00-1-1zm3 0a1 1 0 00-1 1v1a1 1 0 102 0v-1a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            Votre rapport de stage actuel est disponible.
          </p>
          <button @click="downloadRapport" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-md shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
            Télécharger
          </button>
        </div>
        <div v-else class="mb-6 text-gray-600 italic p-4 bg-gray-100 border border-gray-200 rounded-lg">
          Vous n'avez pas encore téléversé votre rapport de stage.
        </div>

        <form @submit.prevent="uploadRapport" class="mt-4 p-6 border border-blue-200 rounded-lg bg-blue-50 shadow-md">
          <h4 class="text-xl font-medium text-blue-700 mb-4">Téléverser/Modifier votre rapport</h4>
          <p v-if="stage.statut !== 'termine'" class="text-red-600 bg-red-100 p-3 rounded-md mb-4 border border-red-200">
            * Vous ne pouvez téléverser ou modifier le rapport que si votre stage est **terminé**.
          </p>
          <div class="mb-4">
            <label for="rapport_stage_file" class="block text-blue-800 text-sm font-bold mb-2">
              Fichier du rapport (PDF, DOC, DOCX - max 10MB) :
            </label>
            <input
              type="file"
              id="rapport_stage_file"
              ref="rapportFile"
              @change="handleFileUpload"
              :disabled="stage.statut !== 'termine'"
              class="block w-full text-sm text-gray-900 border border-blue-300 rounded-lg cursor-pointer bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            />
          </div>

          <div v-if="uploadError" class="text-red-500 mb-4 bg-red-50 p-2 rounded-md border border-red-100">{{ uploadError }}</div>
          <div v-if="uploadSuccess" class="text-green-600 mb-4 bg-green-50 p-2 rounded-md border border-green-100">{{ uploadSuccess }}</div>

          <button
            type="submit"
            :disabled="!selectedFile || stage.statut !== 'termine' || uploading"
            :class="{
              'bg-blue-600 hover:bg-blue-700 transform hover:scale-105': selectedFile && stage.statut === 'termine' && !uploading,
              'bg-gray-400 cursor-not-allowed': !selectedFile || stage.statut !== 'termine' || uploading
            }"
            class="text-white font-bold py-2 px-5 rounded-md shadow-lg transition duration-300 ease-in-out"
          >
            <span v-if="uploading">Téléversement...</span>
            <span v-else>Enregistrer le rapport</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EtudiantRapportStage',
  data() {
    return {
      stage: null,
      currentUser: null, // Pour stocker les infos de l'utilisateur connecté
      loading: true,
      error: null,
      selectedFile: null,
      uploading: false,
      uploadError: null,
      uploadSuccess: null,
    };
  },
  computed: {
    downloadReportUrl() {
      // Cette URL sera directement gérée par votre route Laravel pour le téléchargement
      if (this.stage && this.stage.rapport_stage) {
        return `/api/stages/${this.stage.id}/rapport/download`;
      }
      return '#'; // URL par défaut si pas de rapport
    }
  },
  async created() {
    // Récupérer d'abord l'utilisateur actuel pour obtenir son ID étudiant
    await this.getCurrentUser();
    if (this.currentUser && this.currentUser.etudiant) {
      // Une fois l'ID étudiant connu, on peut chercher le stage
      this.fetchMyStage();
    } else {
      this.loading = false;
      this.error = 'Impossible de charger vos informations d\'étudiant. Assurez-vous d\'être connecté en tant qu\'étudiant.';
    }
  },
  methods: {
    async getCurrentUser() {
      try {
        const response = await axios.get('/api/user', {
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
          }
        });
        this.currentUser = response.data;
      } catch (err) {
        console.error("Erreur lors de la récupération de l'utilisateur actuel :", err);
        this.error = "Impossible de récupérer vos informations d'utilisateur.";
        this.currentUser = null;
      }
    },
    async fetchMyStage() {
      this.loading = true;
      this.error = null;
      try {
        // Récupérer TOUS les stages et filtrer pour trouver celui de l'étudiant connecté
        const response = await axios.get('/api/stages', {
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('authToken')
          }
        });

        // Trouver le stage dont l'etudiant_id correspond à celui de l'utilisateur connecté
        this.stage = response.data.find(s => s.etudiant && s.etudiant.id === this.currentUser.etudiant.id);

        if (!this.stage) {
          this.error = 'Aucun stage trouvé pour cet étudiant.';
        }
      } catch (err) {
        console.error('Erreur lors de la récupération des stages :', err);
        if (err.response && err.response.data && err.response.data.message) {
          this.error = err.response.data.message;
        } else {
          this.error = 'Une erreur est survenue lors du chargement de votre stage.';
        }
      } finally {
        this.loading = false;
      }
    },
    handleFileUpload(event) {
      this.selectedFile = event.target.files[0];
      this.uploadError = null;
      this.uploadSuccess = null;
    },
    async uploadRapport() {
      if (!this.selectedFile) {
        this.uploadError = 'Veuillez sélectionner un fichier à téléverser.';
        return;
      }
      if (this.stage.statut !== 'termine') {
        this.uploadError = 'Vous ne pouvez téléverser un rapport que si le stage est terminé.';
        return;
      }

      this.uploading = true;
      this.uploadError = null;
      this.uploadSuccess = null;

      const formData = new FormData();
      formData.append('rapport_stage', this.selectedFile);
      formData.append('_method', 'PUT');

      try {
        const response = await axios.post(`/api/stages/${this.stage.id}`, formData, {
          headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('authToken'),
            'Content-Type': 'multipart/form-data',
          },
        });
        this.stage = response.data.stage;
        this.uploadSuccess = 'Rapport de stage téléversé avec succès !';
        this.selectedFile = null;
        this.$refs.rapportFile.value = '';
      } catch (err) {
        console.error('Erreur lors du téléversement du rapport :', err);
        if (err.response && err.response.data) {
            if (err.response.data.message) {
                this.uploadError = err.response.data.message;
            } else if (err.response.data.errors) {
                this.uploadError = Object.values(err.response.data.errors).flat().join(' ');
            }
        } else {
          this.uploadError = 'Une erreur est survenue lors du téléversement.';
        }
      } finally {
        this.uploading = false;
      }
    },
    async downloadRapport() {
      if (!this.stage || !this.stage.rapport_stage) {
        alert('Aucun rapport de stage à télécharger.');
        return;
      }

      try {
        window.open(`/api/stages/${this.stage.id}/rapport/download`, '_blank');
      } catch (err) {
        console.error('Erreur lors du téléchargement du rapport :', err);
        alert('Impossible de télécharger le rapport. Veuillez réessayer plus tard.');
      }
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('fr-FR', options);
    },
    formatStatus(status) {
      const statuses = {
        'en_attente': 'En attente',
        'en_cours': 'En cours',
        'termine': 'Terminé',
        'suspendu': 'Suspendu',
      };
      return statuses[status] || status;
    },
    getStatusClass(status) {
      switch (status) {
        case 'en_attente': return 'text-yellow-600 font-semibold';
        case 'en_cours': return 'text-blue-600 font-semibold';
        case 'termine': return 'text-green-600 font-semibold';
        case 'suspendu': return 'text-red-600 font-semibold';
        default: return 'text-gray-600';
      }
    }
  },
};
</script>

<style scoped>
/* Aucun style spécifique ajouté ici car Tailwind gère tout en ligne */
</style>