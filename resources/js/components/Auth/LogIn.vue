<template>
    <div   class="login-container">
      <v-row align="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="6">
          <v-card class="pa-6 outlined-card">
            <v-card-title class="text-center">
              <v-img
                src="@/assets/logo.png"
                contain
                max-height="120"
                class="mx-auto"
              ></v-img>
            </v-card-title>
            <v-card-subtitle class="text-center subtitle-1 grey--text">
              Please sign in to continue
            </v-card-subtitle>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <v-text-field
                  v-model="email"
                  label="Email Address"
                  type="email"
                  prepend-icon="mdi-email"
                  outlined
                  dense
                  class="mb-4 custom-field"
                ></v-text-field>
                <v-text-field
                  v-model="password"
                  label="Password"
                  type="password"
                  prepend-icon="mdi-lock"
                  outlined
                  dense
                  class="mb-4 custom-field"
                ></v-text-field>
                <v-btn
                  color="primary"
                  type="submit"
                  class="w-100 login-btn mx-8"
                  :loading="loading"
                  rounded
                  elevation="3"
                >
                  Login
                </v-btn>
                <v-alert v-if="error" type="error" class="mt-4 custom-alert">
                  {{ error }}
                </v-alert>
              </v-form>
            </v-card-text>
            <v-card-actions class="justify-center">
                <v-btn text @click="redirectTo('register')" class="register-link">
  Don't have an account? <strong>Register</strong>
</v-btn>

            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        email: '',
        password: '',
        loading: false,
        error: null
      };
    },
    methods: {
        async submitForm() {
  this.loading = true;
  this.error = null;
  try {
    const response = await this.axios.post('http://127.0.0.1:8000/api/login', {
      email: this.email,
      password: this.password
    });

    const { token } = response.data;

    localStorage.setItem('auth_token', token);

    console.log('Login successful', response.data);

    this.$router.push('/dashboard');
  } catch (err) {
    this.error = 'Invalid email or password';
  } finally {
    this.loading = false;
  }
},

      redirectTo(route) {
        this.$router.push({ name: route });
      }

    }
  };
  </script>

  <style scoped>
  .login-container {
    background: linear-gradient(135deg, #1e88e5, #8e24aa);
    background-size: cover;
    background-position: center;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .v-card {
    background-color: rgba(255, 255, 255, 0.85);
    border-radius: 20px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(15px);
    padding: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .v-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
  }

  .subtitle-1 {
    font-size: 1.2rem;
    font-weight: 500;
  }

  .custom-field {
    border-radius: 8px;
  }

  .login-btn {
    background: linear-gradient(45deg, #42a5f5, #ab47bc);
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    transition: all 0.3s ease;
  }

  .login-btn:hover {
    background: linear-gradient(45deg, #ab47bc, #42a5f5);
    transform: translateY(-2px);
  }

  .custom-alert {
    border-radius: 12px;
  }

  .register-link {
    color: #ab47bc;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
  }
  </style>
