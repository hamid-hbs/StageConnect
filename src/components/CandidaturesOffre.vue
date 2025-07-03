<template>
    <div class="container">
        <h2>Candidatures pour l'offre : {{ offre.titre || 'Chargement...' }}</h2>
        <p v-if="loading" class="info-message">Chargement des candidatures...</p>
        <p v-if="error" class="error-message">Erreur : {{ error }}</p>

        <div v-if="!loading && !error">
            <div v-if="candidatures.length > 0">
                <div v-for="candidature in candidatures" :key="candidature.id" class="candidature-card">
                    <h3>Candidature de :
                        <strong>{{ candidature.etudiant ? (candidature.etudiant.nom_etudiant || candidature.etudiant.user.name) : 'Étudiant inconnu' }}</strong>
                    </h3>
                    <p>Matricule : {{ candidature.etudiant ? candidature.etudiant.matricule : 'N/A' }}</p>
                    <p>
                        Filière/Année :
                        <span v-if="candidature.etudiant && candidature.etudiant.filiere && candidature.etudiant.filannee && candidature.etudiant.filannee.annee">
                            {{ candidature.etudiant.filiere.libfil }} - {{ candidature.etudiant.filannee.annee.libannee }}
                        </span>
                        <span v-else-if="candidature.etudiant && candidature.etudiant.filiere">
                            {{ candidature.etudiant.filiere.libfil }}
                        </span>
                        <span v-else>N/A</span>
                    </p>
                    <p>Statut : <strong :class="getStatusClass(candidature.statut)">{{ getStatusText(candidature.statut) }}</strong></p>
                    <p>Date de candidature : {{ new Date(candidature.date_candidature).toLocaleDateString() }}</p>

                    <div class="card-actions">
                        <template v-if="candidature.statut === 'envoyee' || candidature.statut === 'traitee'">
                            <button @click="accepterCandidature(candidature.id)" class="btn btn-success">Accepter</button>
                            <button @click="refuserCandidature(candidature.id)" class="btn btn-danger">Refuser</button>
                        </template>

                        <p v-else-if="candidature.statut === 'acceptee_entreprise'" class="info-message-inline">
                            En attente de la confirmation de l'étudiant.
                        </p>
                        <p v-else-if="candidature.statut === 'confirmee_etudiant'" class="success-message-inline">
                            Candidature confirmée par l'étudiant. Stage créé.
                        </p>
                        <p v-else-if="candidature.statut === 'desistement_demande'" class="warning-message-inline">
                            Désistement demandé par l'étudiant (En attente établissement).
                            <br> Motif : {{ candidature.motif_desistement }}
                        </p>
                        <p v-else-if="candidature.statut === 'desistement_valide'" class="info-message-inline">
                            Désistement validé par l'établissement.
                        </p>
                        <p v-else-if="candidature.statut === 'desistement_refuse'" class="error-message-inline">
                            Désistement refusé par l'établissement. Stage maintenu.
                            <br> Motif : {{ candidature.motif_desistement_refus }}
                        </p>
                        <p v-else-if="candidature.statut === 'refusee_entreprise'" class="error-message-inline">
                            Candidature refusée par l'entreprise.
                            <span v-if="candidature.motif_refus_entreprise">Motif : {{ candidature.motif_refus_entreprise }}</span>
                        </p>
                        <a v-if="candidature.CV" :href="candidature.CV" target="_blank" class="btn btn-primary">Voir CV</a>
                    </div>
                </div>
            </div>
            <p v-else class="info-message">Aucune candidature trouvée pour cette offre.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const offreId = ref(route.params.offreId); // Récupère l'ID de l'offre depuis l'URL

const offre = ref({});
const candidatures = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchCandidaturesPourOffre = async () => {
    loading.value = true;
    error.value = null;
    try {
        // Cet endpoint doit exister dans votre API Laravel et charger les relations nécessaires
        // (etudiant.user, etudiant.filiere, etudiant.filannee.annee pour chaque candidature)
        const response = await axios.get(`/offres/${offreId.value}/candidatures`);
        candidatures.value = response.data.candidatures;
        offre.value = response.data.offre; // L'API devrait retourner les détails de l'offre aussi
    } catch (err) {
        console.error(`Erreur lors du chargement des candidatures pour l'offre ${offreId.value} :`, err);
        error.value = 'Impossible de charger les candidatures pour cette offre.';
    } finally {
        loading.value = false;
    }
};

const accepterCandidature = async (idCandidature) => {
    if (confirm('Êtes-vous sûr de vouloir accepter cette candidature ? L\'étudiant va être notifié.')) {
        try {
            await axios.post(`/candidatures/${idCandidature}/accepter-entreprise`);
            alert('Candidature acceptée ! L\'étudiant va être notifié.');
            fetchCandidaturesPourOffre(); // Rafraîchir
        } catch (err) {
            console.error('Erreur lors de l\'acceptation de la candidature :', err);
            alert('Erreur lors de l\'acceptation de la candidature.');
        }
    }
};

const refuserCandidature = async (idCandidature) => {
    const motif = prompt('Veuillez entrer un motif de refus (facultatif) :');
    if (confirm('Êtes-vous sûr de vouloir refuser cette candidature ?')) {
        try {
            await axios.post(`/candidatures/${idCandidature}/refuser-entreprise`, { motif_refus: motif });
            alert('Candidature refusée.');
            fetchCandidaturesPourOffre(); // Rafraîchir
        } catch (err) {
            console.error('Erreur lors du refus de la candidature :', err);
            alert('Erreur lors du refus de la candidature.');
        }
    }
};

// Fonctions utilitaires pour le style et le texte du statut (copiées de EntrepriseCandidature.vue pour cohérence)
const getStatusClass = (status) => {
    switch (status) {
        case 'envoyee': return 'status-pending';
        case 'traitee': return 'status-processing';
        case 'acceptee_entreprise': return 'status-accepted';
        case 'confirmee_etudiant': return 'status-confirmed';
        case 'desistement_demande': return 'status-warning';
        case 'desistement_valide': return 'status-info';
        case 'desistement_refuse': return 'status-error';
        case 'refusee_entreprise': return 'status-rejected';
        default: return '';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'envoyee': return 'Envoyée';
        case 'traitee': return 'En cours de traitement';
        case 'acceptee_entreprise': return 'Acceptée par l\'entreprise';
        case 'confirmee_etudiant': return 'Confirmée par l\'étudiant (Stage créé)';
        case 'desistement_demande': return 'Désistement demandé';
        case 'desistement_valide': return 'Désistement validé';
        case 'desistement_refuse': return 'Désistement refusé';
        case 'refusee_entreprise': return 'Refusée par l\'entreprise';
        default: return status;
    }
};

onMounted(fetchCandidaturesPourOffre);
watch(offreId, fetchCandidaturesPourOffre); // Recharger si l'ID de l'offre change dans l'URL
</script>

<style scoped>
.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2em;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
}

.info-message, .error-message, .success-message, .warning-message {
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.info-message { background-color: #e7f3fe; color: #0056b3; border: 1px solid #bce8f1; }
.error-message { background-color: #fbeaea; color: #dc3545; border: 1px solid #f5c6cb; }
.success-message { background-color: #eafbe7; color: #28a745; border: 1px solid #c3e6cb; }
.warning-message { background-color: #fff9e6; color: #856404; border: 1px solid #ffeeba; }

/* Messages "inline" pour les p dans card-actions */
.info-message-inline, .error-message-inline, .success-message-inline, .warning-message-inline {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 0.9em;
}
.info-message-inline { background-color: #e7f3fe; color: #0056b3; border: 1px solid #bce8f1; }
.error-message-inline { background-color: #fbeaea; color: #dc3545; border: 1px solid #f5c6cb; }
.success-message-inline { background-color: #eafbe7; color: #28a745; border: 1px solid #c3e6cb; }
.warning-message-inline { background-color: #fff9e6; color: #856404; border: 1px solid #ffeeba; }


.candidature-card {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.candidature-card h3 {
    color: #007bff;
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.5em;
}

.candidature-card p {
    margin: 0;
    line-height: 1.6;
    color: #555;
}

.candidature-card strong {
    font-weight: 600;
}

.card-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s ease, transform 0.2s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}
.btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
}

.btn-success {
    background-color: #28a745;
    color: white;
}
.btn-success:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}
.btn-danger:hover {
    background-color: #c82333;
    transform: translateY(-1px);
}

/* Styles pour les statuts */
.status-pending { color: #ffc107; font-weight: bold; }
.status-processing { color: #17a2b8; font-weight: bold; }
.status-accepted { color: #007bff; font-weight: bold; }
.status-confirmed { color: #28a745; font-weight: bold; }
.status-warning { color: #ffc107; font-weight: bold; }
.status-info { color: #17a2b8; font-weight: bold; }
.status-error { color: #dc3545; font-weight: bold; }
.status-rejected { color: #6c757d; font-weight: bold; }
</style>