<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
    <v-tooltip bottom>
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          fab
          color="primary"
          class="v-btn--bottom-right"
          v-bind="attrs"
          v-on="on"
          :to="{ name: 'productcreate' }"
        >
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </template>
      <span>Create Product</span>
    </v-tooltip>
        <v-card-title>
          <v-icon left size="24">mdi-cart</v-icon>
          Product List
          <v-spacer></v-spacer>
          <!-- Search Field -->
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Products"
            single-line
            hide-details
          ></v-text-field>
        </v-card-title>

        <!-- Products Table -->
        <v-data-table
          :headers="headers"
          :items="filteredProducts"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.action="{ item }">
            <v-btn icon color="primary" @click="editProduct(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteProduct(item)">
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
          { text: 'Item ID', value: 'itemid' },
          { text: 'Description', value: 'description' },
          { text: 'Category', value: 'category' },
          { text: 'Unit Cost', value: 'unitCost' },
          { text: 'Stock', value: 'stock' },
          { text: 'Action', value: 'action', sortable: false },
        ],
        products: [
          {
            itemid: 'P001',
            description: 'Product 1',
            category: 'Category 1',
            unitCost: 100,
            stock: 50,
          },
          {
            itemid: 'P002',
            description: 'Product 2',
            category: 'Category 2',
            unitCost: 200,
            stock: 30,
          },
          {
            itemid: 'P003',
            description: 'Product 3',
            category: 'Category 3',
            unitCost: 150,
            stock: 40,
          },
          {
            itemid: 'P004',
            description: 'Product 4',
            category: 'Category 1',
            unitCost: 300,
            stock: 20,
          },
          {
            itemid: 'P005',
            description: 'Product 5',
            category: 'Category 2',
            unitCost: 250,
            stock: 60,
          },
        ],
      };
    },
    computed: {
      filteredProducts() {
        return this.products.filter((product) => {
          return (
            product.itemid.toLowerCase().includes(this.search.toLowerCase()) ||
            product.description.toLowerCase().includes(this.search.toLowerCase()) ||
            product.category.toLowerCase().includes(this.search.toLowerCase())
          );
        });
      },
    },
    methods: {
      editProduct(product) {
        console.log('Editing product:', product);
      },
      deleteProduct(product) {
        console.log('Deleting product:', product);
        this.products = this.products.filter((p) => p.itemid !== product.itemid);
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
