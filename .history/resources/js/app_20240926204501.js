require('./bootstrap');
import Vue from 'vue';
import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';
import App from './components/App.vue'; // Import the App component
import VueRouter from 'vue-router';
import VueAxios from 'vue-axios';
import axios from 'axios';
import { routes } from './routes';
import '@mdi/font/css/materialdesignicons.css';



Vue.use(VueRouter);
Vue.use(VueAxios, axios);
Vue.use(Vuetify);

const router = new VueRouter({
  mode: 'history',
  routes: routes
});

const vuetify = new Vuetify();

new Vue({
  el: '#app',
  vuetify,
  router,
  render: h => h(App), // Render the App component
});
