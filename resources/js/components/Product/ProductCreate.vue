<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
            <v-icon left size="24">mdi-cart-plus</v-icon>

        Create New Product
      </v-card-title>
        <v-card-title>
          <v-tabs
            v-model="tab"
            background-color="deep-purple accent-4"
            dark
            slider-color="yellow"
            class="mb-6"
          >
            <v-tab>General Info</v-tab>
            <v-tab>Pricing</v-tab>
            <v-tab>Stock</v-tab>
            <v-tab>Additional Details</v-tab>
          </v-tabs>
        </v-card-title>

        <v-card-subtitle>
          <v-tabs-items v-model="tab">
            <!-- General Info Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.itemid"
                      label="Item ID"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.description"
                      label="Description"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.itemclass_id"
                      :items="itemClasses"
                      label="Item Class"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.itemcategory_id"
                      :items="itemCategories"
                      label="Item Category"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.unitCost"
                      label="Unit Cost"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-checkbox
                      v-model="product.active"
                      label="Active"
                      color="success"
                      class="mb-4"
                      hide-details="auto"
                    ></v-checkbox>
                  </v-col>

                </v-row>
              </v-form>
            </v-tab-item>

            <!-- Pricing Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.sellingPrice"
                      label="Selling Price"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.uom"
                      :items="uoms"
                      label="Unit of Measure"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.price2"
                      label="Price 2"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.price3"
                      label="Price 3"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.costMethod"
                      :items="costMethods"
                      label="Cost Method"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                </v-row>
              </v-form>
            </v-tab-item>

            <!-- Stock Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.reorderLevel"
                      label="Reorder Level"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.preferredVendorId"
                      :items="vendors"
                      label="Preferred Vendor"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.minimumQuantity"
                      label="Minimum Quantity"
                      type="number"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.barcode"
                      label="Barcode"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="product.warehouse"
                      :items="warehouses"
                      label="Warehouse"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-checkbox
                      v-model="product.kotCheck"
                      label="KOT Check"
                      color="primary"
                      class="mb-4"
                      hide-details="auto"
                    ></v-checkbox>
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-checkbox
                      v-model="product.botCheck"
                      label="BOT Check"
                      color="primary"
                      class="mb-4"
                      hide-details="auto"
                    ></v-checkbox>
                  </v-col>


                </v-row>
              </v-form>
            </v-tab-item>

            <!-- Additional Details Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense>
                    <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.custom1"
                      label="Custom1"
                      type="text"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.custom2"
                      label="Custom2"
                      type="text"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.custom3"
                      label="Custom3"
                      type="text"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.Custom4"
                      label="custom4"
                      type="text"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.custom5"
                      label="Custom5"
                      type="text"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-file-input
                      v-model="product.image"
                      label="Image"
                     type="file"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-file-input>
                  </v-col>
                </v-row>
              </v-form>
            </v-tab-item>
          </v-tabs-items>
        </v-card-subtitle>

        <v-card-actions>
          <v-btn

            color="primary"
            @click="submitForm"
            class="ma-2"
            :loading="loading"
          >
            Save
          </v-btn>
          <v-btn
           color="warning"
            @click="cancel"
            class="ma-2"

          >
            Cancel
          </v-btn>
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
        product: {
          itemid: '',
          description: '',
          itemclass_id: '',
          active: false,
          inactive: false,
          itemcategory_id: '',
          unitCost: '',
          sellingPrice: '',
          uom: '',
          price2: '',
          price3: '',
          costMethod: '',
          reorderLevel: '',
          preferredVendorId: '',
          minimumQuantity: '',
          kotCheck: false,
          botCheck: false,
          barcode: '',
          warehouse: '',
          custom1: '',
          custom2: '',
          custom3: '',
          custom4: '',
          custom5: '',
          image: ''
        },
        itemClasses: ['Class 1', 'Class 2', 'Class 3'],
        itemCategories: ['Category 1', 'Category 2', 'Category 3'],
        uoms: ['Unit', 'Box', 'Pack'],
        costMethods: ['FIFO', 'LIFO', 'Average'],
        vendors: ['Vendor 1', 'Vendor 2', 'Vendor 3'],
        warehouses: ['Warehouse 1', 'Warehouse 2', 'Warehouse 3']
      };
    },
    methods: {
        async submitForm() {
  this.loading = true;
  try {
    const formData = new FormData();
    // Append all product fields to FormData
    for (const key in this.product) {
      formData.append(key, this.product[key]);
    }

    if (this.product.id) {
      await this.axios.put(`/api/products/${this.product.itemid}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      console.log('Product updated successfully');
    } else {
      await this.axios.post('/api/products', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      console.log('Product created successfully');
    }
  } catch (error) {
    console.error('Submission error:', error);
  } finally {
    this.loading = false;
  }
},

      cancel() {
        console.log('Form canceled');
      }
    }
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
