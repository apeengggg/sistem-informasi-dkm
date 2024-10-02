import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'
import jwtDecode from 'jwt-decode';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: to => {
        return { name: 'dashboards-analytics' }
      }
    },
    {
      path: '/pages/user-profile',
      redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
    },
    {
      path: '/pages/account-settings',
      redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
    },
    ...setupLayouts(routes),
  ],
})

function isAuthenticated() {
  const token = localStorage.getItem('token');
  console.log("🚀 ~ isAuthenticated ~ token:", token)

  if (!token) {
    return false; // No token
  }

  try {
    const decodedToken = jwtDecode(token);
    console.log("🚀 ~ isAuthenticated ~ decodedToken:", decodedToken)
    const currentTime = Date.now() / 1000;

    if (decodedToken.exp && decodedToken.exp < currentTime) {
      return false; // Token expired
    }

    return true; // Token valid
  } catch (error) {
    console.error('Invalid token:', error);
    return false; // Invalid token
  }
}


// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
router.beforeEach((to, from, next) => {
  console.log("🚀 ~ router.beforeEach ~ to:", to)
  // console.log('env', import.meta.env.VITE_JWT_SECRET)
  if (to.meta.requiresLogin && !isAuthenticated()) {
    // If not authenticated, redirect to login page
    return next({ name: 'apps-login'})
  }
  // // If authenticated or route doesn't require auth, proceed as normal
  next();
})
export default router
