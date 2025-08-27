<template>
  <div class="mahasiswa-kehadiran">
    <div class="page-header">
      <h1>Kehadiran</h1>
      <p>Monitor kehadiran dan absensi perkuliahan</p>
    </div>

    <div class="content-wrapper">
      <!-- Attendance Summary -->
      <div class="attendance-summary">
        <div class="summary-cards">
          <DashboardCard
            title="Tingkat Kehadiran"
            :value="attendanceStats.percentage"
            icon-text="📊"
            variant="primary"
            format="percentage"
            :description="`${attendanceStats.present} dari ${attendanceStats.total} pertemuan`"
          />
          
          <DashboardCard
            title="Tidak Hadir"
            :value="attendanceStats.absent"
            icon-text="❌"
            variant="warning"
            :description="`${attendanceStats.excused} dengan izin`"
          />
          
          <DashboardCard
            title="Terlambat"
            :value="attendanceStats.late"
            icon-text="⏰"
            variant="error"
            :description="`Rata-rata ${attendanceStats.averageLateMinutes} menit`"
          />
          
          <DashboardCard
            title="Pertemuan Tersisa"
            :value="attendanceStats.remaining"
            icon-text="📅"
            variant="success"
            :description="`Dari ${attendanceStats.totalPlanned} pertemuan`"
          />
        </div>
      </div>

      <!-- Attendance by Subject -->
      <div class="attendance-by-subject">
        <DashboardCard title="Kehadiran per Mata Kuliah">
          <DataTable
            :data="attendanceBySubject"
            :columns="subjectColumns"
            :loading="loadingSubjects"
            empty-title="Tidak ada data kehadiran"
            empty-message="Belum ada data kehadiran yang tercatat"
            show-actions
            show-view
            @view="viewSubjectDetail"
          />
        </DashboardCard>
      </div>

      <!-- Recent Attendance -->
      <div class="recent-attendance">
        <DashboardCard title="Riwayat Kehadiran Terbaru">
          <DataTable
            :data="recentAttendance"
            :columns="attendanceColumns"
            :loading="loadingRecent"
            empty-title="Tidak ada riwayat"
            empty-message="Belum ada riwayat kehadiran"
          />
        </DashboardCard>
      </div>

      <!-- Attendance Calendar -->
      <div class="attendance-calendar">
        <DashboardCard title="Kalender Kehadiran">
          <div class="calendar-controls">
            <button @click="previousMonth" class="btn btn--outline">
              ← Bulan Sebelumnya
            </button>
            <h3>{{ currentMonthYear }}</h3>
            <button @click="nextMonth" class="btn btn--outline">
              Bulan Selanjutnya →
            </button>
          </div>
          
          <div class="calendar-legend">
            <div class="legend-item">
              <div class="legend-color present"></div>
              <span>Hadir</span>
            </div>
            <div class="legend-item">
              <div class="legend-color late"></div>
              <span>Terlambat</span>
            </div>
            <div class="legend-item">
              <div class="legend-color absent"></div>
              <span>Tidak Hadir</span>
            </div>
            <div class="legend-item">
              <div class="legend-color excused"></div>
              <span>Izin</span>
            </div>
          </div>
          
          <div class="calendar-grid">
            <div class="calendar-header">
              <div v-for="day in daysShort" :key="day" class="calendar-day-header">
                {{ day }}
              </div>
            </div>
            <div class="calendar-body">
              <div 
                v-for="date in calendarDates" 
                :key="date.date"
                class="calendar-date"
                :class="{ 
                  'other-month': !date.currentMonth,
                  'today': date.isToday
                }"
              >
                <span class="date-number">{{ date.day }}</span>
                <div v-if="date.attendance" class="attendance-indicators">
                  <div 
                    v-for="att in date.attendance" 
                    :key="att.id"
                    class="attendance-dot"
                    :class="att.status"
                    :title="`${att.subject}: ${att.statusText}`"
                  ></div>
                </div>
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
import DashboardCard from '@/components/DashboardCard.vue'
import DataTable from '@/components/DataTable.vue'

// Data
const loadingSubjects = ref(false)
const loadingRecent = ref(false)
const currentDate = ref(new Date())

const daysShort = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

// Mock data
const attendanceStats = ref({
  percentage: 88.5,
  present: 53,
  total: 60,
  absent: 7,
  excused: 3,
  late: 12,
  averageLateMinutes: 8,
  remaining: 24,
  totalPlanned: 84
})

const attendanceBySubject = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    total_pertemuan: 14,
    hadir: 13,
    tidak_hadir: 1,
    terlambat: 2,
    persentase: 92.9,
    status: 'Baik'
  },
  {
    id: 2,
    mata_kuliah: 'Pengantar Sistem Informasi',
    total_pertemuan: 14,
    hadir: 12,
    tidak_hadir: 2,
    terlambat: 1,
    persentase: 85.7,
    status: 'Cukup'
  },
  {
    id: 3,
    mata_kuliah: 'Matematika Diskrit',
    total_pertemuan: 14,
    hadir: 11,
    tidak_hadir: 3,
    terlambat: 3,
    persentase: 78.6,
    status: 'Perlu Perhatian'
  }
])

const recentAttendance = ref([
  {
    id: 1,
    tanggal: '2024-09-25',
    mata_kuliah: 'Algoritma dan Pemrograman',
    jam: '08:00 - 10:30',
    status: 'Hadir',
    keterangan: 'Tepat waktu',
    pertemuan: 'Pertemuan 7'
  },
  {
    id: 2,
    tanggal: '2024-09-24',
    mata_kuliah: 'Pengantar Sistem Informasi',
    jam: '13:00 - 15:30',
    status: 'Terlambat',
    keterangan: '5 menit terlambat',
    pertemuan: 'Pertemuan 6'
  },
  {
    id: 3,
    tanggal: '2024-09-23',
    mata_kuliah: 'Matematika Diskrit',
    jam: '10:30 - 12:00',
    status: 'Tidak Hadir',
    keterangan: 'Sakit (dengan surat dokter)',
    pertemuan: 'Pertemuan 5'
  }
])

// Computed
const currentMonthYear = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    month: 'long',
    year: 'numeric'
  }).format(currentDate.value)
})

const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - (firstDay.getDay() || 7) + 1)
  
  const dates = []
  const current = new Date(startDate)
  
  for (let i = 0; i < 42; i++) {
    const isCurrentMonth = current.getMonth() === month
    const isToday = current.toDateString() === new Date().toDateString()
    
    // Mock attendance data for calendar
    const attendance = isCurrentMonth && current.getDate() <= new Date().getDate() ? [
      { id: 1, subject: 'AlgoPro', status: 'present', statusText: 'Hadir' },
      { id: 2, subject: 'SisInfo', status: 'late', statusText: 'Terlambat' }
    ] : null
    
    dates.push({
      date: new Date(current),
      day: current.getDate(),
      currentMonth: isCurrentMonth,
      isToday,
      attendance
    })
    
    current.setDate(current.getDate() + 1)
  }
  
  return dates
})

// Table columns
const subjectColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'total_pertemuan', label: 'Total', align: 'center', format: 'number' },
  { key: 'hadir', label: 'Hadir', align: 'center', format: 'number' },
  { key: 'tidak_hadir', label: 'Tidak Hadir', align: 'center', format: 'number' },
  { key: 'terlambat', label: 'Terlambat', align: 'center', format: 'number' },
  { key: 'persentase', label: 'Persentase', align: 'center', format: 'percentage' },
  { key: 'status', label: 'Status', align: 'center' }
]

const attendanceColumns = [
  { key: 'tanggal', label: 'Tanggal', sortable: true, format: 'date' },
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'pertemuan', label: 'Pertemuan' },
  { key: 'jam', label: 'Waktu' },
  { key: 'status', label: 'Status', align: 'center' },
  { key: 'keterangan', label: 'Keterangan' }
]

// Methods
const viewSubjectDetail = (subject: any) => {
  console.log('View subject detail:', subject)
  // Implement subject detail view
}

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

// Lifecycle
onMounted(() => {
  // Load attendance data
})
</script>

<style scoped>
.mahasiswa-kehadiran {
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

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.attendance-by-subject,
.recent-attendance,
.attendance-calendar {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.calendar-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.calendar-controls h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #1f2937;
}

.calendar-legend {
  display: flex;
  gap: 20px;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #374151;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-color.present {
  background: #10b981;
}

.legend-color.late {
  background: #f59e0b;
}

.legend-color.absent {
  background: #ef4444;
}

.legend-color.excused {
  background: #6b7280;
}

.calendar-grid {
  padding: 20px;
}

.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  margin-bottom: 8px;
}

.calendar-day-header {
  text-align: center;
  font-weight: 600;
  color: #374151;
  padding: 8px;
}

.calendar-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e5e7eb;
}

.calendar-date {
  background: white;
  min-height: 80px;
  padding: 8px;
  position: relative;
  display: flex;
  flex-direction: column;
}

.calendar-date.other-month {
  background: #f9fafb;
  color: #9ca3af;
}

.calendar-date.today {
  background: #eff6ff;
  border: 2px solid #3b82f6;
}

.date-number {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 4px;
}

.attendance-indicators {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.attendance-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.attendance-dot.present {
  background: #10b981;
}

.attendance-dot.late {
  background: #f59e0b;
}

.attendance-dot.absent {
  background: #ef4444;
}

.attendance-dot.excused {
  background: #6b7280;
}

.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn--outline {
  background: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn--outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

@media (max-width: 768px) {
  .summary-cards {
    grid-template-columns: 1fr;
  }
  
  .calendar-controls {
    flex-direction: column;
    gap: 16px;
  }
  
  .calendar-legend {
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .calendar-date {
    min-height: 60px;
    padding: 4px;
  }
}
</style>