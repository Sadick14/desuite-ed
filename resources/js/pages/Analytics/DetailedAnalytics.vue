<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, TrendingUp, Users, Award, AlertCircle, PieChart } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
  years: any[];
  selectedYear: any;
  yearAnalytics: any;
}>();

const selectedYearId = ref(props.selectedYear?.id || '');

function selectYear() {
  if (selectedYearId.value) {
    router.visit(`/analytics?year_id=${selectedYearId.value}`);
  }
}

const classPerformance = computed(() => {
  if (!props.yearAnalytics?.classBreakdown) {
return [];
}

  return props.yearAnalytics.classBreakdown.sort((a: any, b: any) => b.avg_score - a.avg_score);
});

const gradeDistribution = computed(() => {
  if (!props.yearAnalytics?.gradeDistribution) {
return {};
}

  return props.yearAnalytics.gradeDistribution;
});

const passRate = computed(() => {
  if (!props.yearAnalytics) {
return 0;
}

  const total = props.yearAnalytics.total_marks_recorded || 0;
  const passed = props.yearAnalytics.students_passed || 0;

  return total > 0 ? ((passed / total) * 100).toFixed(1) : 0;
});

const failRate = computed(() => {
  return (100 - parseFloat(passRate.value as any)).toFixed(1);
});
</script>

<template>
  <Head title="Detailed Analytics" />

  <div class="min-h-screen bg-gradient-to-b from-purple-50 via-white to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-purple-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Detailed Analytics</h1>
        <p class="text-sm text-gray-600 mt-1">Comprehensive data science insights and performance analysis</p>
      </div>

      <!-- Year Selection -->
      <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
        <label class="block text-sm font-semibold text-gray-900 mb-2">Select Academic Year</label>
        <select
          v-model="selectedYearId"
          @change="selectYear"
          class="w-full max-w-xs px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-gray-900"
        >
          <option value="">-- Select Year --</option>
          <option v-for="year in years" :key="year.id" :value="year.id">
            {{ year.name }}
          </option>
        </select>
      </div>

      <template v-if="selectedYear && yearAnalytics">

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ yearAnalytics.total_students }}</p>
              </div>
              <Users class="w-8 h-8 text-blue-500 opacity-20" />
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Average Score</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ yearAnalytics.average_gpa?.toFixed(1) || 'N/A' }}</p>
              </div>
              <TrendingUp class="w-8 h-8 text-purple-500 opacity-20" />
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Pass Rate</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ passRate }}%</p>
              </div>
              <Award class="w-8 h-8 text-green-500 opacity-20" />
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Marks Recorded</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ yearAnalytics.total_marks_recorded }}</p>
              </div>
              <BarChart3 class="w-8 h-8 text-orange-500 opacity-20" />
            </div>
          </div>
        </div>

        <!-- Performance Distribution -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Grade Distribution -->
          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <PieChart class="w-5 h-5 text-purple-600" />
              Grade Distribution
            </h2>
            <div class="space-y-3">
              <div v-for="(count, grade) in gradeDistribution" :key="grade" class="flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                  <span class="font-semibold text-gray-900 min-w-fit">{{ grade }}</span>
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-purple-600 h-2 rounded-full"
                      :style="{ width: `${(count / (yearAnalytics.total_marks_recorded || 1)) * 100}%` }"
                    ></div>
                  </div>
                </div>
                <span class="text-sm font-medium text-gray-600 min-w-fit">{{ count }} ({{ ((count / (yearAnalytics.total_marks_recorded || 1)) * 100).toFixed(1) }}%)</span>
              </div>
            </div>
          </div>

          <!-- Pass/Fail Summary -->
          <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <BarChart3 class="w-5 h-5 text-purple-600" />
              Performance Summary
            </h2>
            <div class="space-y-4">
              <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                <p class="text-xs text-gray-600 uppercase font-semibold">Students Passed</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ yearAnalytics.students_passed }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ passRate }}% pass rate</p>
              </div>
              <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                <p class="text-xs text-gray-600 uppercase font-semibold">Students Failed</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ yearAnalytics.students_failed }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ failRate }}% fail rate</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Performance Breakdown -->
        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <TrendingUp class="w-5 h-5 text-purple-600" />
            Class Performance Rankings
          </h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-100">
              <thead class="bg-purple-50/70">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Class</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Students</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Avg Score</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Pass Rate</th>
                  <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-purple-50">
                <tr v-for="(cls, idx) in classPerformance" :key="idx" class="hover:bg-purple-50/30">
                  <td class="px-6 py-4 font-medium text-gray-900">{{ cls.class_name }}</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">{{ cls.student_count }}</td>
                  <td class="px-6 py-4 text-center font-semibold text-gray-900">{{ cls.avg_score?.toFixed(1) || 'N/A' }}</td>
                  <td class="px-6 py-4 text-center text-sm">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                      :class="cls.pass_rate >= 75 ? 'bg-green-100 text-green-800' : cls.pass_rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'">
                      {{ cls.pass_rate?.toFixed(1) || 0 }}%
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span v-if="cls.avg_score >= 70" class="text-green-600 font-medium">Excellent</span>
                    <span v-else-if="cls.avg_score >= 60" class="text-blue-600 font-medium">Good</span>
                    <span v-else-if="cls.avg_score >= 50" class="text-yellow-600 font-medium">Average</span>
                    <span v-else class="text-red-600 font-medium">Needs Improvement</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Course Performance (if available) -->
        <div v-if="yearAnalytics.coursePerformance && yearAnalytics.coursePerformance.length > 0" class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Course Performance</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="course in yearAnalytics.coursePerformance" :key="course.course_id" class="p-4 border border-purple-100 rounded-lg">
              <p class="font-semibold text-gray-900">{{ course.course_name }}</p>
              <p class="text-2xl font-bold text-purple-600 mt-2">{{ course.avg_score?.toFixed(1) || 'N/A' }}</p>
              <p class="text-xs text-gray-600 mt-1">Average: {{ course.student_count }} students</p>
            </div>
          </div>
        </div>

      </template>

      <!-- No Year Selected -->
      <div v-else class="bg-purple-50 rounded-2xl border border-dashed border-purple-200 p-12 text-center">
        <BarChart3 class="w-12 h-12 text-purple-300 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">Select an academic year to view detailed analytics</p>
      </div>

    </div>
  </div>
</template>
