<template>
  <div class="content-page p-4 bg-light min-vh-100 d-flex justify-content-center">
    <div class="container-custom">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="border-bottom border-primary pb-1">
          <h1 class="fs-2 fw-semibold text-dark mb-0">Mur Communautaire</h1>
        </span>
      </div>

      <section class="mb-4">
        <div class="bg-white p-4 rounded shadow-sm">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h3 class="fs-5 fw-medium text-secondary mb-2 mb-md-0">Dernières publications</h3>
            <div class="w-100 w-md-auto">
              <input
                type="text"
                placeholder="Rechercher par nom d'utilisateur, étudiant ou entreprise..."
                v-model="searchQuery"
                @input="resetAndFetchPosts"
                class="form-control rounded"
              />
            </div>
          </div>

          <div v-if="loading && posts.length === 0" class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement des publications...</span>
            </div>
            <p class="ms-3 text-muted">Chargement des publications...</p>
          </div>

          <div v-else-if="error" class="text-center text-danger py-5">
            Erreur lors du chargement des publications : {{ error }}
          </div>

          <div v-else-if="filteredPosts.length > 0" class="row g-4 justify-content-center">
            <div
              v-for="post in filteredPosts"
              :key="post.id"
              class="col-12 col-md-8 col-lg-6 mx-auto" 
            ><!-- CHANGEMENT ICI : Colonnes plus étroites et centrées -->
              <div class="card h-100 shadow-sm custom-card-hover">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-4"> <!-- CHANGEMENT ICI : Taille de l'image -->
                    <img
                      :src="getProfilePicture(post) || 'https://placehold.co/60x60/E0E7FF/4F46E5?text=Profil'"
                      alt="Image de profil"
                      class="rounded-circle object-fit-cover me-3"
                      style="width: 60px; height: 60px;"
                      onerror="this.onerror=null;this.src='https://placehold.co/60x60/E0E7FF/4F46E5?text=Profil';" 
                    />
                    <div>
                      <h4 class="h5 card-title text-dark mb-1">{{ getProfileName(post) }}</h4>
                      <p v-if="getProfileType(post)" class="text-muted small mb-0">
                        {{ getProfileType(post) }}
                      </p>
                    </div>
                  </div>

                  <p class="text-dark mb-4">
                    {{ truncateDescription(getProfileDescription(post) || 'Aucune description disponible.', 150) }}
                    <router-link :to="getProfileLink(post)" class="text-primary text-decoration-underline ms-1">
                      Voir plus
                    </router-link>
                  </p>

                  <div class="d-flex align-items-center justify-content-between text-muted small mb-3">
                    <div class="d-flex align-items-center">
                      <button @click="toggleLike(post)" class="btn btn-sm btn-link text-decoration-none p-0 me-3 d-flex align-items-center">
                        <i :class="['fas fa-heart', post.is_liked_by_current_user ? 'text-danger' : 'text-secondary']"></i>
                        <span class="ms-1">{{ post.likes || 0 }} J'aime</span>
                      </button>
                      <!-- Les éléments liés aux commentaires ont été supprimés ici -->
                    </div>
                  </div>

                  <!-- Les sections pour ajouter et afficher les commentaires ont été supprimées -->

                </div>
              </div>
            </div>
          </div>

          <div v-else-if="!loading" class="text-center text-muted py-5">
            Aucune publication trouvée pour votre recherche.
          </div>

          <div v-if="loading && posts.length > 0" class="d-flex justify-content-center align-items-center py-4">
            <div class="spinner-border text-primary spinner-border-sm" role="status">
              <span class="visually-hidden">Chargement de plus de publications...</span>
            </div>
            <p class="ms-3 text-muted">Chargement de plus de publications...</p>
          </div>
          <div v-if="!hasMore && posts.length > 0" class="text-center text-muted py-4">
            Fin des résultats.
          </div>
        </div>
      </section>
    </div>

    <!-- La modale de commentaires a été supprimée de ce composant -->
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
// L'import de Modal de Bootstrap n'est plus nécessaire car il n'y a plus de modale
// import { Modal } from 'bootstrap';

const router = useRouter();

const posts = ref([]);
const loading = ref(false);
const error = ref(null);
const searchQuery = ref('');
const page = ref(1);
const hasMore = ref(true);
// newCommentContent n'est plus nécessaire car il n'y a plus de champ de commentaire
// const newCommentContent = reactive({});

// currentPostInModal et commentsModalInstance ne sont plus nécessaires
// const currentPostInModal = ref(null);
// let commentsModalInstance = null;

// Fonctions utilitaires pour extraire les données du profil (étudiant ou entreprise)
const getProfilePicture = (post) => {
  if (post.user) {
    if (post.user.etudiant && post.user.etudiant.profile_picture) {
      return post.user.etudiant.profile_picture;
    } else if (post.user.entreprise && post.user.entreprise.logo) {
      return post.user.entreprise.logo;
    }
  }
  return null;
};

const getProfileName = (post) => {
  // Si le post est lié à une entreprise, afficher le nom de l'entreprise
  if (post.user && post.user.entreprise && post.user.entreprise.nom_entreprise) {
    return post.user.entreprise.nom_entreprise;
  }
  // Sinon, afficher le nom de l'utilisateur (pour les étudiants ou autres)
  if (post.user && post.user.name) {
    return post.user.name;
  }
  return 'Utilisateur inconnu';
};

const getProfileType = (post) => {
  if (post.user) {
    if (post.user.etudiant && post.user.etudiant.etablissement) {
      return post.user.etudiant.etablissement.nom_etablissement;
    } else if (post.user.etudiant) {
      return 'Étudiant';
    } else if (post.user.entreprise && post.user.entreprise.domaine) {
      return post.user.entreprise.domaine.libdomaine;
    } else if (post.user.entreprise) {
      return 'Entreprise';
    }
  }
  return null;
};

const getProfileDescription = (post) => {
  if (post.user) {
    if (post.user.etudiant && post.user.etudiant.description) {
      return post.user.etudiant.description;
    } else if (post.user.entreprise && post.user.entreprise.description) {
      return post.user.entreprise.description;
    }
  }
  return null;
};

const getProfileLink = (post) => {
  if (post.user) {
    if (post.user.etudiant && post.user.etudiant.id) {
      return { name: 'etudiant-generic-profile-view', params: { type: 'etudiants', id: post.user.etudiant.id } };
    } else if (post.user.entreprise && post.user.entreprise.id) {
      return { name: 'etudiant-generic-profile-view', params: { type: 'entreprises', id: post.user.entreprise.id } };
    }
  }
  return { name: 'home' };
};


// Fonction pour tronquer la description
const truncateDescription = (text, maxLength) => {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

// Fonction pour récupérer la liste des publications (ProfilCommu)
const fetchPosts = async () => {
  if (loading.value || !hasMore.value) return;

  loading.value = true;
  error.value = null;
  try {
    // Utilisation de l'endpoint ProfilCommu
    const response = await axios.get(`/api/profils-commu?page=${page.value}&search=${searchQuery.value}`);
    
    let newPostsData = [];
    let nextPageUrl = null;

    if (response.data && typeof response.data === 'object' && response.data.data !== undefined) {
      if (Array.isArray(response.data.data)) {
        newPostsData = response.data.data;
        nextPageUrl = response.data.next_page_url;
      } else {
        console.error('response.data.data n\'est pas un tableau :', response.data.data);
        error.value = 'Données paginées reçues dans un format inattendu.';
        hasMore.value = false;
        loading.value = false;
        return;
      }
    } else if (Array.isArray(response.data)) {
      newPostsData = response.data;
      nextPageUrl = null;
    } else {
      console.error('Structure de données inattendue de l\'API :', response.data);
      error.value = 'Données reçues de l\'API dans un format inattendu.';
      hasMore.value = false;
      loading.value = false;
      return;
    }

    newPostsData.forEach(post => {
      const currentUserId = 1; // Remplacez par l'ID de l'utilisateur authentifié
      post.is_liked_by_current_user = post.likers && Array.isArray(post.likers) ? post.likers.some(liker => liker.id === currentUserId) : false;
    });


    posts.value = [...posts.value, ...newPostsData];
    hasMore.value = nextPageUrl !== null;
    if (hasMore.value) {
      page.value++;
    }
    console.log('Publications récupérées :', posts.value);
  } catch (err) {
    console.error('Erreur lors du chargement des publications :', err);
    error.value = 'Impossible de charger la liste des publications.';
  } finally {
    loading.value = false;
  }
};

// Réinitialiser la recherche et recharger les publications
const resetAndFetchPosts = () => {
  posts.value = [];
  page.value = 1;
  hasMore.value = true;
  fetchPosts();
};

// Filtrage côté client (si le backend ne gère pas le search sur la pagination)
const filteredPosts = computed(() => {
  const query = searchQuery.value.toLowerCase();
  if (!query) return posts.value;

  return posts.value.filter(post => {
    const profileName = getProfileName(post).toLowerCase();
    const profileDescription = getProfileDescription(post) ? getProfileDescription(post).toLowerCase() : '';
    const profileType = getProfileType(post) ? getProfileType(post).toLowerCase() : '';

    return profileName.includes(query) || 
           profileDescription.includes(query) ||
           profileType.includes(query);
  });
});


// Infinite scroll logic
const handleScroll = () => {
  const bottomOfWindow = document.documentElement.scrollTop + window.innerHeight >= document.documentElement.offsetHeight - 100;
  if (bottomOfWindow && hasMore.value && !loading.value) {
    fetchPosts();
  }
};

// Toggle Like
const toggleLike = async (post) => {
  const url = post.is_liked_by_current_user ? `/api/profils-commu/${post.id}/unlike` : `/api/profils-commu/${post.id}/like`;
  try {
    const response = await axios.post(url);
    post.is_liked_by_current_user = !post.is_liked_by_current_user;
    post.likes = response.data.data.likes;
  } catch (err) {
    console.error('Erreur lors de la gestion du like :', err);
    alert('Erreur lors de la gestion du like. Veuillez réessayer.');
  }
};

// addComment function and related logic removed
// const addComment = async (post) => {
//   const content = newCommentContent[post.id];
//   if (!content || content.trim() === '') {
//     alert('Le commentaire ne peut pas être vide.');
//     return;
//   }
//   try {
//     const response = await axios.post(`/api/profils-commu/${post.id}/commentaires`, { comment: content.trim() });
//     if (!post.commentaires) { 
//       post.commentaires = [];
//     }
//     post.commentaires.unshift(response.data.data); 
//     newCommentContent[post.id] = '';
//   } catch (err) {
//     console.error('Erreur lors de l\'ajout du commentaire :', err);
//     alert('Erreur lors de l\'ajout du commentaire. Veuillez réessayer.');
//   }
// };

// showCommentsModal function and related logic removed
// const showCommentsModal = (post) => {
//   currentPostInModal.value = post;
//   if (!commentsModalInstance) {
//     commentsModalInstance = new Modal(document.getElementById('commentsModal'));
//   }
//   commentsModalInstance.show();
// };

onMounted(() => {
  fetchPosts();
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
/* Styles Bootstrap sont utilisés principalement dans le template. */
/* Styles additionnels pour les transitions et Font Awesome */

.content-page {
  font-family: 'Inter', sans-serif;
}

/* Custom container to increase width */
.container-custom {
  max-width: 900px; /* CHANGEMENT ICI : Réduction de la largeur maximale du conteneur */
  width: 100%; /* S'assure qu'il prend la largeur disponible jusqu'à max-width */
  padding-left: var(--bs-gutter-x, 0.75rem); /* Conserve le padding de Bootstrap */
  padding-right: var(--bs-gutter-x, 0.75rem); /* Conserve le padding de Bootstrap */
  margin-left: auto; /* Centre le conteneur */
  margin-right: auto; /* Centre le conteneur */
}

/* Custom hover effect for cards */
.custom-card-hover {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.custom-card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; /* Augmente l'ombre au survol */
}

/* Style pour les boutons de like/commentaire pour qu'ils ressemblent à des liens */
.btn-link {
  color: var(--bs-secondary); /* Couleur par défaut pour les icônes non likées */
}

.btn-link:hover {
  color: var(--bs-primary); /* Couleur au survol */
}

/* Styles pour le spinner */
.spinner-border {
  animation: spin 1s linear infinite;
}

</style>
