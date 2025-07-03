<template>
  <div class="container" style="margin-top: 10%">
    <div class="main-content">
      <div v-if="isCheckingExistingUser" class="status-message">
        <div class="spinner"></div>
        <p>Vérification de votre compte...</p>
      </div>

      <div v-else-if="!user">
        <h3
          class="fw-bold mb-4 text-center"
          style="text-decoration: underline; text-decoration-color: #0d6efd"
        >
          Authentification requise
        </h3>
        <p class="error-message">
          Veuillez vous connecter avec Google depuis la page d'accueil.
        </p>
        <div class="d-grid gap-2 mt-4">
          <router-link to="/">
            <button class="btn btn-secondary w-100" type="button">Retour à l'accueil</button>
          </router-link>
        </div>
      </div>

      <div v-else-if="user && !userHasRole">
        <h3
          class="fw-bold mb-4 text-center"
          style="text-decoration: underline; text-decoration-color: #0d6efd"
        >
          Bienvenue, {{ user.displayName || user.email }} !
        </h3>
        <label class="form-label fw-bold mb-4 text-center"
          >Qui êtes-vous ? <span style="color: red">*</span></label
        >
        <div class="role-options">
          <label
            v-for="role in ['etudiant', 'entreprise']"
            :key="role"
            class="role-option"
          >
            <input
              type="radio"
              :value="role"
              v-model="communaute"
              @change="errorCommunaute = false"
            />
            {{ role.charAt(0).toUpperCase() + role.slice(1) }}
          </label>
          <p v-if="errorCommunaute" class="error-message">
            Veuillez choisir une option.
          </p>
        </div>

        <div class="d-grid gap-2">
          <button
            @click="submitRole"
            class="btn btn-primary"
            type="button"
            :disabled="isLoading || !communaute"
          >
            {{ isLoading ? 'Soumission...' : 'Continuer' }}
          </button>
        </div>
      </div>

      <div v-if="connexionMessage" class="status-message">
        <p class="mt-3 text-center success-text">{{ connexionMessage }}</p>
      </div>
      <div v-if="error" class="error mt-3 text-center">{{ error }}</div>
    </div>
  </div>
</template>

<script>
import { onAuthStateChanged, signOut } from 'firebase/auth';
import { auth } from '../firebase';
import axios from '../axios';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      user: null,
      communaute: '',
      userHasRole: false,
      errorCommunaute: false,
      connexionMessage: '',
      error: '',
      isLoading: false,
      isCheckingExistingUser: false,
    };
  },
  async mounted() {
    this.isCheckingExistingUser = true;
    this.error = '';

    onAuthStateChanged(auth, async (user) => {
      this.user = user;

      if (user) {
        try {
          const response = await axios.post(
            `/api/auth/google/firebase-callback`,
            {},
            {
              headers: {
                Accept: 'application/json'
              }
            }
          );

          const { role, next_step } = response.data; // Le token est géré par l'intercepteur Axios

          if (role) {
            this.userHasRole = true;
            this.connexionMessage = `✅ Votre rôle est déjà défini comme ${role}. Redirection vers votre tableau de bord...`;
            console.log(`SelectRole.vue: Rôle trouvé (${role}). Redirection vers ${next_step || `/${role}-dashboard`}`);
            setTimeout(() => this.router.push(next_step || `/${role}-dashboard`), 1500);
          } else {
            this.userHasRole = false;
            this.connexionMessage = "Veuillez choisir votre communauté pour continuer votre inscription.";
            console.log("SelectRole.vue: Utilisateur sans rôle défini. Affichage des options de rôle.");
          }
        } catch (axiosError) {
          console.error("SelectRole.vue: Erreur lors de la vérification du rôle via Laravel:", axiosError);
          let errorMessage = "Impossible de récupérer votre statut. Veuillez réessayer.";

          if (axios.isAxiosError(axiosError) && axiosError.response) {
            errorMessage = axiosError.response.data.message || errorMessage;
            if (axiosError.response.status === 401) {
              errorMessage = "Votre session est invalide ou a expiré. Veuillez vous reconnecter.";
              // La déconnexion Firebase est gérée par l'intercepteur Axios.
              // Ici, on met juste à jour l'état local.
            } else if (axiosError.response.status === 400) {
              errorMessage = axiosError.response.data.message || "Requête invalide envoyée au serveur. Vérifiez les logs backend.";
            }
          } else {
            errorMessage = `Erreur réseau ou inconnue: ${axiosError.message || 'inconnue'}`;
          }
          this.error = errorMessage;
          this.user = null;
          this.userHasRole = false;
        }
      } else {
        this.user = null;
        this.userHasRole = false;
        this.error = "Vous devez être connecté pour accéder à cette page.";
        console.log("SelectRole.vue: Aucun utilisateur Firebase connecté. Affichage du message d'authentification requise.");
      }
      this.isCheckingExistingUser = false;
    });
  },
  methods: {
    async submitRole() {
      this.errorCommunaute = this.connexionMessage = this.error = '';

      if (!this.communaute) {
        this.errorCommunaute = true;
        this.error = 'Veuillez choisir votre communauté avant de continuer.';
        return;
      }

      this.isLoading = true;

      try {
        const user = auth.currentUser;
        if (!user) {
          this.error = 'Aucun utilisateur connecté pour soumettre un rôle. Veuillez vous authentifier.';
          this.isLoading = false;
          return;
        }

        const response = await axios.post(
          `/api/user/set-role`,
          {
            role: this.communaute,
          },
          {
            headers: {
              Accept: 'application/json'
            }
          }
        );

        const { next_step } = response.data; // Le token est géré par l'intercepteur Axios

        this.connexionMessage = '✅ Rôle enregistré avec succès. Redirection...';
        console.log(`SelectRole.vue: Rôle (${this.communaute}) enregistré. Redirection vers ${next_step || `/complete-profile/${this.communaute}`}`);
        setTimeout(() => {
          this.router.push(next_step || `/complete-profile/${this.communaute}`);
        }, 1500);

      } catch (axiosError) {
        console.error('SelectRole.vue: Erreur lors de la soumission du rôle :', axiosError);
        let errorMessage = 'Une erreur est survenue lors de l\'enregistrement de votre rôle.';

        if (axios.isAxiosError(axiosError) && axiosError.response) {
          errorMessage = axiosError.response.data.message || errorMessage;
          if (axiosError.response.status === 401) {
            errorMessage = "Votre session a expiré ou est invalide. Veuillez vous reconnecter.";
            // La déconnexion Firebase est gérée par l'intercepteur Axios.
            // Ici, on met juste à jour l'état local.
          } else if (axiosError.response.status === 400 && axiosError.response.data.message.includes('déjà défini')) {
            errorMessage = axiosError.response.data.message;
            const existingRole = axiosError.response.data.role;
            const existingNextStep = axiosError.response.data.next_step;
            this.connexionMessage = `Votre rôle est déjà défini comme ${existingRole}. Redirection...`;
            console.log(`SelectRole.vue: Rôle déjà défini (${existingRole}). Redirection vers ${existingNextStep || `/${existingRole}-dashboard`}`);
            setTimeout(() => this.router.push(existingNextStep || `/${existingNextStep}-dashboard`), 1500);
            return;
          } else if (axiosError.response.status === 400) {
              errorMessage = axiosError.response.data.message || "Requête invalide envoyée au serveur. Vérifiez les logs backend.";
          }
        } else {
          errorMessage = `Erreur: ${axiosError.message || 'inconnue'}`;
        }
        this.error = errorMessage;
        // On assure juste que l'état local du composant reflète un utilisateur non connecté.
        this.user = null;
        this.userHasRole = false;
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 80vh;
}
.main-content {
  max-width: 500px;
  width: 100%;
  padding: 30px;
  border-radius: 8px;
  background: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
}

.error,
.error-message {
  color: #dc3545;
  font-size: 14px;
  text-align: center;
  margin-top: 10px;
}

.success-message,
.status-message {
  text-align: center;
  padding: 20px;
}

.success-text {
  color: #198754;
}

.role-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 15px;
  margin-bottom: 25px;
}

.role-option {
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: 0.3s;
}

.role-option:hover,
.role-option:has(input:checked) {
  border-color: #0d6efd;
  background: #f8f9ff;
}

.role-option input {
  margin-right: 12px;
  transform: scale(1.2);
}

.btn-primary {
  padding: 12px 20px;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s;
}

.btn-secondary {
  padding: 12px 20px;
  font-weight: 500;
  border: 1px solid #6c757d;
  border-radius: 6px;
  background-color: #6c757d;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.3s;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #5a6268;
  border-color: #545b62;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #0d6efd;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 20px;
    margin: 0 15px;
  }
}
</style>