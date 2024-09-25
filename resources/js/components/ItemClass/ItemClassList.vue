<template>
    <v-container fluid>
      <v-card class="pa-6 elevation-12">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              fab
              color="primary"
              class="v-btn---right"
              v-bind="attrs"
              v-on="on"
              :to="{ name: 'itemclasscreate' }"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
          <span>Create Item Class</span>
        </v-tooltip>
        <v-card-title>
          <v-icon left>mdi-tag-multiple</v-icon>
          <span class="headline">Item Classes</span>
          <v-spacer></v-spacer>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search"
            single-line
            hide-details
            class="mx-4"
          ></v-text-field>

        </v-card-title>

        <v-data-table
          :headers="headers"
          :items="filteredClasses"
          :items-per-page="5"
          class="elevation-1"
          :loading="loading"
          loading-text="Loading... Please wait"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn icon color="primary" @click="editClass(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteClass(item)">
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
        search: '',
        loading: false,
        headers: [
          { text: 'ID', value: 'id' },
          { text: 'Name', value: 'name' },
          { text: 'Description', value: 'description' },
          { text: 'Actions', value: 'actions', sortable: false },
        ],
        itemClasses: [
          { id: 'C001', name: 'Electronics', description: 'Devices and gadgets' },
          { id: 'C002', name: 'Furniture', description: 'Home and office furniture' },
          { id: 'C003', name: 'Clothing', description: 'Apparel and accessories' },
          { id: 'C004', name: 'Books', description: 'Printed and digital books' },
          { id: 'C005', name: 'Toys', description: 'Children\'s toys and games' },
        ],
      };
    },
    computed: {
      filteredClasses() {
        return this.itemClasses.filter(itemClass => {
          return (
            itemClass.name.toLowerCase().includes(this.search.toLowerCase()) ||
            itemClass.description.toLowerCase().includes(this.search.toLowerCase())
          );
        });
      },
    },
    methods: {
      navigateToCreate() {
        this.$router.push('/item-class/create'); // Navigate to the create form
      },
      editClass(itemClass) {
        this.$router.push({ name: 'item-class-edit', params: { id: itemClass.id } }); // Navigate to the edit form
      },
      deleteClass(itemClass) {
        // Handle item class deletion
        console.log('Deleting item class:', itemClass);
        this.itemClasses = this.itemClasses.filter(ic => ic.id !== itemClass.id);
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
