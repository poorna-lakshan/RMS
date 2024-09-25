import Vue from 'vue';
import VueRouter from 'vue-router';
import LogIn from './components/Auth/LogIn.vue';   // Ensure the path is correct
import Register from './components/Auth/Register.vue';  // Ensure the path is correct
import Dashboard from './components/Dashboard/Dashboard.vue';
import ProductCreate from './components/Product/ProductCreate.vue';
import ProductList from './components/Product/ProductList.vue';
import CategoryCreate from './components/Category/CategoryCreate.vue';
import CategoryList from './components/Category/CategoryList.vue';
import ItemClassCreate from './components/ItemClass/ItemClassCreate.vue';
import ItemClassList from './components/ItemClass/ItemClassList.vue';
import CreateWareHouse from './components/WareHouse/CreateWareHouse.vue';
import WareHouseList from './components/WareHouse/WareHouseList.vue';
import CreateCustomer from './components/Customer/CreateCustomer.vue';
import CustomerList from './components/Customer/CustomerList.vue';
import CreateSupplier from './components/Supplier/CreateSupplier.vue';
import SupplierList from './components/Supplier/SupplierList.vue';
Vue.use(VueRouter);

export const routes = [
    {
        name: 'login',
        path: '/login',
        component: LogIn
    },
    {
        name: 'register',
        path: '/register',
        component: Register
    },
    {
        name: 'dashboard',
        path: '/dashboard',
        component: Dashboard
      },
      {
        name: 'productcreate',
        path: '/productcreate',
        component: ProductCreate
      },
      {
        name: 'productlist',
        path: '/productlist',
        component: ProductList
      },
      {
        name: 'categorycreate',
        path: '/categorycreate/:category',
        component: CategoryCreate
      },
      {
        name: 'categorylist',
        path: '/categorylist',
        component: CategoryList
      },
      {
        name: 'itemclasscreate',
        path: '/itemclasscreate',
        component: ItemClassCreate
      },
      {
        name: 'itemclasslist',
        path: '/itemclasslist',
        component: ItemClassList
      },
      {
        name: 'createwarehouse',
        path: '/createwarehouse',
        component: CreateWareHouse
      },
      {
        name: 'warehouselist',
        path: '/warehouselist',
        component: WareHouseList
      },
      {
        name: 'createcustomer',
        path: '/createcustomer',
        component: CreateCustomer
      },

      {
        name: 'customerlist',
        path: '/customerlist',
        component: CustomerList
      },

      {
        name: 'supplierlist',
        path: '/supplierlist',
        component: SupplierList
      },
      {
        name: 'createsupplier',
        path: '/createsupplier',
        component: CreateSupplier
      },


];

const router = new VueRouter({
    mode: 'history',
    routes
});

// Route guard to check for authentication
router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('auth_token');  // Check if the token is present

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            next({ name: 'login' });  // Redirect to login if not authenticated
        } else {
            next();  // Allow the user to proceed
        }
    } else {
        next();  // Always allow access to routes that don't require authentication
    }
});

export default router;
