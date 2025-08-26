import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Layout components
const AuthLayout = () => import('../layouts/AuthLayout.vue')
const MainLayout = () => import('../layouts/MainLayout.vue')

// Auth pages
const LoginPage = () => import('../pages/auth/LoginPage.vue')
const ForgotPasswordPage = () => import('../pages/auth/ForgotPasswordPage.vue')

// Dashboard pages
const AdminDashboard = () => import('../pages/admin/AdminDashboard.vue')
const MahasiswaDashboard = () => import('../pages/mahasiswa/MahasiswaDashboard.vue')
const DosenDashboard = () => import('../pages/dosen/DosenDashboard.vue')
const StafDashboard = () => import('../pages/staf/StafDashboard.vue')

// Mahasiswa pages
const MahasiswaProfile = () => import('../pages/mahasiswa/MahasiswaProfile.vue')
const MahasiswaKRS = () => import('../pages/mahasiswa/MahasiswaKRS.vue')
const MahasiswaNilai = () => import('../pages/mahasiswa/MahasiswaNilai.vue')
const MahasiswaPembayaran = () => import('../pages/mahasiswa/MahasiswaPembayaran.vue')
const MahasiswaTranskrip = () => import('../pages/mahasiswa/MahasiswaTranskrip.vue')

// Dosen pages
const DosenProfile = () => import('../pages/dosen/DosenProfile.vue')
const DosenJadwal = () => import('../pages/dosen/DosenJadwal.vue')
const DosenInputNilai = () => import('../pages/dosen/DosenInputNilai.vue')

// Admin pages
const AdminMahasiswa = () => import('../pages/admin/AdminMahasiswa.vue')
const AdminDosen = () => import('../pages/admin/AdminDosen.vue')
const AdminMataKuliah = () => import('../pages/admin/AdminMataKuliah.vue')
const AdminJadwal = () => import('../pages/admin/AdminJadwal.vue')
const AdminPembayaran = () => import('../pages/admin/AdminPembayaran.vue')
const AdminLaporan = () => import('../pages/admin/AdminLaporan.vue')

// Error pages
const NotFoundPage = () => import('../pages/errors/NotFoundPage.vue')
const UnauthorizedPage = () => import('../pages/errors/UnauthorizedPage.vue')

const routes: Array<RouteRecordRaw> = [
  // Root redirect
  {
    path: '/',
    redirect: '/dashboard'
  },

  // Authentication routes
  {
    path: '/auth',
    component: AuthLayout,
    meta: { requiresGuest: true },
    children: [
      {
        path: '',
        redirect: '/auth/login'
      },
      {
        path: 'login',
        name: 'Login',
        component: LoginPage,
        meta: {
          title: 'Masuk - SIA Universitas'
        }
      },
      {
        path: 'forgot-password',
        name: 'ForgotPassword',
        component: ForgotPasswordPage,
        meta: {
          title: 'Lupa Kata Sandi - SIA Universitas'
        }
      }
    ]
  },

  // Legacy login route (redirect)
  {
    path: '/login',
    redirect: '/auth/login'
  },

  // Main application routes
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      // Dashboard routes
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: AdminDashboard, // Will be dynamically determined
        meta: {
          title: 'Dashboard - SIA Universitas'
        }
      },

      // Admin routes
      {
        path: 'admin',
        meta: { roles: ['admin'] },
        children: [
          {
            path: 'dashboard',
            name: 'AdminDashboard',
            component: AdminDashboard,
            meta: {
              title: 'Dashboard Admin - SIA'
            }
          },
          {
            path: 'mahasiswa',
            name: 'AdminMahasiswa',
            component: AdminMahasiswa,
            meta: {
              title: 'Manajemen Mahasiswa - SIA'
            }
          },
          {
            path: 'dosen',
            name: 'AdminDosen',
            component: AdminDosen,
            meta: {
              title: 'Manajemen Dosen - SIA'
            }
          },
          {
            path: 'mata-kuliah',
            name: 'AdminMataKuliah',
            component: AdminMataKuliah,
            meta: {
              title: 'Manajemen Mata Kuliah - SIA'
            }
          },
          {
            path: 'jadwal',
            name: 'AdminJadwal',
            component: AdminJadwal,
            meta: {
              title: 'Manajemen Jadwal - SIA'
            }
          },
          {
            path: 'pembayaran',
            name: 'AdminPembayaran',
            component: AdminPembayaran,
            meta: {
              title: 'Manajemen Pembayaran - SIA'
            }
          },
          {
            path: 'laporan',
            name: 'AdminLaporan',
            component: AdminLaporan,
            meta: {
              title: 'Laporan - SIA'
            }
          }
        ]
      },

      // Mahasiswa routes
      {
        path: 'mahasiswa',
        meta: { roles: ['mahasiswa', 'admin'] },
        children: [
          {
            path: 'dashboard',
            name: 'MahasiswaDashboard',
            component: MahasiswaDashboard,
            meta: {
              title: 'Dashboard Mahasiswa - SIA'
            }
          },
          {
            path: 'profile',
            name: 'MahasiswaProfile',
            component: MahasiswaProfile,
            meta: {
              title: 'Profil Saya - SIA'
            }
          },
          {
            path: 'krs',
            name: 'MahasiswaKRS',
            component: MahasiswaKRS,
            meta: {
              title: 'Kartu Rencana Studi - SIA'
            }
          },
          {
            path: 'nilai',
            name: 'MahasiswaNilai',
            component: MahasiswaNilai,
            meta: {
              title: 'Nilai Akademik - SIA'
            }
          },
          {
            path: 'pembayaran',
            name: 'MahasiswaPembayaran',
            component: MahasiswaPembayaran,
            meta: {
              title: 'Pembayaran - SIA'
            }
          },
          {
            path: 'transkrip',
            name: 'MahasiswaTranskrip',
            component: MahasiswaTranskrip,
            meta: {
              title: 'Transkrip Nilai - SIA'
            }
          }
        ]
      },

      // Dosen routes
      {
        path: 'dosen',
        meta: { roles: ['dosen', 'admin'] },
        children: [
          {
            path: 'dashboard',
            name: 'DosenDashboard',
            component: DosenDashboard,
            meta: {
              title: 'Dashboard Dosen - SIA'
            }
          },
          {
            path: 'profile',
            name: 'DosenProfile',
            component: DosenProfile,
            meta: {
              title: 'Profil Dosen - SIA'
            }
          },
          {
            path: 'jadwal',
            name: 'DosenJadwal',
            component: DosenJadwal,
            meta: {
              title: 'Jadwal Mengajar - SIA'
            }
          },
          {
            path: 'input-nilai',
            name: 'DosenInputNilai',
            component: DosenInputNilai,
            meta: {
              title: 'Input Nilai - SIA'
            }
          }
        ]
      },

      // Staf routes
      {
        path: 'staf',
        meta: { roles: ['staf', 'admin'] },
        children: [
          {
            path: 'dashboard',
            name: 'StafDashboard',
            component: StafDashboard,
            meta: {
              title: 'Dashboard Staf - SIA'
            }
          }
        ]
      }
    ]
  },

  // Error pages
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: UnauthorizedPage,
    meta: {
      title: 'Akses Ditolak - SIA'
    }
  },
  {
    path: '/404',
    name: 'NotFound',
    component: NotFoundPage,
    meta: {
      title: 'Halaman Tidak Ditemukan - SIA'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title as string
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Store intended route for redirect after login
      const redirectPath = to.fullPath
      next(`/auth/login?redirect=${encodeURIComponent(redirectPath)}`)
      return
    }

    // Check if user has required role
    if (to.meta.roles) {
      const roles = to.meta.roles as string[]
      if (!authStore.hasRole(roles)) {
        next('/unauthorized')
        return
      }
    }
  }

  // Check if route requires guest (not authenticated)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
    return
  }

  // Handle dashboard route - redirect based on role
  if (to.name === 'Dashboard' && authStore.isAuthenticated) {
    const role = authStore.userRole
    switch (role) {
      case 'admin':
        next('/admin/dashboard')
        return
      case 'mahasiswa':
        next('/mahasiswa/dashboard')
        return
      case 'dosen':
        next('/dosen/dashboard')
        return
      case 'staf':
        next('/staf/dashboard')
        return
      default:
        next()
    }
    return
  }

  next()
})

export default router