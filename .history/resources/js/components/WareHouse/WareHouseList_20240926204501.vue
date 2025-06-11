<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              fab
              color="primary"
              class="v-btn-right"
              v-bind="attrs"
              v-on="on"
              :to="{ name: 'createwarehouse' }"
            >
              <v-icon>mdi-plus</v-icon>
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
          :items="filteredWarehouses"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="editWarehouse(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteWarehouse(item)">
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
        search: '', // Search query
        loading: false, // Loading state
        headers: [
          { text: 'Warehouse ID', value: 'warehouseId' },
          { text: 'Name', value: 'name' },
          { text: 'A/P Account', value: 'apAccount' },
          { text: 'A/R Account', value: 'arAccount' },
          { text: 'Cash Account', value: 'cashAccount' },
          { text: 'Sales Account', value: 'salesAccount' },
          { text: 'Cost of Sales Account', value: 'costOfSalesAccount' },
          { text: 'Inventory Account', value: 'inventoryAccount' },
          { text: 'Default Warehouse', value: 'defaultWarehouse' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        warehouses: [
          {
            warehouseId: 'W001',
            name: 'Warehouse 1',
            apAccount: 'Account 1',
            arAccount: 'Account 2',
            cashAccount: 'Account 3',
            salesAccount: 'Account 4',
            costOfSalesAccount: 'Account 5',
            inventoryAccount: 'Account 6',
            defaultWarehouse: true
          },
          // Add more warehouse objects here
        ],
      };
    },
    computed: {
      filteredWarehouses() {
        return this.warehouses.filter((warehouse) => {
          return (
            warehouse.warehouseId.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.name.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.apAccount.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.arAccount.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.cashAccount.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.salesAccount.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.costOfSalesAccount.toLowerCase().includes(this.search.toLowerCase()) ||
            warehouse.inventoryAccount.toLowerCase().includes(this.search.toLowerCase())
          );
        });
      },
    },
    methods: {
      editWarehouse(warehouse) {
        console.log('Editing warehouse:', warehouse);
      },
      deleteWarehouse(warehouse) {
        console.log('Deleting warehouse:', warehouse);
        this.warehouses = this.warehouses.filter((w) => w.warehouseId !== warehouse.warehouseId);
      },
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
  .v-btn--bottom-right {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  </style>
