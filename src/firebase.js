// src/firebase.js

// Importation des fonctions nécessaires des SDK Firebase
import { initializeApp } from 'firebase/app';
// --- MODIFIED: Added onAuthStateChanged and signOut ---
import { getAuth, GoogleAuthProvider, onAuthStateChanged, signOut } from 'firebase/auth';

// Configuration Firebase
const firebaseConfig = {
  apiKey: "AIzaSyDSgq9-MnGFxMEGOGj2YPv-nHZOa4zmvuQ",
  authDomain: "stageconnect-3ad71.firebaseapp.com",
  projectId: "stageconnect-3ad71",
  storageBucket: "stageconnect-3ad71.firebasestorage.app",
  messagingSenderId: "631693366209",
  appId: "1:631693366209:web:9b16227430f97cc2d19540",
  measurementId: "G-2L9ZFT56V2" // Optional
};

// 1. Initialisation de l'application Firebase
const app = initializeApp(firebaseConfig);

// 2. Obtention de l'instance du service d'authentification
const auth = getAuth(app);

// 3. Configuration du fournisseur d'authentification Google
const googleAuthProvider = new GoogleAuthProvider();

// 4. Forcer la sélection du compte Google à chaque fois
googleAuthProvider.setCustomParameters({
  prompt: 'select_account'
});

export {
  app,                  // L'instance de l'application Firebase
  auth,                 // L'objet d'authentification Firebase
  googleAuthProvider,   // Le fournisseur Google (pour signInWithPopup)
  // --- MODIFIED: Added onAuthStateChanged and signOut to exports ---
  onAuthStateChanged,   // Pour observer les changements d'état d'authentification
  signOut               // Pour déconnecter les utilisateurs
};