<template>
    <v-container fluid>
        <v-snackbar class="mt-2" v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <v-btn color="white" text @click="snackbar = false">Close</v-btn>
      </v-snackbar>
      <v-card class="pa-6 elevation-12">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              fab
              color="primary"
              class="v-btn--bottom-right"
              v-bind="attrs"
              v-on="on"
              :to="{ name: 'categorycreate' }"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
          <span>Create Category</span>
        </v-tooltip>

        <v-card-title>
          <v-icon left size="24">mdi-cube-outline</v-icon>
          Category List
          <v-spacer></v-spacer>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Categories"
            single-line
            hide-details
          ></v-text-field>
        </v-card-title>

        <v-data-table
          :headers="headers"
          :items="filteredCategories"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="editCategory(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteCategory(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
    </v-container>
  </template>

  <script>


  export default {
    data() {
      return {
        search: '',
        loading: false,
        headers: [

          { text: 'Name', value: 'name' },
          { text: 'Description', value: 'description' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        categories: [],
        snackbarColor: 'success',
      };
    },
    computed: {
        filteredCategories() {
    return this.categories.filter((category) => {
      const searchTerm = this.search.toLowerCase();
      return (
        (category.categoryId && typeof category.categoryId === 'string' &&
         category.categoryId.toLowerCase().includes(searchTerm)) ||
        (category.name && typeof category.name === 'string' &&
         category.name.toLowerCase().includes(searchTerm)) ||
        (category.description && typeof category.description === 'string' &&
         category.description.toLowerCase().includes(searchTerm))
      );
    });
  },
    },
    methods: {
      async fetchCategories() {
        this.loading = true;
        try {
          const response = await axios.get('/api/categories'); // Ensure this endpoint is correct
          console.log('Fetched categories:', response.data);
          this.categories = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          console.error('Error fetching categories:', error);
          this.categories = [];
        } finally {
          this.loading = false;
        }
      },
      editCategory(category) {
        console.log('Editing category:', category);
        this.$router.push({ name: 'categorycreate', params: { category } });
      },
      deleteCategory(category) {
    axios.delete(`/api/categories/${category.id}`)
        .then(response => {
            this.categories = this.categories.filter((c) => c.id !== category.id);
            this.showSnackbar(response.data.message, 'success'); // Adjust to show message
        })
        .catch(error => {
            console.error(response.data.message, error);
        });
},

      showSnackbar(message, type) {
      this.snackbarText = message;
      this.snackbarColor = type === 'error' ? 'red' : 'success';
      this.snackbar = true;
    },
    },
    mounted() {
      this.fetchCategories(); // Fetch categories when the component mounts
    },
  };
  </script>

  <style scoped>
  .v-data-table {
    background-color: #f5f5f5;
  }
  .v-data-table-header th {
    background-color: #4a148c;
    color: white;
  }
  .v-card-title {
    font-weight: bold;
    background-color: #4a148c;
    color: white;
  }
  .v-text-field input {
    background-color: #fff;
    border-radius: 30px;
    padding: 10px 20px;
  }
  .v-card {
    border-radius: 16px;
  }
  </style>
