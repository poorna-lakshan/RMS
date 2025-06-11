<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <template>
  <v-row>
    <v-dialog
      v-model="dialog"
      persistent
      max-width="600px"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn
              color="primary"
              class="v-btn--bottom-right custom-font-size"
              v-bind="attrs"
              v-on="on"
        >
        <v-icon class="custom-font-size">mdi-plus-circle-outline</v-icon>Create
        </v-btn>
      </template>
    
      <v-card>
        <v-card-title>
          <span class="text-h8">Create Category</span>
        </v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
            
              <v-text-field
                  v-model="category.description"
                  label="Category Name"
                  required
                  outlined
                  dense
                ></v-text-field>
             
            
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="blue darken-1"
            text
            @click="dialog = false;clear()"
          >
            Close
          </v-btn>
          <v-btn
            color="blue darken-1"
            text
            @click="createCategory()"
          >
            Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

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
        category: {
        id: null,
        description: "",
      },
        headers: [

          { text: 'id', value: 'id' },
          { text: 'Description', value: 'description' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        is_edit:false,
        categories: [],
        dialog: false,
      };
    },
    computed: {
        filteredCategories() {
    return this.categories.filter((category) => {
      const searchTerm = this.search.toLowerCase();
      return (
        (category.description && typeof category.description === 'string' &&
         category.description.toLowerCase().includes(searchTerm))
      );
    });
  },
    },
    methods: {

      clear(){
        this.is_edit=false;
        this.category= {
        id: null,
        description: "",
         }
      },
      async fetchCategories() {
        this.loading = true;
        try {
          const response = await axios.get('/api/categories'); // Ensure this endpoint is correct
          this.categories = Array.isArray(response.data) ? response.data : [];
          console.log('Fetched categories:', this.categories);
        } catch (error) {
          console.error('Error fetching categories:', error);
          this.categories = [];
        } finally {
          this.loading = false;
        }
      },

      async createCategory() {
 
         if(this.is_edit){
          this.dialog = false;
          axios
        .put(`/api/categories/${this.category.id}`, this.category)
        .then((response) => {
          // console.log(response.data.message);
          if (response.data == false) {
            this.$toastr.w("No data Found", "Error");
          } else {
            this.$toastr.s(response.data.message);
          }

          this.clear();
          this.fetchCategories();
        })
        .catch((error) => {
          console.error(error);
          this.$toastr.e(error.message, "Error");
        });

      
         }
         else
         {
          this.dialog = false;
         axios
        .post('/api/categories', this.category)
        .then((response) => {
          if (response.data == false) {
            this.$toastr.w("No data Found", "Error");
          } else {
            this.$toastr.s(response.data.message);
          }

          this.clear();
          this.fetchCategories();
        })
        .catch((error) => {
          console.error(error);
          this.$toastr.e(error.message, "Error");
        });

        
         }
           
         
   
      },

      async  editCategory(category) {
        this.is_edit=true;
        this.dialog = true;
        this.category.id =category.id;
        this.category.description = category.description;
      },
      deleteCategory(category) {
  if (confirm(`Are you sure you want to delete category "${category.description}"?`)) {
    axios.delete(`/api/categories/${category.id}`)
      .then(response => {
        this.categories = this.categories.filter((c) => c.id !== category.id);
        this.$toastr.s(response.data.message); // Changed to 's' for success
      })
      .catch(error => {
        this.$toastr.e(error.response?.data?.message || 'Delete failed.');
      });
  }
},

   
    },
    mounted() {
      this.fetchCategories(); // Fetch categories when the component mounts
    },
  };
  </script>

  <style scoped>

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


.custom-font-size {
  font-size: 12px; /* Set the font size you prefer */

}

  </style>
