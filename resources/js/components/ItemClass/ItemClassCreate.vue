<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
          <v-icon left>mdi-tag</v-icon>
          <span class="headline">Create New Item Class</span>
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
              </v-col>
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="category.description"
                  label="Description"
                  outlined
                  dense
                ></v-textarea>
              </v-col>
            </v-row>

            <v-card-actions>
              <v-btn
                color="primary"
                type="submit"
                :loading="loading"
                :disabled="loading"
              >
                Save
              </v-btn>
              <v-btn
                color="warning"
                @click="cancel"
              >
                Cancel
              </v-btn>
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
          name: '',
          description: '',
        },
        nameRules: [
          v => !!v || 'Name is required',
          v => (v && v.length <= 50) || 'Name must be less than 50 characters',
        ],
      };
    },
    methods: {
      async submitForm() {
        if (this.$refs.form.validate()) {
          this.loading = true;
          try {
            // Handle form submission
            console.log('Category data:', this.category);
            // Example: await this.axios.post('/api/categories', this.category);
            this.$router.push('/categories'); // Navigate to categories list page after successful submission
          } catch (error) {
            console.error('Submission error:', error);
          } finally {
            this.loading = false;
          }
        }
      },
      cancel() {
        this.$router.push('/categories'); // Navigate to categories list page on cancel
      },
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
