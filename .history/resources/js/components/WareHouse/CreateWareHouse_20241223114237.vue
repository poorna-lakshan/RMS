<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12 ">
        <v-card-title>
          <v-icon left size="24">mdi-warehouse</v-icon>
          Create New Warehouse
        </v-card-title>

        <v-form ref="form">
          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form_data.code"
                label="Warehouse ID"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form_data.name"
                label="Name"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.ap_acc"
                :items="accounts"
                label="A/P Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.ar_acc"
                :items="accounts"
                label="A/R Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.cash_acc"
                :items="accounts"
                label="Cash Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.sales_acc"
                :items="accounts"
                label="Sales Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.cos_acc"
                :items="accounts"
                label="Cost of Sales Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form_data.inv_acc"
                :items="accounts"
                label="Inventory Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>
          </v-row>

          <v-col cols="12" md="6">
              <v-select
                v-model="form_data.price_level"
                :items="price_levels"
                label="PriceLevel   "
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

          <v-card-actions>
            <v-btn color="primary" @click="submitForm" class="ma-2" :loading="loading">
              Save
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
        form_data: {
          id: 0,
          code: 0,
          name: '',
          ap_acc: '',
          ar_acc: '',
          cash_acc: '',
          sales_acc: '',
          cos_acc: '',
          inv_acc: '',
          price_level:'',
        },
        accounts: [
          'Account 1',
          'Account 2',
          'Account 3'
        ],
        price_levels: [
          'price_level1',
          'price_level2',
          'price_level3'
        ],
        isEditMode:false,
      };
    },
    methods: {
        async submitForm() {
      this.loading = true;
      try {
        if (this.isEditMode) {
          // Update existing warehouse
          await axios.put(`/api/warehouses/${this.warehouseId}`, this.warehouse);
          console.log("Warehouse updated:", this.warehouse);
        } else {
          // Create new warehouse
          const response = await axios.post("/api/warehouse", this.warehouse);
          console.log("New warehouse created:", response.data);
        }
        // Reset form or navigate away
      } catch (error) {
        console.error("Error saving warehouse:", error);
      } finally {
        this.loading = false;
      }
    },
      
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
