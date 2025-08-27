<template>
  <div class="mahasiswa-dashboard">
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard Mahasiswa</h1>
      <p class="dashboard-subtitle">Selamat datang, {{ mahasiswaName }}</p>
      <div class="dashboard-info">
        <span class="nim">NIM: {{ mahasiswaNim }}</span>
        <span class="semester">Semester {{ currentSemester }}</span>
        <span class="date">{{ currentDate }}</span>
      </div>
    </div>

    <!-- Academic Status Cards -->
    <div class="dashboard-stats">
      <DashboardCard
        title="IPK Kumulatif"
        :value="academicStats.ipk"
        icon-text="🏆"
        variant="primary"
        format="number"
        :description="`Total ${academicStats.totalSks} SKS telah diselesaikan`"
      />
      
      <DashboardCard
        title="SKS Semester Ini"
        :value="academicStats.sksSekarang"
        icon-text="📚"
        variant="success"
        :description="`${academicStats.mataKuliahSekarang} mata kuliah diambil`"
        @view-more="navigateTo('/mahasiswa/krs')"
        show-view-more
      />
      
      <DashboardCard
        title="Tagihan SPP"
        :value="paymentStats.tagihanAktif"
        icon-text="💳"
        variant="warning"
        format="currency"
        :description="`Jatuh tempo: ${paymentStats.jatuhTempo}`"
        @view-more="navigateTo('/mahasiswa/pembayaran')"
        show-view-more
      />
      
      <DashboardCard
        title="Kehadiran"
        :value="academicStats.tingkatKehadiran"
        icon-text="✅"
        variant="error"
        format="percentage"
        :description="`${academicStats.totalPertemuan} pertemuan semester ini`"
        @view-more="navigateTo('/mahasiswa/kehadiran')"
        show-view-more
      />
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
      <h2 class="section-title">Menu Utama</h2>
      <div class="action-grid">
        <div class="action-category">
          <h3 class="category-title">Akademik</h3>
          <div class="action-cards">
            <router-link to="/mahasiswa/krs" class="action-card">
              <div class="action-icon">📝</div>
              <div class="action-content">
                <h4>Kartu Rencana Studi (KRS)</h4>
                <p>Daftar mata kuliah, lihat jadwal, dan kelola KRS Anda</p>
                <div class="action-stats">
                  <span class="stat-item">{{ academicStats.mataKuliahSekarang }} mata kuliah</span>
                  <span class="stat-item">{{ academicStats.sksSekarang }} SKS</span>
                </div>
              </div>
            </router-link>

            <router-link to="/mahasiswa/nilai" class="action-card">
              <div class="action-icon">📊</div>
              <div class="action-content">
                <h4>Transkrip Nilai</h4>
                <p>Lihat nilai semester dan transkrip akademik lengkap</p>
                <div class="action-stats">
                  <span class="stat-item">IPK {{ academicStats.ipk }}</span>
                  <span class="stat-item">{{ academicStats.totalSks }} SKS total</span>
                </div>
              </div>
            </router-link>

            <router-link to="/mahasiswa/jadwal" class="action-card">
              <div class="action-icon">📅</div>
              <div class="action-content">
                <h4>Jadwal Kuliah</h4>
                <p>Jadwal perkuliahan dan agenda akademik Anda</p>
                <div class="action-stats">
                  <span class="stat-item">{{ todayClasses.length }} kelas hari ini</span>
                  <span class="stat-item">Semester {{ currentSemester }}</span>
                </div>
              </div>
            </router-link>

            <router-link to="/mahasiswa/kehadiran" class="action-card">
              <div class="action-icon">✅</div>
              <div class="action-content">
                <h4>Kehadiran</h4>
                <p>Monitor kehadiran dan absensi perkuliahan</p>
                <div class="action-stats">
                  <span class="stat-item">{{ academicStats.tingkatKehadiran }}% hadir</span>
                  <span class="stat-item">{{ academicStats.totalPertemuan }} pertemuan</span>
                </div>
              </div>
            </router-link>
          </div>
        </div>

        <div class="action-category">
          <h3 class="category-title">Keuangan & Administrasi</h3>
          <div class="action-cards">
            <router-link to="/mahasiswa/pembayaran" class="action-card">
              <div class="action-icon">💳</div>
              <div class="action-content">
                <h4>Pembayaran SPP</h4>
                <p>Bayar SPP, lihat riwayat pembayaran dan tagihan</p>
                <div class="action-stats">
                  <span class="stat-item">{{ formatCurrency(paymentStats.tagihanAktif) }}</span>
                  <span class="stat-item">{{ paymentStats.status }}</span>
                </div>
              </div>
            </router-link>

            <router-link to="/mahasiswa/berkas" class="action-card">
              <div class="action-icon">📄</div>
              <div class="action-content">
                <h4>Berkas & Dokumen</h4>
                <p>Unduh transkrip, sertifikat, dan dokumen akademik</p>
                <div class="action-stats">
                  <span class="stat-item">{{ documentStats.available }} tersedia</span>
                  <span class="stat-item">Download center</span>
                </div>
              </div>
            </router-link>

            <router-link to="/mahasiswa/profil" class="action-card">
              <div class="action-icon">👤</div>
              <div class="action-content">
                <h4>Profil Mahasiswa</h4>
                <p>Update data pribadi, kontak, dan informasi akademik</p>
                <div class="action-stats">
                  <span class="stat-item">Data {{ profileStatus.completeness }}%</span>
                  <span class="stat-item">{{ profileStatus.status }}</span>
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
      <div class="today-schedule">
        <DashboardCard title="Jadwal Kuliah Hari Ini">
          <DataTable
            :data="todayClasses"
            :columns="scheduleColumns"
            :loading="loadingSchedule"
            empty-title="Tidak ada jadwal hari ini"
            empty-message="Anda tidak memiliki jadwal kuliah hari ini. Selamat menikmati hari libur!"
            show-actions
            show-view
            @view="viewClassDetail"
          />
        </DashboardCard>
      </div>
    </div>

    <!-- Academic Progress -->
    <div class="dashboard-section">
      <h2 class="section-title">Progress Akademik</h2>
      <div class="academic-progress">
        <DashboardCard title="Nilai Terbaru">
          <DataTable
            :data="recentGrades"
            :columns="gradeColumns"
            :loading="loadingGrades"
            empty-title="Belum ada nilai"
            empty-message="Nilai untuk semester ini belum tersedia"
          />
        </DashboardCard>

        <DashboardCard title="Pembayaran Terbaru">
          <DataTable
            :data="recentPayments"
            :columns="paymentColumns"
            :loading="loadingPayments"
            empty-title="Belum ada pembayaran"
            empty-message="Belum ada riwayat pembayaran bulan ini"
            show-actions
            show-view
            @view="viewPaymentDetail"
          />
        </DashboardCard>
      </div>
    </div>

    <!-- Announcements -->
    <div class="dashboard-section">
      <h2 class="section-title">Pengumuman</h2>
      <div class="announcements">
        <DashboardCard title="Pengumuman Terbaru">
          <div v-if="announcements.length === 0" class="empty-announcements">
            <div class="empty-icon">📢</div>
            <h4>Tidak ada pengumuman</h4>
            <p>Saat ini tidak ada pengumuman terbaru</p>
          </div>
          <div v-else class="announcement-list">
            <div 
              v-for="announcement in announcements" 
              :key="announcement.id"
              class="announcement-item"
            >
              <div class="announcement-icon">
                {{ announcement.priority === 'high' ? '🔴' : announcement.priority === 'medium' ? '🟡' : '🟢' }}
              </div>
              <div class="announcement-content">
                <h5>{{ announcement.title }}</h5>
                <p>{{ announcement.excerpt }}</p>
                <span class="announcement-date">{{ announcement.date }}</span>
              </div>
            </div>
          </div>
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
const loadingGrades = ref(false)
const loadingPayments = ref(false)

// Computed values
const mahasiswaName = computed(() => {
  return authStore.user?.nama_pengguna || 'Mahasiswa'
})

const mahasiswaNim = computed(() => {
  return '24010001' // TODO: Get from auth store
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
  return '1' // TODO: Get from academic data
})

// Mock data - replace with actual API calls
const academicStats = ref({
  ipk: 0.00, // New student
  totalSks: 0,
  sksSekarang: 18,
  mataKuliahSekarang: 6,
  tingkatKehadiran: 95,
  totalPertemuan: 24
})

const paymentStats = ref({
  tagihanAktif: 12500000,
  jatuhTempo: '30 September 2024',
  status: 'Belum Bayar'
})

const documentStats = ref({
  available: 3
})

const profileStatus = ref({
  completeness: 85,
  status: 'Lengkap'
})

const todayClasses = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    dosen: 'Dr. Bambang Suharto',
    waktu: '08:00 - 10:30',
    ruang: 'R.101',
    kelas: 'A'
  },
  {
    id: 2,
    mata_kuliah: 'Pengantar Sistem Informasi',
    dosen: 'Dr. Citra Dewi',
    waktu: '13:00 - 15:30',
    ruang: 'R.201',
    kelas: 'A'
  }
])

const recentGrades = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    jenis: 'Tugas 1',
    nilai: 85,
    tanggal: '2024-09-20'
  }
])

const recentPayments = ref([
  {
    id: 1,
    jenis: 'SPP',
    periode: 'September 2024',
    jumlah: 2500000,
    tanggal: '2024-09-01',
    status: 'Lunas'
  }
])

const announcements = ref([
  {
    id: 1,
    title: 'Pengumuman Libur Nasional',
    excerpt: 'Kampus akan libur pada tanggal 17 Agustus 2024 dalam rangka HUT RI ke-79',
    date: '2024-08-15',
    priority: 'high'
  },
  {
    id: 2,
    title: 'Pendaftaran Beasiswa',
    excerpt: 'Pendaftaran beasiswa prestasi akademik telah dibuka untuk semester ganjil 2024/2025',
    date: '2024-08-20',
    priority: 'medium'
  }
])

// Table columns
const scheduleColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'dosen', label: 'Dosen', sortable: true },
  { key: 'waktu', label: 'Waktu', sortable: true },
  { key: 'ruang', label: 'Ruang', align: 'center' },
  { key: 'kelas', label: 'Kelas', align: 'center' }
]

const gradeColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'jenis', label: 'Jenis', sortable: true },
  { key: 'nilai', label: 'Nilai', align: 'center', format: 'number' },
  { key: 'tanggal', label: 'Tanggal', sortable: true, format: 'date' }
]

const paymentColumns = [
  { key: 'jenis', label: 'Jenis Pembayaran', sortable: true },
  { key: 'periode', label: 'Periode', sortable: true },
  { key: 'jumlah', label: 'Jumlah', align: 'right', format: 'currency' },
  { key: 'tanggal', label: 'Tanggal', sortable: true, format: 'date' },
  { key: 'status', label: 'Status', align: 'center' }
]

// Methods
const navigateTo = (path: string) => {
  router.push(path)
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const viewClassDetail = (classItem: any) => {
  console.log('View class detail:', classItem)
  // Implement class detail view
}

const viewPaymentDetail = (payment: any) => {
  console.log('View payment detail:', payment)
  // Implement payment detail view
}

// Lifecycle
onMounted(() => {
  // Load dashboard data
  loadDashboardData()
})

const loadDashboardData = async () => {
  try {
    // TODO: Replace with actual API calls
    console.log('Loading mahasiswa dashboard data...')
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}
</script>

<style scoped>
.mahasiswa-dashboard {
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

.dashboard-info {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  align-items: center;
}

.nim {
  font-weight: 600;
  color: #374151;
  background: #f3f4f6;
  padding: 6px 12px;
  border-radius: 6px;
}

.semester {
  color: #059669;
  background: #dcfce7;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
}

.date {
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

.today-schedule {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.academic-progress {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

.announcements {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-announcements {
  text-align: center;
  padding: 40px 20px;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.empty-announcements h4 {
  color: #374151;
  margin-bottom: 8px;
}

.empty-announcements p {
  color: #6b7280;
  margin: 0;
}

.announcement-list {
  padding: 16px;
}

.announcement-item {
  display: flex;
  gap: 12px;
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
}

.announcement-item:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.announcement-item:last-child {
  margin-bottom: 0;
}

.announcement-icon {
  font-size: 1.2rem;
  flex-shrink: 0;
}

.announcement-content {
  flex: 1;
  min-width: 0;
}

.announcement-content h5 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.announcement-content p {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.announcement-date {
  color: #9ca3af;
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .dashboard-stats {
    grid-template-columns: 1fr;
  }
  
  .action-cards {
    grid-template-columns: 1fr;
  }
  
  .academic-progress {
    grid-template-columns: 1fr;
  }
  
  .action-card {
    flex-direction: column;
    text-align: center;
  }
  
  .action-stats {
    justify-content: center;
  }
  
  .dashboard-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .announcement-item {
    flex-direction: column;
    text-align: center;
  }
}
