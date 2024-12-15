<template>
    <v-container fluid>

        <v-snackbar class="mt-2" v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <v-btn color="white" text @click="snackbar = false">Close</v-btn>
      </v-snackbar>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
          <v-icon left>mdi-cube-outline</v-icon>
          <span class="headline">{{ isEditMode ? 'Edit Category' : 'Create New Category' }}</span>
        </v-card-title>

        <v-card-subtitle>
          <v-form ref="form" v-model="valid" @submit.prevent="submitForm">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="category.name"
                  label="Category Name"
                  required
                  :rules="nameRules"
                  outlined
                  dense
                ></v-text-field>
                <img v-if="images" :src="images" alt="Category Image" style="width: 200px; height: auto; border-radius: 10px;" />

              </v-col>
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="category.description"
                  label="Description"
                  outlined
                  dense
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-file-input
                  v-model="category.image"
                  label="Category Image"
                  accept="image/*"
                  outlined
                  dense
                ></v-file-input>
              </v-col>
            </v-row>



            <v-card-actions>
              <v-btn color="primary" type="submit" :loading="loading">Save</v-btn>
              <v-btn color="warning" @click="cancel">Cancel</v-btn>
            </v-card-actions>
          </v-form>
        </v-card-subtitle>
      </v-card>
    </v-container>
  </template>

  <script>


  export default {
    data() {
      return {
        valid: false,
        loading: false,
        category: {
            id:'',
          name: '',
          description: '',
          image: null,
        },
        images: '',
        nameRules: [
          (v) => !!v || 'Name is required',
          (v) => (v && v.length <= 50) || 'Name must be less than 50 characters',
        ],
        isEditMode: false, // Set default mode
        categoryId: null, // To hold category ID in edit mode
        snackbarColor: 'success',
      };
    },
    methods: {

        async submitForm() {
    this.loading = true; // Start loading

    const formData = new FormData();
    formData.append('name', this.category.name);
    formData.append('description', this.category.description);

    // Append image if it exists
    if (this.category.image) {
        formData.append('image', this.category.image);
    }

    try {
        if (this.category.id) {
            // PUT request for updating an existing category
            const response = await axios.post(`/api/categories/${this.category.id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data', // Important for file uploads

                }
            });
            this.$refs.form.reset()
            this.showSnackbar(response.data.message, 'success');

        } else {
            // POST request for creating a new category
            const response = await axios.post('/api/categories', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data', // Important for file uploads

                }
            });
            this.$refs.form.reset()
            this.showSnackbar(response.data.message, 'success');
        }

        // Redirect after successful operation

    } catch (error) {
        console.error('Submission error:', error);
        this.showSnackbar(response.data.message, 'error');
    } finally {
        this.loading = false; // Stop loading
    }
},

showSnackbar(message, type) {
      this.snackbarText = message;
      this.snackbarColor = type === 'error' ? 'red' : 'success';
      this.snackbar = true;

    },


      cancel() {
        this.$router.push('/categories'); // Navigate to categories list page on cancel
      },
      async fetchCategory(category) {
        this.category.id = category.id;
          this.category.name = category.name;
          this.category.description=category.description
          this.isEditMode = true;
          this.images=category.image_url

      },
    },
    mounted() {
      const category = this.$route.params.category; // Assuming you pass the category ID in the route
      if (category) {
        this.fetchCategory(category);
      }
    },
  };
  </script>

  <style scoped>
  .v-card {
    border-radius: 16px;
  }
  .v-btn {
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 20px;
    padding: 10px 20px;
    transition: background-color 0.3s, transform 0.2s;
  }
  .v-btn:hover {
    background-color: #4527a0;
    transform: scale(1.05);
  }
  .v-btn:focus {
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.5);
  }
  </style>
