<template>
  <div>
    <nav class="navbar">
      <div class="navbar-brand">
        <a class="navbar-brand text-primary fw-bold" href="#">
          <img src="/toge.png" alt="StageConnect Logo" />
          StageConnect
        </a>
      </div>

      <!-- Hamburger Menu for Mobile -->
      <button class="hamburger" @click="toggleSidebar" aria-label="Toggle sidebar">
        <i class="fas fa-bars" v-if="!isSidebarOpen"></i>
        <i class="fas fa-times" v-if="isSidebarOpen"></i>
      </button>

      <div class="navbar-links">
        <router-link to="/admin-dashboard" class="nav-link">
          <i class="fas fa-user-shield icon-spacing-navbar"></i> Admin
        </router-link>
      </div>
    </nav>

    <aside class="sidebar" :class="{ 'sidebar-open': isSidebarOpen }">
      <ul class="sidebar-menu">
        <router-link to="/gest-users" custom v-slot="{ href, navigate, isActive }">
          <li :class="{ 'active-button': isActive }">
            <a :href="href" @click="navigate"><i class="fas fa-users icon-spacing-sidebar"></i>Gestion des utilisateurs</a>
          </li>
        </router-link>
        <router-link to="/gest-etablissements" custom v-slot="{ href, navigate, isActive }">
          <li :class="{ 'active-button': isActive }">
            <a :href="href" @click="navigate"><i class="fas fa-school icon-spacing-sidebar"></i>Gestion des établissements</a>
          </li>
        </router-link>
        <router-link to="/gest-filieres" custom v-slot="{ href, navigate, isActive }">
          <li :class="{ 'active-button': isActive }">
            <a :href="href" @click="navigate"><i class="fas fa-book icon-spacing-sidebar"></i>Gestion des filières</a>
          </li>
        </router-link>
        <li>
          <button class="btn btn-outline-danger sidebar-signout" @click.prevent="confirmSignOut" title="Déconnexion">
            <i class="fa-solid fa-right-from-bracket icon-spacing-sidebar"></i>Déconnexion
          </button>
        </li>
      </ul>
    </aside>

    <main class="main-content" :class="{ 'main-content-full': !isSidebarOpen }">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from '../axios';

const router = useRouter();
const isSidebarOpen = ref(false);

// Function to toggle the sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Function to confirm sign-out
const confirmSignOut = () => {
  if (window.confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
    signOut();
  }
};

// Function to handle sign-out
const signOut = () => {
  console.log('Déconnexion en cours...');
  localStorage.removeItem('authToken');
  sessionStorage.removeItem('userData');
  router.push('/');
};

// Close sidebar when resizing to larger screens
const handleResize = () => {
  if (window.innerWidth > 768) {
    isSidebarOpen.value = true; // Sidebar visible by default on larger screens
  } else {
    isSidebarOpen.value = false; // Sidebar hidden by default on smaller screens
  }
};

onMounted(() => {
  handleResize(); // Set initial sidebar state based on screen size
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Navbar fixe */
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

/* Hamburger Menu */
.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #333;
  cursor: pointer;
  padding: 8px;
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

/* Sidebar */
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
  transition: transform 0.3s ease;
}

.sidebar-open {
  transform: translateX(0);
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar-open {
    transform: translateX(0);
  }
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

/* Sign-out button in sidebar */
.sidebar-signout {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px 15px;
  border-radius: 8px;
  font-weight: 500;
  color: var(--bs-danger);
  border-color: var(--bs-danger);
  background: none;
  text-align: left;
}

.sidebar-signout:hover {
  background-color: var(--bs-danger);
  color: #fff;
  border-color: var(--bs-danger);
}

/* Main Content */
.main-content {
  margin-left: 250px;
  margin-top: 70px;
  padding: 0;
  min-height: calc(100vh - 70px);
  background-color: #f8f9fa;
  overflow-x: auto;
  overflow-y: auto;
  transition: margin-left 0.3s ease;
}

.main-content-full {
  margin-left: 0;
}

.main-content > * {
  padding: 20px;
  width: 100%;
  box-sizing: border-box;
}

/* Icons */
.icon-spacing-navbar {
  margin-right: 5px;
}

.icon-spacing-sidebar {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

/* Notification Badge */
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

/* Responsive Adjustments */
@media (max-width: 992px) {
  .sidebar {
    width: 200px;
  }
  .main-content {
    margin-left: 200px;
  }
  .main-content-full {
    margin-left: 0;
  }
}

@media (max-width: 768px) {
  .hamburger {
    display: block;
  }
  .navbar-links {
    gap: 10px;
  }
  .nav-link {
    padding: 6px 12px;
    font-size: 14px;
  }
  .sidebar-signout {
    padding: 10px 15px;
    font-size: 14px;
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
  .navbar-links {
    gap: 5px;
  }
  .nav-link {
    padding: 4px 8px;
    font-size: 12px;
  }
  .sidebar {
    width: 100%;
    max-width: 250px;
  }
  .sidebar-signout {
    padding: 10px 15px;
    font-size: 12px;
  }
}
</style>