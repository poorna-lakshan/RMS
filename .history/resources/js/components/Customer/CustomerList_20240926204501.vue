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
              :to="{ name: 'createcustomer' }"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
          <span>Create Customer</span>
        </v-tooltip>
        <v-card-title>
          <v-icon left size="24">mdi-account-multiple</v-icon>
          Customer List
          <v-spacer></v-spacer>
          <!-- Search Field -->
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Customers"
            single-line
            hide-details
          ></v-text-field>
        </v-card-title>

        <!-- Customers Table -->
        <v-data-table
          :headers="headers"
          :items="filteredCustomers"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="editCustomer(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteCustomer(item)">
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
          { text: 'Customer ID', value: 'customerId' },
          { text: 'Name', value: 'customerName' },
          { text: 'Email', value: 'email' },
          { text: 'Telephone', value: 'tel' },
          { text: 'Customer Type', value: 'customerType' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        customers: [
          {
            customerId: 'C001',
            customerName: 'John Doe',
            email: 'johndoe@example.com',
            tel: '555-1234',
            customerType: 'Retail',
          },
          {
            customerId: 'C002',
            customerName: 'Jane Smith',
            email: 'janesmith@example.com',
            tel: '555-5678',
            customerType: 'Wholesale',
          },
          // Add more customer objects here
        ],
      };
    },
    computed: {
      filteredCustomers() {
        return this.customers.filter((customer) => {
          return (
            customer.customerId.toLowerCase().includes(this.search.toLowerCase()) ||
            customer.customerName.toLowerCase().includes(this.search.toLowerCase()) ||
            customer.email.toLowerCase().includes(this.search.toLowerCase()) ||
            customer.tel.toLowerCase().includes(this.search.toLowerCase()) ||
            customer.customerType.toLowerCase().includes(this.search.toLowerCase())
          );
        });
      },
    },
    methods: {
      editCustomer(customer) {
        console.log('Editing customer:', customer);
        // Navigate to edit page or open modal for editing
      },
      deleteCustomer(customer) {
        console.log('Deleting customer:', customer);
        this.customers = this.customers.filter((c) => c.customerId !== customer.customerId);
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
