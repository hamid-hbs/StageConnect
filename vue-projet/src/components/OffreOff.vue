<template>
  <div>
    <!-- Navigation -->
    <nav ref="navBar" class="fixed top-0 w-full bg-white shadow-sm border-b border-gray-200 z-50">
      <div class="container mx-auto px-5 flex justify-between items-center py-3">
        <router-link to="/" class="flex items-center gap-2 font-bold text-xl no-underline">
          <img src="/toge.png" alt="StageConnect Logo" class="h-8 w-8 object-contain">
          <span class="bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">StageConnect</span>
        </router-link>
        
        <!-- Desktop Auth Buttons -->
        <div class="hidden md:flex flex-col sm:flex-row gap-3">
          <router-link to="/inscription" class="no-underline">
            <button class="rounded-md px-4 py-1 flex items-center justify-center gap-2 font-medium text-white 
              bg-gradient-to-r from-green-700 via-green-800 to-green-900 
              hover:from-green-800 hover:via-green-800 hover:to-green-700 
              transition-colors disabled:opacity-50">
              <span>S'inscrire</span>
            </button>
          </router-link>
          <router-link to="/connexion" class="no-underline">
            <button class="rounded-md px-4 py-1 flex items-center justify-center gap-2 font-medium text-white 
              bg-gradient-to-r from-blue-700 via-blue-800 to-blue-900 
              hover:from-blue-800 hover:via-blue-800 hover:to-blue-700 
              transition-colors disabled:opacity-50">
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
            <router-link to="/inscription" class="no-underline" @click="toggleMobileMenu">
              <button class="rounded-md px-4 py-1 flex items-center justify-center gap-2 font-medium text-white 
                bg-gradient-to-r from-green-700 via-green-800 to-green-900 
                hover:from-green-800 hover:via-green-800 hover:to-green-700 
                transition-colors disabled:opacity-50">
                <span>S'inscrire</span>
              </button>
            </router-link>
            <router-link to="/connexion" class="no-underline" @click="toggleMobileMenu">
              <button class="rounded-md px-4 py-1 flex items-center justify-center gap-2 font-medium text-white 
                bg-gradient-to-r from-blue-700 via-blue-800 to-blue-900 
                hover:from-blue-800 hover:via-blue-800 hover:to-blue-700 
                transition-colors disabled:opacity-50">
                <span>Se connecter</span>
              </button>
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenu principal avec padding dynamique -->
    <div class="content-page" :style="{ paddingTop: navHeight + 'px' }">
      <!-- Toast Container for Notifications -->
      <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="liveToastOffers" class="toast" role="alert" aria-live="assertive" aria-atomic="true" :class="`text-bg-${toastTypeOffers}`">
          <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            {{ toastMessageOffers }}
          </div>
        </div>
      </div>

      <!-- Header with Title and Back Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="page-main-title fs-4 text-dark mb-0">Détails de l'Offre</h1>
          <div style="width: 80px; height: 3px; background: #0d6efd; border-radius: 1.5px;"></div>
        </div>
        <button @click="goBack" class="btn btn-secondary">
          <i class="fas fa-arrow-left me-2"></i>Retour
        </button>
      </div>

      <!-- Offer Details Section -->
      <section class="offer-details-section bg-white p-4 rounded shadow-sm">
        <!-- Loading State -->
        <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <p class="ms-3">Chargement des détails de l'offre...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center text-danger py-5">
          <p class="fs-5 mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Erreur</p>
          <p>{{ error }}</p>
        </div>

        <!-- Main Content -->
        <div v-else-if="offreDetails" class="row">
          <div class="col-lg-8 col-md-12">
            <!-- Offer Title and Status -->
            <div class="offer-title-container mb-4 p-3 bg-light rounded shadow-sm d-flex justify-content-between align-items-center">
              <h2 class="fs-4 text-dark mb-0">{{ offreDetails.titre }}</h2>
              <span :class="['status-badge badge fs-6', getStatusBadgeClass(offreDetails.statut, offreDetails.date_expiration)]">
                {{ getDisplayStatus(offreDetails.statut, offreDetails.date_expiration) }}
              </span>
            </div>

            <!-- Company Card -->
            <div class="card mb-4 p-4 shadow-sm">
              <h4 class="section-header fs-5 mb-3 text-dark">
                <i class="fas fa-building me-2 text-primary"></i>
                <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Entreprise</span>
              </h4>
              <p class="mb-0 text-primary text-decoration-none fw-bold">
                {{ offreDetails.entreprise?.nom_entreprise || 'Entreprise inconnue' }}
              </p>
            </div>

            <!-- Domain Card -->
            <div class="card mb-4 p-4 shadow-sm">
              <h4 class="section-header fs-5 mb-3 text-dark">
                <i class="fas fa-industry me-2 text-primary"></i>
                <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Domaine</span>
              </h4>
              <p class="mb-0">{{ offreDetails.domaine?.libdomaine || 'Non spécifié' }}</p>
            </div>

            <!-- General Information Card -->
            <div class="card mb-4 p-4 shadow-sm">
              <h4 class="section-header fs-5 mb-3 text-dark">
                <i class="fas fa-info-circle me-2 text-primary"></i>
                <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Informations générales</span>
              </h4>
              <div class="row">
                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                      <i class="fas fa-map-marker-alt me-2 text-primary fs-5"></i>
                      <span class="fw-bold">Adresse :</span>
                      <span class="ms-2">{{ offreDetails.adresse || 'Non spécifié' }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                      <i class="fas fa-calendar-alt me-2 text-primary fs-5"></i>
                      <span class="fw-bold">Date de début :</span>
                      <span class="ms-2">{{ formatDate(offreDetails.date_debut) }}</span>
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                      <i class="fas fa-calendar-times me-2 text-primary fs-5"></i>
                      <span class="fw-bold">Date d'expiration :</span>
                      <span class="ms-2">{{ formatDate(offreDetails.date_expiration) }}</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                      <i class="fas fa-clock me-2 text-primary fs-5"></i>
                      <span class="fw-bold">Durée :</span>
                      <span class="ms-2">{{ offreDetails.duree_en_semaines ? offreDetails.duree_en_semaines + ' semaines' : 'Non spécifié' }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Description Card -->
            <div class="card mb-4 p-4 shadow-sm">
              <h4 class="section-header fs-5 mb-3 text-dark">
                <i class="fas fa-align-left me-2 text-primary"></i>
                <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Description de l'offre</span>
              </h4>
              <div class="description-content p-3 border rounded bg-light">
                <p class="mb-0" style="white-space: pre-wrap;">{{ offreDetails.description || 'Aucune description disponible.' }}</p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-12 mt-md-4 mt-lg-0">
            <!-- Statistics Card -->
            <div class="card p-4 shadow-sm mb-4">
              <h4 class="section-header fs-5 mb-3 text-dark">
                <i class="fas fa-chart-bar me-2 text-primary"></i>
                <span style="text-decoration: underline; text-decoration-color: #0d6efd; text-decoration-thickness: 2px;">Statistiques</span>
              </h4>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex align-items-center">
                  <i class="fas fa-users me-2 text-primary fs-5"></i>
                  <span class="fw-bold me-auto">Candidatures :</span>
                  <span class="badge bg-primary-subtle text-primary fs-6">{{ nombreCandidatures }}</span>
                </li>
              </ul>
            </div>

            <!-- Apply Button -->
            <div class="text-center mt-5">
              <router-link to="/inscription">
                <button class="btn btn-primary btn-lg" @click="showAuthRequiredToast">
                  <i class="fas fa-paper-plane me-2"></i>
                  Postuler
                </button>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center text-muted py-5">
          <p class="fs-5">Aucune offre trouvée pour l'ID spécifié.</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';
import { Toast } from 'bootstrap';

const props = defineProps({ id: { type: [String, Number], required: true } });

const offreDetails = ref(null);
const nombreCandidatures = ref(0);
const loading = ref(true);
const error = ref(null);
const isMobileMenuOpen = ref(false);

const toastMessageOffers = ref('');
const toastTypeOffers = ref('success');

const navBar = ref(null);
const navHeight = ref(0);

const route = useRoute();
const router = useRouter();

const toggleMobileMenu = () => { isMobileMenuOpen.value = !isMobileMenuOpen.value; };

const fetchOfferDetails = async (offerId) => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`/api/offres-off/${offerId}`);
    offreDetails.value = response.data.offre;
    nombreCandidatures.value = response.data.nombre_candidatures;
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur réseau ou serveur.';
    offreDetails.value = null;
  } finally { loading.value = false; }
};

const showToast = (message, isError) => {
  toastMessageOffers.value = message;
  toastTypeOffers.value = isError ? 'danger' : 'success';
  const toastEl = document.getElementById('liveToastOffers');
  if (toastEl) new Toast(toastEl).show();
};

const showAuthRequiredToast = () => { showToast("Veuillez vous authentifier pour postuler à cette offre.", true); };

const formatDate = (dateString) => dateString ? new Date(dateString.split('T')[0]).toLocaleDateString('fr-FR', { year:'numeric', month:'long', day:'numeric' }) : 'Non spécifié';

const isExpired = (expirationDate) => expirationDate ? new Date(expirationDate.split('T')[0]) < new Date() : true;

const getDisplayStatus = (statut, expirationDate) => (statut === 'expiree' || isExpired(expirationDate)) ? 'Expirée' : 'Disponible';
const getStatusBadgeClass = (statut, expirationDate) => (statut === 'expiree' || isExpired(expirationDate)) ? 'bg-danger text-white' : 'bg-success text-white';

const goBack = () => { router.go(-1); };

onMounted(() => {
  if (props.id) fetchOfferDetails(props.id);
  if (navBar.value) navHeight.value = navBar.value.offsetHeight;
});

watch(() => props.id, (newId) => { if (newId) fetchOfferDetails(newId); });
</script>

<style scoped>
.content-page { padding: 1rem; max-width: 1200px; margin: 0 auto; }
.page-main-title { font-size: 1.5rem; font-weight: 600; color: #212529; }
.section-header { font-size: 1.1rem; color: #212529; margin-bottom: 0.5rem; padding-bottom: 0.3rem; }
.description-content { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.75rem 1rem; min-height: 80px; color: #212529; }
.text-decoration-thickness { text-decoration-thickness: 2px; }
</style>
