<template>
  <div class="content-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <span style="text-decoration: underline; text-decoration-color: #0d6efd;">
        <h1 class="text-dark page-main-title mb-0">Gestion des tuteurs</h1>
      </span>
      <button class="btn btn-primary" @click="openAddTutorModal">
        Ajouter un tuteur
      </button>
    </div>

    <section class="tutors-management offers-list">
      <div class="list-header bg-primary text-white p-3 rounded-top d-flex justify-content-between align-items-center">
        <h3 class="mb-0 fs-5">Nos tuteurs</h3>
        <div class="d-flex align-items-center">
          <div class="input-group search-input-group me-3">
            <input type="text" class="form-control form-control-sm" placeholder="Rechercher un tuteur" v-model="searchQuery" @input="filterTutors">
          </div>
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle btn-sm" type="button" id="filterFiliereDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              {{ filterFiliere === '' ? 'Filière' : filterFiliere }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterFiliereDropdown">
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterFiliere('')">Toutes les filières</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterFiliere('Informatique')">Informatique</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterFiliere('Comptabilité')">Comptabilité</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="setFilterFiliere('Finance')">Finance</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="filteredTutorsGrouped.length > 0" class="bg-white rounded-bottom shadow-sm border-start border-end border-bottom">
        <div
          v-for="(category, index) in filteredTutorsGrouped"
          :key="index"
          :class="['tutor-category', {'mb-4': index < filteredTutorsGrouped.length - 1}]"
        >
          <h4 class="fs-5 text-dark mb-3 pb-2 border-bottom px-4 pt-4">{{ category.filiere }}</h4>
          <div class="p-4 pt-0">
            <div
              v-for="tutor in category.tutors"
              :key="tutor.id"
              class="tutor-item bg-white p-3 rounded shadow-sm mb-3 d-flex flex-column"
            >
              <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="tutor-name text-dark fw-bold mb-0">
                  <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>{{ tutor.name }}
                  <span class="badge bg-secondary-subtle text-secondary ms-2">{{ tutor.students.length }} étudiant(s)</span>
                </p>
                <div class="tutor-actions">
                  <button class="btn btn-outline-primary btn-sm me-2" @click="editTutor(tutor.id)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" @click="deleteTutor(tutor.id)">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </div>

              <div v-if="tutor.students.length > 0" class="students-table-container">
                <table class="table mb-0">
                  <thead>
                    <tr>
                      <th>Étudiant</th>
                      <th>Statut</th>
                      <th class="text-center">Note</th>
                      <th class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="student in tutor.students" :key="student.id">
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="student-initials-container me-2">
                            <img v-if="student.profilePic" :src="student.profilePic" :alt="student.name" class="rounded-circle w-100 h-100 object-fit-cover" />
                            <div v-else class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center w-100 h-100">
                              {{ student.name.charAt(0).toUpperCase() }}
                            </div>
                          </div>
                          {{ student.name }}
                        </div>
                      </td>
                      <td><span :class="['status', 'badge', getStatusBadgeClass(student.status)]">{{ student.status }}</span></td>
                      <td class="text-center fw-bold">{{ student.grade || '-' }}</td>
                      <td class="text-center">
                        <button v-if="student.status === 'En stage' && !student.grade" class="btn btn-success btn-sm" @click="assignGrade(student.id)">Attribuer</button>
                        <span v-else-if="student.status === 'Stage achevé' && student.grade" class="text-muted"><i class="fas fa-check-circle text-success me-1"></i>Évalué</span>
                        <span v-else class="text-muted">-</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center text-muted p-3 border rounded-sm bg-light">
                Ce tuteur n'a pas encore d'étudiants assignés.
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="bg-white p-3 rounded-bottom shadow-sm text-center text-muted border-start border-end border-bottom">
          Aucun tuteur trouvé pour la filière ou la recherche sélectionnée.
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const tutors = ref([
  {
    id: 1,
    name: 'TAIO Sylvain',
    filiere: 'Informatique',
    students: [
      { id: 101, name: 'BAWA SACCA Hamid', status: 'Stage achevé', grade: 18, profilePic: 'https://i.pravatar.cc/300?img=6' },
      { id: 102, name: 'DEGLA Kurt', status: 'En stage', grade: null, profilePic:'https://i.pravatar.cc/300?img=21'  },
    ]
  },
  {
    id: 2,
    name: 'AKADI Madina',
    filiere: 'Comptabilité',
    students: [
      { id: 201, name: 'DIDE Mary', status: 'Stage achevé', grade: 15, profilePic: 'https://i.pravatar.cc/300?img=20' },
      { id: 202, name: 'ZONGO Ben', status: 'En stage', grade: null, profilePic: 'https://i.pravatar.cc/300?img=61' },
    ]
  },
  {
    id: 3,
    name: 'NOUVEL Marc',
    filiere: 'Informatique',
    students: [] // Tuteur sans étudiant pour l'exemple
  },
  {
    id: 4,
    name: 'LAMBERT Sophie',
    filiere: 'Finance',
    students: [
      { id: 401, name: 'DURAND Émilie', status: 'En stage', grade: null, profilePic: 'https://i.pravatar.cc/300?img=47' },
    ]
  },
]);

const filterFiliere = ref('');
const searchQuery = ref('');

const filteredTutorsGrouped = computed(() => {
  let filtered = tutors.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(tutor =>
      tutor.name.toLowerCase().includes(query) ||
      tutor.students.some(student => student.name.toLowerCase().includes(query))
    );
  }

  if (filterFiliere.value) {
    filtered = filtered.filter(tutor => tutor.filiere === filterFiliere.value);
  }

  const grouped = filtered.reduce((acc, tutor) => {
    const key = tutor.filiere;
    if (!acc[key]) {
      acc[key] = { filiere: key, tutors: [] };
    }
    acc[key].tutors.push(tutor);
    return acc;
  }, {});

  return Object.values(grouped).sort((a, b) => a.filiere.localeCompare(b.filiere));
});

const openAddTutorModal = () => {
  alert('Ouvrir le modal pour ajouter un nouveau tuteur.');
  // Implémenter ici la logique pour ouvrir un modal ou naviguer vers une page d'ajout
};

const editTutor = (tutorId) => {
  alert(`Éditer le tuteur avec l'ID: ${tutorId}`);
  // Logique pour éditer un tuteur
};

const deleteTutor = (tutorId) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer le tuteur avec l'ID: ${tutorId} ?`)) {
    tutors.value = tutors.value.filter(t => t.id !== tutorId);
    alert('Tuteur supprimé.');
  }
};

const assignGrade = (studentId) => {
  const tutor = tutors.value.find(t => t.students.some(s => s.id === studentId));
  if (tutor) {
    const student = tutor.students.find(s => s.id === studentId);
    if (student) {
      let grade = prompt(`Attribuer une note à ${student.name} (entre 0 et 20) :`);
      grade = parseFloat(grade);
      if (!isNaN(grade) && grade >= 0 && grade <= 20) {
        student.grade = grade;
        student.status = 'Stage achevé';
        alert(`Note de ${grade}/20 attribuée à ${student.name}.`);
      } else if (grade !== null) {
        alert('Veuillez entrer une note valide entre 0 et 20.');
      }
    }
  }
};

const setFilterFiliere = (filiere) => {
  filterFiliere.value = filiere;
};

const filterTutors = () => {
  // La computed property `filteredTutorsGrouped` gère le filtrage automatique.
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Stage achevé': return 'bg-success-subtle text-success';
    case 'En stage': return 'bg-info-subtle text-info';
    default: return 'bg-secondary-subtle text-secondary';
  }
};
</script>

<style scoped>
/* Variables de base - cohérentes avec vos autres composants */
:root {
  --primary-blue: #0d6efd; /* Couleur primaire de Bootstrap */
  --light-gray-bg: #f8f9fa; /* Fond de page */
  --dark-text: #212529; /* Texte principal */
  --secondary-text: #6c757d; /* Texte secondaire/gris */
  --border-color: #dee2e6; /* Couleur des bordures */
  --success-green: #198754; /* Vert de succès */
  --danger-red: #dc3545; /* Rouge de danger */
  --warning-orange: #ffc107; /* Jaune d'avertissement */
  --info-cyan: #0dcaf0; /* Cyan pour info/en stage */
}

.content-page {
  padding: 1.5rem;
  background-color: var(--light-gray-bg);
  min-height: 100vh;
}

.page-main-title {
  font-size: 1.8em;
  font-weight: 500;
}

/* Section Tuteurs (utilise les styles de offers-list pour la cohérence) */
.tutors-management.offers-list {
  border-radius: 0.25rem;
  overflow: hidden;
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.list-header {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.list-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

/* Champ de recherche */
.list-header .search-input-group {
  max-width: 250px;
  position: relative;
}

.list-header .search-input-group::before {
    content: "\f002"; /* Font Awesome search icon */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
    z-index: 2;
}

.list-header .form-control-sm {
  border-color: rgba(255, 255, 255, 0.4);
  color: white;
  background-color: rgba(255, 255, 255, 0.2);
  padding-left: 35px;
}
.list-header .form-control-sm::placeholder {
  color: rgba(255, 255, 255, 0.7);
}
.list-header .form-control-sm:focus {
  background-color: white;
  color: var(--dark-text);
  border-color: white;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.list-header .search-input-group:focus-within::before {
  color: var(--secondary-text);
}

/* Dropdown de filtre */
.list-header .dropdown-toggle {
  border-color: rgba(255, 255, 255, 0.4);
  color: white;
  background-color: rgba(255, 255, 255, 0.2);
  font-size: 0.875rem;
  padding: .25rem .75rem;
}
.list-header .dropdown-toggle::after {
  vertical-align: middle;
}
.list-header .dropdown-toggle:hover {
  background-color: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}
.list-header .dropdown-menu {
  background-color: white;
  border: 1px solid rgba(0, 0, 0, 0.15);
}
.list-header .dropdown-item {
  color: var(--dark-text);
  background-color: white;
}
.list-header .dropdown-item:hover,
.list-header .dropdown-item:focus {
  background-color: var(--light-gray-bg);
  color: var(--dark-text);
}


/* Catégories de tuteurs */
.tutor-category h4 {
  font-size: 1.25rem;
  font-weight: 600;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 0.5rem;
  margin-bottom: 15px !important;
}

/* Conteneur principal du tableau */
.bg-white.rounded-bottom {
  border-bottom-left-radius: 0.25rem;
  border-bottom-right-radius: 0.25rem;
}

/* Item de tuteur individuel */
.tutor-item {
  border: 1px solid var(--border-color);
  border-radius: 0.25rem !important;
  box-shadow: 0 0.0625rem 0.125rem rgba(0,0,0,0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tutor-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.tutor-name {
  font-size: 1.1rem;
}

.tutor-actions .btn {
    font-size: 0.75rem; /* Slightly smaller buttons for actions */
    padding: 0.3rem 0.6rem;
}

/* Tableau des étudiants */
.students-table-container {
  overflow-x: auto;
}

.students-table-container table {
  width: 100%;
  margin-top: 0;
  border-collapse: collapse;
}

.students-table-container th,
.students-table-container td {
  padding: 10px 15px;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}

.students-table-container th {
  background-color: var(--light-gray-bg);
  font-weight: bold;
  color: var(--dark-text);
  border-top: none;
  border-bottom: 2px solid var(--border-color);
}

.students-table-container tbody tr:last-child td {
  border-bottom: none;
}

/* Colonne de la photo de profil/initiales */
.students-table-container .student-profile-col {
  width: 60px;
  text-align: center;
}

.student-initials-container {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  border: 1px solid var(--border-color);
}

.student-initials-container > div {
  font-size: 1.1em;
  background-color: var(--primary-blue) !important;
  color: white;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.student-initials-container img {
  object-fit: cover;
  width: 100%;
  height: 100%;
}


/* Badges de statut */
.status.badge {
  padding: 0.4em 0.8em;
  font-size: 0.95em;
  font-weight: 600;
  border-radius: 0.375rem;
}

/* Boutons d'action */
.btn-success, .btn-danger, .btn-info {
  font-size: 0.85rem;
  padding: 0.45rem 0.9rem;
  font-weight: 500;
}

.btn-info {
  background-color: #0dcaf0 !important;
  border-color: #0dcaf0 !important;
}
.btn-info:hover {
  background-color: #31d2f2 !important;
  border-color: #25cff2 !important;
}

/* Classes Bootstrap pour les couleurs de badge */
.bg-success-subtle { background-color: #d1e7dd !important; }
.text-success { color: var(--success-green) !important; }
.bg-info-subtle { background-color: #fff3cd !important; }
.text-info { color: var(--info-cyan) !important; }
.bg-secondary-subtle { background-color: #e2e3e5 !important; }
.text-secondary { color: var(--secondary-text) !important; }
</style>