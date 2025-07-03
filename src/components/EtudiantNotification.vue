<template>
  <div class="container-fluid py-4">
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

    <div class="row justify-content-center">
      <div class="col-lg-10 col-md-12">
        <div class="card shadow-lg border-0 rounded-3">
          <div class="card-header bg-primary text-white py-3 rounded-top-3 d-flex justify-content-between align-items-center">
            <h2 class="mb-0 fs-4">Mes Notifications</h2>
          </div>
          <div class="card-body p-4">
            <p v-if="loading" class="alert alert-info text-center d-flex align-items-center justify-content-center">
              <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Chargement des notifications...
            </p>
            <p v-if="error" class="alert alert-danger text-center">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> Erreur : {{ error }}
            </p>

            <div v-if="!loading && !error">
              <div v-if="notifications.length > 0">
                <div v-for="notification in sortedNotifications" :key="notification.id"
                     :class="['notification-item', {'unread': !notification.is_read}, 'p-3 mb-3 border rounded']">
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start">
                    <div class="flex-grow-1 mb-2 mb-sm-0">
                      <h5 class="notification-title mb-1">{{ notification.type || 'Nouvelle notification' }}</h5>
                      <p class="notification-message mb-1">{{ notification.message || 'Pas de message.' }}</p>
                      <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>{{ formatDateTime(notification.created_at) }}
                      </small>
                      <div v-if="notification.donnees_sup" class="mt-2">
                        <small class="text-info">
                          <span v-if="notification.donnees_sup.offre_id">Offre ID: {{ notification.donnees_sup.offre_id }}</span>
                          <span v-if="notification.donnees_sup.candidature_id">Candidature ID: {{ notification.donnees_sup.candidature_id }}</span>
                          <span v-if="notification.donnees_sup.partenariat_id">Partenariat ID: {{ notification.donnees_sup.partenariat_id }}</span>
                           <span v-if="notification.donnees_sup.nouveau_statut">Nouveau statut: {{ notification.donnees_sup.nouveau_statut }}</span>
                          </small>
                      </div>
                    </div>
                    <div class="d-flex align-items-center flex-shrink-0">
                      <button v-if="!notification.is_read" @click="viewNotificationAndMarkAsRead(notification.id)"
                              class="btn btn-sm btn-primary me-2">
                        <i class="bi bi-eye me-1"></i>Marquer comme lu
                      </button>
                      <button @click="deleteNotification(notification.id)" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Supprimer
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <p v-else class="alert alert-warning text-center">
                <i class="bi bi-info-circle-fill me-2"></i> Vous n'avez aucune notification pour le moment.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios'; // Assurez-vous que le chemin vers axios est correct pour votre setup
import { Toast } from 'bootstrap'; // Importez Toast depuis bootstrap

// --- Données réactives ---
const notifications = ref([]);
const loading = ref(true);
const error = ref(null);
const toastMessage = ref('');
const toastType = ref('success');

// --- Propriétés calculées ---
const sortedNotifications = computed(() => {
  // Trie les notifications par date de création (les plus récentes en premier), puis par statut non lu
  return [...notifications.value].sort((a, b) => {
    // Les non lues avant les lues
    if (a.is_read === b.is_read) {
      // Si même statut, trier par date de création (plus récent en premier)
      return new Date(b.created_at) - new Date(a.created_at);
    }
    return a.is_read ? 1 : -1; // Les non lues (false) viennent avant les lues (true)
  });
});

const unreadNotificationsCount = computed(() => {
  return notifications.value.filter(n => !n.is_read).length;
});

// --- Fonctions utilitaires ---

/**
 * Affiche un toast de notification.
 * @param {string} message - Le message à afficher dans le toast.
 * @param {string} type - Le type de toast ('success', 'danger', 'info', 'warning').
 */
const displayToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const toast = new Toast(toastEl, { delay: 3000 });
    toast.show();
  }
};

/**
 * Formate une date et une heure pour l'affichage.
 * @param {string} dateTimeString - La chaîne de date/heure.
 * @returns {string} La date/heure formatée.
 */
const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateTimeString).toLocaleDateString('fr-FR', options);
};

// --- Fonctions d'API ---

/**
 * Récupère toutes les notifications de l'utilisateur actuel.
 * Gère la pagination renvoyée par le contrôleur Laravel.
 */
const fetchNotifications = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/notifications');
    // Le contrôleur NotificationController renvoie la pagination Laravel,
    // donc les notifications sont dans response.data.data
    notifications.value = response.data.data;
  } catch (err) {
    console.error('Erreur lors du chargement des notifications :', err.response ? err.response.data : err.message);
    error.value = err.response?.data?.message || 'Impossible de charger vos notifications.';
    displayToast('Échec du chargement des notifications.', 'danger');
  } finally {
    loading.value = false;
  }
};

/**
 * Affiche une notification et la marque comme lue via l'endpoint SHOW du backend.
 * @param {number} notificationId - L'ID de la notification à marquer.
 */
const viewNotificationAndMarkAsRead = async (notificationId) => {
  try {
    // L'appel à l'endpoint GET /notifications/{id} marque la notification comme lue selon votre contrôleur Laravel
    await axios.get(`/api/notifications/${notificationId}`);
    // Mettre à jour l'état local sans recharger toutes les notifications
    const index = notifications.value.findIndex(n => n.id === notificationId);
    if (index !== -1) {
      notifications.value[index].is_read = true; // Met à jour is_read localement
    }
    displayToast('Notification marquée comme lue.', 'success');
  } catch (err) {
    console.error('Erreur lors du marquage comme lu ou de la visualisation :', err.response ? err.response.data : err.message);
    displayToast('Échec du marquage de la notification.', 'danger');
  }
};

/**
 * Supprime une notification spécifique.
 * @param {number} notificationId - L'ID de la notification à supprimer.
 */
const deleteNotification = async (notificationId) => {
  if (confirm('Voulez-vous vraiment supprimer cette notification ?')) {
    try {
      await axios.delete(`/api/notifications/${notificationId}`);
      // Retirer la notification de la liste locale
      notifications.value = notifications.value.filter(n => n.id !== notificationId);
      displayToast('Notification supprimée avec succès.', 'success');
    } catch (err) {
      console.error('Erreur lors de la suppression de la notification :', err.response ? err.response.data : err.message);
      displayToast('Échec de la suppression de la notification.', 'danger');
    }
  }
};

// --- Cycle de vie du composant ---
onMounted(fetchNotifications);
</script>

<style scoped>
/* Conteneur principal et carte */
.container-fluid.py-4 {
  background-color: #f0f2f5;
  min-height: calc(100vh - 60px);
}

.card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08); /* Ombre plus douce */
}

.card-header {
  background-color: #0d6efd; /* primary-blue */
  border-bottom: none; /* Retirer la bordure */
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}

.card-header h2 {
  color: white;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  letter-spacing: 0.5px;
}

/* Styles pour les messages de chargement/erreur */
.alert {
  border-radius: 8px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Styles des éléments de notification individuels */
.notification-item {
  background-color: white;
  border-left: 5px solid transparent; /* Bordure par défaut */
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05); /* Ombre légère pour chaque item */
  padding: 1.25rem;
}

.notification-item.unread {
  background-color: #e9f2ff; /* Couleur plus claire pour les non lues */
  border-left-color: #0d6efd; /* Bordure bleue pour les non lues */
  font-weight: 500; /* Texte un peu plus gras pour non lu */
}

.notification-item:hover {
  box-shadow: 0 6px 16px rgba(0,0,0,0.12); /* Ombre plus prononcée au survol */
  transform: translateY(-3px); /* Léger déplacement vers le haut */
}

.notification-title {
  font-weight: 600;
  color: #343a40;
  font-size: 1.1rem;
}

.notification-message {
  color: #495057;
  line-height: 1.5;
}

.notification-item small {
  font-size: 0.85rem;
  color: #6c757d;
}

/* Boutons dans les notifications */
.notification-item .btn {
  font-size: 0.85rem;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  transition: all 0.2s ease-in-out;
}

.notification-item .btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
.notification-item .btn-primary:hover {
  background-color: #0a58ca;
  border-color: #0a58ca;
}

.notification-item .btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}
.notification-item .btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
  .notification-item .d-flex.flex-column.flex-sm-row {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  .notification-item .flex-grow-1 {
    width: 100%;
    margin-bottom: 0.75rem !important;
  }
  .notification-item .flex-shrink-0 {
    width: 100%;
    justify-content: flex-end; /* Aligner les boutons à droite si en colonne */
  }
  .notification-item .btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }
  .notification-item .btn:last-child {
    margin-bottom: 0;
  }
  .notification-item .me-2 {
    margin-right: 0 !important;
  }
}
</style>