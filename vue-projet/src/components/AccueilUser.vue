<template>
  <div>
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white shadow-sm border-b border-gray-200 z-50">
      <div class="container mx-auto px-5 flex justify-between items-center py-3">
        <router-link to="/" class="flex items-center gap-2 font-bold text-xl no-underline">
          <img src="/toge.png" alt="StageConnect Logo" class="h-8 w-8 object-contain">
          <span class="bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">StageConnect</span>
        </router-link>
        
        <!-- Desktop Menu -->
        <div class="hidden md:flex gap-8">
          <a href="#" class="text-gray-500 hover:text-blue-600 text-sm font-medium uppercase tracking-wider transition-colors no-underline">Accueil</a>
          <a href="#communautes" class="text-gray-500 hover:text-blue-600 text-sm font-medium uppercase tracking-wider transition-colors no-underline">Communautés</a>
          <a href="#apropos" class="text-gray-500 hover:text-blue-600 text-sm font-medium uppercase tracking-wider transition-colors no-underline">À propos</a>
        </div>

        <!-- Desktop Auth Buttons -->
        <div class="hidden md:flex flex-col sm:flex-row gap-3">
          <div class="d-flex align-items-center gap-2">
            <router-link :to="dashboardPath" class="btn btn-outline-primary d-flex align-items-center">
              <img 
                :src="displayedDashboardPhoto" 
                alt="User Logo"
                class="w-8 h-8 rounded-full object-cover me-2"
              />
              Vous
            </router-link>
            <button class="btn btn-outline-danger" @click.prevent="confirmSignOut" title="Déconnexion">
              <i class="fa-solid fa-right-from-bracket"></i>
            </button>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
          <button @click="toggleMobileMenu" class="text-gray-500 hover:text-blue-600 focus:outline-none">
            <i class="fas fa-bars text-xl" v-if="!isMobileMenuOpen"></i>
            <i class="fas fa-times text-xl" v-if="isMobileMenuOpen"></i>
          </button>
        </div>

        <!-- Mobile Menu -->
        <div v-if="isMobileMenuOpen" class="md:hidden absolute top-16 right-5 w-48 bg-white shadow-lg rounded-lg p-4 z-50">
          <div class="flex flex-col gap-4">
            <a href="#" class="text-gray-500 hover:text-blue-600 text-sm font-medium transition-colors no-underline" @click="toggleMobileMenu">Accueil</a>
            <a href="#communautes" class="text-gray-500 hover:text-blue-600 text-sm font-medium transition-colors no-underline" @click="toggleMobileMenu">Communautés</a>
            <a href="#apropos" class="text-gray-500 hover:text-blue-600 text-sm font-medium transition-colors no-underline" @click="toggleMobileMenu">À propos</a>
            <div class="d-flex align-items-center gap-2 mt-2">
              <router-link :to="dashboardPath" class="btn btn-outline-primary d-flex align-items-center w-full" @click="toggleMobileMenu">
                <img 
  :src="displayedDashboardPhoto" 
  alt="User Logo"
  class="w-8 h-8 rounded-full object-cover me-2"
/>
                Vous
              </router-link>
              <button class="btn btn-outline-danger w-full" @click.prevent="confirmSignOut" title="Déconnexion" @click="toggleMobileMenu">
                <i class="fa-solid fa-right-from-bracket"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-20">
      <div class="absolute inset-0 bg-gradient-to-br from-blue-50/90 to-white/90"></div>
      <div class="container mx-auto px-5 relative z-10 py-16 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="text-center md:text-left">
          <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-4 text-gray-900">
            Trouvez le stage <span class="bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">idéal</span> pour votre parcours
          </h1>
          <p class="text-lg text-gray-500 mb-8 max-w-lg mx-auto md:mx-0">
            La plateforme qui connecte les talents émergents avec les entreprises innovantes. Découvrez des opportunités adaptées à votre parcours académique.
          </p>
        </div>
        <div class="flex justify-center">
          <img src="/Job offers.gif" alt="Illustration d'offres de stage" class="w-full max-w-md rounded-xl shadow-xl">
        </div>
      </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-16 bg-white" id="communautes">
      <div class="container mx-auto px-5">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">
            Nos <span class="text-blue-600">Communautés</span>
          </h2>
          <p class="text-lg text-gray-500 max-w-2xl mx-auto mt-4">
            Une plateforme conçue pour répondre aux besoins de tous les acteurs du stage académique
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <!-- Étudiants Card -->
          <div class="relative overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-500 opacity-0 hover:opacity-5 transition-opacity"></div>
            <div class="p-6 relative">
              <div class="flex items-center mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600 text-xl mr-4">
                  <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Étudiants</h3>
              </div>
              
              <img src="/etudiants.jpg" alt="Étudiants" class="w-full h-48 rounded-lg object-cover mb-4">
              
              <ul class="space-y-3">
                <li class="flex">
                  <i class="fas fa-check-circle text-blue-600 mt-1 mr-2"></i>
                  <span class="text-gray-600">Trouvez des stages adaptés à votre profil académique</span>
                </li>
                <li class="flex">
                  <i class="fas fa-check-circle text-blue-600 mt-1 mr-2"></i>
                  <span class="text-gray-600">Gérez vos candidatures en temps réel</span>
                </li>
                <li class="flex">
                  <i class="fas fa-check-circle text-blue-600 mt-1 mr-2"></i>
                  <span class="text-gray-600">Recevez des notifications personnalisées</span>
                </li>
              </ul>
            </div>
          </div>
          
          <!-- Entreprises Card -->
          <div class="relative overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-400 opacity-0 hover:opacity-5 transition-opacity"></div>
            <div class="p-6 relative">
              <div class="flex items-center mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-50 text-green-500 text-xl mr-4">
                  <i class="fas fa-building"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Entreprises</h3>
              </div>
              
              <img src="/entreprises.jpg" alt="Entreprises" class="w-full h-48 rounded-lg object-cover mb-4">
              
              <ul class="space-y-3">
                <li class="flex">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                  <span class="text-gray-600">Publiez vos offres de stage facilement</span>
                </li>
                <li class="flex">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                  <span class="text-gray-600">Gérez les candidatures en un seul endroit</span>
                </li>
                <li class="flex">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                  <span class="text-gray-600">Découvrez les talents de demain</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- About Section -->
    <section class="py-16 bg-white" id="apropos">
      <div class="container mx-auto px-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">
              À propos de <span class="text-blue-600">StageConnect</span>
            </h2>
            <p class="text-lg text-gray-500 mt-3">
              Une plateforme innovante dédiée à la gestion des stages académiques, conçue pour simplifier le processus pour tous les acteurs.
            </p>
            
            <div class="mt-8 space-y-6">
              <div class="flex">
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 text-white text-lg mr-4 flex-shrink-0">
                  <i class="fas fa-bullseye"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Notre Mission</h3>
                  <p class="text-gray-600 mt-1">Briser les barrières entre le monde académique et professionnel en fournissant une plateforme intuitive et efficace.</p>
                </div>
              </div>
              
              <div class="flex">
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 text-white text-lg mr-4 flex-shrink-0">
                  <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Notre Vision</h3>
                  <p class="text-gray-600 mt-1">Devenir la référence en matière de gestion de stages académiques, en connectant talents et opportunités à l'échelle nationale.</p>
                </div>
              </div>
              
              <div class="flex">
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 text-white text-lg mr-4 flex-shrink-0">
                  <i class="fas fa-chart-line"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Nos Valeurs</h3>
                  <p class="text-gray-600 mt-1">Innovation, Accessibilité, Transparence et Professionnalisme sont au cœur de notre démarche.</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-gray-50 rounded-xl p-6 shadow-inner">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ce que nous offrons</h3>
            <ul class="space-y-4">
              <li class="flex">
                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs mr-3 mt-0.5 flex-shrink-0">
                  <i class="fas fa-check"></i>
                </div>
                <p class="text-gray-600">
                  <span class="font-medium">Pour les étudiants :</span> Un espace centralisé pour rechercher, postuler et suivre vos candidatures de stage.
                </p>
              </li>
              <li class="flex">
                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs mr-3 mt-0.5 flex-shrink-0">
                  <i class="fas fa-check"></i>
                </div>
                <p class="text-gray-600">
                  <span class="font-medium">Pour les entreprises :</span> Des outils puissants pour publier des offres, gérer les candidatures et trouver les meilleurs talents.
                </p>
              </li>
            </ul>
            
            <div class="mt-6">
              <img src="/Task-amico.png" alt="Illustration" class="rounded-lg w-50 h-50 ml-40">
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
      <div class="container mx-auto px-5 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 class="text-xl font-bold mb-4">StageConnect</h3>
            <p class="text-gray-400">
              La plateforme de référence pour la gestion des stages académiques, connectant étudiants et entreprises.
            </p>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4">Navigation</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-gray-400 hover:text-white transition-colors no-underline">Accueil</a></li>
              <li><a href="#communautes" class="text-gray-400 hover:text-white transition-colors no-underline">Communautés</a></li>
              <li><a href="#apropos" class="text-gray-400 hover:text-white transition-colors no-underline">À propos</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4">Légal</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-gray-400 hover:text-white transition-colors no-underline">Conditions d'utilisation</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white transition-colors no-underline">Politique de confidentialité</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white transition-colors no-underline">Mentions légales</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4">Contact</h3>
            <ul class="space-y-3 text-gray-400">
              <li class="flex items-start">
                <i class="fas fa-envelope mt-1 mr-3"></i>
                <a href="mailto:hamidbawasacca@gmail.com" class="no-underline">
                  hamidbawasacca@gmail.com
                </a>
              </li>
              <li class="flex items-start">
                <i class="fas fa-phone-alt mt-1 mr-3"></i>
                <a href="tel:+2290191863975" class="no-underline">
                  +229 0191863975
                </a>
              </li>
              <li class="flex items-start">
                <i class="fas fa-map-marker-alt mt-1 mr-3"></i>
                <span>Cotonou, Bénin</span>
              </li>
            </ul>
            
            <div class="flex gap-4 mt-4">
              <a href="https://wa.me/2290191863975" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-blue-600 transition-colors" target="_blank">
                <i class="fab fa-whatsapp text-sm"></i>
              </a>
              <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-blue-600 transition-colors">
                <i class="fab fa-facebook-f text-sm"></i>
              </a>
              <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-blue-700 transition-colors">
                <i class="fab fa-linkedin-in text-sm"></i>
              </a>
            </div>
          </div>
        </div>
        
        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
          <p>&copy; {{ new Date().getFullYear() }} StageConnect. Tous droits réservés.</p>
        </div>
      </div>
    </footer>
    
    <!-- Toast Notification -->
    <div class="fixed bottom-0 right-0 p-4 z-50">
      <div id="liveToast" class="hidden bg-white rounded-lg shadow-lg overflow-hidden w-80" role="alert">
        <div class="flex justify-between items-center px-4 py-2 border-b">
          <strong class="font-semibold" :class="{'text-green-600': !error, 'text-red-600': error}">
            {{ error ? 'Erreur' : 'Succès' }}
          </strong>
          <button type="button" class="text-gray-400 hover:text-gray-500" @click="hideToast">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="px-4 py-3 text-sm">
          {{ toastMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, computed, onMounted } from 'vue';
import axios from '../axios';
import { Toast } from 'bootstrap';

const router = useRouter();

// --- Variables d'état ---
const userPhoto = ref('');
const userRole = ref('');
const dashboardPath = ref('/'); // Nouvelle ref pour stocker le chemin du tableau de bord
const defaultDashboardPhoto = '/photo-default.jpeg';
const toastMessage = ref('');
const error = ref(false);
const isMobileMenuOpen = ref(false);

// --- Propriétés calculées ---
const displayedDashboardPhoto = computed(() => {
  const url = userPhoto.value || defaultDashboardPhoto;
  return url.includes('?') ? url : `${url}?t=${Date.now()}`;
});

// --- Fonctions ---
const showToast = (message, isError) => {
  toastMessage.value = message;
  error.value = isError;
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const bsToast = new Toast(toastEl);
    bsToast.show();
  }
};

const hideToast = () => {
  const toastEl = document.getElementById('liveToast');
  if (toastEl) {
    const bsToast = Toast.getInstance(toastEl);
    if (bsToast) bsToast.hide();
  }
};

const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/user/getProfile');
    const user = response.data;

    if (user) {
      if (user.photo) {
        userPhoto.value = user.photo;
      } else {
        userPhoto.value = defaultDashboardPhoto;
      }

      if (user.role) {
        userRole.value = user.role;
        switch (user.role) {
          case 'etudiant':
            dashboardPath.value = '/etudiant-dashboard';
            break;
          case 'entreprise':
            dashboardPath.value = '/entreprise-dashboard';
            break;
          case 'etablissement':
            dashboardPath.value = '/etablissement-dashboard';
            break;
          default:
            dashboardPath.value = '/';
        }
      } else {
        console.warn("Le rôle de l'utilisateur n'a pas été trouvé dans le profil.");
        userRole.value = '';
        dashboardPath.value = '/';
      }
    } else {
      userPhoto.value = defaultDashboardPhoto;
      userRole.value = '';
      dashboardPath.value = '/';
    }
  } catch (err) {
    console.error('Erreur lors du chargement du profil utilisateur :', err);
    userPhoto.value = defaultDashboardPhoto;
    userRole.value = '';
    dashboardPath.value = '/';
  }
};

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
  showToast('Vous avez été déconnecté avec succès.', false);
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// --- Cycle de vie du composant ---
onMounted(() => {
  fetchUserProfile();
});
</script>