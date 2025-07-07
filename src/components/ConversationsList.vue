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
              <span v-if="selectedConversationUserId !== conv.participant.id && conv.unread_count > 0" class="badge bg-primary rounded-pill ms-2">
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
            @message-sent="handleMessageSent" :conversations-list="conversations"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onBeforeUnmount, inject } from 'vue'; // Ajout de 'inject'
import axios from '../axios'; 
import { useRoute, useRouter } from 'vue-router';
import moment from 'moment';
import 'moment/locale/fr';

import ConversationDetail from '../components/ConversationDetail.vue';

moment.locale('fr');

const route = useRoute();
const router = useRouter();
const refreshUnreadNotificationsLayout = inject('refreshUnreadNotifications', () => {}); // Récupère la fonction du layout parent

// --- Données réactives ---
const conversations = ref([]);
const currentUser = ref({ id: null, name: 'Vous', photo: null, is_online: false, role: null });
const selectedConversationUserId = ref(null);
const searchTerm = ref('');
const searchResults = ref([]); 
const searchingUsers = ref(false); 

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
    conversations.value = response.data;

    // Assurez-vous que la conversation actuellement sélectionnée a 0 messages non lus localement
    if (selectedConversationUserId.value) {
        const selectedConv = conversations.value.find(conv => conv.participant.id === selectedConversationUserId.value);
        if (selectedConv) {
            selectedConv.unread_count = 0; // Force à 0 pour la conversation active
        }
    }

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

// Fonction pour marquer les messages comme lus (côté backend)
const markMessagesAsRead = async (conversationId) => {
    if (!conversationId) return;
    try {
        await axios.post(`/api/messages/${conversationId}/mark-as-read`);
        console.log(`Conversation ${conversationId} marquée comme lue sur le backend.`);
        // Rafraîchir le compteur global de notifications dans le layout parent
        refreshUnreadNotificationsLayout(); 
    } catch (error) {
        console.error("Erreur lors du marquage des messages comme lus sur le backend:", error);
    }
};


// --- Fonctions utilitaires d'affichage et de navigation ---
const selectConversation = async (userId, isNew = false) => {
  if (selectedConversationUserId.value !== userId) {
    selectedConversationUserId.value = userId;
    router.push({ params: { userId: userId } });

    // Trouver la conversation et la marquer comme lue côté backend ET localement
    const conversation = conversations.value.find(conv => conv.participant.id === selectedConversationUserId.value);
    if (conversation) {
      if (conversation.unread_count > 0) {
        conversation.unread_count = 0; // Mettre à zéro localement immédiatement
        await markMessagesAsRead(conversation.id); // Notifier le backend
      }
    }

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
watch(() => route.params.userId, async (newUserId) => {
  selectedConversationUserId.value = newUserId ? parseInt(newUserId) : null;
  if (selectedConversationUserId.value) {
    // Re-appliquer le marquage lu si l'URL change alors que le composant est monté
    const conversation = conversations.value.find(conv => conv.participant.id === selectedConversationUserId.value);
    if (conversation && conversation.unread_count > 0) {
      conversation.unread_count = 0; // Mettre à zéro localement
      await markMessagesAsRead(conversation.id); // Notifier le backend
    }
  }
}, { immediate: true }); 

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
const POLLING_INTERVAL_MS = 3000; // 3 secondes

const startPollingConversations = () => {
  if (!conversationPollingInterval) {
    conversationPollingInterval = setInterval(fetchConversations, POLLING_INTERVAL_MS);
    console.log(`Démarrage du rafraîchissement des conversations toutes les ${POLLING_INTERVAL_MS / 1000} secondes.`);
  }
};

const stopPollingConversations = () => {
  if (conversationPollingInterval) {
    clearInterval(conversationPollingInterval);
    conversationPollingInterval = null;
    console.log('Arrêt du rafraîchissement des conversations.');
  }
};

// --- Gestion des événements du composant enfant ---
const handleMessagesRead = () => {
    // Cette fonction est appelée par ConversationDetail lorsque des messages sont lus.
    // Cela déclenche un rafraîchissement des conversations pour mettre à jour les compteurs.
    fetchConversations();
};

const handleMessageSent = async (conversationId) => {
    // Lorsque l'utilisateur envoie un message, assurez-vous que le compteur est à zéro
    // pour cette conversation et rafraîchissez pour refléter les derniers messages.
    const conversation = conversations.value.find(conv => conv.id === conversationId);
    if (conversation) {
        conversation.unread_count = 0; // Mettre à zéro localement
    }
    await fetchConversations(); // Rafraîchir pour obtenir le dernier message et trier
};

// --- Cycle de vie du composant ---
onMounted(async () => {
  await fetchCurrentUser();
  await fetchConversations();

  // Démarrer l'intervalle de rafraîchissement
  startPollingConversations();
});

onBeforeUnmount(() => {
  // Nettoyer l'intervalle avant que le composant ne soit détruit
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

/* Styles pour le hover sur les éléments de conversation */
.hover-bg-light:hover {
  background-color: #e2e6ea; /* Une couleur un peu plus foncée pour le hover */
}
</style>