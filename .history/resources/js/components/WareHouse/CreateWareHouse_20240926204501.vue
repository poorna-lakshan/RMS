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
                v-model="warehouse.id"
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
                v-model="warehouse.name"
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
                v-model="warehouse.apAccount"
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
                v-model="warehouse.arAccount"
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
                v-model="warehouse.cashAccount"
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
                v-model="warehouse.salesAccount"
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
                v-model="warehouse.costOfSalesAccount"
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
                v-model="warehouse.inventoryAccount"
                :items="accounts"
                label="Inventory Account"
                outlined
                dense
                class="mb-4"
                hide-details="auto"
                required
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-checkbox
                v-model="warehouse.defaultWarehouse"
                label="Default Warehouse"
                color="primary"
                class="mb-4"
                hide-details="auto"
              ></v-checkbox>
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
        warehouse: {
          id: '',
          name: '',
          apAccount: '',
          arAccount: '',
          cashAccount: '',
          salesAccount: '',
          costOfSalesAccount: '',
          inventoryAccount: '',
          defaultWarehouse: false
        },
        accounts: [
          'Account 1',
          'Account 2',
          'Account 3'
          // Add more account options as needed
        ]
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
          const response = await axios.post("/api/warehouses", this.warehouse);
          console.log("New warehouse created:", response.data);
        }
        // Reset form or navigate away
      } catch (error) {
        console.error("Error saving warehouse:", error);
      } finally {
        this.loading = false;
      }
    },
      cancel() {
        // Reset form or navigate away
        this.warehouse = {
          id: '',
          name: '',
          apAccount: '',
          arAccount: '',
          cashAccount: '',
          salesAccount: '',
          costOfSalesAccount: '',
          inventoryAccount: '',
          defaultWarehouse: false
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
