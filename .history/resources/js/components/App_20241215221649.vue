<template>
    <v-app>
      <v-app-bar
        v-if="!isAuthPage"
        app
        color="#020430"
        dark
        elevation="2"
      >
        <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title></v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon>
          <v-icon>mdi-bell</v-icon>
        </v-btn>
        <v-menu
          v-model="profileMenu"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon v-bind="attrs" v-on="on">
              <v-icon>mdi-account-circle</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="login">
              <v-list-item-icon>
                <v-icon small>mdi-login</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title style="font-size: 14px;">Login</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="register">
              <v-list-item-icon>
                <v-icon small>mdi-account-plus</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title style="font-size: 14px;">Register</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item @click="logout">
              <v-list-item-icon>
                <v-icon small>mdi-logout</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title style="font-size: 14px;">Logout</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-app-bar>

      <v-navigation-drawer
        v-if="!isAuthPage"
        app
        v-model="drawer"
        color="#020430"
        dark
      >
        <v-list dense>
          <v-list-item>
            <v-list-item-avatar>
              <v-icon large>mdi-pos</v-icon>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title class="text-h7 white--text">Logo</v-list-item-title>
            </v-list-item-content>
          </v-list-item>

          <v-divider></v-divider>

          <v-list-item-group>
            <v-list-item
              v-for="item in menuItems"
              :key="item.title"
              :to="item.route"
              link
              v-if="!item.subItems"
            >
              <v-list-item-icon>
                <v-icon>{{ item.icon }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title class="white--text">{{ item.title }}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>

            <v-list-group v-if="item.subItems" v-for="item in menuItems" :key="item.title" :value="item.value">
              <template v-slot:activator>
                <v-list-item-icon>
                  <v-icon>{{ item.icon }}</v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title class="white--text">{{ item.title }}</v-list-item-title>
                </v-list-item-content>
              </template>

              <v-list-item class="mx-4"
                v-for="subItem in item.subItems"
                :key="subItem.title"
                :to="subItem.route"
                link
              >
                <v-list-item-icon>
                  <v-icon small>{{ subItem.icon }}</v-icon>
                </v-list-item-icon>
                <v-list-item-content >
                  <v-list-item-title class="white--text">{{ subItem.title }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-group>
          </v-list-item-group>
        </v-list>
      </v-navigation-drawer>

      <v-main>
        <v-container fluid>
          <div v-if="showAlert">
            <v-alert
              v-if="!isAuthPage"
              class="mx-3"
              type="success"
              dismissible
              border="left"
              colored-border
              elevation="2"
              @input="showAlert = false"
            >
              Login successful! Welcome to User, {{ username }}.
            </v-alert>
          </div>
          <router-view></router-view>
        </v-container>
      </v-main>
    </v-app>
  </template>

  <script>
  export default {
    data() {
      return {
        drawer: true,
        profileMenu: false,
        showAlert: true,
        username: 'User',
        menuItems: [
          { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/' },

          { title: 'Category', icon: 'mdi-cube-outline', route: '/categorylist' },
          { title: 'Item Class', icon: 'mdi-tag', route: '/itemclasslist' },
          { title: 'Warehouse', icon: 'mdi-warehouse', route: '/warehouselist',  subItems: [
            { title: 'Warehouse Transition', icon: 'mdi-transfer', route: '/warehousetransition' },
             ], },
          { title: 'Supplier', icon: 'mdi-truck', route: '/supplierlist' },
          { title: 'Customer', icon: 'mdi-account', route: '/customerlist' },
          {
            title: 'Products',
            icon: 'mdi-cart',
            subItems: [
              { title: 'Update Price', icon: 'mdi-cash', route: '/updateprice' },
              { title: 'Product List', icon: 'mdi-cart-outline', route: '/productlist' },
              { title: 'GRN', icon: 'mdi-receipt', route: '/grn' }

            ],
          },


        ]
      };
    },
    computed: {
      isAuthPage() {
        return this.$route.path === '/login' || this.$route.path === '/register';
      }
    },
    methods: {
      login() {
        this.$router.push('/login');
      },
      register() {
        this.$router.push('/register');
      },
      logout() {
        console.log('Logging out...');
        this.$router.push('/'); // Navigate to home or login page
      }
    }
  };
  </script>

  <style scoped>
  .v-navigation-drawer {
    background-color: #160538;
  }

  .v-list-item-title {
    font-weight: bold;
  }
  </style>
