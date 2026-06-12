<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Download } from 'lucide-vue-next';

const props = defineProps<{
  student: any;
  terms: any[];
  academicYears: any[];
  grades: any[];
  selectedTermId: number | null;
  selectedAcademicYearId: number | null;
  summary: any;
}>();

function goBack() {
  router.visit('/reports');
}

function downloadReport() {
  const params = new URLSearchParams();

  if (props.selectedTermId) {
params.append('term_id', String(props.selectedTermId));
}

  window.location.href = `/reports/students/${props.student.id}/download?${params.toString()}`;
}

function getGradeColor(finalScore: number | null) {
  if (!finalScore) {
return 'bg-gray-50';
}

  if (finalScore >= 80) {
return 'bg-green-50';
}

  if (finalScore >= 60) {
return 'bg-blue-50';
}

  if (finalScore >= 50) {
return 'bg-yellow-50';
}

  return 'bg-red-50';
}
</script>

<template>
  <Head :title="`${student.first_name} ${student.last_name} - Report Card`" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <button @click="goBack" class="p-2 hover:bg-gray-100 rounded-lg transition">
          <ArrowLeft class="w-5 h-5 text-gray-600" />
        </button>
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-gray-900">
            {{ student.first_name }} {{ student.last_name }}
          </h1>
          <p class="text-sm text-gray-600 mt-1">Admission #: {{ student.admission_number }}</p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border border-blue-100 p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Total Courses</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ summary.totalSubjects }}</p>
        </div>
        <div class="bg-white rounded-lg border border-green-100 p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Passed</p>
          <p class="text-2xl font-bold text-green-600 mt-1">{{ summary.passedSubjects }}</p>
        </div>
        <div class="bg-white rounded-lg border border-red-100 p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Failed</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.failedSubjects }}</p>
        </div>
        <div class="bg-white rounded-lg border border-purple-100 p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Average</p>
          <p class="text-2xl font-bold text-purple-600 mt-1">{{ summary.averageScore.toFixed(1) }}</p>
        </div>
      </div>

      <!-- Grades Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Course</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">CA</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Exam</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Final</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Grade</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="mark in grades" :key="mark.id" :class="getGradeColor(mark.final_score) + ' transition-colors'">
                <td class="px-6 py-4 font-medium text-gray-900">
                  {{ mark.course?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-900 font-semibold">
                  {{ mark.ca_total ? mark.ca_total.toFixed(1) : '-' }}
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-900 font-semibold">
                  {{ mark.exam_score ? mark.exam_score.toFixed(1) : '-' }}
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-900 font-bold">
                  {{ mark.final_score ? mark.final_score.toFixed(1) : '-' }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                    {{ mark.letter_grade || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                      (mark.final_score ?? 0) >= 50
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ (mark.final_score ?? 0) >= 50 ? 'Pass' : 'Fail' }}
                  </span>
                </td>
              </tr>
              <tr v-if="grades.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  No grades available for the selected term.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Summary Section -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Overall Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div>
            <p class="text-sm text-gray-600">Total Score</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ summary.totalScore.toFixed(1) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Average</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ summary.percentage.toFixed(1) }}%</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Pass Rate</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
              {{ summary.totalSubjects > 0 ? Math.round((summary.passedSubjects / summary.totalSubjects) * 100) : 0 }}%
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Remark</p>
            <p class="text-lg font-bold text-purple-600 mt-1">
              {{ summary.percentage >= 80 ? 'Excellent' : summary.percentage >= 60 ? 'Good' : summary.percentage >= 50 ? 'Fair' : 'Poor' }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
