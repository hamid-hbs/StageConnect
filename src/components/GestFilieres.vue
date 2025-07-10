<template>
  <div class="container mt-4">
    <span style="text-decoration: underline; text-decoration-color: #0d6efd;"><h3 class="mb-4">Gestion des Filières</h3></span>

    <!-- Bouton pour ajouter une nouvelle filière et barre de recherche -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
      <input
        type="text"
        class="form-control w-25"
        placeholder="Rechercher une filière..."
        v-model="searchQuery"
        
      />
      <button class="btn btn-primary" @click="openAddFiliereModal">
        <i class="fas fa-plus me-2"></i> Ajouter une filière
      </button>
    </div>

    <!-- Affichage des états de chargement, d'erreur ou d'absence de données -->
    <div v-if="isLoading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="mt-2">Chargement des filières...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      Erreur lors du chargement des filières : {{ error }}
    </div>

    <!-- MODIFIED: Condition pour l'affichage "Aucune filière trouvée" -->
    <div v-else-if="filteredFilieres.length === 0" class="alert alert-info" role="alert">
      Aucune filière trouvée correspondant à votre recherche.
    </div>

    <!-- Tableau des filières -->
    <div v-else class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Libellé de la Filière</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- MODIFIED: Itérer sur filteredFilieres au lieu de filieres -->
          <tr v-for="filiere in filteredFilieres" :key="filiere.id">
            <th scope="row">{{ filiere.id }}</th>
            <td>{{ filiere.libfil }}</td>
            <td>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-warning btn-sm"
                  @click="openEditFiliereModal(filiere)"
                  :disabled="isActionLoading[filiere.id]"
                >
                  <span v-if="isActionLoading[filiere.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  Modifier
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="confirmDeleteFiliere(filiere.id)"
                  :disabled="isActionLoading[filiere.id]"
                >
                  <span v-if="isActionLoading[filiere.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  Supprimer
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modale pour Ajouter/Modifier une filière -->
    <div class="modal fade" id="filiereModal" tabindex="-1" aria-labelledby="filiereModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="saveFiliere">
            <div class="modal-header">
              <h5 class="modal-title" id="filiereModalLabel">
                {{ isEditing ? 'Modifier la Filière' : 'Ajouter une Filière' }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="libfil" class="form-label">Libellé de la Filière</label>
                <input
                  type="text"
                  class="form-control"
                  id="libfil"
                  v-model="currentFiliere.libfil"
                  required
                />
                <div v-if="formErrors.libfil" class="text-danger mt-1">
                  {{ formErrors.libfil[0] }}
                </div>
              </div>
              <!-- Ajoutez d'autres champs si votre modèle Filiere en a -->
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
    <div class="modal fade" id="deleteFiliereModal" tabindex="-1" aria-labelledby="deleteFiliereModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteFiliereModalLabel">Confirmer la suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir supprimer la filière "{{ filiereToDeleteName }}" ? Cette action est irréversible.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" @click="deleteFiliere" :disabled="isActionLoading[filiereToDeleteId]">
              <span v-if="isActionLoading[filiereToDeleteId]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
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

const filieres = ref([]); // Stores all filieres fetched from the API
const isLoading = ref(false);
const error = ref(null);
const searchQuery = ref(''); // NEW: Model for the search bar
const isActionLoading = ref({}); // Pour gérer l'état de chargement par action/filière

// Pour la modale d'ajout/édition
const filiereModalInstance = ref(null);
const isEditing = ref(false);
const currentFiliere = ref({ id: null, libfil: '' }); // Initialisation pour le formulaire
const isSaving = ref(false);
const formErrors = ref({});

// Pour la modale de suppression
const deleteFiliereModalInstance = ref(null);
const filiereToDeleteId = ref(null);
const filiereToDeleteName = ref('');

// NEW: Computed property for client-side filtering
const filteredFilieres = computed(() => {
  if (!searchQuery.value) {
    return filieres.value; // If no search query, return all filieres
  }
  const query = searchQuery.value.toLowerCase();
  return filieres.value.filter(filiere => {
    // Search by libfil
    return filiere.libfil && filiere.libfil.toLowerCase().includes(query);
  });
});

// Initialisation des modales Bootstrap au montage du composant
onMounted(() => {
  filiereModalInstance.value = new Modal(document.getElementById('filiereModal'));
  deleteFiliereModalInstance.value = new Modal(document.getElementById('deleteFiliereModal'));
  fetchFilieres(); // Charger toutes les filières au démarrage
});

// Récupérer la liste des filières (maintenant sans paramètre de recherche)
const fetchFilieres = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/admin/filieres'); // Utilise la route de l'AdminController
    filieres.value = response.data; // Store all fetched filieres
  } catch (err) {
    console.error('Erreur lors de la récupération des filières:', err);
    error.value = err.response?.data?.message || 'Une erreur est survenue lors du chargement des filières.';
  } finally {
    isLoading.value = false;
  }
};

// Ouvre la modale pour ajouter une nouvelle filière
const openAddFiliereModal = () => {
  isEditing.value = false;
  currentFiliere.value = { id: null, libfil: '' };
  formErrors.value = {}; // Réinitialise les erreurs du formulaire
  filiereModalInstance.value.show();
};

// Ouvre la modale pour modifier une filière existante
const openEditFiliereModal = (filiere) => {
  isEditing.value = true;
  currentFiliere.value = { ...filiere }; // Copie l'objet pour éviter la modification directe
  formErrors.value = {}; // Réinitialise les erreurs du formulaire
  filiereModalInstance.value.show();
};

// Enregistre (ajoute ou modifie) une filière
const saveFiliere = async () => {
  isSaving.value = true;
  formErrors.value = {}; // Réinitialise les erreurs
  try {
    let response;
    if (isEditing.value) {
      response = await axios.put(`/api/admin/filieres/${currentFiliere.value.id}`, currentFiliere.value);
    } else {
      response = await axios.post('/api/admin/filieres', currentFiliere.value);
    }
    alert(response.data.message); // Ou un système de toast
    filiereModalInstance.value.hide();
    fetchFilieres(); // Recharger la liste après succès (pour que la recherche client soit à jour)
  } catch (err) {
    console.error('Erreur lors de l\'enregistrement de la filière:', err);
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors; // Affiche les erreurs de validation
    } else {
      alert('Erreur lors de l\'enregistrement : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
    }
  } finally {
    isSaving.value = false;
  }
};

// Prépare la suppression d'une filière (ouvre la modale de confirmation)
const confirmDeleteFiliere = (filiereId) => {
  const filiere = filieres.value.find(f => f.id === filiereId);
  if (filiere) {
    filiereToDeleteId.value = filiereId;
    filiereToDeleteName.value = filiere.libfil;
    deleteFiliereModalInstance.value.show();
  }
};

// Exécute la suppression de la filière
const deleteFiliere = async () => {
  if (!filiereToDeleteId.value) return;

  isActionLoading.value[filiereToDeleteId.value] = true;
  try {
    const response = await axios.delete(`/api/admin/filieres/${filiereToDeleteId.value}`);
    alert(response.data.message);
    deleteFiliereModalInstance.value.hide();
    fetchFilieres(); // Recharger la liste après suppression (pour que la recherche client soit à jour)
  } catch (err) {
    console.error('Erreur lors de la suppression de la filière:', err);
    alert('Erreur lors de la suppression : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
  } finally {
    isActionLoading.value[filiereToDeleteId.value] = false;
    filiereToDeleteId.value = null;
    filiereToDeleteName.value = '';
  }
};
</script>

<style scoped>
.container {
  padding-top: 20px;
}
/* Ajoutez ici des styles spécifiques à votre page de gestion des filières */
</style>
