<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              color="primary"
              class="v-btn-right custom-font-size"
              v-bind="attrs"
              v-on="on"
              :to="{ name: 'createwarehouse' }"
            >
            <v-icon class="custom-font-size">mdi-plus-circle-outline</v-icon>Create
            </v-btn>
          </template>
          <span>Create Warehouse</span>
        </v-tooltip>

        <v-card-title>
          <v-icon left size="24">mdi-warehouse</v-icon>
          Warehouse List
          <v-spacer></v-spacer>
          <!-- Search Field -->
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Warehouses"
            single-line
            hide-details
          ></v-text-field>
        </v-card-title>

        <!-- Warehouses Table -->
        <v-data-table
          :headers="headers"
          :items="filteredRows"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="edit_row(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="delete_row(item)">
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
        category: {
        id: null,
        description: "",
      },
        headers: [
          { text: 'Warehouse ID', value: 'warehouseId' },
          { text: 'Name', value: 'name' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        is_edit:false,
        rows: [],
        dialog: false,
      };
    },

    computed: {
        filteredRows() {
    return this.rows.filter((category) => {
      const searchTerm = this.search.toLowerCase();
      return (
        (category.code && typeof category.code === 'string' &&
         category.code.toLowerCase().includes(searchTerm)||
         category.name && typeof category.name === 'string' &&
         category.name.toLowerCase().includes(searchTerm)
        
        )
         
      );
    });
  },
    },

   
    methods: {
      async fetch() {
        this.loading = true;
        try {
          const response = await axios.get('/api/warehouse'); // Ensure this endpoint is correct
          this.rows = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          console.error('Error fetching categories:', error);
          this.rows = [];
        } finally {
          this.loading = false;
        }
      },

      async create() {
 
      },

      async  edit_row(category) {
        this.is_edit=true;
        this.dialog = true;
        this.category.id =category.id;
        this.category.description = category.description;
      },
      delete_row(row) {
      axios.delete(`/api/warehouse/${row.id}`)
        .then(response => {
            this.rows = this.rows.filter((c) => c.id !== row.id);
            this.$toastr.e(response.data.message);
        })
        .catch(error => {
          this.$toastr.e(response.data.message);
        });
      },

   
    },

    mounted() {
      this.fetch(); 
    },

  };
  </script>

  <style scoped>

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
  .v-btn--bottom-right {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  .custom-font-size {
    font-size: 12px; /* Set the font size you prefer */
  
  }
  </style>
