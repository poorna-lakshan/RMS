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
  <v-icon>mdi-cash</v-icon>
  Update Prices
  <v-spacer></v-spacer>
</v-card-title>


        <v-row class="mb-4">
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search Products"
              single-line
              hide-details
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="4">
            <v-select
              v-model="selectedWarehouse"
              :items="warehouses"
              label="Filter by Warehouse"
              @change="filterByWarehouse"
            ></v-select>
          </v-col>

          <v-col cols="12" md="4">
            <v-select
              v-model="selectedCategory"
              :items="categories"
              label="Filter by Category"
              @change="filterByCategory"
            ></v-select>
          </v-col>
        </v-row>

        <!-- Products Table -->
        <v-data-table
          :headers="headers"
          :items="filteredProducts"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.unitCost="{ item }">
            <v-text-field
              v-model="item.unitCost"
              type="number"
              @change="updatePrice(item)"
              hide-details
              small-chips
              class="unit-cost-input"
            ></v-text-field>
          </template>
          <template v-slot:item.sellingPrice="{ item }">
            <v-text-field
              v-model="item.sellingPrice"
              type="number"
              @change="updateSellingPrice(item)"
              hide-details
              small-chips
              class="selling-price-input"
            ></v-text-field>
          </template>
          <template v-slot:item.action="{ item }">
            <v-btn icon color="green" @click="saveProduct(item)">
              <v-icon>mdi-content-save</v-icon>
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
      search: '',
      loading: false,
      selectedWarehouse: 'All', // Default selection set to 'All'
      warehouses: ['All', 'Warehouse 1', 'Warehouse 2', 'Warehouse 3'],
      selectedCategory: 'All', // Default selection set to 'All'
      categories: ['All', 'Category 1', 'Category 2', 'Category 3'],
      headers: [
        { text: 'Item ID', value: 'itemid' },
        { text: 'Description', value: 'description' },
        { text: 'Category', value: 'category' },
        { text: 'Unit Cost', value: 'unitCost' },
        { text: 'Selling Price', value: 'sellingPrice' },
        { text: 'Stock', value: 'stock' },
        { text: 'Warehouse', value: 'warehouse' },
        { text: 'Action', value: 'action', sortable: false },
      ],
      products: [
        { itemid: 'P001', description: 'Product 1', category: 'Category 1', unitCost: 100, sellingPrice: 150, stock: 50, warehouse: 'Warehouse 1' },
        { itemid: 'P002', description: 'Product 2', category: 'Category 2', unitCost: 200, sellingPrice: 250, stock: 30, warehouse: 'Warehouse 2' },
        { itemid: 'P003', description: 'Product 3', category: 'Category 3', unitCost: 150, sellingPrice: 200, stock: 40, warehouse: 'Warehouse 1' },
        { itemid: 'P004', description: 'Product 4', category: 'Category 1', unitCost: 300, sellingPrice: 400, stock: 20, warehouse: 'Warehouse 3' },
        { itemid: 'P005', description: 'Product 5', category: 'Category 2', unitCost: 250, sellingPrice: 300, stock: 60, warehouse: 'Warehouse 2' },
      ],
    };
  },
  computed: {
    filteredProducts() {
      return this.products.filter((product) => {
        const matchesSearch = product.itemid.toLowerCase().includes(this.search.toLowerCase()) ||
                              product.description.toLowerCase().includes(this.search.toLowerCase()) ||
                              product.category.toLowerCase().includes(this.search.toLowerCase());

        const matchesWarehouse = this.selectedWarehouse === 'All' || product.warehouse === this.selectedWarehouse;
        const matchesCategory = this.selectedCategory === 'All' || product.category === this.selectedCategory;

        return matchesSearch && matchesWarehouse && matchesCategory;
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
    updatePrice(product) {
      console.log('Updated unit cost for product:', product);
    },
    updateSellingPrice(product) {
      console.log('Updated selling price for product:', product);
    },
    saveProduct(product) {
      console.log('Saved product:', product);
    },
    filterByWarehouse() {
      console.log('Filtered by warehouse:', this.selectedWarehouse);
    },
    filterByCategory() {
      console.log('Filtered by category:', this.selectedCategory);
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
  .unit-cost-input,
  .selling-price-input {
    max-width: 80px; /* Adjust width as needed */
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
