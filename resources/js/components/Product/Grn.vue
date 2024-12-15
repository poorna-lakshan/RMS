<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <!-- Save Button with Tooltip -->
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn fab color="primary" v-bind="attrs" v-on="on" @click="openSaveDialog">
              <v-icon>mdi-content-save</v-icon>
            </v-btn>
          </template>
          <span>Save GRN</span>
        </v-tooltip>

        <v-card-title>
          <v-icon left size="24">mdi-receipt</v-icon>
          Product GRN
          <v-spacer></v-spacer>
        </v-card-title>

        <!-- Search and Filters -->
        <v-row class="mb-4">
          <v-col cols="12" md="6">
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search Products" single-line hide-details></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-select v-model="selectedWarehouse" :items="warehouses" label="Filter by Warehouse" @change="filterByWarehouse"></v-select>
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
          <!-- Checkbox for Selection -->
          <template v-slot:item="{ item }">
            <tr>
              <td>
                <v-checkbox v-model="item.selected" @change="enableEditFields(item)"></v-checkbox>
              </td>
              <td>{{ item.lineNo }}</td>
              <td>{{ item.barcode }}</td>
              <td>{{ item.itemCode }}</td>
              <td>{{ item.qoh }}</td>
              <td>
                <v-text-field class="mb-3"
                  v-model="item.unitCost"
                  type="number"
                  :disabled="!item.selected"
                  hide-details
                ></v-text-field>
              </td>
              <td>
                <v-text-field class="mb-3"
                  v-model="item.sellingPrice"
                  type="number"
                  :disabled="!item.selected"
                  hide-details
                ></v-text-field>
              </td>
              <td>
                <v-text-field class="mb-3"
                  v-model="item.receivedQuantity"
                  type="number"
                  :disabled="!item.selected"
                  hide-details
                ></v-text-field>
              </td>
              <td>
                <v-text-field class="mb-3"
                  v-model="item.freeQuantity"
                  type="number"
                  :disabled="!item.selected"
                  hide-details
                ></v-text-field>
              </td>
              <td>
                <v-menu
                  v-model="item.expireDateMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="item.expireDate"
                      label="Expiration Date"
                      readonly
                      v-on="on"
                      :disabled="!item.selected"
                    ></v-text-field>
                  </template>
                  <v-date-picker v-model="item.expireDate" @input="item.expireDateMenu = false"></v-date-picker>
                </v-menu>
              </td>
              <td>{{ calculateLineTotal(item) }}</td>
            </tr>
          </template>
        </v-data-table>
      </v-card>

      <!-- Save Dialog -->
      <v-dialog v-model="dialog" max-width="400">
        <v-card>
          <v-card-title>Save Product GRN</v-card-title>
          <v-card-text>

            <v-select v-model="currentProduct.supplier" :items="suppliers" label="Supplier" required></v-select>
            <v-text-field v-model="billNumber" label="Bill Number" required></v-text-field>
            <v-textarea v-model="description" label="Description" rows="3" required></v-textarea>

            <!-- Date Field -->
            <v-menu ref="dateMenu" v-model="dateMenu" :close-on-content-click="false" transition="scale-transition" offset-y>
              <template v-slot:activator="{ on }">
                <v-text-field v-model="selectedDate" label="Date" readonly v-on="on"></v-text-field>
              </template>
              <v-date-picker v-model="selectedDate" @input="dateMenu = false"></v-date-picker>
            </v-menu>

            <v-text-field v-model="payment" label="Payment" required></v-text-field>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn outlined color="warning" text @click="dialog = false">Cancel</v-btn>
            <v-btn outlined color="primary" text @click="saveProductWithDetails">Save</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Snackbar for Feedback -->
      <v-snackbar v-model="snackbar" timeout="3000">
        Product saved successfully!
        <template v-slot:action="{ attrs }">
          <v-btn text v-bind="attrs" @click="snackbar = false">Close</v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </template>

  <script>
  export default {
    data() {
      return {
        search: '',
        loading: false,
        selectedWarehouse: 'All',
        warehouses: ['All', 'Warehouse 1', 'Warehouse 2', 'Warehouse 3'],
        selectedSupplier: 'All',
        suppliers: ['All', 'Supplier 1', 'Supplier 2', 'Supplier 3'],
        dialog: false,
        billNumber: '',
        description: '',
        selectedDate: '',
        dateMenu: false,
        payment: '',
        currentProduct: {},
        snackbar: false,
        products: [
          { lineNo: 1, barcode: 'B001', itemCode: 'ITEM001', qoh: 10, unitCost: 100, sellingPrice: 150, receivedQuantity: 0, freeQuantity: 0, expireDate: '', expireDateMenu: false, selected: false },
          { lineNo: 2, barcode: 'B002', itemCode: 'ITEM002', qoh: 15, unitCost: 200, sellingPrice: 250, receivedQuantity: 0, freeQuantity: 0, expireDate: '', expireDateMenu: false, selected: false },
          { lineNo: 3, barcode: 'B003', itemCode: 'ITEM003', qoh: 5, unitCost: 150, sellingPrice: 200, receivedQuantity: 0, freeQuantity: 0, expireDate: '', expireDateMenu: false, selected: false },
          { lineNo: 4, barcode: 'B004', itemCode: 'ITEM004', qoh: 20, unitCost: 300, sellingPrice: 400, receivedQuantity: 0, freeQuantity: 0, expireDate: '', expireDateMenu: false, selected: false },
          { lineNo: 5, barcode: 'B005', itemCode: 'ITEM005', qoh: 8, unitCost: 250, sellingPrice: 300, receivedQuantity: 0, freeQuantity: 0, expireDate: '', expireDateMenu: false, selected: false },
        ],
        // Define the headers for the data table
        headers: [
          { text: 'Select', value: 'select', sortable: false },
          { text: 'Line No', value: 'lineNo' },
          { text: 'Barcode', value: 'barcode' },
          { text: 'Item Code', value: 'itemCode' },
          { text: 'QOH', value: 'qoh' },
          { text: 'Unit Cost', value: 'unitCost' },
          { text: 'Selling Price', value: 'sellingPrice' },
          { text: 'Received Quantity', value: 'receivedQuantity' },
          { text: 'Free Quantity', value: 'freeQuantity' },
          { text: 'Expiration Date', value: 'expireDate' },
          { text: 'Line Total', value: 'lineTotal', sortable: false },
        ],
      };
    },
    computed: {
      filteredProducts() {
        return this.products.filter((product) => {
          const matchesSearch = product.barcode.toLowerCase().includes(this.search.toLowerCase());
          const matchesWarehouse = this.selectedWarehouse === 'All' || product.warehouse === this.selectedWarehouse;
          const matchesSupplier = this.selectedSupplier === 'All' || product.supplier === this.selectedSupplier;
          return matchesSearch && matchesWarehouse && matchesSupplier;
        });
      },
    },
    methods: {
      openSaveDialog() {
        this.dialog = true;
      },
      saveProductWithDetails() {
        // Save the product details here (add your logic)
        this.snackbar = true;
        this.dialog = false;
        // Reset form fields
        this.billNumber = '';
        this.description = '';
        this.selectedDate = '';
        this.payment = '';
      },
      calculateLineTotal(item) {
        return item.unitCost * item.receivedQuantity;
      },
      filterByWarehouse() {
        // Optional: Implement additional filtering logic if needed
      },
      filterBySupplier() {
        // Optional: Implement additional filtering logic if needed
      },
      enableEditFields(item) {
        // Add logic to enable fields based on selection
        if (item.selected) {
          item.unitCost = item.unitCost; // Optional logic
        }
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
