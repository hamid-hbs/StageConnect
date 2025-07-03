<template>
  <div class="content-page p-4 bg-light min-vh-100 d-flex justify-content-center">
    <div class="container-custom">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="border-bottom border-primary pb-1">
          <h1 class="fs-2 fw-semibold text-dark mb-0">Mur des Entreprises</h1>
        </span>
      </div>

      <section class="mb-4">
        <div class="bg-white p-4 rounded shadow-sm">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h3 class="fs-5 fw-medium text-secondary mb-2 mb-md-0">Liste des Entreprises</h3>
            <div class="w-100 w-md-auto">
              <input
                type="text"
                placeholder="Rechercher une entreprise par nom ou domaine..."
                v-model="searchQuery"
                @input="resetAndFetchCompanies"
                class="form-control rounded"
              />
            </div>
          </div>

          <div v-if="loading && companies.length === 0" class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement des entreprises...</span>
            </div>
            <p class="ms-3 text-muted">Chargement des entreprises...</p>
          </div>

          <div v-else-if="error" class="text-center text-danger py-5">
            Erreur lors du chargement des entreprises : {{ error }}
          </div>

          <div v-else-if="filteredCompanies.length > 0" class="row g-4 justify-content-center">
            <div
              v-for="company in filteredCompanies"
              :key="company.id"
              class="col-12 col-sm-6 col-lg-4"
            >
              <div class="card h-100 shadow-sm custom-card-hover">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-4">
                    <img
                      :src="company.logo || 'https://placehold.co/80x80/E0E7FF/4F46E5?text=Logo'"
                      alt="Logo de l'entreprise"
                      class="rounded-circle object-fit-cover me-3"
                      style="width: 80px; height: 80px;"
                      onerror="this.onerror=null;this.src='https://placehold.co/80x80/E0E7FF/4F46E5?text=Logo';"
                    />
                    <div>
                      <h4 class="h5 card-title text-dark mb-1">{{ company.nom_entreprise || 'Nom inconnu' }}</h4>
                      <p v-if="company.domaine" class="text-muted small mb-0">
                        {{ company.domaine.libdomaine }}
                      </p>
                    </div>
                  </div>

                  <p class="text-dark mb-4">
                    {{ truncateDescription(company.description || 'Aucune description disponible.', 150) }}
                    <router-link :to="{ name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: company.id } }" class="text-primary text-decoration-underline ms-1">
                      Voir plus
                    </router-link>
                  </p>

                  <div class="d-flex align-items-center justify-content-between text-muted small mb-3">
                    <div class="d-flex align-items-center">
                      <button @click="toggleLike(company)" class="btn btn-sm btn-link text-decoration-none p-0 me-3 d-flex align-items-center">
                        <i :class="['fas fa-heart', company.is_liked_by_current_user ? 'text-danger' : 'text-secondary']"></i>
                        <span class="ms-1">{{ company.likes || 0 }} J'aime</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="!loading" class="text-center text-muted py-5">
            Aucune entreprise trouvée pour votre recherche.
          </div>

          <div v-if="loading && companies.length > 0" class="d-flex justify-content-center align-items-center py-4">
            <div class="spinner-border text-primary spinner-border-sm" role="status">
              <span class="visually-hidden">Chargement de plus d'entreprises...</span>
            </div>
            <p class="ms-3 text-muted">Chargement de plus d'entreprises...</p>
          </div>
          <div v-if="!hasMore && companies.length > 0" class="text-center text-muted py-4">
            Fin des résultats.
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const companies = ref([]);
const loading = ref(false);
const error = ref(null);
const searchQuery = ref('');
const page = ref(1);
const hasMore = ref(true);

// Fonction pour tronquer la description
const truncateDescription = (text, maxLength) => {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

// Fonction pour récupérer la liste des entreprises
const fetchCompanies = async () => {
  if (loading.value || !hasMore.value) return;

  loading.value = true;
  error.value = null;
  try {
    // Endpoint spécifique aux entreprises
    const response = await axios.get(`/api/entreprises?page=${page.value}&search=${searchQuery.value}`);
    
    let newCompaniesData = [];
    let nextPageUrl = null;

    if (response.data && typeof response.data === 'object' && response.data.data !== undefined) {
      if (Array.isArray(response.data.data)) {
        newCompaniesData = response.data.data;
        nextPageUrl = response.data.next_page_url;
      } else {
        console.error('response.data.data n\'est pas un tableau :', response.data.data);
        error.value = 'Données paginées reçues dans un format inattendu.';
        hasMore.value = false;
        loading.value = false;
        return;
      }
    } else if (Array.isArray(response.data)) {
      newCompaniesData = response.data;
      nextPageUrl = null;
    } else {
      console.error('Structure de données inattendue de l\'API :', response.data);
      error.value = 'Données reçues de l\'API dans un format inattendu.';
      hasMore.value = false;
      loading.value = false;
      return;
    }

    newCompaniesData.forEach(company => {
      const currentUserId = 1; // Remplacez par l'ID de l'utilisateur authentifié
      company.is_liked_by_current_user = company.likers && Array.isArray(company.likers) ? company.likers.some(liker => liker.id === currentUserId) : false;
    });

    companies.value = [...companies.value, ...newCompaniesData];
    hasMore.value = nextPageUrl !== null;
    if (hasMore.value) {
      page.value++;
    }
    console.log('Entreprises récupérées :', companies.value);
  } catch (err) {
    console.error('Erreur lors du chargement des entreprises :', err);
    error.value = 'Impossible de charger la liste des entreprises.';
  } finally {
    loading.value = false;
  }
};

// Réinitialiser la recherche et recharger les entreprises
const resetAndFetchCompanies = () => {
  companies.value = [];
  page.value = 1;
  hasMore.value = true;
  fetchCompanies();
};

// Filtrage côté client (si le backend ne gère pas le search sur la pagination)
const filteredCompanies = computed(() => {
  const query = searchQuery.value.toLowerCase();
  if (!query) return companies.value;

  return companies.value.filter(company => {
    const companyName = company.nom_entreprise ? company.nom_entreprise.toLowerCase() : '';
    const domainName = company.domaine ? company.domaine.libdomaine.toLowerCase() : '';
    return companyName.includes(query) || domainName.includes(query);
  });
});

// Infinite scroll logic
const handleScroll = () => {
  const bottomOfWindow = document.documentElement.scrollTop + window.innerHeight >= document.documentElement.offsetHeight - 100;
  if (bottomOfWindow && hasMore.value && !loading.value) {
    fetchCompanies();
  }
};

// Toggle Like
const toggleLike = async (company) => {
  const url = company.is_liked_by_current_user ? `/api/entreprises/${company.id}/unlike` : `/api/entreprises/${company.id}/like`;
  try {
    const response = await axios.post(url);
    company.is_liked_by_current_user = !company.is_liked_by_current_user;
    company.likes = response.data.data.likes;
  } catch (err) {
    console.error('Erreur lors de la gestion du like :', err);
    alert('Erreur lors de la gestion du like. Veuillez réessayer.');
  }
};

onMounted(() => {
  fetchCompanies();
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.content-page {
  font-family: 'Inter', sans-serif;
}

.container-custom {
  max-width: 1400px;
  width: 100%;
  padding-left: var(--bs-gutter-x, 0.75rem);
  padding-right: var(--bs-gutter-x, 0.75rem);
  margin-left: auto;
  margin-right: auto;
}

.custom-card-hover {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.custom-card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.btn-link {
  color: var(--bs-secondary);
}

.btn-link:hover {
  color: var(--bs-primary);
}

.spinner-border {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.fa-heart, .fa-comment {
  transition: color 0.2s ease-in-out;
}
</style>
