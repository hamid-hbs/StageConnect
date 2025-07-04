<template>
  <div class="card-header bg-white p-3 border-bottom d-flex align-items-center justify-content-between" v-if="userId && headerParticipantData">
    <div class="d-flex align-items-center">
      <img :src="getProfilePhoto(headerParticipantData.photo)" :alt="headerParticipantData.name" class="rounded-circle object-fit-cover me-2" style="width: 40px; height: 40px;">
      <div>
        <h5 class="fw-semibold mb-0 text-dark">{{ headerParticipantData.name }}</h5>
        <!--<p class="text-muted mb-0 small d-flex align-items-center">
          <span :class="{'rounded-circle me-1': true, 'bg-success': isUserOnline(headerParticipantData.id), 'bg-secondary': !isUserOnline(headerParticipantData.id)}" style="width: 8px; height: 8px;"></span>
          {{ isUserOnline(headerParticipantData.id) ? 'En ligne' : 'Hors ligne' }} 
        </p> -->
      </div>
    </div>
  </div>

  <div v-if="loadingMessages" class="flex-grow-1 d-flex justify-content-center align-items-center text-muted fs-5">
    Chargement des messages...
  </div>
  <div v-else-if="!userId" class="flex-grow-1 d-flex justify-content-center align-items-center text-muted fs-5">
    <i class="fas fa-comments icon-spacing-sidebar"></i>Sélectionnez une conversation pour commencer à discuter.
  </div>
  <div v-else class="messages-container p-3" ref="messagesContainer">
    <div v-if="messages.length === 0 && userId" class="text-center text-muted my-3">
      Envoyez le premier message à {{ headerParticipantData?.name || 'cet utilisateur' }}.
    </div>
    <template v-for="(message, index) in messages" :key="message.id">
      <div v-if="shouldShowDateSeparator(message.created_at, index)" class="text-center text-muted small my-3">
        {{ formatDateSeparator(message.created_at) }}
      </div>

      <div v-if="message.expediteur_id !== currentUser.id" class="d-flex align-items-start mb-2">
        <img :src="getProfilePhoto(message.expediteur?.photo)" :alt="message.expediteur?.name" class="rounded-circle object-fit-cover me-2" style="width: 32px; height: 32px;">
        <div class="message-bubble message-received bg-white p-2 border">
          {{ message.contenu }}
          <div class="text-end small text-muted mt-1">{{ formatDateFull(message.created_at) }}</div>
        </div>
      </div>

      <div v-else class="d-flex justify-content-end mb-2">
        <div class="message-bubble message-sent bg-primary text-white p-2">
          {{ message.contenu }}
          <div class="text-end small mt-1">
            {{ formatDateFull(message.created_at) }}
            <i :class="{'fas fa-check-double ms-1': true, 'text-white-50': !message.lu, 'text-success': message.lu}"></i>
          </div>
        </div>
      </div>
    </template>

    <!--<div v-if="isOtherUserTyping && headerParticipantData" class="d-flex align-items-start mb-2">
      <img :src="getProfilePhoto(headerParticipantData?.photo)" :alt="headerParticipantData?.name" class="rounded-circle object-fit-cover me-2" style="width: 32px; height: 32px;">
      <div class="message-bubble message-received bg-white p-2 border d-flex align-items-center">
        <span class="typing-indicator">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </span>
      </div>
    </div> -->
  </div>

  <div v-if="userId" class="card-footer bg-white p-3 message-input-area">
    <div class="input-group">
      <textarea
        v-model="newMessageContent"
        @keydown.enter.exact.prevent="sendMessage"
        placeholder="Écrivez un message"
        rows="1"
        class="form-control rounded-pill me-2 focus-ring"
        @input="adjustTextareaHeight"
      ></textarea>
      <button
        @click="sendMessage"
        :disabled="!newMessageContent.trim() || sendingMessage"
        class="btn btn-primary rounded-circle"
      >
        <i :class="{'fas': true, 'fa-paper-plane': !sendingMessage, 'fa-spinner fa-spin': sendingMessage}"></i>
      </button>
    </div>
    <div v-if="messageError" class="alert alert-danger text-center mt-2 p-2" role="alert">
      {{ messageError }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount, computed } from 'vue';
import axios from 'axios';
import moment from 'moment';
import 'moment/locale/fr';

moment.locale('fr');

const props = defineProps({
  userId: {
    type: [String, Number],
    required: false,
    default: null,
  },
  currentUser: {
    type: Object,
    required: true,
    default: () => ({ id: null, name: 'Vous', photo: null, is_online: false, role: null })
  },
  conversationsList: {
    type: Array,
    required: true,
    default: () => []
  }
});

const emit = defineEmits(['messages-read', 'conversation-updated']);

// --- Données réactives spécifiques à la conversation ---
const messages = ref([]);
const newMessageContent = ref('');
const loadingMessages = ref(false);
const sendingMessage = ref(false);
const messageError = ref(null);
const messagesContainer = ref(null);
const isOtherUserTyping = ref(false);
const onlineUsers = ref({}); // Maintenu pour la présence en ligne si vous utilisez encore Echo pour ça

// NOUVEAU: Une ref pour stocker les données du participant à afficher dans l'en-tête
const headerParticipantData = ref(null);

// Propriété calculée pour trouver le participant dans la liste des conversations existantes
const participantFromList = computed(() => {
  if (!props.userId || !props.conversationsList.length) {
    return null;
  }
  const conversation = props.conversationsList.find(conv => conv.participant.id == props.userId);
  return conversation ? conversation.participant : null;
});

// --- Polling Configuration ---
let pollingInterval = null;
const POLLING_INTERVAL_MS = 3000; // Poll toutes les 3 secondes (ajustez si besoin)

// Fonction pour démarrer le polling
const startPolling = () => {
  // Arrêter tout polling précédent pour éviter les duplications
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
  if (props.userId) { // Ne démarrer le polling que si une conversation est sélectionnée
    console.log(`Démarrage du polling pour les messages toutes les ${POLLING_INTERVAL_MS / 1000} secondes.`);
    pollingInterval = setInterval(async () => {
      await fetchConversationMessages(props.userId, true); // True pour indiquer que c'est un polling
    }, POLLING_INTERVAL_MS);
  }
};

// Fonction pour arrêter le polling
const stopPolling = () => {
  if (pollingInterval) {
    console.log("Arrêt du polling des messages.");
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

// --- Gestion des canaux Laravel Echo (si vous les conservez pour la présence en ligne) ---
const setupPresenceChannel = () => {
    if (window.Echo) {
        window.Echo.join('online-users')
            .here((users) => {
                console.log('Utilisateurs en ligne (HERE - Detail):', users);
                onlineUsers.value = {};
                users.forEach(user => {
                    onlineUsers.value[user.id] = user;
                });
                // Mettre à jour le statut en ligne de l'interlocuteur si il est dans la liste des connectés
                if (headerParticipantData.value) {
                    headerParticipantData.value.is_online = isUserOnline(headerParticipantData.value.id);
                }
            })
            .joining((user) => {
                console.log('Utilisateur rejoint (JOINING - Detail):', user.name);
                onlineUsers.value[user.id] = user;
                // Si l'utilisateur qui rejoint est notre interlocuteur actuel
                if (headerParticipantData.value && user.id === headerParticipantData.value.id) {
                    headerParticipantData.value.is_online = true;
                }
            })
            .leaving((user) => {
                console.log('Utilisateur quitte (LEAVING - Detail):', user.name);
                delete onlineUsers.value[user.id];
                // Si l'utilisateur qui quitte est notre interlocuteur actuel
                if (headerParticipantData.value && user.id === headerParticipantData.value.id) {
                    headerParticipantData.value.is_online = false;
                }
            })
            .error((error) => {
                console.error('Erreur du canal de présence (Detail):', error);
            });
    } else {
        console.warn("Laravel Echo n'est pas initialisé. La présence en ligne ne fonctionnera pas dans ConversationDetail.");
    }
};

const isUserOnline = (userId) => {
    return onlineUsers.value.hasOwnProperty(userId);
};


// --- Fonctions de récupération et d'envoi ---
// Ajout du paramètre `isPolling`
const fetchConversationMessages = async (userId, isPolling = false) => {
  if (!userId) {
    messages.value = [];
    headerParticipantData.value = null;
    loadingMessages.value = false;
    stopPolling(); // Arrête le polling si plus de userId
    return;
  }

  // Ne montrer le loader que pour le chargement initial ou le changement de conversation
  if (!isPolling) {
    loadingMessages.value = true;
    messageError.value = null;
    messages.value = []; // Effacer les messages avant de charger les nouveaux
  }

  // Tente d'abord de récupérer les infos du participant depuis la liste des conversations
  if (participantFromList.value) {
    headerParticipantData.value = participantFromList.value;
    // Mettez à jour le statut en ligne de l'interlocuteur même si c'est un appel de polling
    headerParticipantData.value.is_online = isUserOnline(participantFromList.value.id);
  } else if (!isPolling) { // Ne le faire que lors du chargement initial, pas à chaque polling
    try {
        const userResponse = await axios.get(`/api/users/${userId}/profile`);
        // Assurez-vous que la structure de headerParticipantData est cohérente
        headerParticipantData.value = {
          id: userResponse.data.id,
          name: userResponse.data.name,
          photo: userResponse.data.photo,
          is_online: isUserOnline(userResponse.data.id) // Utilise l'état en ligne de la présence
        };
    } catch (error) {
        console.error("Erreur lors de la récupération des infos du participant pour l'en-tête:", error);
        headerParticipantData.value = null;
    }
  }

  try {
    const response = await axios.get(`/api/messages/conversation/${userId}`);
    const newMessages = response.data; // Les nouveaux messages récupérés du serveur

    // Comparer les messages pour voir s'il y a des nouveaux ou des mises à jour (ex: lu)
    const hasNewMessages = newMessages.length > messages.value.length;
    const hasStatusChanged = newMessages.some(newMessage => {
        const oldMessage = messages.value.find(m => m.id === newMessage.id);
        return oldMessage && oldMessage.lu !== newMessage.lu;
    });

    messages.value = newMessages; // Met à jour tous les messages

    // Filtrer les messages non lus qui ont été envoyés PAR l'autre utilisateur
    const unreadMessages = messages.value.filter(msg => msg.expediteur_id === props.userId && !msg.lu);
    if (unreadMessages.length > 0) {
      await markMessagesAsRead(unreadMessages);
      emit('messages-read'); // Émet un événement pour que le parent mette à jour le nombre de messages non lus
    }
    // Émettez toujours 'conversation-updated' en cas de polling pour maintenir la liste à jour
    // (par ex., pour les badges de non lus ou l'ordre des conversations).
    emit('conversation-updated');

    // Faire défiler vers le bas uniquement si de nouveaux messages sont arrivés
    if (hasNewMessages || hasStatusChanged) {
        nextTick(() => scrollToBottom());
    }

  } catch (error) {
    console.error("Erreur lors de la récupération des messages:", error);
    messageError.value = "Impossible de charger les messages pour cette conversation.";
  } finally {
    if (!isPolling) { // Ne masquer le loader que si ce n'était pas un appel de polling
      loadingMessages.value = false;
    }
  }
};

const markMessagesAsRead = async (messagesToMark) => {
    try {
        await Promise.all(messagesToMark.map(msg => axios.put(`/api/messages/${msg.id}/mark-as-read`)));

        messagesToMark.forEach(msgToMark => {
          const index = messages.value.findIndex(m => m.id === msgToMark.id);
          if (index !== -1) {
            messages.value[index].lu = true;
          }
        });
    } catch (error) {
        console.error("Erreur lors du marquage des messages comme lus:", error);
    }
};

const sendMessage = async () => {
  if (!newMessageContent.value.trim() || !props.userId) {
    return;
  }

  sendingMessage.value = true;
  messageError.value = null;

  try {
    const response = await axios.post('/api/messages', {
      destinataire_id: props.userId,
      contenu: newMessageContent.value,
    });

    messages.value.push({
      ...response.data.data,
      expediteur: props.currentUser,
      destinataire: headerParticipantData.value,
    });

    newMessageContent.value = '';
    adjustTextareaHeight();

    emit('conversation-updated');

    nextTick(() => scrollToBottom());

  } catch (error) {
    console.error("Erreur lors de l'envoi du message:", error);
    messageError.value = "Impossible d'envoyer le message.";
    if (error.response?.data?.message) {
      messageError.value = error.response.data.message;
    }
  } finally {
    sendingMessage.value = false;
  }
};

// --- Fonctions utilitaires d'affichage ---
const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const formatDateFull = (date) => {
  if (!date) return '';
  return moment(date).format('HH:mm');
};

const formatDateSeparator = (date) => {
  if (!date) return '';
  const m = moment(date);
  if (m.isSame(moment(), 'day')) return 'Aujourd\'hui';
  if (m.isSame(moment().subtract(1, 'days'), 'day')) return 'Hier';
  return m.format('DD MMMM');
};

const shouldShowDateSeparator = (currentMessageDate, index) => {
  if (index === 0) return true;
  const prevMessageDate = messages.value[index - 1].created_at;
  return !moment(currentMessageDate).isSame(moment(prevMessageDate), 'day');
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


const adjustTextareaHeight = () => {
  const textarea = document.querySelector('.message-input-area textarea');
  if (textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
  }
};

// --- Watchers ---
watch(() => props.userId, async (newUserId) => {
  await fetchConversationMessages(newUserId); // Appel initial
  if (newUserId) {
    startPolling(); // Démarrer le polling si une conversation est sélectionnée
  } else {
    stopPolling(); // Arrêter le polling si aucune conversation n'est sélectionnée
  }
}, { immediate: true });

// --- Cycle de vie du composant ---
onMounted(() => {
  setupPresenceChannel(); // Si vous voulez garder la présence en ligne via Echo
  // Démarrer le polling si un userId est déjà présent au montage (ex: rechargement de page)
  if (props.userId) {
    startPolling();
  }
});

onBeforeUnmount(() => {
  stopPolling(); // Arrêter le polling lors du démontage
  if (window.Echo) { // Nettoyage de la présence Echo si utilisée
    window.Echo.leave('online-users');
  }
});
</script>

<style scoped>
/* Vos styles personnalisés avec Bootstrap pour ConversationDetail.vue */

/* Styles pour les bulles de message */
.messages-container {
  flex-grow: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.message-bubble {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  max-width: 75%; /* Limite la largeur des bulles de message */
  word-wrap: break-word; /* Casse les mots longs */
}

.message-received {
  background-color: #ffffff; /* Fond blanc pour les messages reçus */
  color: #343a40; /* Texte foncé */
  border: 1px solid #e9ecef; /* Petite bordure pour les distinguer */
  border-bottom-left-radius: 0.25rem; /* Coin "pointu" en bas à gauche */
}

.message-sent {
  background-color: #007bff; /* Bleu Bootstrap pour les messages envoyés */
  color: white;
  border-bottom-right-radius: 0.25rem; /* Coin "pointu" en bas à droite */
}

/* Animation de l'indicateur de saisie */
.typing-indicator .dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  margin: 0 1px;
  background-color: #6c757d; /* Couleur de point */
  border-radius: 50%;
  opacity: 0;
  animation: typing-dot 1.2s infinite ease-in-out;
}

.typing-indicator .dot:nth-child(1) { animation-delay: 0s; }
.typing-indicator .dot:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing-dot {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Ajustements pour le textarea (hauteur automatique) */
textarea.form-control {
  resize: none;
  overflow: hidden;
  min-height: 40px; /* Hauteur minimale */
  max-height: 150px; /* Hauteur maximale pour éviter un textarea trop grand */
  border-radius: 20px; /* Rendre le textarea rond */
  padding-top: 10px; /* Ajuste le padding pour le texte */
  padding-bottom: 10px;
}

/* Ajustements pour les boutons ronds */
.btn.rounded-circle {
  width: 40px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0; /* Supprime le padding par défaut pour centrer l'icône */
}
</style>