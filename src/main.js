import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// Importation du CSS de Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';

// Importation du JavaScript de Bootstrap.
// Il est préférable d'importer le module principal de Bootstrap
// et de laisser Vue gérer l'inclusion dans le bundle.
// Cela garantit que les composants JS de Bootstrap sont correctement initialisés.
import * as bootstrap from 'bootstrap'; // Importez tous les modules de Bootstrap

import '@fortawesome/fontawesome-free/css/all.min.css';
import './axios'; // C'est crucial pour que l'intercepteur soit configuré !

const app = createApp(App);

app.use(router);
app.mount('#app');

// Optionnel : Vous pouvez rendre l'objet Bootstrap disponible globalement
// si certains de vos composants ont besoin d'y accéder directement,
// mais ce n'est généralement pas nécessaire pour les data-attributes.
// app.config.globalProperties.$bootstrap = bootstrap;