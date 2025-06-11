<template>
   <v-container fluid v-if="loading" class="d-flex justify-center align-center" style="height: 60vh;">
  <v-progress-circular indeterminate color="purple" :size="70" :width="7" class="ma-5"></v-progress-circular>
</v-container>

    <v-container fluid v-else>
      <v-card class="pa-6 elevation-12">
        <v-card-title>
          <v-icon left size="24">mdi-cart-plus</v-icon>
            Create New Product
      </v-card-title>
        <v-card-title>
          <v-tabs
            v-model="tab"

            slider-color="#f78166"
            class="mb-6"

          >
          <v-tab>
      <v-icon left>mdi-information</v-icon>
      General Info
    </v-tab>
    <v-tab>
      <v-icon left>mdi-currency-usd</v-icon>
      Pricing
    </v-tab>
    <v-tab>
      <v-icon left>mdi-package</v-icon>
      Stock
    </v-tab>
    <v-tab>
      <v-icon left>mdi-details</v-icon>
      Additional Details
    </v-tab>
    <v-tab>
      <v-icon left>mdi-warehouse</v-icon>
      Warehouse
    </v-tab>
    <v-tab>
      <v-icon left>mdi-storefront-check</v-icon>
      Kitchen
    </v-tab>
    
          </v-tabs>
        </v-card-title>

        <v-card-subtitle>
          <v-tabs-items v-model="tab">
            <!-- General Info Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense class="custom-row">
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="product.itemid"
                      label="Item ID"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                       :rules="[v => !!v || 'Item ID is required']"
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
                    <v-combobox
                      v-model="product.itemclass_id"
                      label="Item Class"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                     :items="itemClasses"
                      item-text="description"
                      item-value="id"
                    ></v-combobox>

                  </v-col>
                  <v-col cols="12" md="6">
                  <v-combobox
                      v-model="product.itemcategory_id"
                      label="Item Category"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                     :items="itemCategories"
                      item-text="description"
                      item-value="id"
                    ></v-combobox>
                    </v-col>

                    <v-col cols="12" md="6">
                  <v-combobox
                      v-model="product.itemcategory_id"
                      label="Item Sub Category"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                     :items="[]"
                      item-text="description"
                      item-value="id"
                    ></v-combobox>
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
                <v-row dense  class="custom-row">
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
                <v-row dense  class="custom-row">
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
                    <v-combobox
                      v-model="product.preferredVendorId"
                      label="Preferred Vendor"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                     :items="vendors"
                      item-text="description"
                      item-value="id"
                    ></v-combobox>
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
                    <v-text-field
                      v-model="product.barcode"
                      label="Barcode"
                      outlined
                      dense
                      class="mb-4"
                      hide-details="auto"
                    ></v-text-field>
                  </v-col>
              


                </v-row>
              </v-form>
            </v-tab-item>

            <!-- Additional Details Tab -->
            <v-tab-item>
              <v-form>
                <v-row dense  class="custom-row">
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
                      label="Custom4"
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
                </v-row>
              </v-form>
            </v-tab-item>



              <!--Warehouse Tab-->
              <v-tab-item>
              <template>
  <v-card flat>
    <v-card-text>
      <v-container fluid>
        <v-row>
          <v-col
            cols="12"
            sm="4"
            md="4"
          >
            <v-checkbox
              v-for="warehouse in warehouses"
              v-model="ex4"
              :label="warehouse.code"
              color="red"
              :value="warehouse.id"
              hide-details
            ></v-checkbox>
          
          </v-col>
        </v-row>

        <v-row class="mt-12">
          <v-col
            cols="12"
            sm="4"
            md="4"
          >
           
          </v-col>
        
        </v-row>
      </v-container>
    </v-card-text>
  </v-card>
</template>

            </v-tab-item>



            <!--Kitchen Tab-->
            <v-tab-item>
              <template>
  <v-card flat>
    <v-card-text>
      <v-container fluid>
        <v-row>
          <v-col
            cols="12"
            sm="4"
            md="4"
          >
           
          <v-checkbox
              v-for="kitchen in kitchens"
              v-model="ex4"
              :label="kitchen.code"
              color="red"
              :value="kitchen.id"
              hide-details
            ></v-checkbox>

          </v-col>
       
        </v-row>

        <v-row class="mt-12">
          <v-col
            cols="12"
            sm="4"
            md="4"
          >
           
          </v-col>
        
        </v-row>
      </v-container>
    </v-card-text>
  </v-card>
</template>

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
        warehouses: ['Warehouse 1', 'Warehouse 2', 'Warehouse 3'],
        kitchens:[]
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
      },
      async fetchClass(){
        try {
          const response = await axios.get('/api/class');
          this.itemClasses = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          this.itemClasses = [];
        } 
      },
      async fetchCategory(){
        try {
          const response = await axios.get('/api/categories');
          this.itemCategories = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          this.itemCategories = [];
        } 
      },
      async fetchVendor(){
        try {
          const response = await axios.get('/api/vendor');
          this.vendors = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          this.vendors = [];
        } 
      },
      async fetchWarehouse(){
        try {
          const response = await axios.get('/api/warehouse');
          this.warehouses = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          this.warehouses = [];
        }
      },
      async fetchKitchen(){
        try {
          const response = await axios.get('/api/kitchen');
          this.kitchens = Array.isArray(response.data) ? response.data : [];
        } catch (error) {
          this.kitchens = [];
        }
      },
    },
  
    mounted() {
      this.loading = true;
      Promise.all([
      this.fetchClass(),
      this.fetchCategory(),
      this.fetchVendor(),
      this.fetchWarehouse(),
      this.fetchKitchen(),
    ])
      .finally(() => {
        this.loading = false; // Set loading to false when all the data is fetched
      });
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
    color: #062e52;
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

  .custom-row {
  padding-top: 4px;
  background-color: #f5f5f5;
  border-radius: 8px;
  border: 1px solid #ccc;
}
  </style>
