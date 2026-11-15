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
          <a href="#offres-stages" class="text-gray-500 hover:text-blue-600 text-sm font-medium uppercase tracking-wider transition-colors no-underline">Offres</a>
          <a href="#apropos" class="text-gray-500 hover:text-blue-600 text-sm font-medium uppercase tracking-wider transition-colors no-underline">À propos</a>
        </div>

        <!-- Desktop Auth Buttons -->
        <div class="hidden md:flex flex-col sm:flex-row gap-3">
          <router-link to="/inscription" class="no-underline">
            <button 
              class="rounded-md px-3 py-1 flex items-center justify-center gap-2 font-medium text-white 
              bg-gradient-to-r from-green-700 via-green-800 to-green-900 
              hover:from-green-800 hover:via-green-800 hover:to-green-700 
              transition-colors disabled:opacity-50 w-40"
            >
              <span>S'inscrire</span>
            </button>
          </router-link>
          <router-link to="/connexion" class="no-underline">
            <button
              class="rounded-md px-3 py-1 flex items-center justify-center gap-2 font-medium text-white 
              bg-gradient-to-r from-blue-700 via-blue-800 to-blue-900 
              hover:from-blue-800 hover:via-blue-800 hover:to-blue-700 
              transition-colors disabled:opacity-50 w-40"
            >
              <span>Se connecter</span>
            </button>
          </router-link>
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
            <a href="#offres-stages" class="text-gray-500 hover:text-blue-600 text-sm font-medium transition-colors no-underline" @click="toggleMobileMenu">Offres</a>
            <a href="#apropos" class="text-gray-500 hover:text-blue-600 text-sm font-medium transition-colors no-underline" @click="toggleMobileMenu">À propos</a>
            <router-link to="/inscription" class="no-underline" @click="toggleMobileMenu">
              <button 
                class="rounded-md px-3 py-1 flex items-center justify-center gap-2 font-medium text-white 
                bg-gradient-to-r from-green-700 via-green-800 to-green-900 
                hover:from-green-800 hover:via-green-800 hover:to-green-700 
                transition-colors disabled:opacity-50 w-40"
              >
                <span>S'inscrire</span>
              </button>
            </router-link>
            <router-link to="/connexion" class="no-underline" @click="toggleMobileMenu">
              <button
                class="rounded-md px-3 py-1 flex items-center justify-center gap-2 font-medium text-white 
                bg-gradient-to-r from-blue-700 via-blue-800 to-blue-900 
                hover:from-blue-800 hover:via-blue-800 hover:to-blue-700 
                transition-colors disabled:opacity-50 w-40"
              >
                <span>Se connecter</span>
              </button>
            </router-link>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
      <div id="liveToastCommencer" class="toast text-bg-primary" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ toastMessageCommencer }}
        </div>
      </div>
    </div>

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
          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="showAuthRequiredToast"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-medium rounded-md shadow-sm hover:from-blue-700 hover:to-blue-600 transition-all text-center no-underline flex items-center justify-center"
            >
              Commencer maintenant
            </button>
            <a 
              href="#offres-stages" 
              class="px-6 py-3 border border-gray-200 text-gray-800 font-medium rounded-md hover:bg-gray-50 transition-colors text-center no-underline flex items-center justify-center"
            >
              Voir les offres
            </a>
          </div>
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
    
    <!-- Offers Section -->
    <section class="py-16 bg-gray-50" id="offres-stages">
      <div class="container mx-auto px-5">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">
            Dernières <span class="text-blue-600">Offres</span> de stage
          </h2>
          <p class="text-lg text-gray-500 max-w-2xl mx-auto mt-4">
            Trouvez l'opportunité qui correspond à vos compétences et aspirations
          </p>
        </div>
        
        <!-- Updated Filtering Bar -->
        <div class="flex flex-col md:flex-row justify-between gap-4 mb-8 filter-container">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-500 text-lg"></i>
            <input 
              type="text" 
              placeholder="Rechercher par titre, entreprise ou domaine..." 
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg text-sm bg-white shadow-md focus:border-blue-600 focus:ring-2 focus:ring-blue-200 transition-all duration-300 placeholder-gray-400"
              v-model="searchQuery"
              @input="applyFilters"
            >
          </div>
          <div class="relative flex-1">
            <i class="fas fa-filter absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-500 text-lg"></i>
            <select 
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg text-sm bg-white shadow-md focus:border-blue-600 focus:ring-2 focus:ring-blue-200 transition-all duration-300 text-gray-700"
              v-model="selectedDomaineId"
              @change="applyFilters"
            >
              <option value="">Filtrer par domaine</option>
              <option 
                v-for="domaine in domainesList" 
                :key="domaine.id" 
                :value="domaine.id"
              >
                {{ domaine.libdomaine }}
              </option>
            </select>
          </div>
        </div>
        
        <div v-if="isLoadingOffers" class="flex justify-center items-center p-12">
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
          <p class="ml-3 text-gray-500">Chargement des offres...</p>
        </div>
        
        <div v-else-if="errorMessageOffers" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded text-center">
          {{ errorMessageOffers }}
        </div>
        
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Offer Cards -->
          <div 
            v-for="offer in paginatedOffers" 
            :key="offer.id" 
            class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all hover:-translate-y-1"
          >
            <div class="p-6">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-3">
                {{ offer.domaine.libdomaine }}
              </span>
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ offer.titre }}</h3>
              <p class="flex items-center text-gray-500 text-sm mb-4">
                <i class="fas fa-building mr-2 text-gray-300"></i>
                <span>{{ offer.entreprise.nom_entreprise }}</span>
              </p>
              <p 
                class="text-gray-600 mb-4"
                :class="{ 'line-clamp-3': !offer.showFullDescription }"
              >
                {{ offer.description }}
              </p>
              <a 
                href="#" 
                class="text-blue-600 text-sm font-medium hover:underline"
                @click.prevent="toggleDescription(offer)"
              >
                {{ offer.showFullDescription ? 'Réduire' : 'Lire la suite' }}
              </a>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
              <router-link 
                :to="{ name: 'offre-off', params: { id: offer.id } }"
                class="block w-full px-4 py-2 bg-blue-600 text-white text-center font-medium rounded-md hover:bg-blue-700 transition-colors no-underline"
              >
                Voir les détails
              </router-link>
            </div>
          </div>
        </div>
        
        <div v-if="filteredOffers.length === 0 && !isLoadingOffers" class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded text-center">
          Aucune offre de stage trouvée pour le moment ou ne correspondant à vos critères de recherche.
        </div>
        
        <!-- Pagination Controls -->
        <div v-if="filteredOffers.length > 0 && !isLoadingOffers" class="mt-8 flex justify-center items-center gap-4">
          <button 
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            Précédent
          </button>
          <span class="text-gray-600">
            Page {{ currentPage }} de {{ totalPages }}
          </span>
          <button 
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            Suivant
          </button>
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
              <li><a href="#offres-stages" class="text-gray-400 hover:text-white transition-colors no-underline">Offres</a></li>
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
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { signInWithPopup } from 'firebase/auth';
import { auth, googleAuthProvider } from '../firebase';
import axios from '../axios'; 
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

export default {
  name: 'Home',
  setup() {
    const router = useRouter();
    const offers = ref([]);
    const domainesList = ref([]);
    const searchQuery = ref('');
    const selectedDomaineId = ref(null);
    const isLoadingOffers = ref(true);
    const errorMessageOffers = ref('');
    const toastMessageCommencer = ref('');
    const toastTypeCommencer = ref('success');
    const isMobileMenuOpen = ref(false);
    const currentPage = ref(1);
    const offersPerPage = 3;

    const filteredOffers = computed(() => {
      let filtered = offers.value;

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(offer =>
          offer.titre.toLowerCase().includes(query) ||
          offer.description.toLowerCase().includes(query) ||
          offer.entreprise.nom_entreprise.toLowerCase().includes(query)
        );
      }

      if (selectedDomaineId.value) {
        filtered = filtered.filter(offer => offer.domaine_id === selectedDomaineId.value);
      }
      return filtered;
    });

    const totalPages = computed(() => {
      return Math.ceil(filteredOffers.value.length / offersPerPage);
    });

    const paginatedOffers = computed(() => {
      const start = (currentPage.value - 1) * offersPerPage;
      const end = start + offersPerPage;
      return filteredOffers.value.slice(start, end);
    });

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
      }
    };

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
      }
    };

    const showToast = (message, isError) => {
      toastMessageCommencer.value = message;
      toastTypeCommencer.value = isError ? 'danger' : 'success';
      const toastEl = document.getElementById('liveToastCommencer');
      if (toastEl) {
        // Dispose of any existing toast instance to avoid conflicts
        const existingToast = Toast.getInstance(toastEl);
        if (existingToast) {
          existingToast.dispose();
        }
        // Create a new toast instance with autohide enabled
        const toast = new Toast(toastEl, {
          autohide: true,
          delay: 3000
        });
        toast.show();
      }
    };

    const showAuthRequiredToast = () => {
      showToast("Veuillez vous authentifier pour commencer.", true);
    };

    const applyFilters = () => {
      currentPage.value = 1; // Reset to first page when filters change
    };

    const toggleDescription = (offer) => {
      offer.showFullDescription = !offer.showFullDescription;
    };

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value;
    };

    onMounted(async () => {
      try {
        isLoadingOffers.value = true;
        errorMessageOffers.value = '';

        const offersResponse = await axios.get('/api/offres-off');
        offers.value = offersResponse.data.map(offer => ({
          ...offer,
          showFullDescription: false
        }));

        const domainesResponse = await axios.get('/api/domaines');
        domainesList.value = domainesResponse.data;

      } catch (err) {
        console.error('Erreur lors du chargement des données des offres :', err);
        if (err.response && err.response.status === 403) {
          errorMessageOffers.value = 'Vous n\'êtes pas autorisé à voir ces offres ou votre profil est incomplet.';
        } else {
          errorMessageOffers.value = 'Une erreur est survenue lors du chargement des offres de stage.';
        }
        showToast('Échec du chargement des offres.', true);
      } finally {
        isLoadingOffers.value = false;
      }
    });

    return {
      router,
      offers,
      domainesList,
      searchQuery,
      selectedDomaineId,
      isLoadingOffers,
      errorMessageOffers,
      toastMessageCommencer,
      toastTypeCommencer,
      isMobileMenuOpen,
      filteredOffers,
      paginatedOffers,
      currentPage,
      totalPages,
      prevPage,
      nextPage,
      applyFilters,
      toggleDescription,
      showToast,
      showAuthRequiredToast,
      toggleMobileMenu
    };
  },
  data() {
    return {
      isLoading: false
    };
  },
  computed: {
    isAuthenticated() {
      return localStorage.getItem('authToken') !== null;
    }
  },
  methods: {
    async handleAuthClick() {
      this.isLoading = true;
      try {
        const result = await signInWithPopup(auth, googleAuthProvider);
        const user = result.user;
        const idToken = await user.getIdToken();
        
        let formData = new FormData();
        formData.append('id_token', idToken);
        
        axios.defaults.withCredentials = true;
        const response = await axios.post('/api/auth/google/firebase-callback', formData);
        const { token, user: userData, next_step } = response.data;

        if (token) {
          localStorage.setItem('authToken', token);
        }
        if (userData) {
          localStorage.setItem('currentUser', JSON.stringify(userData));
        }

        this.showToast("Authentification réussie ! Redirection...", false);

        setTimeout(() => {
          this.router.push(next_step);
        }, 1500);

      } catch (err) {
        console.error("Erreur d'authentification ou communication Laravel:", err);
        let displayMessage = "Une erreur est survenue lors de l'authentification.";

        if (err.code) {
          displayMessage = {
            'auth/popup-closed-by-user': 'Connexion annulée par l\'utilisateur.',
            'auth/popup-blocked': 'La fenêtre pop-up a été bloquée par votre navigateur.',
            'auth/cancelled-popup-request': 'La demande de pop-up a été annulée.',
            'auth/account-exists-with-different-credential': 'Un compte existe déjà avec cette adresse email mais avec un autre fournisseur d\'authentification.',
            'auth/network-request-failed': 'Problème de connexion réseau. Veuillez vérifier votre internet.',
            'auth/internal-error': 'Une erreur interne est survenue dans Firebase. Cela peut être dû à un problème réseau ou une configuration.',
          }[err.code] || displayMessage;
        } else if (axios.isAxiosError(err) && err.response && err.response.data && err.response.data.message) {
          displayMessage = err.response.data.message;
        } else if (err.message) {
          displayMessage = err.message;
        }

        this.showToast(displayMessage, true);

        if (auth.currentUser) {
          auth.signOut().catch(signOutErr => console.error("Erreur de déconnexion Firebase:", signOutErr));
        }
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.filter-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #1271d0;
  padding: 1rem;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.filter-container input,
.filter-container select {
  transition: all 0.3s ease;
}

.filter-container input:focus,
.filter-container select:focus {
  transform: scale(1.02);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>