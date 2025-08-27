<template>
  <div class="dosen-dashboard">
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard Dosen</h1>
      <p class="dashboard-subtitle">Selamat datang, {{ dosenName }}</p>
      <div class="dashboard-date">
        <span>{{ currentDate }}</span>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-stats">
      <DashboardCard
        title="Mata Kuliah Aktif"
        :value="stats.totalMataKuliah"
        icon-text="📚"
        variant="primary"
        :description="`${stats.kelasAktif} kelas sedang berjalan`"
        @view-more="navigateTo('/dosen/mata-kuliah')"
        show-view-more
      />
      
      <DashboardCard
        title="Total Mahasiswa"
        :value="stats.totalMahasiswa"
        icon-text="👥"
        variant="success"
        :description="`Di ${stats.totalKelas} kelas yang Anda ajar`"
        @view-more="navigateTo('/dosen/mahasiswa')"
        show-view-more
      />
      
      <DashboardCard
        title="Tugas Belum Dinilai"
        :value="stats.tugasBelumDinilai"
        icon-text="📝"
        variant="warning"
        :description="`${stats.ujianBelumDinilai} ujian juga menunggu`"
        @view-more="navigateTo('/dosen/input-nilai')"
        show-view-more
      />
      
      <DashboardCard
        title="Kehadiran Minggu Ini"
        :value="stats.kehadiranRate"
        icon-text="📊"
        variant="error"
        format="percentage"
        :description="`${stats.kelasHariIni} kelas hari ini`"
        @view-more="navigateTo('/dosen/kehadiran')"
        show-view-more
      />
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
      <h2 class="section-title">Menu Utama</h2>
      <div class="action-grid">
        <div class="action-category">
          <h3 class="category-title">Pengajaran</h3>
          <div class="action-cards">
            <router-link to="/dosen/jadwal" class="action-card">
              <div class="action-icon">📅</div>
              <div class="action-content">
                <h4>Jadwal Mengajar</h4>
                <p>Lihat jadwal kuliah dan atur waktu mengajar Anda</p>
                <div class="action-stats">
                  <span class="stat-item">{{ stats.kelasHariIni }} kelas hari ini</span>
                  <span class="stat-item">{{ stats.kelasAktif }} kelas aktif</span>
                </div>
              </div>
            </router-link>

            <router-link to="/dosen/mata-kuliah" class="action-card">
              <div class="action-icon">📚</div>
              <div class="action-content">
                <h4>Mata Kuliah</h4>
                <p>Kelola materi, silabus, dan informasi mata kuliah</p>
                <div class="action-stats">
                  <span class="stat-item">{{ stats.totalMataKuliah }} mata kuliah</span>
                  <span class="stat-item">Semester {{ currentSemester }}</span>
                </div>
              </div>
            </router-link>

            <router-link to="/dosen/mahasiswa" class="action-card">
              <div class="action-icon">👥</div>
              <div class="action-content">
                <h4>Daftar Mahasiswa</h4>
                <p>Lihat daftar mahasiswa yang mengambil mata kuliah Anda</p>
                <div class="action-stats">
                  <span class="stat-item">{{ stats.totalMahasiswa }} mahasiswa</span>
                  <span class="stat-item">{{ stats.totalKelas }} kelas</span>
                </div>
              </div>
            </router-link>

            <router-link to="/dosen/kehadiran" class="action-card">
              <div class="action-icon">✅</div>
              <div class="action-content">
                <h4>Kehadiran</h4>
                <p>Catat dan monitoring kehadiran mahasiswa</p>
                <div class="action-stats">
                  <span class="stat-item">{{ stats.kehadiranRate }}% rata-rata</span>
                  <span class="stat-item">Update real-time</span>
                </div>
              </div>
            </router-link>
          </div>
        </div>

        <div class="action-category">
          <h3 class="category-title">Penilaian</h3>
          <div class="action-cards">
            <router-link to="/dosen/input-nilai" class="action-card">
              <div class="action-icon">📝</div>
              <div class="action-content">
                <h4>Input Nilai</h4>
                <p>Input nilai tugas, UTS, UAS, dan komponen lainnya</p>
                <div class="action-stats">
                  <span class="stat-item">{{ stats.tugasBelumDinilai }} pending tugas</span>
                  <span class="stat-item">{{ stats.ujianBelumDinilai }} pending ujian</span>
                </div>
              </div>
            </router-link>

            <router-link to="/dosen/analisis-nilai" class="action-card">
              <div class="action-icon">📊</div>
              <div class="action-content">
                <h4>Analisis Nilai</h4>
                <p>Statistik dan analisis nilai mahasiswa per mata kuliah</p>
                <div class="action-stats">
                  <span class="stat-item">Rata-rata {{ stats.rataRataNilai }}</span>
                  <span class="stat-item">Dashboard analitik</span>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div class="dashboard-section">
      <h2 class="section-title">Jadwal Hari Ini</h2>
      <div class="schedule-today">
        <DashboardCard title="Jadwal Kuliah Hari Ini">
          <DataTable
            :data="todaySchedule"
            :columns="scheduleColumns"
            :loading="loadingSchedule"
            empty-title="Tidak ada jadwal hari ini"
            empty-message="Anda tidak memiliki jadwal mengajar hari ini"
            show-actions
            show-view
            @view="viewScheduleDetail"
          />
        </DashboardCard>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="dashboard-section">
      <h2 class="section-title">Aktivitas Terbaru</h2>
      <div class="recent-activities">
        <DashboardCard title="Tugas Baru Diserahkan">
          <DataTable
            :data="recentSubmissions"
            :columns="submissionColumns"
            :loading="loadingSubmissions"
            empty-title="Tidak ada tugas baru"
            empty-message="Belum ada tugas yang diserahkan hari ini"
            show-actions
            show-view
            @view="viewSubmission"
          />
        </DashboardCard>

        <DashboardCard title="Mahasiswa Terlambat">
          <DataTable
            :data="lateStudents"
            :columns="attendanceColumns"
            :loading="loadingAttendance"
            empty-title="Semua mahasiswa hadir tepat waktu"
            empty-message="Tidak ada keterlambatan hari ini"
          />
        </DashboardCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import DashboardCard from '../../components/DashboardCard.vue'
import DataTable from '../../components/DataTable.vue'

const router = useRouter()
const authStore = useAuthStore()

// Data states
const loadingSchedule = ref(false)
const loadingSubmissions = ref(false)
const loadingAttendance = ref(false)

// Computed values
const dosenName = computed(() => {
  return authStore.user?.nama_pengguna || 'Dosen'
})

const currentDate = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(new Date())
})

const currentSemester = computed(() => {
  return 'Ganjil 2024/2025'
})

// Mock data - replace with actual API calls
const stats = ref({
  totalMataKuliah: 4,
  kelasAktif: 6,
  totalMahasiswa: 180,
  totalKelas: 6,
  tugasBelumDinilai: 23,
  ujianBelumDinilai: 8,
  kehadiranRate: 85,
  kelasHariIni: 2,
  rataRataNilai: 78.5
})

const todaySchedule = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    kelas: 'A',
    waktu: '08:00 - 10:30',
    ruang: 'R.101',
    jumlah_mahasiswa: 35
  },
  {
    id: 2,
    mata_kuliah: 'Basis Data',
    kelas: 'B',
    waktu: '13:00 - 15:30',
    ruang: 'R.203',
    jumlah_mahasiswa: 40
  }
])

const recentSubmissions = ref([
  {
    id: 1,
    mahasiswa: 'Ahmad Sutanto',
    mata_kuliah: 'Algoritma dan Pemrograman',
    tugas: 'Tugas 3 - Sorting Algorithm',
    waktu_submit: '2 jam yang lalu',
    status: 'Baru'
  },
  {
    id: 2,
    mahasiswa: 'Siti Nurhaliza',
    mata_kuliah: 'Basis Data',
    tugas: 'Tugas 2 - ERD Design',
    waktu_submit: '4 jam yang lalu',
    status: 'Baru'
  }
])

const lateStudents = ref([
  {
    id: 1,
    mahasiswa: 'Budi Santoso',
    mata_kuliah: 'Algoritma dan Pemrograman',
    waktu_terlambat: '15 menit',
    keterangan: 'Macet di jalan'
  }
])

// Table columns
const scheduleColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'waktu', label: 'Waktu', sortable: true },
  { key: 'ruang', label: 'Ruang', align: 'center' },
  { key: 'jumlah_mahasiswa', label: 'Mahasiswa', align: 'center', format: 'number' }
]

const submissionColumns = [
  { key: 'mahasiswa', label: 'Mahasiswa', sortable: true },
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'tugas', label: 'Tugas' },
  { key: 'waktu_submit', label: 'Waktu Submit', sortable: true },
  { key: 'status', label: 'Status', align: 'center' }
]

const attendanceColumns = [
  { key: 'mahasiswa', label: 'Mahasiswa', sortable: true },
  { key: 'mata_kuliah', label: 'Mata Kuliah' },
  { key: 'waktu_terlambat', label: 'Terlambat', align: 'center' },
  { key: 'keterangan', label: 'Keterangan' }
]

// Methods
const navigateTo = (path: string) => {
  router.push(path)
}

const viewScheduleDetail = (schedule: any) => {
  console.log('View schedule detail:', schedule)
  // Implement schedule detail view
}

const viewSubmission = (submission: any) => {
  console.log('View submission:', submission)
  // Implement submission detail view
}

// Lifecycle
onMounted(() => {
  // Load dashboard data
  loadDashboardData()
})

const loadDashboardData = async () => {
  try {
    // TODO: Replace with actual API calls
    console.log('Loading dosen dashboard data...')
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}
</script>

<style scoped>
.dosen-dashboard {
  padding: 20px 0;
}

.dashboard-header {
  margin-bottom: 32px;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.dashboard-subtitle {
  color: #6b7280;
  font-size: 1.1rem;
  margin: 0 0 16px 0;
}

.dashboard-date {
  color: #9ca3af;
  font-size: 0.9rem;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.dashboard-section {
  margin-bottom: 32px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 8px;
}

.action-grid {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.action-category {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.action-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
}

.action-card {
  display: flex;
  gap: 16px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s ease;
  background: white;
}

.action-card:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
  transform: translateY(-2px);
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
  min-width: 0;
}

.action-content h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.action-content p {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0 0 12px 0;
  line-height: 1.4;
}

.action-stats {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.stat-item {
  font-size: 0.8rem;
  color: #9ca3af;
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 6px;
}

.schedule-today {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.recent-activities {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

@media (max-width: 768px) {
  .dashboard-stats {
    grid-template-columns: 1fr;
  }
  
  .action-cards {
    grid-template-columns: 1fr;
  }
  
  .recent-activities {
    grid-template-columns: 1fr;
  }
  
  .action-card {
    flex-direction: column;
    text-align: center;
  }
  
  .action-stats {
    justify-content: center;
  }
}
</style>
