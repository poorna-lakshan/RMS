<template>
  <v-container fluid>
    <v-card class="pa-6 elevation-12">
      <template>
        <v-row>
          <v-dialog v-model="dialog" persistent max-width="600px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                color="primary"
                class="v-btn--bottom-right custom-font-size"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon class="custom-font-size">mdi-plus-circle-outline</v-icon>Create
              </v-btn>
            </template>

            <v-card>
              <v-card-title>
                <span class="text-h8">Create Class</span>
              </v-card-title>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-text-field
                      v-model="itemClass.description"
                      label="Class Name"
                      required
                      outlined
                      dense
                    ></v-text-field>
                  </v-row>
                </v-container>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="dialog = false; clear()">Close</v-btn>
                <v-btn color="blue darken-1" text @click="createClass()">Save</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-row>
      </template>

      <v-card-title>
        <v-icon left size="24">mdi-cube-outline</v-icon>
        Class List
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Class"
          single-line
          hide-details
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
          <!-- Debugging: Add a div with a border to verify if the slot is populated -->
          <div class="debug-action-slot">
            <v-btn icon color="primary" @click="editClass(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon color="red" @click="deleteClass(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </div>
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
      itemClass: {
        id: null,
        description: "",
      },
      headers: [
        { text: 'ID', value: 'id' },
        { text: 'Description', value: 'description' },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      itemClasses: [],
      is_edit: false,
      dialog: false,
    };
  },
  computed: {
    filteredClasses() {
      return this.itemClasses.filter((itemClass) => {
        const searchTerm = this.search.toLowerCase();
        return (
          itemClass.description && 
          typeof itemClass.description === 'string' &&
          itemClass.description.toLowerCase().includes(searchTerm)
        );
      });
    },
  },
  methods: {
    clear() {
      this.is_edit = false;
      this.itemClass = { id: null, description: "" };
    },
    async fetchClass() {
      this.loading = true;
      try {
        const response = await axios.get('/api/class');
        this.itemClasses = Array.isArray(response.data) ? response.data : [];
        console.log('Fetched classes:', this.itemClasses); // Log the data
      } catch (error) {
        console.error('Error fetching categories:', error);
        this.itemClasses = [];
      } finally {
        this.loading = false;
      }
    },
    async createClass() {
      if (this.is_edit) {
        this.dialog = false;
        axios
          .put(`/api/class/${this.itemClass.id}`, this.itemClass)
          .then((response) => {
            if (response.data === false) {
              this.$toastr.w("No data Found", "Error");
            } else {
              this.$toastr.s(response.data.message);
            }
            this.clear();
            this.fetchClass();
          })
          .catch((error) => {
            console.error(error);
            this.$toastr.e(error.message, "Error");
          });
      } else {
        this.dialog = false;
        axios
          .post('/api/class', this.itemClass)
          .then((response) => {
            if (response.data === false) {
              this.$toastr.w("No data Found", "Error");
            } else {
              this.$toastr.s(response.data.message);
            }
            this.clear();
            this.fetchClass();
          })
          .catch((error) => {
            console.error(error);
            this.$toastr.e(error.message, "Error");
          });
      }
    },
    async editClass(itemclass) {
      this.is_edit = true;
      this.dialog = true;
      this.itemClass.id = itemclass.id;
      this.itemClass.description = itemclass.description;
    },
    deleteClass(itemClass) {

      if (confirm(`Are you sure you want to delete class "${itemclass.description}"?`)) {
      axios
        .delete(`/api/class/${itemClass.id}`)
        .then((response) => {
          this.itemClasses = this.itemClasses.filter((c) => c.id !== itemClass.id);
          this.$toastr.e(response.data.message);
        })
        .catch((error) => {
          this.$toastr.e(error.message);
        });
      }
    },
  },
  mounted() {
    this.fetchClass();
  },
};
</script>

<style scoped>
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
.custom-font-size {
  font-size: 12px;
}

</style>
