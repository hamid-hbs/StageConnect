<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <img class="mx-auto h-12 w-auto" src="/toge.png" alt="StageConnect Logo">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Créer un compte
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Ou 
        <router-link to="/connexion" class="font-medium text-blue-600 hover:text-blue-500">
          connectez-vous à votre compte
        </router-link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <!-- Toast notification -->
        <div v-if="toastMessage" id="authToast" class="mb-4" :class="error ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
          <div class="p-3 rounded">
            {{ toastMessage }}
          </div>
        </div>

        <!-- Formulaire d'inscription -->
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
            <div class="mt-1">
              <input id="email" v-model="form.email" name="email" type="email" autocomplete="email" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <div class="mt-1 relative">
              <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" name="password" autocomplete="new-password" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <button type="button" @click="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg v-if="showPassword" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
            <div class="mt-1 relative">
              <input id="password_confirmation" v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              <button type="button" @click="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg v-if="showPasswordConfirmation" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <div>
            <button type="submit" :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Création en cours...
              </span>
              <span v-else>S'inscrire</span>
            </button>
          </div>
        </form>

        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">
                Ou continuer avec
              </span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 gap-3">
            <button @click="handleAuthClick" :disabled="isLoading"
              class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Connexion...
              </span>
              <span v-else class="flex items-center justify-center">
                <img src="/google-icon.png" alt="Google" class="h-5 w-5 mr-2">
                Google
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { signInWithPopup } from 'firebase/auth';
import { auth, googleAuthProvider } from '../firebase';
import axios from '../axios'; 
import { useRouter } from 'vue-router';
import { Toast } from 'bootstrap';

export default {
  name: 'Inscription',
  setup() {
    const router = useRouter();
    const isLoading = ref(false);
    const error = ref(false);
    const toastMessage = ref('');
    const showPassword = ref(false);
    const showPasswordConfirmation = ref(false);
    const form = ref({
      email: '',
      password: '',
      password_confirmation: ''
    });

    const handleSubmit = async () => {
      isLoading.value = true;
      error.value = false;
      toastMessage.value = '';

      // Vérification que les mots de passe correspondent
      if (form.value.password !== form.value.password_confirmation) {
        showToast("Les mots de passe ne correspondent pas.", true);
        isLoading.value = false;
        return;
      }

      try {
        const response = await axios.post('/api/auth/register', {
          email: form.value.email,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation
        });

        const { token, user, next_step } = response.data;

        if (token) {
          localStorage.setItem('authToken', token);
        }
        if (user) {
          localStorage.setItem('currentUser', JSON.stringify(user));
        }

        showToast("Inscription réussie ! Redirection...", false);

        setTimeout(() => {
          router.push(next_step || '/');
        }, 1500);

      } catch (err) {
        console.error("Erreur d'inscription:", err);
        error.value = true;
        let displayMessage = "Une erreur est survenue lors de l'inscription.";

        if (err.response?.data?.errors) {
          // Gestion des erreurs de validation du serveur
          const errors = err.response.data.errors;
          displayMessage = Object.values(errors).flat().join(' ');
        } else if (err.response?.data?.message) {
          displayMessage = err.response.data.message;
        }

        showToast(displayMessage, true);
      } finally {
        isLoading.value = false;
      }
    };

    const handleAuthClick = async () => {
      isLoading.value = true;
      error.value = false;
      toastMessage.value = '';

      try {
        const result = await signInWithPopup(auth, googleAuthProvider);
        const user = result.user;
        const idToken = await user.getIdToken();
        
        const formData = new FormData();
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

        showToast("Authentification réussie ! Redirection...", false);

        setTimeout(() => {
          router.push(next_step);
        }, 1500);

      } catch (err) {
        console.error("Erreur d'authentification:", err);
        error.value = true;
        let displayMessage = "Une erreur est survenue lors de l'authentification.";

        if (err.code) {
          const errorMessages = {
            'auth/popup-closed-by-user': 'Connexion annulée par l\'utilisateur.',
            'auth/popup-blocked': 'La fenêtre pop-up a été bloquée par votre navigateur.',
            'auth/cancelled-popup-request': 'La demande de pop-up a été annulée.',
            'auth/account-exists-with-different-credential': 'Un compte existe déjà avec cette adresse email mais avec un autre fournisseur.',
            'auth/network-request-failed': 'Problème de connexion réseau.',
            'auth/internal-error': 'Erreur interne du serveur d\'authentification.',
          };
          displayMessage = errorMessages[err.code] || displayMessage;
        } else if (err.response?.data?.message) {
          displayMessage = err.response.data.message;
        }

        showToast(displayMessage, true);

        if (auth.currentUser) {
          await auth.signOut();
        }
      } finally {
        isLoading.value = false;
      }
    };

    const showToast = (message, isError) => {
      toastMessage.value = message;
      error.value = isError;
      const toastEl = document.getElementById('authToast');
      if (toastEl) {
        const toast = new Toast(toastEl);
        toast.show();
      }
    };

    const togglePassword = (field) => {
      if (field === 'password') {
        showPassword.value = !showPassword.value;
      } else if (field === 'password_confirmation') {
        showPasswordConfirmation.value = !showPasswordConfirmation.value;
      }
    };

    return {
      isLoading,
      form,
      handleSubmit,
      handleAuthClick,
      toastMessage,
      error,
      showPassword,
      showPasswordConfirmation,
      togglePassword
    };
  }
};
</script>