<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <!-- Create Supplier Button with Tooltip -->
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              fab
              color="primary"
              class="v-btn--bottom-right"
              v-bind="attrs"
              v-on="on"
              :to="{ name: 'createsupplier' }"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
          <span>Create Supplier</span>
        </v-tooltip>

        <!-- Supplier List Title and Search -->
        <v-card-title>
          <v-icon left size="24">mdi-truck</v-icon>
          Supplier List
          <v-spacer></v-spacer>
          <!-- Search Field -->
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Suppliers"
            single-line
            hide-details
          ></v-text-field>
        </v-card-title>

        <!-- Suppliers Table -->
        <v-data-table
          :headers="headers"
          :items="filteredSuppliers"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="editSupplier(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteSupplier(item)">
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
          { text: 'Supplier ID', value: 'supplierId' },
          { text: 'Supplier Name', value: 'supplierName' },
          { text: 'Company Name', value: 'companyName' },
          { text: 'Company Phone', value: 'companyPhone' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        suppliers: [
          {
            supplierId: 'S001',
            supplierName: 'ABC Suppliers',
            companyName: 'ABC Corp',
            companyPhone: '123-456-7890',
          },
          {
            supplierId: 'S002',
            supplierName: 'XYZ Distributors',
            companyName: 'XYZ Ltd',
            companyPhone: '098-765-4321',
          },
          {
            supplierId: 'S003',
            supplierName: 'Global Supplies',
            companyName: 'Global Inc',
            companyPhone: '555-123-4567',
          },
        ],
      };
    },
    computed: {
      filteredSuppliers() {
        return this.suppliers.filter((supplier) => {
          return (
            supplier.supplierId.toLowerCase().includes(this.search.toLowerCase()) ||
            supplier.supplierName.toLowerCase().includes(this.search.toLowerCase()) ||
            supplier.companyName.toLowerCase().includes(this.search.toLowerCase())
          );
        });
      },
    },
    methods: {
      editSupplier(supplier) {
        console.log('Editing supplier:', supplier);
      },
      deleteSupplier(supplier) {
        console.log('Deleting supplier:', supplier);
        this.suppliers = this.suppliers.filter((s) => s.supplierId !== supplier.supplierId);
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
  </style>
