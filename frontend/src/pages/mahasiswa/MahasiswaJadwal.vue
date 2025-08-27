<template>
  <div class="mahasiswa-jadwal">
    <div class="page-header">
      <h1>Jadwal Kuliah</h1>
      <p>Jadwal perkuliahan semester {{ currentSemester }}</p>
    </div>

    <div class="content-wrapper">
      <!-- Weekly Schedule -->
      <div class="schedule-section">
        <DashboardCard title="Jadwal Mingguan">
          <div class="schedule-grid">
            <div class="schedule-header">
              <div class="time-col">Waktu</div>
              <div class="day-col" v-for="day in days" :key="day">{{ day }}</div>
            </div>
            <div class="schedule-body">
              <div 
                v-for="time in timeSlots" 
                :key="time" 
                class="schedule-row"
              >
                <div class="time-cell">{{ time }}</div>
                <div 
                  v-for="day in daysEn" 
                  :key="day" 
                  class="schedule-cell"
                >
                  <div 
                    v-if="getScheduleForDayTime(day, time)"
                    class="class-block"
                    :class="getClassType(getScheduleForDayTime(day, time))"
                  >
                    <div class="class-name">{{ getScheduleForDayTime(day, time)?.mata_kuliah }}</div>
                    <div class="class-room">{{ getScheduleForDayTime(day, time)?.ruang }}</div>
                    <div class="class-teacher">{{ getScheduleForDayTime(day, time)?.dosen }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>

      <!-- Schedule List -->
      <div class="schedule-list">
        <DashboardCard title="Daftar Mata Kuliah">
          <DataTable
            :data="scheduleList"
            :columns="scheduleColumns"
            :loading="loading"
            empty-title="Tidak ada jadwal"
            empty-message="Belum ada jadwal kuliah yang terdaftar"
            show-actions
            show-view
            @view="viewClassDetail"
          />
        </DashboardCard>
      </div>

      <!-- Calendar View -->
      <div class="calendar-section">
        <DashboardCard title="Kalender Akademik">
          <div class="calendar-controls">
            <button @click="previousMonth" class="btn btn--outline">
              ← Bulan Sebelumnya
            </button>
            <h3>{{ currentMonthYear }}</h3>
            <button @click="nextMonth" class="btn btn--outline">
              Bulan Selanjutnya →
            </button>
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
                  'has-class': date.hasClass,
                  'today': date.isToday
                }"
              >
                <span class="date-number">{{ date.day }}</span>
                <div v-if="date.classes" class="date-classes">
                  <div 
                    v-for="cls in date.classes.slice(0, 2)" 
                    :key="cls.id"
                    class="mini-class"
                  >
                    {{ cls.mata_kuliah }}
                  </div>
                  <div v-if="date.classes.length > 2" class="more-classes">
                    +{{ date.classes.length - 2 }} lainnya
                  </div>
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
const loading = ref(false)
const currentDate = ref(new Date())

const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']
const daysEn = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
const daysShort = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

const timeSlots = [
  '07:00', '08:00', '09:00', '10:00', '11:00', 
  '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'
]

// Mock schedule data
const scheduleData = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    kode: 'TI101',
    sks: 3,
    dosen: 'Dr. Bambang Suharto',
    ruang: 'R.101',
    hari: 'monday',
    jam_mulai: '08:00',
    jam_selesai: '10:30',
    kelas: 'A'
  },
  {
    id: 2,
    mata_kuliah: 'Pengantar Sistem Informasi',
    kode: 'SI101',
    sks: 3,
    dosen: 'Dr. Citra Dewi',
    ruang: 'R.201',
    hari: 'tuesday',
    jam_mulai: '13:00',
    jam_selesai: '15:30',
    kelas: 'A'
  }
])

// Computed
const currentSemester = computed(() => 'Ganjil 2024/2025')

const currentMonthYear = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    month: 'long',
    year: 'numeric'
  }).format(currentDate.value)
})

const scheduleList = computed(() => {
  return scheduleData.value.map(item => ({
    ...item,
    jadwal: `${days[daysEn.indexOf(item.hari)]}, ${item.jam_mulai} - ${item.jam_selesai}`
  }))
})

const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - (firstDay.getDay() || 7) + 1)
  
  const dates = []
  const current = new Date(startDate)
  
  for (let i = 0; i < 42; i++) {
    const isCurrentMonth = current.getMonth() === month
    const isToday = current.toDateString() === new Date().toDateString()
    
    dates.push({
      date: new Date(current),
      day: current.getDate(),
      currentMonth: isCurrentMonth,
      isToday,
      hasClass: false, // Would be calculated based on schedule
      classes: [] // Would contain classes for this date
    })
    
    current.setDate(current.getDate() + 1)
  }
  
  return dates
})

// Table columns
const scheduleColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kode', label: 'Kode', align: 'center' },
  { key: 'sks', label: 'SKS', align: 'center' },
  { key: 'dosen', label: 'Dosen', sortable: true },
  { key: 'jadwal', label: 'Jadwal', sortable: true },
  { key: 'ruang', label: 'Ruang', align: 'center' }
]

// Methods
const getScheduleForDayTime = (day: string, time: string) => {
  return scheduleData.value.find(item => {
    const itemHour = parseInt(item.jam_mulai.split(':')[0])
    const timeHour = parseInt(time.split(':')[0])
    const endHour = parseInt(item.jam_selesai.split(':')[0])
    
    return item.hari === day && timeHour >= itemHour && timeHour < endHour
  })
}

const getClassType = (classData: any) => {
  // Return different types based on class properties
  return 'theory' // Could be 'theory', 'lab', 'seminar', etc.
}

const viewClassDetail = (classItem: any) => {
  console.log('View class detail:', classItem)
  // Implement class detail view
}

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

// Lifecycle
onMounted(() => {
  // Load schedule data
})
</script>

<style scoped>
.mahasiswa-jadwal {
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

.schedule-section {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.schedule-grid {
  overflow-x: auto;
}

.schedule-header,
.schedule-row {
  display: grid;
  grid-template-columns: 80px repeat(7, 1fr);
  min-width: 800px;
}

.schedule-header {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

.schedule-header > div,
.schedule-row > div {
  padding: 12px 8px;
  border: 1px solid #e5e7eb;
  font-size: 0.9rem;
}

.time-col,
.time-cell {
  background: #f3f4f6;
  font-weight: 500;
  text-align: center;
}

.day-col {
  text-align: center;
}

.schedule-cell {
  position: relative;
  min-height: 60px;
}

.class-block {
  background: #667eea;
  color: white;
  border-radius: 6px;
  padding: 8px;
  margin: 2px;
  font-size: 0.8rem;
  line-height: 1.2;
}

.class-block.theory {
  background: #667eea;
}

.class-block.lab {
  background: #059669;
}

.class-block.seminar {
  background: #d97706;
}

.class-name {
  font-weight: 600;
  margin-bottom: 2px;
}

.class-room,
.class-teacher {
  font-size: 0.7rem;
  opacity: 0.9;
}

.schedule-list {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.calendar-section {
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
}

.calendar-date.other-month {
  background: #f9fafb;
  color: #9ca3af;
}

.calendar-date.today {
  background: #eff6ff;
  border: 2px solid #3b82f6;
}

.calendar-date.has-class {
  background: #fef3c7;
}

.date-number {
  font-weight: 600;
  font-size: 0.9rem;
}

.date-classes {
  margin-top: 4px;
}

.mini-class {
  background: #667eea;
  color: white;
  font-size: 0.7rem;
  padding: 2px 4px;
  border-radius: 3px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.more-classes {
  font-size: 0.7rem;
  color: #6b7280;
  text-align: center;
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
  .schedule-grid {
    font-size: 0.8rem;
  }
  
  .calendar-controls {
    flex-direction: column;
    gap: 16px;
  }
  
  .calendar-date {
    min-height: 60px;
    padding: 4px;
  }
}
</style>