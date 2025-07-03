import { createRouter, createWebHistory } from 'vue-router';

// Composants de base
import Home from '../views/Home.vue';
import CompleteProfile from '../components/CompleteProfile.vue';
import SelectRole from '../components/SelectRole.vue';
import AccueilUser from '../components/AccueilUser.vue';
import OffreDetails from '../components/OffreDetails.vue';
//import ConversationDetail from '../components/ConversationDetail.vue';
import ConversationsList from '../components/ConversationsList.vue';


// Composants de profil D'ÉDITION PROPRE (utilisés pour le profil de l'utilisateur connecté)
import EntrepriseProfile from '../components/EntrepriseProfile.vue';
//import EtablissementProfile from '../components/EtablissementProfile.vue';
import EtudiantProfile from '../components/EtudiantProfile.vue';

// Composants de Dashboard (Layouts avec sidebar/header)
import EntrepriseDashboard from '../components/EntrepriseDashboard.vue';
//import EtablissementDashboard from '../components/EtablissementDashboard.vue';
import EtudiantDashboard from '../components/EtudiantDashboard.vue';

// Autres composants spécifiques à chaque dashboard
import EntrepriseOffre from '../components/EntrepriseOffre.vue';
//import EntreprisePartenariat from '../components/EntreprisePartenariat.vue';
import EntrepriseCandidature from '../components/EntrepriseCandidature.vue';
//import EntrepriseMessagerie from '../components/EtablissementMessagerie.vue'; // Correction du chemin si nécessaire
import EntrepriseNotification from '../components/EntrepriseNotification.vue';

/*import EtablissementFiliere from '../components/EtablissementFiliere.vue';
import EtablissementPartenariat from '../components/EtablissementPartenariat.vue';
import EtablissementSuiviCandidature from '../components/EtablissementSuiviCandidature.vue';
import EtablissementSuiviStage from '../components/EtablissementSuiviStage.vue';
import EtablissementMessagerie from '../components/EtablissementMessagerie.vue';
import EtablissementNotification from '../components/EtablissementNotification.vue';*/
 
import EtudiantCandidature from '../components/EtudiantCandidature.vue';
//import EtudiantRapportStage from '../components/EtudiantRapportStage.vue';
import EtudiantRechercheStage from '../components/EtudiantRechercheStage.vue';
//import EtudiantMessagerie from '../components/EtudiantMessagerie.vue';
import EtudiantNotification from '../components/EtudiantNotification.vue';


//import Communaute from '../components/Communaute.vue';

// Composant UNIQUE pour l'affichage des profils TIERS (étudiant, entreprise, établissement)
import GenericProfileView from '../components/GenericProfileView.vue'; 

// Composants liés aux candidatures
//import CandidaturesOffre from '../components/CandidaturesOffre.vue';
//import CandidaturesDetails from '../components/CandidaturesDetails.vue';


const routes = [
  { path: '/', component: Home },
  { path: '/select-role', component: SelectRole },
  { path: '/complete-profile/:role', component: CompleteProfile },
  { path: '/accueil-user', component: AccueilUser },
  //{ path: '/communaute', component: Communaute, name: 'communaute' },
 


  // Dashboard Entreprise
  { 
    path: '/entreprise-dashboard',
    component: EntrepriseDashboard,
    children: [
      {
        path: '', 
        name: 'entreprise-dashboard-default',
        component: EntrepriseProfile // Profil de L'ENTREPRISE CONNECTÉE (ÉDITABLE)
      },
      { path: '/entreprise-profile', component: EntrepriseProfile, name: 'entreprise-profile' }, 
      { path: '/entreprise-offre', component: EntrepriseOffre, name: 'entreprise-offre' },
      //{ path: '/entreprise-partenariat', component: EntreprisePartenariat, name: 'entreprise-partenariat' },
      { path: '/entreprise-notification', component: EntrepriseNotification, name: 'entreprise-notification' },
      { path: 'offre-details/:id', component: OffreDetails, name: 'entreprise-offre-details', props: true},

      { path: 'messages/:userId?', component: ConversationsList, name: 'entreprise-messagerie',props: true  },


      // ROUTE GÉNÉRIQUE POUR L'AFFICHAGE DES PROFILS TIERS (Étudiant, Entreprise, Établissement)
      // Exemple: /entreprise-dashboard/etudiants/123/profile
      { 
        path: ':type/:id/profile', // ':type' peut être 'etudiants', 'entreprises', 'etablissements'
        name: 'entreprise-generic-profile-view', 
        component: GenericProfileView, 
        props: true // Permet de passer 'type' et 'id' au composant GenericProfileView
      },

      { path: '/entreprise-candidature', component: EntrepriseCandidature, name: 'entreprise-candidature', props: true},
      //{ path: '/entreprise/offres/:offreId/candidatures', component: CandidaturesOffre, name: 'candidatures-pour-offre-entreprise', props: true},
      //{ path: '/entreprise/candidatures/:id/details', component: CandidaturesDetails, name: 'candidature-details-entreprise', props: true},
    ]
  },

  // Dashboard Établissement
  /*{ 
    path: '/etablissement-dashboard', 
    component: EtablissementDashboard,
    children: [ 
      {
        path: '', 
        name: 'etablissement-dashboard-default',
        component: EtablissementProfile // Profil de L'ÉTABLISSEMENT CONNECTÉ (ÉDITABLE)
      },
      { path: '/etablissement-profile', component: EtablissementProfile, name: 'etablissement-profile' }, 
      { path: '/etablissement-filiere', component: EtablissementFiliere, name: 'etablissement-filiere' },
      { path: '/etablissement-partenariat', component: EtablissementPartenariat, name: 'etablissement-partenariat' },
      { path: '/etablissement-suivistage', component: EtablissementSuiviStage, name: 'etablissement-suivistage' }, 
      { path: '/etablissement-suivicandidature', component: EtablissementSuiviCandidature, name: 'etablissement-suivicandidature' }, 

      { path: '/etablissement-notification', component: EtablissementNotification, name: 'etablissement-notification' },
      { path: 'offre-details/:id', component: OffreDetails, name: 'etablissement-offre-details', props: true},


      // ROUTE GÉNÉRIQUE POUR L'AFFICHAGE DES PROFILS TIERS (Étudiant, Entreprise, Établissement)
      // Exemple: /etablissement-dashboard/etudiants/123/profile
      { 
        path: ':type/:id/profile', // ':type' peut être 'etudiants', 'entreprises', 'etablissements'
        name: 'etablissement-generic-profile-view', 
        component: GenericProfileView, 
        props: true 
      },
    ]
  },*/

  // Dashboard Étudiant
  { 
    path: '/etudiant-dashboard', 
    component: EtudiantDashboard,
    children: [ 
      {
        path: '', 
        name: 'etudiant-dashboard-default',
        component: EtudiantProfile // Profil de L'ÉTUDIANT CONNECTÉ (ÉDITABLE)
      },
      { path: '/etudiant-profile', component: EtudiantProfile, name: 'etudiant-profile' }, 
      { path: '/etudiant-candidature', component: EtudiantCandidature, name: 'etudiant-candidature' },
     // { path: '/etudiant-rapportstage', component: EtudiantRapportStage, name: 'etudiant-rapportstage' }, 
      { path: '/etudiant-recherchestage', component: EtudiantRechercheStage, name: 'etudiant-recherchestage' }, 

      { path: '/etudiant-notification', component: EtudiantNotification, name: 'etudiant-notification' },
      { path: 'offre-details/:id', component: OffreDetails, name: 'etudiant-offre-details', props: true},

      { path: 'messages/:userId?', component: ConversationsList, name: 'etudiant-messagerie',props: true  },

      // ROUTE GÉNÉRIQUE POUR L'AFFICHAGE DES PROFILS TIERS (Étudiant, Entreprise, Établissement)
      // Exemple: /etudiant-dashboard/entreprises/123/profile
      { 
        path: ':type/:id/profile', // ':type' peut être 'etudiants', 'entreprises', 'etablissements'
        name: 'etudiant-generic-profile-view', 
        component: GenericProfileView, 
        props: true 
      },
    ]
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
