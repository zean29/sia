<template>
  <div class="admin-dashboard">
    <!-- Modern Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="header-text">
          <h1 class="dashboard-title">Dashboard Administrator</h1>
          <p class="dashboard-subtitle">Kelola sistem informasi akademik universitas</p>
        </div>
        <div class="header-info">
          <div class="period-badge">
            <span class="period-label">Periode Aktif</span>
            <span class="period-value">{{ currentSemester }}</span>
          </div>
          <div class="date-info">
            <span class="date">{{ currentDate }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card stat-card--primary">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.totalMahasiswa || 0 }}</div>
            <div class="stat-label">Total Mahasiswa</div>
            <div class="stat-detail">{{ stats.mahasiswaAktif || 0 }} aktif</div>
          </div>
          <div class="stat-action">
            <router-link to="/admin/mahasiswa" class="view-btn">Kelola</router-link>
          </div>
        </div>

        <div class="stat-card stat-card--success">
          <div class="stat-icon">👨‍🏫</div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.totalDosen || 0 }}</div>
            <div class="stat-label">Total Dosen</div>
            <div class="stat-detail">{{ stats.dosenAktif || 0 }} mengajar</div>
          </div>
          <div class="stat-action">
            <router-link to="/admin/dosen" class="view-btn">Kelola</router-link>
          </div>
        </div>

        <div class="stat-card stat-card--warning">
          <div class="stat-icon">📚</div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.totalMataKuliah || 0 }}</div>
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-detail">{{ stats.mataKuliahAktif || 0 }} aktif</div>
          </div>
          <div class="stat-action">
            <router-link to="/admin/mata-kuliah" class="view-btn">Kelola</router-link>
          </div>
        </div>

        <div class="stat-card stat-card--info">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <div class="stat-value">{{ formatCurrency(stats.pendapatanBulanIni || 0) }}</div>
            <div class="stat-label">Pendapatan</div>
            <div class="stat-detail">Bulan ini</div>
          </div>
          <div class="stat-action">
            <router-link to="/admin/pembayaran" class="view-btn">Lihat</router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Access Menu -->
    <div class="menu-section">
      <h2 class="section-title">Menu Utama</h2>
      <div class="menu-grid">
        <!-- Academic Management -->
        <div class="menu-category">
          <h3 class="category-title">Manajemen Akademik</h3>
          <div class="menu-items">
            <router-link to="/admin/mahasiswa" class="menu-item">
              <div class="menu-icon">👥</div>
              <div class="menu-content">
                <div class="menu-title">Data Mahasiswa</div>
                <div class="menu-desc">Kelola pendaftaran dan data mahasiswa</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>

            <router-link to="/admin/dosen" class="menu-item">
              <div class="menu-icon">👨‍🏫</div>
              <div class="menu-content">
                <div class="menu-title">Data Dosen</div>
                <div class="menu-desc">Kelola profil dan jadwal dosen</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>

            <router-link to="/admin/mata-kuliah" class="menu-item">
              <div class="menu-icon">📚</div>
              <div class="menu-content">
                <div class="menu-title">Mata Kuliah</div>
                <div class="menu-desc">Manajemen kurikulum dan prasyarat</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>

            <router-link to="/admin/jadwal" class="menu-item">
              <div class="menu-icon">📅</div>
              <div class="menu-content">
                <div class="menu-title">Jadwal Kuliah</div>
                <div class="menu-desc">Atur jadwal dan ruang kelas</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>
          </div>
        </div>

        <!-- Financial Management -->
        <div class="menu-category">
          <h3 class="category-title">Manajemen Keuangan</h3>
          <div class="menu-items">
            <router-link to="/admin/pembayaran" class="menu-item">
              <div class="menu-icon">💳</div>
              <div class="menu-content">
                <div class="menu-title">Pembayaran SPP</div>
                <div class="menu-desc">Monitor dan verifikasi pembayaran</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>

            <router-link to="/admin/laporan" class="menu-item">
              <div class="menu-icon">📊</div>
              <div class="menu-content">
                <div class="menu-title">Laporan Keuangan</div>
                <div class="menu-desc">Generate laporan dan statistik</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>
          </div>
        </div>

        <!-- System Management -->
        <div class="menu-category">
          <h3 class="category-title">Sistem & Laporan</h3>
          <div class="menu-items">
            <router-link to="/admin/laporan" class="menu-item">
              <div class="menu-icon">📋</div>
              <div class="menu-content">
                <div class="menu-title">Laporan Akademik</div>
                <div class="menu-desc">Analisis dan laporan akademik</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>

            <router-link to="/admin/sistem" class="menu-item">
              <div class="menu-icon">⚙️</div>
              <div class="menu-content">
                <div class="menu-title">Pengaturan Sistem</div>
                <div class="menu-desc">Konfigurasi dan manajemen user</div>
              </div>
              <div class="menu-arrow">→</div>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="activity-section">
      <h2 class="section-title">Aktivitas Terbaru</h2>
      <div class="activity-grid">
        <div class="activity-card">
          <div class="activity-header">
            <h3>Mahasiswa Terdaftar Hari Ini</h3>
            <span class="activity-count">{{ recentStudents.length }}</span>
          </div>
          <div class="activity-content">
            <div v-if="loadingStudents" class="loading-state">
              <div class="loading-spinner"></div>
              <span>Memuat data...</span>
            </div>
            <div v-else-if="recentStudents.length === 0" class="empty-state">
              <div class="empty-icon">👥</div>
              <div class="empty-text">Belum ada pendaftaran hari ini</div>
            </div>
            <div v-else class="activity-list">
              <div v-for="student in recentStudents.slice(0, 5)" :key="student.id" class="activity-item">
                <div class="item-info">
                  <div class="item-title">{{ student.nama }}</div>
                  <div class="item-detail">{{ student.nim }} - {{ student.program_studi }}</div>
                </div>
                <div class="item-time">{{ formatTime(student.tanggal_daftar) }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="activity-card">
          <div class="activity-header">
            <h3>Pembayaran Terbaru</h3>
            <span class="activity-count">{{ recentPayments.length }}</span>
          </div>
          <div class="activity-content">
            <div v-if="loadingPayments" class="loading-state">
              <div class="loading-spinner"></div>
              <span>Memuat data...</span>
            </div>
            <div v-else-if="recentPayments.length === 0" class="empty-state">
              <div class="empty-icon">💳</div>
              <div class="empty-text">Belum ada pembayaran hari ini</div>
            </div>
            <div v-else class="activity-list">
              <div v-for="payment in recentPayments.slice(0, 5)" :key="payment.id" class="activity-item">
                <div class="item-info">
                  <div class="item-title">{{ payment.mahasiswa.nama }}</div>
                  <div class="item-detail">{{ formatCurrency(payment.jumlah) }} - {{ payment.status }}</div>
                </div>
                <div class="item-time">{{ formatTime(payment.tanggal) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '../../services/api'

// Interfaces for type safety
interface Student {
  id: number
  nim: string
  nama: string
  program_studi: string
  tanggal_daftar: string
}

interface Payment {
  id: number
  mahasiswa: {
    nim: string
    nama: string
  }
  jumlah: number
  tanggal: string
  status: string
}

const router = useRouter()

const stats = ref({
  totalMahasiswa: 0,
  mahasiswaAktif: 0,
  mahasiswaBaruBulanIni: 0,
  totalDosen: 0,
  dosenAktif: 0,
  totalMataKuliah: 0,
  mataKuliahAktif: 0,
  totalJadwal: 0,
  totalProdi: 0,
  pendapatanBulanIni: 0,
  targetPendapatan: 500000000,
  pembayaranPending: 0,
  totalUsers: 0
})

const recentStudents = ref<Student[]>([])
const recentPayments = ref<Payment[]>([])
const loadingStudents = ref(false)
const loadingPayments = ref(false)
const loading = ref(true)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const currentSemester = computed(() => {
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth() + 1
  return month >= 7 ? `Ganjil ${year}/${year + 1}` : `Genap ${year - 1}/${year}`
})

const studentColumns = [
  { key: 'nim', label: 'NIM', sortable: true },
  { key: 'nama', label: 'Nama Lengkap', sortable: true },
  { key: 'program_studi', label: 'Program Studi' },
  { key: 'tanggal_daftar', label: 'Tanggal Daftar', format: 'date' as const }
]

const paymentColumns = [
  { key: 'mahasiswa.nim', label: 'NIM' },
  { key: 'mahasiswa.nama', label: 'Nama' },
  { key: 'jumlah', label: 'Jumlah', format: 'currency' as const, align: 'right' as const },
  { key: 'tanggal', label: 'Tanggal', format: 'datetime' as const },
  { key: 'status', label: 'Status' }
]

const navigateTo = (path: string) => {
  router.push(path)
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewPayment = (payment: any) => {
  router.push(`/admin/pembayaran/${payment.id}`)
}

const loadDashboardData = async () => {
  try {
    loading.value = true
    console.log('Loading dashboard data...')
    
    // Load dashboard data from API
    const response = await apiService.dashboard.get()
    
    if (response.data.sukses) {
      const data = response.data.data
      
      // Update statistics
      if (data.statistik_umum) {
        stats.value = {
          ...stats.value,
          totalMahasiswa: data.statistik_umum.total_mahasiswa || 0,
          mahasiswaAktif: data.statistik_umum.mahasiswa_aktif || 0,
          totalDosen: data.statistik_umum.total_dosen || 0,
          dosenAktif: data.statistik_umum.dosen_aktif || 0,
          totalMataKuliah: data.statistik_umum.total_mata_kuliah || 0,
          mataKuliahAktif: data.statistik_umum.kelas_periode_ini || 0,
          pembayaranPending: data.pembayaran_tertunggak || 0
        }
      }
      
      console.log('Dashboard data loaded successfully', stats.value)
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
    
    // Use fallback dummy data for demo
    stats.value = {
      totalMahasiswa: 1250,
      mahasiswaAktif: 1180,
      mahasiswaBaruBulanIni: 45,
      totalDosen: 85,
      dosenAktif: 82,
      totalMataKuliah: 120,
      mataKuliahAktif: 85,
      totalJadwal: 340,
      totalProdi: 8,
      pendapatanBulanIni: 425000000,
      targetPendapatan: 500000000,
      pembayaranPending: 23,
      totalUsers: 1350
    }
  } finally {
    loading.value = false
  }
}

const loadRecentActivities = async () => {
  try {
    // Load recent students
    loadingStudents.value = true
    // TODO: Replace with actual API call when available
    recentStudents.value = [
      {
        id: 1,
        nim: '24010101',
        nama: 'Ahmad Rizki Pratama',
        program_studi: 'Teknik Informatika',
        tanggal_daftar: new Date().toISOString()
      },
      {
        id: 2,
        nim: '24010102',
        nama: 'Siti Aisyah Putri',
        program_studi: 'Sistem Informasi',
        tanggal_daftar: new Date().toISOString()
      },
      {
        id: 3,
        nim: '24010103',
        nama: 'Muhammad Fajar',
        program_studi: 'Teknik Komputer',
        tanggal_daftar: new Date().toISOString()
      }
    ]
    loadingStudents.value = false
    
    // Load recent payments
    loadingPayments.value = true
    // TODO: Replace with actual API call when available
    recentPayments.value = [
      {
        id: 1,
        mahasiswa: { nim: '23010001', nama: 'Budi Santoso' },
        jumlah: 2500000,
        tanggal: new Date().toISOString(),
        status: 'Berhasil'
      },
      {
        id: 2,
        mahasiswa: { nim: '23010002', nama: 'Maya Sari Dewi' },
        jumlah: 2500000,
        tanggal: new Date().toISOString(),
        status: 'Pending'
      },
      {
        id: 3,
        mahasiswa: { nim: '23010003', nama: 'Andi Wijaya' },
        jumlah: 2750000,
        tanggal: new Date().toISOString(),
        status: 'Berhasil'
      }
    ]
    loadingPayments.value = false
  } catch (error) {
    console.error('Failed to load recent activities:', error)
    loadingStudents.value = false
    loadingPayments.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    loadDashboardData(),
    loadRecentActivities()
  ])
})
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background: #fafbfc;
  padding: 0;
}

/* Header Styles */
.dashboard-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem 0;
  margin: -20px -20px 2rem -20px;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.header-text {
  flex: 1;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.025em;
}

.dashboard-subtitle {
  font-size: 1.1rem;
  margin: 0;
  opacity: 0.9;
  font-weight: 300;
}

.header-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.period-badge {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 0.75rem 1rem;
  border-radius: 10px;
  text-align: center;
}

.period-label {
  display: block;
  font-size: 0.8rem;
  opacity: 0.8;
  margin-bottom: 0.25rem;
}

.period-value {
  font-weight: 600;
  font-size: 0.9rem;
}

.date-info {
  font-size: 0.9rem;
  opacity: 0.8;
}

/* Statistics Section */
.stats-section {
  margin-bottom: 3rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  transition: all 0.3s ease;
}

.stat-card--primary::before { background: #667eea; }
.stat-card--success::before { background: #10b981; }
.stat-card--warning::before { background: #f59e0b; }
.stat-card--info::before { background: #3b82f6; }

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  border-radius: 12px;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
  margin: 0 0 0.25rem 0;
}

.stat-detail {
  font-size: 0.75rem;
  color: #9ca3af;
}

.stat-action {
  flex-shrink: 0;
}

.view-btn {
  background: #f3f4f6;
  color: #374151;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
}

.view-btn:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

/* Menu Section */
.menu-section {
  margin-bottom: 3rem;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
  padding: 0 2rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1.5rem 0;
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.menu-category {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.category-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem 0;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.menu-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
  border: 1px solid #f3f4f6;
}

.menu-item:hover {
  background: #f8fafc;
  border-color: #667eea;
  transform: translateX(4px);
}

.menu-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  flex-shrink: 0;
}

.menu-content {
  flex: 1;
  min-width: 0;
}

.menu-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.menu-desc {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
}

.menu-arrow {
  font-size: 1.25rem;
  color: #9ca3af;
  transition: all 0.2s ease;
}

.menu-item:hover .menu-arrow {
  color: #667eea;
  transform: translateX(4px);
}

/* Activity Section */
.activity-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.activity-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.activity-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.activity-header {
  padding: 1.5rem 1.5rem 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f3f4f6;
}

.activity-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.activity-count {
  background: #667eea;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
}

.activity-content {
  padding: 1rem 1.5rem 1.5rem 1.5rem;
}

.loading-state {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  justify-content: center;
  color: #6b7280;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
  opacity: 0.5;
}

.empty-text {
  font-size: 0.875rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.activity-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #f3f4f6;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.item-detail {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

.item-time {
  font-size: 0.75rem;
  color: #9ca3af;
  flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .header-info {
    align-items: flex-start;
    width: 100%;
  }
  
  .dashboard-title {
    font-size: 2rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    padding: 0 1rem;
  }
  
  .menu-grid {
    grid-template-columns: 1fr;
  }
  
  .activity-grid {
    grid-template-columns: 1fr;
  }
  
  .menu-section {
    padding: 0 1rem;
  }
  
  .activity-section {
    padding: 0 1rem;
  }
}

@media (max-width: 480px) {
  .dashboard-header {
    margin: -20px -20px 1rem -20px;
  }
  
  .header-content {
    padding: 0 1rem;
  }
  
  .stats-grid {
    padding: 0 0.5rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .menu-section {
    padding: 0 0.5rem;
  }
  
  .activity-section {
    padding: 0 0.5rem;
  }
}
</style>