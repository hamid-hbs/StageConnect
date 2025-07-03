<template>
  <div class="container-fluid py-4">
    <div class="card shadow-lg rounded-lg overflow-hidden">

      <div class="d-flex chat-container">
        <div class="col-md-4 border-end bg-white d-none d-md-block">
          <div class="p-3 bg-light">
            <div class="input-group">
              <span class="input-group-text bg-white border-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input
                type="text"
                placeholder="Rechercher ou démarrer une discussion"
                class="form-control border-0 focus-ring"
                v-model="searchTerm"
              />
            </div>
          </div>

          <div class="overflow-auto" style="height: calc(100% - 70px);">
            <div v-if="loadingConversations">
              <!--Chargement des conversations...-->
            </div>
            <div v-else-if="searchTerm && filteredConversations.length === 0">
                <div v-if="searchingUsers" class="text-center p-4 text-muted">
                    Recherche d'utilisateurs...
                </div>
                <div v-else-if="searchResults.length === 0" class="text-center p-4 text-muted">
                    Aucun utilisateur trouvé.
                </div>
                <div
                    v-for="user in searchResults"
                    :key="user.id"
                    @click="selectConversation(user.id, true)"
                    class="p-3 border-bottom cursor-pointer d-flex align-items-center list-group-item-action hover-bg-light"
                    :class="{'bg-light': selectedConversationUserId === user.id}"
                >
                    <div class="position-relative me-3">
                        <img
                            :src="getProfilePhoto(user.photo)"
                            :alt="user.name"
                            class="rounded-circle object-fit-cover"
                            style="width: 50px; height: 50px;"
                        />
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-semibold mb-0 text-dark">{{ user.name }}</h6>
                        </div>
                        <p v-if="user.company_name" class="text-muted text-truncate mb-0">
                            {{ user.company_name }}
                        </p>
                        <p class="text-muted text-truncate mb-0">
                            Nouvelle conversation
                        </p>
                    </div>
                </div>
            </div>

            <div v-else-if="filteredConversations.length === 0" class="text-center p-4 text-muted">
              Aucune conversation trouvée.
            </div>
            <div
              v-for="conv in filteredConversations"
              :key="conv.participant.id"
              @click="selectConversation(conv.participant.id)"
              :class="{
                'p-3 border-bottom cursor-pointer d-flex align-items-center': true,
                'list-group-item-action': true,
                'bg-light': selectedConversationUserId === conv.participant.id,
                'hover-bg-light': selectedConversationUserId !== conv.participant.id
              }"
            >
              <div class="position-relative me-3">
                <img
                  :src="getProfilePhoto(conv.participant.photo)"
                  :alt="conv.participant.name"
                  class="rounded-circle object-fit-cover"
                  style="width: 50px; height: 50px;"
                />
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="fw-semibold mb-0 text-dark">{{ conv.participant.name }}</h6>
                  <small class="text-muted">{{ formatDateShort(conv.last_message?.created_at) }}</small>
                </div>
                <p v-if="conv.participant.company_name" class="text-muted text-truncate mb-0">
                  {{ conv.participant.company_name }}
                </p>
                <p class="text-muted text-truncate mb-0">
                  {{ conv.last_message ? (conv.last_message.expediteur_id === currentUser.id ? 'Moi: ' : '') + truncateText(conv.last_message.contenu, 25) : 'Pas de messages' }}
                </p>
              </div>
              <span v-if="conv.unread_count > 0" class="badge bg-primary rounded-pill ms-2">
                {{ conv.unread_count }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column bg-light-subtle">
          <ConversationDetail
            :userId="selectedConversationUserId"
            :current-user="currentUser"
            @messages-read="handleMessagesRead"
            @conversation-updated="fetchConversations"
            :conversations-list="conversations"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import moment from 'moment';
import 'moment/locale/fr';

import ConversationDetail from '../components/ConversationDetail.vue';

moment.locale('fr');

const route = useRoute();
const router = useRouter();

// --- Données réactives ---
const conversations = ref([]);
const currentUser = ref({ id: null, name: 'Vous', photo: null, is_online: false, role: null });
const selectedConversationUserId = ref(null);
const searchTerm = ref('');
const searchResults = ref([]); // Pour stocker les résultats de la recherche d'utilisateurs
const searchingUsers = ref(false); // Pour indiquer si une recherche d'utilisateurs est en cours

const loadingConversations = ref(true);
const messageError = ref(null);

// --- Computed properties ---
const sortedConversations = computed(() => {
  return [...conversations.value].sort((a, b) => {
    const dateA = a.last_message ? moment(a.last_message.created_at) : moment('1970-01-01');
    const dateB = b.last_message ? moment(b.last_message.created_at) : moment('1970-01-01');
    return dateB.diff(dateA);
  });
});

const filteredConversations = computed(() => {
  if (!searchTerm.value) {
    return sortedConversations.value;
  }
  const lowerCaseSearchTerm = searchTerm.value.toLowerCase();
  return sortedConversations.value.filter(conv => {
    const participantName = conv.participant.name.toLowerCase();
    const companyName = conv.participant.company_name ? conv.participant.company_name.toLowerCase() : '';
    const lastMessageContent = conv.last_message ? conv.last_message.contenu.toLowerCase() : '';

    return participantName.includes(lowerCaseSearchTerm) ||
           companyName.includes(lowerCaseSearchTerm) ||
           lastMessageContent.includes(lowerCaseSearchTerm);
  });
});


// --- Fonctions de récupération des données ---
const fetchCurrentUser = async () => {
  try {
    const response = await axios.get('/api/user');
    currentUser.value = response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération de l'utilisateur actuel:", error);
    messageError.value = "Impossible de charger les informations de l'utilisateur. Veuillez vous connecter.";
    if (error.response && error.response.status === 401) {
        router.push('/');
    }
  }
};

const fetchConversations = async () => {
  loadingConversations.value = true;
  try {
    const response = await axios.get('/api/messages');
    const newConversations = response.data;

    conversations.value = newConversations; // Met à jour la liste des conversations

    // La ligne problématique qui réinitialisait selectedConversationUserId a déjà été supprimée
    // dans les versions précédentes pour assurer la permanence de la sélection.

  } catch (error) {
    console.error("Erreur lors de la récupération des conversations:", error);
    messageError.value = "Impossible de charger les conversations.";
  } finally {
    loadingConversations.value = false;
  }
};

// Fonction pour rechercher des utilisateurs
const searchUsers = async () => {
  if (!searchTerm.value.trim()) {
    searchResults.value = [];
    return;
  }
  searchingUsers.value = true;
  try {
    const response = await axios.get(`/api/users/search?query=${searchTerm.value}`);
    const existingParticipantIds = new Set(conversations.value.map(c => c.participant.id));
    searchResults.value = response.data.filter(user =>
      user.id !== currentUser.value.id && !existingParticipantIds.has(user.id)
    );
  } catch (error) {
    console.error("Erreur lors de la recherche d'utilisateurs:", error);
    searchResults.value = [];
  } finally {
    searchingUsers.value = false;
  }
};


// --- Fonctions utilitaires d'affichage et de navigation ---
const selectConversation = (userId, isNew = false) => {
  if (selectedConversationUserId.value !== userId) {
    selectedConversationUserId.value = userId;
    router.push({ params: { userId: userId } });

    if (isNew) {
      searchTerm.value = '';
      searchResults.value = [];
    }
  }
};

const formatDateShort = (date) => {
  if (!date) return '';
  const m = moment(date);
  if (m.isSame(moment(), 'day')) return m.format('HH:mm');
  if (m.isSame(moment().subtract(1, 'days'), 'day')) return 'Hier';
  if (m.isSame(moment(), 'week')) return m.format('ddd.');
  return m.format('DD/MM/YY');
};

const truncateText = (text, maxLength) => {
  if (text && text.length > maxLength) {
    return text.substring(0, maxLength) + '...';
  }
  return text || '';
};

const getProfilePhoto = (photoUrl) => {
  const baseURL = import.meta.env.VITE_API_URL || '';
  if (photoUrl) {
    if (photoUrl.startsWith('http://') || photoUrl.startsWith('https://')) {
        return photoUrl;
    }
    const cleanedPhotoUrl = photoUrl.startsWith('public/') ? photoUrl.substring(7) : photoUrl;
    return `${baseURL}/storage/${cleanedPhotoUrl}`;
  }
  return '/images/default-avatar.png';
};

// --- Watchers ---
// Surveille les changements de l'ID utilisateur dans l'URL.
watch(() => route.params.userId, (newUserId) => {
  selectedConversationUserId.value = newUserId ? parseInt(newUserId) : null;
}, { immediate: true }); // Exécute immédiatement au montage du composant

// --- LOGIQUE DE SÉLECTION AUTOMATIQUE DÉSACTIVÉE ---
// Le watcher suivant est celui qui était responsable de la sélection automatique.
// Nous le commentons ou le supprimons pour désactiver ce comportement.
/*
watch(filteredConversations, (newConversations) => {
  if (!selectedConversationUserId.value && currentUser.value.id && newConversations.length > 0) {
    selectConversation(newConversations[0].participant.id);
  }
}, { immediate: true });
*/
// --- FIN DE LA LOGIQUE DE SÉLECTION AUTOMATIQUE DÉSACTIVÉE ---


// Surveille le terme de recherche pour déclencher la recherche d'utilisateurs
watch(searchTerm, (newVal) => {
  if (newVal.trim()) {
    searchUsers();
  } else {
    searchResults.value = [];
  }
});


// --- Gestion des intervalles de rafraîchissement ---
let conversationPollingInterval = null;

const startPollingConversations = () => {
  if (!conversationPollingInterval) {
    conversationPollingInterval = setInterval(fetchConversations, 10000);
  }
};

const stopPollingConversations = () => {
  if (conversationPollingInterval) {
    clearInterval(conversationPollingInterval);
    conversationPollingInterval = null;
  }
};

// --- Gestion des événements du composant enfant ---
const handleMessagesRead = () => {
    fetchConversations();
};

// --- Cycle de vie du composant ---
onMounted(async () => {
  await fetchCurrentUser();
  await fetchConversations();

  // Si vous voulez une sélection automatique seulement lors du premier chargement
  // et non à chaque mise à jour de filteredConversations, vous pouvez la mettre ici.
  // Cependant, si vous la supprimez complètement, aucune sélection automatique n'aura lieu.
  // Exemple si vous vouliez une sélection automatique au premier chargement SEULEMENT:
  /*
  if (!selectedConversationUserId.value && currentUser.value.id && sortedConversations.value.length > 0) {
    selectConversation(sortedConversations.value[0].participant.id);
  }
  */

  startPollingConversations();
});

onBeforeUnmount(() => {
  stopPollingConversations();
});
</script>

<style scoped>
.chat-container {
  height: calc(100vh - 80px);
  display: flex;
  flex-grow: 1;
}

.card {
  height: 100%;
}

.list-group-item-action:not(.bg-light):hover {
  background-color: #f8f9fa;
}

.input-group .form-control.border-0.focus-ring {
  box-shadow: none !important;
}
</style>