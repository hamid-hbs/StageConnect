<template>
  <div>
    <div v-if="userIsActive">
      <nav class="navbar fixed-top">
        <div class="container-fluid">
          <div class="navbar-brand">
            <a class="navbar-brand text-primary fw-bold" href="#">
              <img src="/toge.png" alt="StageConnect Logo" />
              StageConnect
            </a>
          </div>
          <div class="d-flex align-items-center">
            <div class="navbar-links d-none d-sm-flex">
              <router-link to="/accueil-user" class="nav-link">
                <i class="fas fa-home icon-spacing-navbar"></i> Accueil
              </router-link>
              <router-link to="/entreprise-dashboard" class="nav-link">
                <img :src="displayedDashboardPhoto" class="user-logo"/> Vous
              </router-link>
              <button class="btn btn-outline-danger" @click.prevent="confirmSignOut" title="Déconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
              </button>
            </div>
            <button class="btn btn-outline-primary d-sm-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
              <i class="fas fa-bars"></i>
            </button>
          </div>
        </div>
      </nav>

      <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title fw-bold" id="sidebarOffcanvasLabel">StageConnect</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="sidebar-menu list-unstyled">
            <li class="mb-2">
              <router-link to="/accueil-user" class="nav-link d-block" @click="closeOffcanvas">
                <i class="fas fa-home icon-spacing-sidebar"></i> Accueil
              </router-link>
            </li>
            <li class="mb-2">
              <router-link to="/entreprise-dashboard" class="nav-link d-block" @click="closeOffcanvas">
                <img :src="displayedDashboardPhoto" class="user-logo me-2"/> Vous
              </router-link>
            </li>
            <router-link to="/entreprise-profile" custom v-slot="{ href, navigate, isActive }">
              <li :class="{ 'active-button': isActive }" class="mb-2">
                <a :href="href" @click="navigate; closeOffcanvas()" class="nav-link d-block">
                  <i class="fas fa-building icon-spacing-sidebar"></i> Entreprise
                </a>
              </li>
            </router-link>
            <router-link to="/entreprise-offre" custom v-slot="{ href, navigate, isActive }">
              <li :class="{ 'active-button': isActive }" class="mb-2">
                <a :href="href" @click="navigate; closeOffcanvas()" class="nav-link d-block">
                  <i class="fas fa-briefcase icon-spacing-sidebar"></i> Nos offres de stage
                </a>
              </li>
            </router-link>
            <router-link to="/entreprise-candidature" custom v-slot="{ href, navigate, isActive }">
              <li :class="{ 'active-button': isActive }" class="mb-2">
                <a :href="href" @click="navigate; closeOffcanvas()" class="nav-link d-block">
                  <i class="fas fa-file-alt icon-spacing-sidebar"></i> Gestion candidatures
                </a>
              </li>
            </router-link>
            <router-link :to="{ name: 'entreprise-messagerie' }" custom v-slot="{ href, navigate, isActive }">
              <li :class="{ 'active-button': isActive }" class="mb-2">
                <a :href="href" @click="navigate; closeOffcanvas()" class="nav-link d-block">
                  <i class="fas fa-comments icon-spacing-sidebar"></i> Messagerie
                </a>
              </li>
            </router-link>
            <router-link to="/entreprise-notification" custom v-slot="{ href, navigate, isActive }">
              <li :class="{ 'active-button': isActive }" class="notification-item-sidebar mb-2">
                <a :href="href" @click="navigate; closeOffcanvas()" class="nav-link d-block">
                  <i class="fas fa-bell icon-spacing-sidebar"></i> Notifications
                  <span v-if="unreadNotificationsCount > 0" class="badge bg-danger rounded-pill notification-badge">
                    {{ unreadNotificationsCount }}
                  </span>
                </a>
              </li>
            </router-link>
            <li class="mb-2">
              <button class="nav-link d-block text-danger" @click="confirmSignOut">
                <i class="fa-solid fa-right-from-bracket icon-spacing-sidebar"></i> Déconnexion
              </button>
            </li>
          </ul>
        </div>
      </div>

      <aside class="sidebar d-none d-sm-block">
        <ul class="sidebar-menu">
          <router-link to="/entreprise-profile" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-building icon-spacing-sidebar"></i>Entreprise</a>
            </li>
          </router-link>
          <router-link to="/entreprise-offre" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-briefcase icon-spacing-sidebar"></i>Nos offres de stage</a>
            </li>
          </router-link>
          <router-link to="/entreprise-candidature" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-file-alt icon-spacing-sidebar"></i>Gestion candidatures</a>
            </li>
          </router-link>
          <router-link :to="{ name: 'entreprise-messagerie' }" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }">
              <a :href="href" @click="navigate"><i class="fas fa-comments icon-spacing-sidebar"></i>Messagerie</a>
            </li>
          </router-link>
          <router-link to="/entreprise-notification" custom v-slot="{ href, navigate, isActive }">
            <li :class="{ 'active-button': isActive }" class="notification-item-sidebar">
              <a :href="href" @click="navigate">
                <i class="fas fa-bell icon-spacing-sidebar"></i>Notifications
                <span v-if="unreadNotificationsCount > 0" class="badge bg-danger rounded-pill notification-badge">
                  {{ unreadNotificationsCount }}
                </span>
              </a>
            </li>
          </router-link>
        </ul>
      </aside>

      <main class="main-content">
        <router-view />
      </main>
    </div>
    
    <div v-else class="min-h-screen d-flex align-items-center justify-content-center bg-gray-50">
      <div class="bg-red-50 p-4 rounded-lg shadow-md max-w-md w-100 mx-3 border border-red-100 text-center">
        <div class="text-red-500 mb-3">
          <i class="fas fa-user-lock fs-3"></i>
        </div>
        <h2 class="fs-5 fw-bold text-gray-800 mb-3">Votre compte est désactivé</h2>
        <div class="text-gray-600 mb-4">
          <p class="mb-3">Pour plus d'informations, veuillez contacter l'administrateur :</p>
          <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center justify-content-center">
              <i class="fas fa-phone-alt me-2 text-blue-500"></i>
              <a href="tel:0191863975" class="text-decoration-none"><span class="fw-medium">0191863975</span></a>
            </div>
            <div class="d-flex align-items-center justify-content-center">
              <i class="fas fa-envelope me-2 text-blue-500"></i>
              <a href="mailto:hamidbawasacca@gmail.com" class="text-decoration-none"><span class="fw-medium">hamidbawasacca@gmail.com</span></a>
            </div>
          </div>
        </div>
        <button 
          class="btn btn-primary w-100"
          @click="signOut"
        >
          Se déconnecter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from '../axios';

const router = useRouter();

const photo = ref('');
const isLoadingDashboard = ref(true);
const unreadNotificationsCount = ref(0);
let refreshIntervalId = null;
const userIsActive = ref(true);

const displayedDashboardPhoto = computed(() => {
  const url = photo.value;
  const defaultPhoto = '/images/default-user.png';
  const finalUrl = url || defaultPhoto;
  return finalUrl.includes('?') ? finalUrl : `${finalUrl}?t=${Date.now()}`;
});

const confirmSignOut = () => {
  if (window.confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    signOut();
  }
};

const signOut = () => {
  console.log('Déconnexion en cours...');
  localStorage.removeItem('authToken');
  sessionStorage.removeItem('userData');
  router.push('/');
};

const fetchUnreadNotificationsCount = async () => {
  try {
    const response = await axios.get('/api/notifications/unread/count');
    unreadNotificationsCount.value = response.data.count;
    console.log('Notifications non lues mises à jour (Entreprise):', unreadNotificationsCount.value);
  } catch (error) {
    console.error('Erreur lors du chargement du nombre de notifications non lues (Entreprise):', error.response ? error.response.data : error.message);
    unreadNotificationsCount.value = 0;
  }
};

const fetchProfileData = async () => {
  try {
    const userResponse = await axios.get('/api/user/getProfile');
    const user = userResponse.data;
    if (user) {
      photo.value = user.photo;
      userIsActive.value = user.is_active == 1;
      console.log('Photo de profil mise à jour (Entreprise):', photo.value);
      console.log('Statut is_active (Entreprise):', user.is_active);
    }
  } catch (error) {
    console.error('Erreur lors du chargement du profil utilisateur (Entreprise):', error.response ? error.response.data : error.message);
    photo.value = '';
    userIsActive.value = false;
  }
};

const closeOffcanvas = () => {
  const offcanvasElement = document.getElementById('sidebarOffcanvas');
  const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement) || new bootstrap.Offcanvas(offcanvasElement);
  offcanvas.hide();
};

onMounted(async () => {
  isLoadingDashboard.value = true;
  await fetchProfileData();
  if (userIsActive.value) {
    await fetchUnreadNotificationsCount();
  }
  isLoadingDashboard.value = false;

  refreshIntervalId = setInterval(async () => {
    await fetchProfileData();
    if (userIsActive.value) {
      await fetchUnreadNotificationsCount();
    }
  }, 3000);
});

onUnmounted(() => {
  if (refreshIntervalId) {
    clearInterval(refreshIntervalId);
    console.log('Intervalle de rafraîchissement nettoyé (Entreprise).');
  }
});
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 70px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  display: flex;
  align-items: center;
  text-decoration: none;
}

.navbar-brand img {
  height: 40px;
  margin-right: 10px;
}

.navbar-links {
  display: flex;
  gap: 20px;
  align-items: center;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  color: #333;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-link:hover {
  background-color: #f0f0f0;
  color: #007bff;
}

.nav-link.router-link-active {
  background-color: #007bff;
  color: white;
}

.btn-outline-danger {
  color: var(--bs-danger);
  border-color: var(--bs-danger);
}

.btn-outline-danger:hover {
  color: #fff;
  background-color: var(--bs-danger);
  border-color: var(--bs-danger);
}

.sidebar {
  position: fixed;
  left: 0;
  top: 70px;
  width: 250px;
  height: calc(100vh - 70px);
  background-color: #ffffff;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
  z-index: 999;
  overflow-y: auto;
  padding: 20px;
}

.sidebar-menu {
  list-style: none;
}

.sidebar-menu li {
  margin-bottom: 10px;
}

.sidebar-menu a {
  display: block;
  padding: 12px 15px;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.sidebar-menu a:hover {
  background-color: #f0f0f0;
}

.sidebar-menu li.active-button a {
  background-color: #007bff;
  color: white;
  font-weight: bold;
}

.icon-spacing-sidebar {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.notification-item-sidebar {
  position: relative;
}

.notification-badge {
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  font-size: 0.75em;
  padding: 0.2em 0.5em;
  line-height: 1;
  border-radius: 999px;
  background-color: #dc3545;
  color: white;
  min-width: 20px;
  text-align: center;
}

.sidebar-menu li.notification-item-sidebar a {
  padding-right: 40px;
}

.main-content {
  margin-left: 250px;
  margin-top: 70px;
  padding: 20px;
  min-height: calc(100vh - 70px);
  background-color: #f8f9fa;
  overflow-x: auto;
  overflow-y: auto;
}

.user-logo {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #f8f9fa;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 4px;
  margin-right: 5px;
  object-fit: cover;
}

.icon-spacing-navbar {
  margin-right: 5px;
}

.offcanvas {
  width: 280px !important;
  background-color: #ffffff;
  box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
}

.offcanvas-header {
  padding: 15px 20px;
  border-bottom: 1px solid #dee2e6;
}

.offcanvas-body {
  padding: 20px;
}

.offcanvas-body .nav-link {
  display: block;
  padding: 12px 15px;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.offcanvas-body .nav-link:hover {
  background-color: #f0f0f0;
}

.offcanvas-body .active-button .nav-link {
  background-color: #007bff;
  color: white;
  font-weight: bold;
}

.offcanvas-body .notification-item-sidebar .nav-link {
  padding-right: 40px;
}

@media (max-width: 768px) {
  .navbar-links {
    gap: 10px;
  }

  .nav-link {
    padding: 6px 12px;
    font-size: 14px;
  }

  .sidebar {
    width: 200px;
  }

  .main-content {
    margin-left: 200px;
  }
}

@media (max-width: 576px) {
  .navbar {
    padding: 0 10px;
  }

  .navbar-brand {
    font-size: 1.2rem;
  }

  .navbar-brand img {
    height: 30px;
  }

  .main-content {
    margin-left: 0;
    padding: 10px;
  }

  .offcanvas-body .nav-link {
    font-size: 0.9rem;
    padding: 10px 12px;
  }

  .offcanvas-body .user-logo {
    width: 24px;
    height: 24px;
  }

  .offcanvas-body .notification-badge {
    font-size: 0.7em;
    padding: 0.15em 0.4em;
  }
}
</style>