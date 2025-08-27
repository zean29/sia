<template>
  <div class="dosen-kehadiran">
    <div class="page-header">
      <h1>Kehadiran</h1>
      <p>Catat dan monitoring kehadiran mahasiswa</p>
    </div>

    <div class="content-wrapper">
      <!-- Attendance Stats -->
      <div class="attendance-stats">
        <div class="stats-cards">
          <DashboardCard
            title="Rata-rata Kehadiran"
            :value="attendanceStats.average"
            icon-text="📊"
            variant="primary"
            format="percentage"
            description="Kehadiran semua kelas"
          />
          
          <DashboardCard
            title="Pertemuan Hari Ini"
            :value="attendanceStats.todayClasses"
            icon-text="📅"
            variant="success"
            description="Kelas yang perlu diabsen"
          />
          
          <DashboardCard
            title="Mahasiswa Hadir"
            :value="attendanceStats.presentToday"
            icon-text="✅"
            variant="warning"
            description="Dari total hari ini"
          />
          
          <DashboardCard
            title="Keterlambatan"
            :value="attendanceStats.lateToday"
            icon-text="⏰"
            variant="error"
            description="Mahasiswa terlambat"
          />
        </div>
      </div>

      <!-- Today's Classes -->
      <div class="todays-classes">
        <DashboardCard title="Kelas Hari Ini">
          <DataTable
            :data="todayClasses"
            :columns="classColumns"
            :loading="loadingClasses"
            empty-title="Tidak ada kelas hari ini"
            empty-message="Anda tidak memiliki kelas yang perlu diabsen hari ini"
            show-actions
            show-view
            @view="takeAttendance"
          />
        </DashboardCard>
      </div>

      <!-- Attendance History -->
      <div class="attendance-history">
        <DashboardCard title="Riwayat Kehadiran">
          <div class="history-filters">
            <select v-model="selectedCourse" class="filter-select">
              <option value="">Semua Mata Kuliah</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.nama }}
              </option>
            </select>
            <select v-model="selectedWeek" class="filter-select">
              <option value="">Minggu Ini</option>
              <option value="last">Minggu Lalu</option>
              <option value="all">Semua</option>
            </select>
          </div>
          
          <DataTable
            :data="attendanceHistory"
            :columns="historyColumns"
            :loading="loadingHistory"
            empty-title="Tidak ada riwayat"
            empty-message="Belum ada riwayat kehadiran"
            show-actions
            show-view
            show-edit
            @view="viewAttendanceDetail"
            @edit="editAttendance"
          />
        </DashboardCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import DashboardCard from '@/components/DashboardCard.vue'
import DataTable from '@/components/DataTable.vue'

// Data
const loadingClasses = ref(false)
const loadingHistory = ref(false)
const selectedCourse = ref('')
const selectedWeek = ref('')

// Mock data
const attendanceStats = ref({
  average: 88.5,
  todayClasses: 2,
  presentToday: 45,
  lateToday: 3
})

const courses = ref([
  { id: 1, nama: 'Algoritma dan Pemrograman' },
  { id: 2, nama: 'Basis Data' }
])

const todayClasses = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    kelas: 'A',
    waktu: '08:00 - 10:30',
    ruang: 'R.101',
    jumlah_mahasiswa: 35,
    status_absen: 'Belum Diabsen'
  },
  {
    id: 2,
    mata_kuliah: 'Basis Data',
    kelas: 'B',
    waktu: '13:00 - 15:30',
    ruang: 'R.203',
    jumlah_mahasiswa: 40,
    status_absen: 'Sudah Diabsen'
  }
])

const attendanceHistory = ref([
  {
    id: 1,
    tanggal: '2024-09-25',
    mata_kuliah: 'Algoritma dan Pemrograman',
    kelas: 'A',
    pertemuan: 7,
    hadir: 32,
    tidak_hadir: 3,
    terlambat: 2,
    persentase: 91.4
  },
  {
    id: 2,
    tanggal: '2024-09-24',
    mata_kuliah: 'Basis Data',
    kelas: 'B',
    pertemuan: 6,
    hadir: 38,
    tidak_hadir: 2,
    terlambat: 1,
    persentase: 95.0
  }
])

// Table columns
const classColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'waktu', label: 'Waktu', sortable: true },
  { key: 'ruang', label: 'Ruang', align: 'center' },
  { key: 'jumlah_mahasiswa', label: 'Mahasiswa', align: 'center', format: 'number' },
  { key: 'status_absen', label: 'Status', align: 'center' }
]

const historyColumns = [
  { key: 'tanggal', label: 'Tanggal', sortable: true, format: 'date' },
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'pertemuan', label: 'Pertemuan', align: 'center', format: 'number' },
  { key: 'hadir', label: 'Hadir', align: 'center', format: 'number' },
  { key: 'tidak_hadir', label: 'Tidak Hadir', align: 'center', format: 'number' },
  { key: 'terlambat', label: 'Terlambat', align: 'center', format: 'number' },
  { key: 'persentase', label: 'Persentase', align: 'center', format: 'percentage' }
]

// Methods
const takeAttendance = (classItem: any) => {
  console.log('Take attendance for:', classItem)
  // Navigate to attendance taking page
}

const viewAttendanceDetail = (attendance: any) => {
  console.log('View attendance detail:', attendance)
}

const editAttendance = (attendance: any) => {
  console.log('Edit attendance:', attendance)
}

// Lifecycle
onMounted(() => {
  // Load attendance data
})
</script>

<style scoped>
.dosen-kehadiran {
  padding: 20px 0;
}

.page-header {
  margin-bottom: 32px;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.page-header p {
  color: #6b7280;
  margin: 0;
}

.content-wrapper {
  display: grid;
  gap: 32px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.history-filters {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .history-filters {
    flex-direction: column;
  }
}
</style>