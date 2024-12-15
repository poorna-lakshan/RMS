<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
          <v-icon left size="24">mdi-truck</v-icon>
          Create New Supplier
        </v-card-title>

        <v-card-title>
  <v-tabs
    v-model="tab"
    slider-color="#f78166"
    class="mb-6"
  >
    <v-tab>
      <v-icon left>mdi-account</v-icon> Supplier Info
    </v-tab>
    <v-tab>
      <v-icon left>mdi-book-open</v-icon> Reference Info
    </v-tab>
  </v-tabs>
</v-card-title>


        <v-card-subtitle>
          <v-tabs-items v-model="tab">
            <!-- Supplier Info Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.supplierId" label="Supplier ID" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.supplierName" label="Supplier Name" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.authorName" label="Author Name" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.companyName" label="Company Name" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.address" label="Address" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.companyPhone" label="Company Phone" type="tel" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="supplier.type" :items="supplierTypes" label="Supplier Type" outlined dense class="mb-4" hide-details="auto"></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-checkbox v-model="supplier.inactive" label="Inactive" color="success" class="mb-4" hide-details="auto"></v-checkbox>
                  </v-col>
                </v-row>
              </v-form>
            </v-tab-item>

            <!-- Reference Info Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.refName" label="Reference Name" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.refMobile" label="Reference Mobile" type="tel" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom1" label="Custom Field 1" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom2" label="Custom Field 2" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom3" label="Custom Field 3" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom4" label="Custom Field 4" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom5" label="Custom Field 5" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="supplier.custom6" label="Custom Field 6" outlined dense class="mb-4" hide-details="auto"></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-tab-item>
          </v-tabs-items>
        </v-card-subtitle>

        <v-card-actions>
          <v-btn color="primary" @click="submitForm" class="ma-2" :loading="loading">Save</v-btn>
          <v-btn color="warning" @click="cancel" class="ma-2">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-container>
  </template>

  <script>
  export default {
    data() {
      return {
        tab: 0,
        loading: false,
        supplier: {
          supplierId: '',
          supplierName: '',
          inactive: false,
          authorName: '',
          companyName: '',
          address: '',
          companyPhone: '',
          type: '',
          refName: '',
          refMobile: '',
          custom1: '',
          custom2: '',
          custom3: '',
          custom4: '',
          custom5: '',
          custom6: '',
        },
        supplierTypes: ['Manufacturer', 'Distributor', 'Retailer', 'Service Provider'],
      };
    },
    methods: {
      async submitForm() {
        this.loading = true;
        try {
          console.log('Supplier data:', this.supplier);
          // Example: this.axios.post('/api/suppliers', this.supplier);
        } catch (error) {
          console.error('Submission error:', error);
        } finally {
          this.loading = false;
        }
      },

      cancel() {
      // Reset the form
      this.supplier = {
        supplierId: '',
        supplierName: '',
        inactive: false,
        authorName: '',
        companyName: '',
        address: '',
        companyPhone: '',
        type: '',
        refName: '',
        refMobile: '',
        custom1: '',
        custom2: '',
        custom3: '',
        custom4: '',
        custom5: '',
        custom6: '',
      };
    },
  },
};
</script>


<style scoped>
  .v-tabs .v-tab {
    font-weight: bold;
    transition: background-color 0.3s;
  }
  .v-tabs .v-tab--active {
    background-color: #e8eaf6;
    color: #1e88e5;
    box-shadow: inset 0 -2px 0 0 #ffeb3b;
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
  .v-card {
    border-radius: 16px;
  }
  </style>
