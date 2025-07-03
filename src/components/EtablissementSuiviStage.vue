<template>
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-12 col-md-12">
        <div class="card shadow-sm border-0">
          <div
            class="card-header bg-primary text-white py-3 rounded-top d-flex flex-column flex-md-row justify-content-between align-items-center"
          >
            <h2 class="mb-2 mb-md-0 fs-4">Suivi des Stages de votre Établissement</h2>

            <div class="d-flex flex-column flex-md-row align-items-center flex-wrap justify-content-center justify-content-md-end filter-controls">
              <div class="input-group search-input-group me-md-3 mb-2 mb-md-0">
                <input
                  type="text"
                  class="form-control form-control-sm"
                  placeholder="Rechercher par étudiant, offre, entreprise..."
                  v-model="searchQuery"
                  @input="applyFilters"
                />
              </div>

              <div class="dropdown me-md-3 mb-2 mb-md-0">
                <button
                  class="btn btn-outline-light dropdown-toggle btn-sm"
                  type="button"
                  id="filterStatusDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  {{ filterStatus === '' ? 'Filtrer par statut' : getStageStatusText(filterStatus) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterStatusDropdown">
                  <li><a class="dropdown-item" href="#" @click.prevent="setFilterStatus('')">Tous les statuts</a></li>
                  <li v-for="status in availableStatuses" :key="status.value">
                    <a class="dropdown-item" href="#" @click.prevent="setFilterStatus(status.value)">
                      {{ status.text }}
                    </a>
                  </li>
                </ul>
              </div>

              <div class="dropdown me-md-3 mb-2 mb-md-0">
                <button
                  class="btn btn-outline-light dropdown-toggle btn-sm"
                  type="button"
                  id="filterFiliereDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  {{ filterFiliere === '' ? 'Filtrer par filière' : getFiliereText(filterFiliere) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterFiliereDropdown">
                  <li><a class="dropdown-item" href="#" @click.prevent="setFilterFiliere('')">Toutes les filières</a></li>
                  <li v-for="filiere in availableFilieres" :key="filiere.id">
                    <a class="dropdown-item" href="#" @click.prevent="setFilterFiliere(filiere.id)">
                      {{ filiere.libfil }}
                    </a>
                  </li>
                </ul>
              </div>

              <div class="dropdown mb-2 mb-md-0">
                <button
                  class="btn btn-outline-light dropdown-toggle btn-sm"
                  type="button"
                  id="filterAnneeDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  {{ filterAnnee === '' ? 'Filtrer par année' : getAnneeText(filterAnnee) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterAnneeDropdown">
                  <li><a class="dropdown-item" href="#" @click.prevent="setFilterAnnee('')">Toutes les années</a></li>
                  <li v-for="annee in availableAnnees" :key="annee.id">
                    <a class="dropdown-item" href="#" @click.prevent="setFilterAnnee(annee.id)">
                      {{ annee.libannee }}
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="card-body p-4">
            <p v-if="loading" class="alert alert-info text-center">Chargement des stages...</p>
            <p v-if="error" class="alert alert-danger text-center">Erreur : {{ error }}</p>

            <div v-if="!loading && !error">
              <div v-if="filteredStages.length > 0" class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Titre du Stage</th>
                      <th scope="col">Étudiant (Matricule)</th>
                      <th scope="col">Filière / Année</th>
                      <th scope="col">Entreprise</th>
                      <th scope="col">Période</th>
                      <th scope="col">Statut</th>
                      <th scope="col">Rapport / Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="stage in filteredStages" :key="stage.id">
                      <td>{{ stage.id }}</td>
                      <td>
                        <strong>{{ stage.offre?.titre || 'Stage sans titre' }}</strong>
                      </td>
                      <td>
                        <router-link :to="{ name: 'etablissement-generic-profile-view', params: { type: 'etudiants', id: stage.etudiant?.id } }">
  <span class="fw-bold ">{{ stage.etudiant?.user?.name || 'Inconnu' }}</span>
</router-link>
                        <small class="text-muted">({{ stage.etudiant?.matricule || 'N/A' }})</small>
                      </td>
                      <td>
                        <span v-if="stage.etudiant?.filiere && stage.etudiant?.filannee?.annee">
                          {{ stage.etudiant?.filiere.libfil }} / {{ stage.etudiant?.filannee?.annee.libannee }}
                        </span>
                        <span v-else class="text-muted">N/A</span>
                      </td>
                      <td>
                        <router-link :to="{ name: 'etablissement-generic-profile-view', params: { type: 'entreprises', id: stage.offre?.entreprise?.id } }">
  <span class="fw-bold ">{{ stage.offre?.entreprise.nom_entreprise || 'Inconnue' }}</span>
</router-link>  
                      </td>
                      <td>
                        Du {{ formatDate(stage.date_debut) }}<br />au {{ formatDate(stage.date_fin) }}
                      </td>
                      <td>
                        <span :class="['status', 'badge', 'py-2', getStageStatusClass(stage.statut)]">
                          {{ getStageStatusText(stage.statut) }}</span
                        >
                      </td>
                      <td>
                        <template v-if="stage.rapport_stage">
                          <a
                            :href="getPhotoUrl(stage.rapport_stage)"
                            target="_blank"
                            class="btn btn-outline-info btn-sm mb-1"
                          >
                            <i class="bi bi-file-earmark-text"></i> Rapport
                          </a>
                        </template>
                        <template v-else>
                          <span class="text-muted small">Pas de rapport</span>
                        </template>
                        <br />
                        <template v-if="stage.note_stage !== null">
                          <strong class="text-success">{{ stage.note_stage }}/20</strong>
                        </template>
                        <template v-else>
                          <span class="text-muted small">Pas de note</span>
                        </template>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="alert alert-warning text-center">
                Aucun stage trouvé correspondant à vos critères de recherche.
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

const stages = ref([]);
const loading = ref(true);
const error = ref(null);

// Variables pour la recherche et le filtrage
const searchQuery = ref('');
const filterStatus = ref('');
const filterFiliere = ref(''); // New filter for Filière (ID)
const filterAnnee = ref(''); // New filter for Année (ID)


// Statuts disponibles pour le filtre (doivent correspondre aux statuts définis dans votre migration et backend)
const availableStatuses = [  { value: 'en_attente', text: 'En attente' },  { value: 'en_cours', text: 'En cours' },  { value: 'termine', text: 'Terminé' },  { value: 'annule', text: 'Annulé' },  { value: 'suspendu', text: 'Suspendu' },];

// Placeholder for available Filières and Années
// In a real application, you would fetch these from your API
const availableFilieres = ref([]);
const availableAnnees = ref([]);


/**
 * Propriété calculée pour filtrer les stages en fonction de la recherche et du statut.
 */
const filteredStages = computed(() => {
  let filtered = stages.value;

  // Filtrage par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (stage) =>
        stage.etudiant?.user?.name?.toLowerCase().includes(query) ||
        stage.etudiant?.matricule?.toLowerCase().includes(query) ||
        stage.offre?.titre?.toLowerCase().includes(query) ||
        stage.offre?.entreprise?.nom_entreprise?.toLowerCase().includes(query) || // Corrected entreprise name access
        stage.tuteur_stage?.user?.name?.toLowerCase().includes(query)
    );
  }

  // Filtrage par statut
  if (filterStatus.value) {
    filtered = filtered.filter((stage) => stage.statut === filterStatus.value);
  }

  // Filtrage par Filière
  if (filterFiliere.value) {
    filtered = filtered.filter((stage) => stage.etudiant?.filiere?.id === filterFiliere.value);
  }

  // Filtrage par Année
  if (filterAnnee.value) {
    filtered = filtered.filter((stage) => stage.etudiant?.filannee?.annee?.id === filterAnnee.value);
  }

  return filtered;
});

/**
 * Applique les filtres (déclenchée par la saisie ou le changement de sélection).
 * Le `computed` `filteredStages` se mettra à jour automatiquement.
 */
const applyFilters = () => {
  // Cette fonction ne fait rien d'autre que déclencher la réactivité de `filteredStages`.
};

/**
 * Définit le statut de filtre et déclenche l'application des filtres.
 * @param {string} status - Le statut sélectionné.
 */
const setFilterStatus = (status) => {
  filterStatus.value = status;
  applyFilters();
};

/**
 * Définit la filière de filtre et déclenche l'application des filtres.
 * @param {number} filiereId - L'ID de la filière sélectionnée.
 */
const setFilterFiliere = (filiereId) => {
  filterFiliere.value = filiereId;
  applyFilters();
};

/**
 * Définit l'année de filtre et déclenche l'application des filtres.
 * @param {number} anneeId - L'ID de l'année sélectionnée.
 */
const setFilterAnnee = (anneeId) => {
  filterAnnee.value = anneeId;
  applyFilters();
};

/**
 * Récupère les stages pertinents pour l'établissement authentifié depuis l'API.
 */
const fetchStagesEtablissement = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/stages');
    stages.value = response.data;

    // Extract unique filieres and annees from fetched stages for filter options
    const uniqueFilieres = new Set();
    const uniqueAnnees = new Set();
    stages.value.forEach(stage => {
      if (stage.etudiant?.filiere) {
        uniqueFilieres.add(JSON.stringify(stage.etudiant.filiere)); // Use stringify to compare objects
      }
      if (stage.etudiant?.filannee?.annee) {
        uniqueAnnees.add(JSON.stringify(stage.etudiant.filannee.annee));
      }
    });
    availableFilieres.value = Array.from(uniqueFilieres).map(f => JSON.parse(f));
    availableAnnees.value = Array.from(uniqueAnnees).map(a => JSON.parse(a));

  } catch (err) {
    console.error('Erreur lors du chargement des stages de l\'établissement :', err.response ? err.response.data : err.message);
    error.value = err.response?.data?.message || 'Impossible de charger les stages de votre établissement. Veuillez réessayer plus tard.';
  } finally {
    loading.value = false;
  }
};


/**
 * Formate une chaîne de date dans un format plus lisible.
 * @param {string} dateString - La chaîne de date à formater.
 * @returns {string} La date formatée ou 'N/A'.
 */
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('fr-FR', options);
};

/**
 * Renvoie la classe CSS Bootstrap pour le statut du stage.
 * @param {string} status - Le statut du stage.
 * @returns {string} La classe CSS correspondante.
 */
const getStageStatusClass = (status) => {
  switch (status) {
    case 'en_cours':
      return 'bg-info-subtle text-dark';
    case 'termine':
      return 'bg-success-subtle text-dark';
    case 'annule':
      return 'bg-danger-subtle text-dark';
    case 'en_attente':
      return 'bg-secondary-subtle text-dark';
    case 'suspendu':
      return 'bg-danger-subtle text-dark';
    default:
      return 'bg-light text-dark';
  }
};

/**
 * Renvoie le texte convivial pour le statut du stage.
 * @param {string} status - Le statut du stage.
 * @returns {string} Le texte affichable.
 */
const getStageStatusText = (status) => {
  switch (status) {
    case 'en_cours':
      return 'En cours';
    case 'termine':
      return 'Terminé';
    case 'annule':
      return 'Annulé';
    case 'en_attente':
      return 'En attente';
    case 'suspendu':
      return 'Suspendu';
    default:
      return status;
  }
};

/**
 * Renvoie le texte convivial pour la filière.
 * @param {number} filiereId - L'ID de la filière.
 * @returns {string} Le texte affichable de la filière.
 */
const getFiliereText = (filiereId) => {
  const filiere = availableFilieres.value.find(f => f.id === filiereId);
  return filiere ? filiere.libfil : 'Filière inconnue';
};

/**
 * Renvoie le texte convivial pour l'année.
 * @param {number} anneeId - L'ID de l'année.
 * @returns {string} Le texte affichable de l'année.
 */
const getAnneeText = (anneeId) => {
  const annee = availableAnnees.value.find(a => a.id === anneeId);
  return annee ? annee.libannee : 'Année inconnue';
};

/**
 * Construit l'URL complète pour un fichier (rapport de stage ou photo de profil).
 * @param {string} filePath - Le chemin relatif du fichier.
 * @returns {string} L'URL complète ou un chemin par défaut.
 */
const getPhotoUrl = (filePath) => {
  const defaultFile = '/images/default_file.pdf'; // Assurez-vous que ce chemin est correct
  if (!filePath) {
    return defaultFile;
  }
  // Gérer si le chemin est déjà une URL complète
  if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
    return filePath;
  }
  // Construire l'URL avec la variable d'environnement VITE_API_URL
  const baseUrl = import.meta.env.VITE_API_URL || '';
  return `${baseUrl}${filePath.startsWith('/') || baseUrl.endsWith('/') ? '' : '/'}${filePath}`;
};

// Exécute le chargement des stages au montage du composant
onMounted(fetchStagesEtablissement);
</script>

<style scoped>
/* Votre style CSS existant de la page des offres, avec quelques ajustements */

/* Conteneur principal et carte */
.container-fluid.py-4 {
  background-color: #f0f2f5;
  min-height: calc(100vh - 60px);
}

.card {
  border-radius: 12px;
  overflow: hidden;
}

/* En-tête de la carte */
.card-header {
  background-color: #0d6efd; /* primary-blue */
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
  display: flex;
  flex-direction: column; /* Default to column for mobile */
  align-items: center;
  gap: 1rem; /* Spacing between direct children */
}

.card-header h2 {
  color: white;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  letter-spacing: 0.5px;
  text-align: center; /* Center title on small screens */
  width: 100%; /* Ensure title takes full width */
  margin-bottom: 0.5rem; /* Add some space below title on mobile */
}

/* Container for search and filter controls */
.filter-controls {
  width: 100%; /* Take full width of the header on small screens */
  display: flex;
  flex-direction: column; /* Stack vertically on small screens */
  align-items: center;
  gap: 0.5rem; /* Spacing between filter items on small screens */
}


/* Styles for search input group */
.search-input-group {
  width: 100%; /* Make search full width on small screens */
  max-width: 300px; /* Limit max-width for aesthetic on larger screens */
}

.search-input-group .form-control-sm {
  border-radius: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.5);
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
}
.search-input-group .form-control-sm::placeholder {
  color: rgba(255, 255, 255, 0.7);
}
.search-input-group .form-control-sm:focus {
  background-color: white;
  color: #212529;
  box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

/* Styles for dropdowns */
.dropdown {
  width: 100%; /* Make dropdowns full width on small screens */
  max-width: 300px; /* Limit max-width for aesthetic on larger screens */
}

.dropdown .btn-outline-light {
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
  width: 100%; /* Ensure button takes full width of its container */
}
.dropdown .btn-outline-light:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}
.dropdown-menu {
  border-radius: 0.25rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Adjustments for medium screens and up */
@media (min-width: 768px) {
  .card-header {
    flex-direction: row; /* Align header items in a row */
    justify-content: space-between;
    align-items: center;
    gap: 0; /* Remove gap from direct children if they have individual margins */
  }

  .card-header h2 {
    text-align: start; /* Align title to the start on larger screens */
    width: auto; /* Allow title to take its natural width */
    margin-bottom: 0 !important; /* Remove bottom margin */
  }

  .filter-controls {
    flex-direction: row; /* Align filter controls in a row */
    justify-content: flex-end; /* Align to the right */
    width: auto; /* Allow filter controls to take natural width */
    gap: 0.75rem; /* Spacing between filter items */
  }

  .search-input-group,
  .dropdown {
    width: auto; /* Allow elements to take natural width */
    margin-right: 0.75rem; /* Add right margin for spacing */
    max-width: 250px; /* Re-apply max width for search input on desktop */
  }

  .search-input-group:last-child,
  .dropdown:last-child {
    margin-right: 0; /* Remove right margin from the last item */
  }
}


/* Styles for the card body (unchanged or slightly adapted) */
.card-body {
  padding: 1.5rem !important;
}

/* Loading/error messages */
.alert {
  border-radius: 8px;
  font-weight: 500;
}

/* Stages table */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 1rem;
  vertical-align: middle;
  border-top: 1px solid #dee2e6;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
}

.table tbody tr:hover {
  background-color: #f2f2f2;
}

/* Status badges */
.status.badge {
  padding: 0.5em 0.7em;
  border-radius: 0.25rem;
  font-weight: 600;
  font-size: 0.85em; /* Adjusted for readability */
}

/* Badge colors */
.bg-info-subtle {
  background-color: #d1ecf1 !important;
}
.text-dark {
  color: #212529 !important;
}
.bg-success-subtle {
  background-color: #d4edda !important;
}
.bg-danger-subtle {
  background-color: #f8d7da !important;
}
.bg-secondary-subtle {
  background-color: #e2e3e5 !important;
}

/* Buttons in the table */
.table .btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

</style>