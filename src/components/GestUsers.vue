<template>
  <div class="container mt-4">
    <span style="text-decoration: underline; text-decoration-color: #0d6efd;"><h3 class="mb-4">Gestion des Utilisateurs</h3></span>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <input
        type="text"
        class="form-control w-25"
        placeholder="Rechercher un utilisateur..."
        v-model="searchQuery"
        
      />
    </div>

    <div v-if="isLoading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="mt-2">Chargement des utilisateurs...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      Une erreur est survenue lors du chargement des utilisateurs. Veuillez réessayer plus tard.
    </div>

    <!-- MODIFIED: Condition pour l'affichage "Aucun utilisateur trouvé" -->
    <div v-else-if="filteredUsers.length === 0" class="alert alert-info" role="alert">
      Aucun utilisateur trouvé correspondant à votre recherche.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Photo</th>
            <th scope="col">Nom / Entreprise</th>
            <th scope="col">Email</th>
            <th scope="col">Rôle</th>
            <th scope="col">Statut</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- MODIFIED: Itérer sur filteredUsers au lieu de users -->
          <tr v-for="user in filteredUsers" :key="user.id">
            <th scope="row">{{ user.id }}</th>
            <td>
              <img
                :src="user.photo ? getFullImageUrl(user.photo) : '/photo-default.jpeg'"
                alt="Photo de profil"
                class="rounded-circle"
                style="width: 40px; height: 40px; object-fit: cover"
              />
            </td>
            <td>{{ getUserDisplayName(user) }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span class="badge" :class="getRoleBadgeClass(user.role)">
                {{ user.role }}
              </span>
            </td>
            <td>
              <span class="badge" :class="user.is_active ? 'bg-success' : 'bg-secondary'">
                {{ user.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td>
              <div class="d-flex gap-2">
                <button
                  class="btn btn-sm"
                  :class="user.is_active ? 'btn-warning' : 'btn-success'"
                  @click="toggleUserActiveStatus(user)"
                  :disabled="isActionLoading[user.id] || user.id === authenticatedUserId"
                  :title="user.id === authenticatedUserId ? 'Impossible d\'activer/désactiver votre propre compte administrateur.' : ''"
                >
                  <span v-if="isActionLoading[user.id]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  {{ user.is_active ? 'Désactiver' : 'Activer' }}
                </button>
                <button
                  class="btn btn-info btn-sm"
                  @click="viewUserDetails(user.id)"
                  :disabled="isActionLoading[user.id]"
                >
                  Détails
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="deleteUser(user.id)"
                  :disabled="isActionLoading[user.id] || user.id === authenticatedUserId"
                  :title="user.id === authenticatedUserId ? 'Impossible de supprimer votre propre compte administrateur.' : ''"
                >
                  Supprimer
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteUserModalLabel">Confirmer la suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" @click="confirmDelete" :disabled="isActionLoading[userToDeleteId]">
              <span v-if="isActionLoading[userToDeleteId]" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
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
import axios from '../axios';
import { useRouter } from 'vue-router';
import { Modal } from 'bootstrap';

const users = ref([]); // Stores all users fetched from the API
const isLoading = ref(false);
const error = ref(null);
const searchQuery = ref(''); // Model for the search bar
const isActionLoading = ref({});

const router = useRouter();
const authenticatedUserId = ref(null);

const userToDeleteId = ref(null);
let deleteModalInstance = null;

// NEW: Computed property for client-side filtering
const filteredUsers = computed(() => {
  if (!searchQuery.value) {
    return users.value; // If no search query, return all users
  }
  const query = searchQuery.value.toLowerCase();
  return users.value.filter(user => {
    // Search by user.name (from 'users' table)
    if (user.name && user.name.toLowerCase().includes(query)) return true;
    // Search by email
    if (user.email && user.email.toLowerCase().includes(query)) return true;
    // Search by role
    if (user.role && user.role.toLowerCase().includes(query)) return true;
    // Search by specific profile names if they exist
    if (user.role === 'etudiant' && user.etudiant && user.etudiant.nom_etudiant && user.etudiant.nom_etudiant.toLowerCase().includes(query)) return true;
    if (user.role === 'entreprise' && user.entreprise && user.entreprise.nom_entreprise && user.entreprise.nom_entreprise.toLowerCase().includes(query)) return true;
    return false;
  });
});


onMounted(() => {
  deleteModalInstance = new Modal(document.getElementById('deleteUserModal'));

  const userIdFromStorage = localStorage.getItem('authenticated_user_id');
  if (userIdFromStorage) {
    authenticatedUserId.value = parseInt(userIdFromStorage);
  } else {
    axios.get('/api/user/getProfile')
      .then(response => {
        authenticatedUserId.value = response.data.id;
        localStorage.setItem('authenticated_user_id', response.data.id.toString());
      })
      .catch(err => {
        console.warn("Impossible de récupérer l'ID de l'utilisateur authentifié.");
        console.error("Détails de l'erreur d'authentification:", err);
      });
  }

  fetchUsers(); // Fetch all users once on mount
});

const getFullImageUrl = (path) => {
  if (path && path.startsWith('http')) {
    return path;
  }
  const baseUrl = axios.defaults.baseURL ? axios.defaults.baseURL.replace('/api', '') : '';
  return `${baseUrl}/storage/${path}`;
};

const fetchUsers = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    // REMOVED: params: { search: searchQuery.value } as search is now client-side
    const response = await axios.get('/api/admin/users');
    users.value = response.data; // Store all fetched users
  } catch (err) {
    console.error('Erreur lors de la récupération des utilisateurs:', err);
    if (err.response && err.response.data && err.response.data.message) {
      if (err.response.data.message.includes('Target class [auth.admin] does not exist')) {
        error.value = "Une erreur de configuration interne est survenue. Veuillez contacter l'administrateur.";
      } else {
        error.value = err.response.data.message;
      }
    } else {
      error.value = 'Une erreur est survenue lors du chargement des utilisateurs. Veuillez réessayer plus tard.';
    }
  } finally {
    isLoading.value = false;
  }
};

const getUserDisplayName = (user) => {
  if (user.role === 'etudiant' && user.etudiant && user.etudiant.nom_etudiant) {
    return user.etudiant.nom_etudiant;
  }
  if (user.role === 'entreprise' && user.entreprise && user.entreprise.nom_entreprise) {
    return user.entreprise.nom_entreprise;
  }
  return user.name || 'Nom non défini';
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin':
      return 'bg-dark';
    case 'etudiant':
      return 'bg-primary';
    case 'entreprise':
      return 'bg-info';
    case 'etablissement':
      return 'bg-secondary';
    default:
      return 'bg-light text-dark';
  }
};

const toggleUserActiveStatus = async (user) => {
  isActionLoading.value[user.id] = true;
  try {
    const endpoint = user.is_active ? `/api/admin/users/${user.id}/deactivate` : `/api/admin/users/${user.id}/activate`;
    const response = await axios.post(endpoint);
    user.is_active = !user.is_active;
    alert(response.data.message);
  } catch (err) {
    console.error('Erreur lors du changement de statut:', err);
    alert('Erreur lors du changement de statut : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
  } finally {
    isActionLoading.value[user.id] = false;
  }
};

const viewUserDetails = (userId) => {
  router.push(`/admin/users/${userId}`);
};

const deleteUser = (userId) => {
  userToDeleteId.value = userId;
  deleteModalInstance.show();
};

const confirmDelete = async () => {
  if (!userToDeleteId.value) return;

  isActionLoading.value[userToDeleteId.value] = true;
  try {
    const response = await axios.delete(`/api/admin/users/${userToDeleteId.value}`);
    users.value = users.value.filter(u => u.id !== userToDeleteId.value); // Update the original users array
    alert(response.data.message);
    deleteModalInstance.hide();
  } catch (err) {
    console.error('Erreur lors de la suppression de l\'utilisateur:', err);
    alert('Erreur lors de la suppression : ' + (err.response?.data?.message || 'Une erreur est survenue.'));
  } finally {
    isActionLoading.value[userToDeleteId.value] = false;
    userToDeleteId.value = null;
  }
};
</script>

<style scoped>
.container {
  padding-top: 20px;
}
</style>
