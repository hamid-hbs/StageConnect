<template>
  <div class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-2xl w-full">
      <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Messagerie Établissement</h1>
      <p class="text-gray-600 text-center mb-6">Liste de vos conversations.</p>

      <!-- Barre de recherche d'utilisateurs -->
      <div class="mb-6 border-b pb-4 border-gray-200">
        <label for="user-search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher un utilisateur:</label>
        <div class="flex gap-2">
          <input
            type="text"
            id="user-search"
            v-model="searchQuery"
            @keyup.enter="searchUsers"
            class="flex-grow p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Rechercher par nom..."
          />
          <button
            @click="searchUsers"
            class="bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition-colors duration-200 shadow-md"
            :disabled="searchingUsers"
          >
            {{ searchingUsers ? 'Recherche...' : 'Rechercher' }}
          </button>
        </div>
        <div v-if="searchUserError" class="text-red-600 text-sm mt-2">{{ searchUserError }}</div>

        <!-- Résultats de la recherche -->
        <div v-if="searchResults.length > 0" class="mt-3 bg-gray-50 rounded-md border border-gray-200 max-h-48 overflow-y-auto">
          <div
            v-for="user in searchResults"
            :key="user.id"
            @click="selectUserAndGoToConversation(user.id, user.name)"
            class="p-3 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0 flex justify-between items-center"
          >
            <span class="font-medium text-gray-800">{{ user.name }}</span>
            <span class="text-xs text-gray-500">ID: {{ user.id }}</span>
          </div>
        </div>
        <p v-else-if="searchQuery && !searchingUsers && searchResults.length === 0" class="text-center text-gray-500 text-sm mt-2">Aucun utilisateur trouvé.</p>
      </div>

      <!-- Conteneur pour les conversations existantes -->
      <div id="conversations-list" class="space-y-4">
        <p v-if="loadingConversations" class="text-center text-gray-500">Chargement des conversations...</p>
        <p v-if="!loadingConversations && conversations.length === 0 && !searchQuery" class="text-center text-gray-500">Aucune conversation trouvée.</p>

        <div
          v-for="conv in conversations"
          :key="conv.participant.id"
          class="bg-gray-50 p-4 rounded-md shadow-sm border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors duration-200"
          @click="selectUserAndGoToConversation(conv.participant.id, conv.participant.name)"
        >
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-800">{{ conv.participant.name }}</h3>
            <span v-if="conv.unread_count > 0" class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">
              {{ conv.unread_count }} non lus
            </span>
          </div>
          <template v-if="conv.last_message">
            <p class="text-sm text-gray-700">{{ conv.last_message.is_my_message ? 'Vous: ' : '' }}{{ conv.last_message.contenu }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ new Date(conv.last_message.created_at).toLocaleString() }}</p>
          </template>
          <p v-else class="text-sm text-gray-500">Pas encore de message.</p>
        </div>
      </div>

      <div v-if="conversationError" class="text-red-600 mt-4">{{ conversationError }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// Importez `useRouter` si vous utilisez vue-router pour la navigation
// import { useRouter } from 'vue-router';

// Remplacez ceci par l'URL de base de votre API Laravel
const API_BASE_URL = 'http://localhost:8000/api'; // Exemple
// Remplacez ceci par le token d'authentification de l'utilisateur actuel
// Idéalement, ce token proviendrait d'un store Vuex, d'un Pinia store ou d'un service d'authentification
const AUTH_TOKEN = 'VOTRE_TOKEN_JWT_ICI'; // Exemple: Obtenu après la connexion
const CURRENT_USER_ID = 1; // ID de l'utilisateur actuel (à remplacer par l'ID réel de l'utilisateur authentifié)

// const router = useRouter(); // Décommenter si vous utilisez vue-router

// États pour la recherche d'utilisateurs
const searchQuery = ref('');
const searchResults = ref([]);
const searchingUsers = ref(false);
const searchUserError = ref(null);

// États pour les conversations existantes
const conversations = ref([]);
const loadingConversations = ref(true);
const conversationError = ref(null);

/**
 * Fonction pour récupérer les conversations depuis l'API.
 */
const fetchConversations = async () => {
  loadingConversations.value = true;
  conversationError.value = null;

  try {
    const response = await fetch(`${API_BASE_URL}/messages`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${AUTH_TOKEN}` // Ajoutez votre token d'authentification ici
      }
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Erreur lors de la récupération des conversations.');
    }

    conversations.value = await response.json();

  } catch (err) {
    console.error('Erreur lors de la récupération des conversations:', err);
    conversationError.value = `Erreur: ${err.message}`;
  } finally {
    loadingConversations.value = false;
  }
};

/**
 * Fonction pour rechercher les utilisateurs.
 * Pour que cela fonctionne, vous devez créer un point de terminaison API Laravel
 * qui gère la recherche d'utilisateurs (ex: /api/users/search?query={searchQuery}).
 * Cette implémentation simule la recherche si l'endpoint n'est pas disponible.
 */
const searchUsers = async () => {
  const query = searchQuery.value.trim();
  if (!query) {
    searchResults.value = [];
    searchUserError.value = null;
    return;
  }

  searchingUsers.value = true;
  searchUserError.value = null;
  searchResults.value = [];

  try {
    // --- Début de la simulation (à remplacer par un vrai appel API Laravel) ---
    // Exemple d'appel API réel:
    // const response = await fetch(`${API_BASE_URL}/users/search?query=${encodeURIComponent(query)}`, {
    //   headers: { 'Authorization': `Bearer ${AUTH_TOKEN}` }
    // });
    // if (!response.ok) {
    //   const errorData = await response.json();
    //   throw new Error(errorData.message || 'Erreur lors de la recherche des utilisateurs.');
    // }
    // const data = await response.json();
    // searchResults.value = data.users; // Assurez-vous que votre API retourne un tableau sous la clé 'users'

    // Simulation de recherche: Remplacez ceci par un appel API réel à votre backend Laravel
    await new Promise(resolve => setTimeout(resolve, 500)); // Simule un délai réseau
    const allUsers = [
      { id: 1, name: 'Alice Smith' },
      { id: 2, name: 'Bob Johnson' },
      { id: 3, name: 'Charlie Brown' },
      { id: 4, name: 'Diana Prince' },
      { id: 5, name: 'Eve Adams' },
    ];
    searchResults.value = allUsers.filter(user =>
      user.name.toLowerCase().includes(query.toLowerCase()) && user.id !== CURRENT_USER_ID
    );
    // --- Fin de la simulation ---

  } catch (err) {
    console.error('Erreur lors de la recherche des utilisateurs:', err);
    searchUserError.value = `Erreur de recherche: ${err.message}`;
  } finally {
    searchingUsers.value = false;
  }
};

/**
 * Fonction de navigation vers la page de conversation avec l'ID du participant.
 * Utilise window.location pour la démonstration. Pour une application Vue.js réelle,
 * utilisez `router.push()` avec vue-router.
 * @param {number} participantId - L'ID du participant de la conversation.
 * @param {string} participantName - Le nom du participant.
 */
const selectUserAndGoToConversation = (participantId, participantName) => {
  console.log(`Naviguer vers la conversation avec ${participantName} (ID: ${participantId})`);
  // Exemple avec window.location.href (à remplacer par router.push si vous utilisez vue-router)
  window.location.href = `/conversation?userId=${participantId}`; // Adaptez cette URL à votre configuration de routeur
  // Exemple avec vue-router:
  // router.push({ name: 'conversation', params: { id: participantId } });
};

// Appeler la fonction au montage du composant
onMounted(fetchConversations);
</script>

<style scoped>
/* Les styles spécifiques au composant peuvent être ajoutés ici si nécessaire.
   Tailwind CSS est principalement utilisé directement dans le template. */
body {
    font-family: 'Inter', sans-serif;
    background-color: #f3f4f6; /* Gris clair */
}
</style>
