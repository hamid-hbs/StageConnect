<template>
  <div class="container mt-4">
    <span style="text-decoration: underline; text-decoration-color: #0d6efd;"><h3 class="mb-4">Gestion des Établissements</h3></span>

    <!-- Bouton pour ajouter un nouvel établissement et barre de recherche -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
      <input
        type="text"
        class="form-control w-25"
        placeholder="Rechercher un établissement..."
        v-model="searchQuery"
      
      />
      <button class="btn btn-primary" @click="openAddEtablissementModal">
        <i class="fas fa-plus me-2"></i> Ajouter un établissement
      </button>
    </div>

    <!-- Affichage des états de chargement, d'erreur ou d'absence de données -->
    <div v-if="isLoading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="mt-2">Chargement des établissements...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      Erreur lors du chargement des établissements : {{ error }}
    </div>

    <!-- MODIFIED: Condition pour l'affichage "Aucun établissement trouvé" -->
    <div v-else-if="filteredEtablissements.length === 0" class="alert alert-info" role="alert">
      Aucun établissement trouvé correspondant à votre recherche.
    </div>

    <!-- Tableau des établissements -->
    <div v-else class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nom de l'Établissement</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- MODIFIED: Itérer sur filteredEtablissements au lieu de etablissements -->
          <tr v-for="etablissement in filteredEtablissements" :key="etablissement.id">
            <th scope="row">{{ etablissement.id }}</th>
            <td>{{ etablissement.nom_etablissement }}</td>
            <td>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-warning btn-sm"
                  @click="openEditEtablissementModal(etablissement)"
                  :disabled="isActionLoading[etablissement.id]"
                >
                  <span v-if="isActionLoading[etablissement.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  Modifier
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="confirmDeleteEtablissement(etablissement.id)"
                  :disabled="isActionLoading[etablissement.id]"
                >
                  <span v-if="isActionLoading[etablissement.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  Supprimer
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modale pour Ajouter/Modifier un établissement -->
    <div class="modal fade" id="etablissementModal" tabindex="-1" aria-labelledby="etablissementModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="saveEtablissement">
            <div class="modal-header">
              <h5 class="modal-title" id="etablissementModalLabel">
                {{ isEditing ? 'Modifier l\'Établissement' : 'Ajouter un Établissement' }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="nom_etablissement" class="form-label">Nom de l'Établissement</label>
                <input
                  type="text"
                  class="form-control"
                  id="nom_etablissement"
                  v-model="currentEtablissement.nom_etablissement"
                  required
                />
                <div v-if="formErrors.nom_etablissement" class="text-danger mt-1">
                  {{ formErrors.nom_etablissement[0] }}
                </div>
              </div>
              <!-- Ajoutez d'autres champs si votre modèle Etablissement en a et que l'admin doit les gérer -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modale de confirmation pour la suppression -->
    <div class="modal fade" id="deleteEtablissementModal" tabindex="-1" aria-labelledby="deleteEtablissementModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteEtablissementModalLabel">Confirmer la suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir supprimer l'établissement "{{ etablissementToDeleteName }}" ? Cette action est irréversible.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" @click="deleteEtablissement" :disabled="isActionLoading[etablissementToDeleteId]">
              <span v-if="isActionLoading[etablissementToDeleteId]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'; // Added 'computed'
import axios from '../axios'; // Assurez-vous que le chemin est correct
import { Modal } from 'bootstrap'; // Importez la classe Modal de Bootstrap

const etablissements = ref([]); // Stores all etablissements fetched from the API
const isLoading = ref(false);
const error = ref(null);
const searchQuery = ref(''); // NEW: Model for the search bar
const isActionLoading = ref({}); // Pour gérer l'état de chargement par action/établissement

// Pour la modale d'ajout/édition
const etablissementModalInstance = ref(null);
const isEditing = ref(false);
const currentEtablissement = ref({ id: null, nom_etablissement: '' }); // Initialisation pour le formulaire
const isSaving = ref(false);
const formErrors = ref({});

// Pour la modale de suppression
const deleteEtablissementModalInstance = ref(null);
const etablissementToDeleteId = ref(null);
const etablissementToDeleteName = ref('');

// NEW: Computed property for client-side filtering
const filteredEtablissements = computed(() => {
  if (!searchQuery.value) {
    return etablissements.value; // If no search query, return all etablissements
  }
  const query = searchQuery.value.toLowerCase();
  return etablissements.value.filter(etablissement => {
    // Search by nom_etablissement
    return etablissement.nom_etablissement && etablissement.nom_etablissement.toLowerCase().includes(query);
  });
});

// Initialisation des modales Bootstrap au montage du composant
onMounted(() => {
  etablissementModalInstance.value = new Modal(document.getElementById('etablissementModal'));
  deleteEtablissementModalInstance.value = new Modal(document.getElementById('deleteEtablissementModal'));
  fetchEtablissements(); // Charger tous les établissements au démarrage
});

// Récupérer la liste des établissements (maintenant sans paramètre de recherche)
const fetchEtablissements = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/admin/etablissements'); // Utilise la route de l'AdminController
    etablissements.value = response.data; // Store all fetched etablissements
  } catch (err) {
    console.error('Erreur lors de la récupération des établissements:', err);
    error.value = err.response?.data?.message || 'Une erreur est survenue lors du chargement des établissements.';
  } finally {
    isLoading.value = false;
  }
};

// Ouvre la modale pour ajouter un nouvel établissement
const openAddEtablissementModal = () => {
  isEditing.value = false;
  currentEtablissement.value = { id: null, nom_etablissement: '' };
  formErrors.value = {}; // Réinitialise les erreurs du formulaire
  etablissementModalInstance.value.show();
};

// Ouvre la modale pour modifier un établissement existant
const openEditEtablissementModal = (etablissement) => {
  isEditing.value = true;
  currentEtablissement.value = { ...etablissement }; // Copie l'objet pour éviter la modification directe
  formErrors.value = {}; // Réinitialise les erreurs du formulaire
  etablissementModalInstance.value.show();
};

// Enregistre (ajoute ou modifie) un établissement
const saveEtablissement = async () => {
  isSaving.value = true;
  formErrors.value = {}; // Réinitialise les erreurs
  try {
    let response;
    if (isEditing.value) {
      response = await axios.put(`/api/admin/etablissements/${currentEtablissement.value.id}`, currentEtablissement.value);
    } else {
      response = await axios.post('/api/admin/etablissements', currentEtablissement.value);
    }
    alert(response.data.message); // Ou un système de toast
    etablissementModalInstance.value.hide();
    fetchEtablissements(); // Recharger la liste après succès (pour que la recherche client soit à jour)
  } catch (err) {
    console.error('Erreur lors de l\'enregistrement de l\'établissement:', err);
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors; // Affiche les erreurs de validation
    } else {
      alert('Erreur lors de l\'enregistrement : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
    }
  } finally {
    isSaving.value = false;
  }
};

// Prépare la suppression d'un établissement (ouvre la modale de confirmation)
const confirmDeleteEtablissement = (etablissementId) => {
  const etablissement = etablissements.value.find(e => e.id === etablissementId);
  if (etablissement) {
    etablissementToDeleteId.value = etablissementId;
    etablissementToDeleteName.value = etablissement.nom_etablissement;
    deleteEtablissementModalInstance.value.show();
  }
};

// Exécute la suppression de l'établissement
const deleteEtablissement = async () => {
  if (!etablissementToDeleteId.value) return;

  isActionLoading.value[etablissementToDeleteId.value] = true;
  try {
    const response = await axios.delete(`/api/admin/etablissements/${etablissementToDeleteId.value}`);
    alert(response.data.message);
    deleteEtablissementModalInstance.value.hide();
    fetchEtablissements(); // Recharger la liste après suppression (pour que la recherche client soit à jour)
  } catch (err) {
    console.error('Erreur lors de la suppression de l\'établissement:', err);
    alert('Erreur lors de la suppression : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
  } finally {
    isActionLoading.value[etablissementToDeleteId.value] = false;
    etablissementToDeleteId.value = null;
    etablissementToDeleteName.value = '';
  }
};
</script>

<style scoped>
.container {
  padding-top: 20px;
}
/* Ajoutez ici des styles spécifiques à votre page de gestion des établissements */
</style>
