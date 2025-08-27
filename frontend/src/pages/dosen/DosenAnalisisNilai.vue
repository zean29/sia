<template>
  <div class="dosen-analisis-nilai">
    <div class="page-header">
      <h1>Analisis Nilai</h1>
      <p>Statistik dan analisis nilai mahasiswa per mata kuliah</p>
    </div>

    <div class="content-wrapper">
      <!-- Grade Statistics -->
      <div class="grade-stats">
        <div class="stats-cards">
          <DashboardCard
            title="Rata-rata Keseluruhan"
            :value="gradeStats.overall"
            icon-text="📊"
            variant="primary"
            :description="`Dari ${gradeStats.totalStudents} mahasiswa`"
          />
          
          <DashboardCard
            title="Nilai Tertinggi"
            :value="gradeStats.highest"
            icon-text="🏆"
            variant="success"
            :description="'Nilai terbaik semester ini'"
          />
          
          <DashboardCard
            title="Tingkat Kelulusan"
            :value="gradeStats.passRate"
            icon-text="✅"
            variant="warning"
            format="percentage"
            :description="`${gradeStats.passCount} mahasiswa lulus`"
          />
          
          <DashboardCard
            title="Perlu Perbaikan"
            :value="gradeStats.needImprovement"
            icon-text="⚠️"
            variant="error"
            :description="'Mahasiswa nilai < 70'"
          />
        </div>
      </div>

      <!-- Course Analysis -->
      <div class="course-analysis">
        <DashboardCard title="Analisis per Mata Kuliah">
          <div class="analysis-filters">
            <select v-model="selectedCourse" class="filter-select">
              <option value="">Pilih Mata Kuliah</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.nama }}
              </option>
            </select>
            <select v-model="selectedSemester" class="filter-select">
              <option value="current">Semester Ini</option>
              <option value="previous">Semester Lalu</option>
              <option value="all">Semua Semester</option>
            </select>
          </div>
          
          <DataTable
            :data="courseAnalysis"
            :columns="analysisColumns"
            :loading="loadingAnalysis"
            empty-title="Pilih mata kuliah"
            empty-message="Silakan pilih mata kuliah untuk melihat analisis"
            show-actions
            show-view
            @view="viewDetailedAnalysis"
          />
        </DashboardCard>
      </div>

      <!-- Grade Distribution -->
      <div class="grade-distribution">
        <DashboardCard title="Distribusi Nilai">
          <div class="distribution-chart">
            <div class="chart-header">
              <h4>{{ selectedCourseData?.nama || 'Semua Mata Kuliah' }}</h4>
              <span class="chart-subtitle">Distribusi nilai huruf</span>
            </div>
            
            <div class="grade-bars">
              <div v-for="grade in gradeDistribution" :key="grade.grade" class="grade-bar-item">
                <div class="grade-info">
                  <span class="grade-label">{{ grade.grade }}</span>
                  <span class="grade-range">{{ grade.range }}</span>
                </div>
                <div class="grade-bar">
                  <div 
                    class="grade-fill" 
                    :style="{ width: `${grade.percentage}%` }"
                    :class="`grade-${grade.grade.toLowerCase()}`"
                  ></div>
                </div>
                <div class="grade-stats">
                  <span class="grade-count">{{ grade.count }}</span>
                  <span class="grade-percentage">{{ grade.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
        </DashboardCard>
      </div>

      <!-- Performance Trends -->
      <div class="performance-trends">
        <DashboardCard title="Tren Performa">
          <div class="trends-content">
            <div class="trend-item">
              <h4>📈 Peningkatan Nilai</h4>
              <p>Mahasiswa dengan peningkatan signifikan</p>
              <div class="student-list">
                <div v-for="student in improvingStudents" :key="student.id" class="student-item">
                  <span class="student-name">{{ student.nama }}</span>
                  <span class="improvement">+{{ student.improvement }}</span>
                </div>
              </div>
            </div>

            <div class="trend-item">
              <h4>📉 Perlu Perhatian</h4>
              <p>Mahasiswa dengan penurunan nilai</p>
              <div class="student-list">
                <div v-for="student in decliningStudents" :key="student.id" class="student-item">
                  <span class="student-name">{{ student.nama }}</span>
                  <span class="decline">{{ student.decline }}</span>
                </div>
              </div>
            </div>

            <div class="trend-item">
              <h4>🎯 Konsisten</h4>
              <p>Mahasiswa dengan performa stabil</p>
              <div class="student-list">
                <div v-for="student in consistentStudents" :key="student.id" class="student-item">
                  <span class="student-name">{{ student.nama }}</span>
                  <span class="consistent">{{ student.average }}</span>
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
const loadingAnalysis = ref(false)
const selectedCourse = ref('')
const selectedSemester = ref('current')

// Mock data
const gradeStats = ref({
  overall: 78.5,
  totalStudents: 180,
  highest: 95,
  passRate: 85.5,
  passCount: 154,
  needImprovement: 26
})

const courses = ref([
  { id: 1, nama: 'Algoritma dan Pemrograman' },
  { id: 2, nama: 'Basis Data' },
  { id: 3, nama: 'Struktur Data' }
])

const courseAnalysis = ref([
  {
    id: 1,
    mata_kuliah: 'Algoritma dan Pemrograman',
    kelas: 'A',
    jumlah_mahasiswa: 35,
    rata_rata: 78.5,
    tertinggi: 95,
    terendah: 55,
    standar_deviasi: 12.3,
    tingkat_kelulusan: 88.6
  },
  {
    id: 2,
    mata_kuliah: 'Basis Data',
    kelas: 'A',
    jumlah_mahasiswa: 40,
    rata_rata: 82.1,
    tertinggi: 92,
    terendah: 60,
    standar_deviasi: 10.8,
    tingkat_kelulusan: 92.5
  }
])

const gradeDistribution = ref([
  { grade: 'A', range: '85-100', count: 15, percentage: 25 },
  { grade: 'B', range: '70-84', count: 25, percentage: 42 },
  { grade: 'C', range: '60-69', count: 18, percentage: 30 },
  { grade: 'D', range: '50-59', count: 2, percentage: 3 },
  { grade: 'E', range: '0-49', count: 0, percentage: 0 }
])

const improvingStudents = ref([
  { id: 1, nama: 'Ahmad Sutanto', improvement: 15 },
  { id: 2, nama: 'Siti Nurhaliza', improvement: 12 }
])

const decliningStudents = ref([
  { id: 1, nama: 'Budi Santoso', decline: -8 },
  { id: 2, nama: 'Lisa Wijaya', decline: -5 }
])

const consistentStudents = ref([
  { id: 1, nama: 'Ricky Hakim', average: 85 },
  { id: 2, nama: 'Nina Sari', average: 88 }
])

// Computed
const selectedCourseData = computed(() => {
  return courses.value.find(c => c.id === parseInt(selectedCourse.value))
})

// Table columns
const analysisColumns = [
  { key: 'mata_kuliah', label: 'Mata Kuliah', sortable: true },
  { key: 'kelas', label: 'Kelas', align: 'center' },
  { key: 'jumlah_mahasiswa', label: 'Mahasiswa', align: 'center', format: 'number' },
  { key: 'rata_rata', label: 'Rata-rata', align: 'center', format: 'number' },
  { key: 'tertinggi', label: 'Tertinggi', align: 'center', format: 'number' },
  { key: 'terendah', label: 'Terendah', align: 'center', format: 'number' },
  { key: 'standar_deviasi', label: 'Std Dev', align: 'center', format: 'number' },
  { key: 'tingkat_kelulusan', label: 'Kelulusan', align: 'center', format: 'percentage' }
]

// Methods
const viewDetailedAnalysis = (analysis: any) => {
  console.log('View detailed analysis:', analysis)
}

// Lifecycle
onMounted(() => {
  // Load analysis data
})
</script>

<style scoped>
.dosen-analisis-nilai {
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

.analysis-filters {
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

.distribution-chart {
  padding: 20px;
}

.chart-header {
  text-align: center;
  margin-bottom: 24px;
}

.chart-header h4 {
  color: #1f2937;
  margin: 0 0 4px 0;
  font-size: 1.2rem;
}

.chart-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
}

.grade-bars {
  display: grid;
  gap: 16px;
}

.grade-bar-item {
  display: grid;
  grid-template-columns: 80px 1fr 80px;
  gap: 16px;
  align-items: center;
}

.grade-info {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.grade-label {
  font-weight: 700;
  font-size: 1.2rem;
  color: #1f2937;
}

.grade-range {
  font-size: 0.8rem;
  color: #6b7280;
}

.grade-bar {
  height: 24px;
  background: #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}

.grade-fill {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 12px;
}

.grade-fill.grade-a { background: #10b981; }
.grade-fill.grade-b { background: #3b82f6; }
.grade-fill.grade-c { background: #f59e0b; }
.grade-fill.grade-d { background: #ef4444; }
.grade-fill.grade-e { background: #6b7280; }

.grade-stats {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.grade-count {
  font-weight: 600;
  color: #1f2937;
}

.grade-percentage {
  font-size: 0.8rem;
  color: #6b7280;
}

.trends-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  padding: 20px;
}

.trend-item {
  background: #f9fafb;
  border-radius: 8px;
  padding: 20px;
}

.trend-item h4 {
  color: #1f2937;
  margin: 0 0 8px 0;
  font-size: 1.1rem;
}

.trend-item p {
  color: #6b7280;
  margin: 0 0 16px 0;
  font-size: 0.9rem;
}

.student-list {
  display: grid;
  gap: 8px;
}

.student-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
}

.student-name {
  font-weight: 500;
  color: #1f2937;
}

.improvement {
  color: #10b981;
  font-weight: 600;
}

.decline {
  color: #ef4444;
  font-weight: 600;
}

.consistent {
  color: #3b82f6;
  font-weight: 600;
}

@media (max-width: 768px) {
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .analysis-filters {
    flex-direction: column;
  }
  
  .grade-bar-item {
    grid-template-columns: 60px 1fr 60px;
    gap: 12px;
  }
  
  .trends-content {
    grid-template-columns: 1fr;
  }
}
</style>