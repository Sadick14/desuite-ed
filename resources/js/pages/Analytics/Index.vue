<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, Calendar, Users, FileText, TrendingUp, Award, PieChart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
  academicYears: any[];
  activeYear: any;
  selectedYear: any;
  yearStats: any;
  yearAnalytics: any;
}>();

const selectedYearId = ref(props.selectedYear?.id || props.activeYear?.id || '');

const filteredYears = computed(() => {
  return props.academicYears.map((year: any) => ({
    ...year,
    status: year.ended_at ? 'Ended' : 'Active',
  }));
});

const passRate = computed(() => {
  if (!props.yearAnalytics) {
return 0;
}

  const total = props.yearAnalytics.total_marks_recorded || 0;
  const passed = props.yearAnalytics.students_passed || 0;

  return total > 0 ? ((passed / total) * 100).toFixed(1) : 0;
});

function selectYear() {
  if (selectedYearId.value) {
    router.visit(`/analytics?year_id=${selectedYearId.value}`);
  } else {
    router.visit('/analytics');
  }
}

function downloadYearReport() {
  if (selectedYearId.value) {
    window.open(`/exports/analytics?year_id=${selectedYearId.value}`);
  }
}
</script>

<template>
  <Head title="Analytics & Reports" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-blue-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Analytics & Reports</h1>
        <p class="text-sm text-gray-600 mt-1">View and compare academic year data</p>
      </div>

      <!-- Year Selection -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Select Academic Year</label>
            <select
              v-model="selectedYearId"
              @change="selectYear"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
            >
              <option value="">-- All Years --</option>
              <option v-for="year in filteredYears" :key="year.id" :value="year.id">
                {{ year.name }} ({{ year.status }})
              </option>
            </select>
          </div>
          <button
            v-if="selectedYear"
            @click="downloadYearReport"
            class="px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition"
          >
            Download Report
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="selectedYear && yearStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Academic Year</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearStats.name }}</p>
            </div>
            <Calendar class="w-8 h-8 text-blue-500 opacity-20" />
          </div>
          <div class="mt-3 text-xs">
            <span v-if="yearStats.is_ended" class="px-2 py-1 bg-gray-100 text-gray-700 rounded">Ended</span>
            <span v-else class="px-2 py-1 bg-amber-100 text-amber-700 rounded">Active</span>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Total Students</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearStats.total_students }}</p>
            </div>
            <Users class="w-8 h-8 text-green-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Marks Recorded</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearStats.total_marks_recorded }}</p>
            </div>
            <FileText class="w-8 h-8 text-orange-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Average Score</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearStats.average_gpa ? yearStats.average_gpa.toFixed(1) : 'N/A' }}</p>
            </div>
            <TrendingUp class="w-8 h-8 text-purple-500 opacity-20" />
          </div>
        </div>
      </div>

      <!-- Summary when no year selected -->
      <div v-else class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
        <p class="text-gray-600 text-sm">Select an academic year above to view detailed statistics</p>
      </div>

      <!-- Detailed Analytics Section -->
      <template v-if="selectedYear && yearAnalytics">
        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Students Passed</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ yearAnalytics.students_passed }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ passRate }}% pass rate</p>
              </div>
              <Award class="w-8 h-8 text-green-500 opacity-20" />
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Students Failed</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ yearAnalytics.students_failed }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ (100 - parseFloat(passRate as any)).toFixed(1) }}% fail rate</p>
              </div>
              <TrendingUp class="w-8 h-8 text-red-500 opacity-20" />
            </div>
          </div>
        </div>

        <!-- Grade Distribution -->
        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <PieChart class="w-5 h-5 text-blue-600" />
            Grade Distribution
          </h2>
          <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div v-for="(count, grade) in yearAnalytics.gradeDistribution" :key="grade" class="text-center p-4 bg-blue-50 rounded-lg">
              <p class="text-2xl font-bold text-blue-600">{{ grade }}</p>
              <p class="text-sm text-gray-600 mt-1">{{ count }} students</p>
            </div>
          </div>
        </div>

        <!-- Class Performance -->
        <div v-if="yearAnalytics.classBreakdown" class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Class Performance Rankings</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-50">
              <thead class="bg-blue-50/70">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Class</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Students</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Avg Score</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Pass Rate</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-blue-50">
                <tr v-for="cls in yearAnalytics.classBreakdown.sort((a: any, b: any) => b.avg_score - a.avg_score)" :key="cls.class_name" class="hover:bg-blue-50/30">
                  <td class="px-6 py-4 font-medium text-gray-900">{{ cls.class_name }}</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">{{ cls.student_count }}</td>
                  <td class="px-6 py-4 text-center font-semibold text-gray-900">{{ cls.avg_score?.toFixed(1) || 'N/A' }}</td>
                  <td class="px-6 py-4 text-center">
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold', cls.pass_rate >= 75 ? 'bg-green-100 text-green-800' : cls.pass_rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800']">
                      {{ cls.pass_rate?.toFixed(1) || 0 }}%
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Years Summary Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Year</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Active Term</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="year in filteredYears" :key="year.id" class="hover:bg-blue-50/30 transition-colors">
                <td class="px-6 py-4 font-medium text-gray-900">{{ year.name }}</td>
                <td class="px-6 py-4 text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                      year.ended_at ? 'bg-gray-100 text-gray-800' : 'bg-amber-100 text-amber-800'
                    ]"
                  >
                    {{ year.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span v-if="year.terms?.find((t: any) => t.is_active)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    {{ year.terms.find((t: any) => t.is_active)?.name }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button
                    @click="selectedYearId = year.id; selectYear()"
                    class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                  >
                    View Details
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Placeholder for detailed analysis -->
      <div v-if="!selectedYear" class="bg-white rounded-2xl border border-dashed border-blue-200 p-12 text-center">
        <BarChart3 class="w-12 h-12 text-blue-300 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">Select an academic year to view detailed analytics and statistics</p>
      </div>

    </div>
  </div>
</template>
