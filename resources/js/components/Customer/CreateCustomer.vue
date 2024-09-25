<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
          <v-icon left size="24">mdi-account</v-icon>
          Create New Customer
        </v-card-title>

        <v-form ref="form">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.customerId"
                label="Customer ID"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.customerName"
                label="Customer Name"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.address"
                label="Address"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.tel"
                label="Telephone"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.tel2"
                label="Alternate Telephone"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.fax"
                label="Fax"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.email"
                label="Email"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="customer.vatNo"
                label="VAT Number"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="customer.customerType"
                :items="customerTypes"
                label="Customer Type"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>
          </v-row>

          <v-card-actions>
            <v-btn color="primary" @click="submitForm" class="ma-2" :loading="loading">
              Save
            </v-btn>
            <v-btn color="warning" @click="cancel" class="ma-2">
              Cancel
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-container>
  </template>

  <script>
  export default {
    data() {
      return {
        loading: false,
        customer: {
          customerId: '',
          customerName: '',
          address: '',
          tel: '',
          tel2: '',
          fax: '',
          email: '',
          vatNo: '',
          customerType: ''
        },
        customerTypes: [
          'Retail',
          'Wholesale',
          'Corporate'
          // Add more customer types as needed
        ]
      };
    },
    methods: {
      async submitForm() {
        this.loading = true;
        try {
          // Validation logic
          if (this.$refs.form.validate()) {
            console.log('Customer data:', this.customer);
            // Example API call
            // await this.axios.post('/api/customers', this.customer);
          }
        } catch (error) {
          console.error('Submission error:', error);
          // Show error to user
        } finally {
          this.loading = false;
        }
      },
      cancel() {
        // Reset form or navigate away
        this.customer = {
          customerId: '',
          customerName: '',
          address: '',
          tel: '',
          tel2: '',
          fax: '',
          email: '',
          vatNo: '',
          customerType: ''
        };
      }
    }
  };
  </script>

  <style scoped>
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
  .v-card {
    border-radius: 16px;
  }
  </style>
