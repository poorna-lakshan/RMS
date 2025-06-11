<template>
  <v-container fluid>
    <v-card class="pa-6 elevation-12">
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

          <v-col cols="12" md="6">
            <v-select
              v-model="form_data.price_level"
              :items="price_levels"
              label="Price Level"
              outlined
              dense
              class="mb-4"
              hide-details="auto"
              required
            ></v-select>
          </v-col>
        </v-row>

        <v-card-actions>
          <v-btn color="primary" @click="submitForm" class="ma-2" :loading="loading">
            Save
          </v-btn>
          <v-btn color="warning" @click="clear" class="ma-2" :loading="loading">
            Clear
          </v-btn>
          <v-btn color="grey" @click="go_back" class="ma-2" :loading="loading">
            Back
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
        code: '',
        name: '',
        ap_acc: '',
        ar_acc: '',
        cash_acc: '',
        sales_acc: '',
        cos_acc: '',
        inv_acc: '',
        price_level: ''
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
    };
  },
  methods: {
    async submitForm() {
      if (!this.validation()) {
        return;
      }
      this.loading = true;
      try {
        const response = await axios.post('/api/warehouse', this.form_data);
        if (response.data === false) {
          this.$toastr.w("No data Found", "Error");
        } else {
          this.$toastr.s(response.data.message);
        }
        this.clear();
      } catch (error) {
        console.error(error);
        this.$toastr.e(error.message, "Error");
      } finally {
        this.loading = false;
      }
    },
    validation() {
      const fields = [
        { field: 'code', message: 'Please enter warehouse id' },
        { field: 'name', message: 'Please enter warehouse name' },
        { field: 'price_level', message: 'Please enter warehouse price level' },
        { field: 'ap_acc', message: 'Please select A/P Account' },
        { field: 'ar_acc', message: 'Please select A/R Account' },
        { field: 'cash_acc', message: 'Please select Cash Account' },
        { field: 'sales_acc', message: 'Please select Sales Account' },
        { field: 'cos_acc', message: 'Please select Cost of Sales Account' },
        { field: 'inv_acc', message: 'Please select Inventory Account' }
      ];
      for (let field of fields) {
        if (!this.form_data[field.field]) {
          this.$toastr.w(field.message, "Error");
          return false;
        }
      }
      return true;
    },
    clear() {
      this.form_data = {
        id: 0,
        code: '',
        name: '',
        ap_acc: '',
        ar_acc: '',
        cash_acc: '',
        sales_acc: '',
        cos_acc: '',
        inv_acc: '',
        price_level: ''
      };
    },
    go_back() {
      this.$router.push('/warehouselist');
    },
    async load_data(){
      try {
          const response = await axios.get('/api/warehouse/'+this.form_data.id); // Ensure this endpoint is correct
          if (response && response.length > 0) {
            this.form_data = response.data[0]; // Adjust based on the response structure
          } else {
            this.$toastr.w("Warehouse not found", "Error");
          }
        } catch (error) {
          console.error('Error fetching categories:', error);
          this.form_data = [];
        } finally {
          this.loading = false;
        }
    }
  },
  mounted() {
    if (this.$route.params.id) {
      this.form_data.id=this.$route.params.id;
      this.load_data();
    }
  }
};
</script>

<style scoped>
.v-btn {
  font-weight: bold;
  text-transform: uppercase;
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
