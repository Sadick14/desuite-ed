<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { TrendingUp, Users, CheckCircle, AlertCircle, DollarSign } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
  years: any[];
  selectedYear: any;
  yearData: any;
}>();

const selectedYearId = ref(props.selectedYear?.id || '');

function filterByYear() {
  if (selectedYearId.value) {
    router.visit(`/finance/year-end-report?year_id=${selectedYearId.value}`);
  } else {
    router.visit('/finance/year-end-report');
  }
}

const expandedStudents = ref<number[]>([]);

function toggleExpand(studentId: number) {
  const idx = expandedStudents.value.indexOf(studentId);

  if (idx > -1) {
    expandedStudents.value.splice(idx, 1);
  } else {
    expandedStudents.value.push(studentId);
  }
}
</script>

<template>
  <Head title="Year-End Financial Report" />

  <div class="min-h-screen bg-gradient-to-b from-purple-50 via-white to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-purple-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Year-End Financial Report</h1>
        <p class="text-sm text-gray-600 mt-1">Complete financial settlement summary for the academic year</p>
      </div>

      <!-- Year Selection -->
      <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
        <label class="block text-sm font-semibold text-gray-900 mb-2">Select Academic Year</label>
        <select
          v-model="selectedYearId"
          @change="filterByYear"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-gray-900"
        >
          <option value="">-- Select Year --</option>
          <option v-for="year in years" :key="year.id" :value="year.id">
            {{ year.name }}
          </option>
        </select>
      </div>

      <!-- Financial Summary Cards -->
      <div v-if="yearData" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Year</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearData.year_name }}</p>
            </div>
            <DollarSign class="w-8 h-8 text-purple-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Total Students</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ yearData.total_students }}</p>
            </div>
            <Users class="w-8 h-8 text-blue-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Total Fee Structure</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">₦{{ (yearData.total_fee_structure || 0).toLocaleString() }}</p>
            </div>
            <TrendingUp class="w-8 h-8 text-orange-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Amount Collected</p>
              <p class="text-2xl font-bold text-green-600 mt-2">₦{{ (yearData.total_collected || 0).toLocaleString() }}</p>
            </div>
            <CheckCircle class="w-8 h-8 text-green-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Collection Rate</p>
              <p class="text-2xl font-bold text-purple-600 mt-2">{{ yearData.collection_rate.toFixed(1) }}%</p>
            </div>
            <TrendingUp class="w-8 h-8 text-purple-500 opacity-20" />
          </div>
        </div>
      </div>

      <!-- Summary Box -->
      <div v-if="yearData" class="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl border border-red-100 p-6">
        <div class="flex items-center gap-4">
          <AlertCircle class="w-8 h-8 text-red-500 flex-shrink-0" />
          <div>
            <p class="font-semibold text-gray-900">Outstanding Balance</p>
            <p class="text-2xl font-bold text-red-600 mt-1">
              ₦{{ (yearData.total_outstanding || 0).toLocaleString() }}
            </p>
            <p class="text-sm text-gray-600 mt-2">
              {{ yearData.unpaid_students }} student(s) still owe fees
            </p>
          </div>
        </div>
      </div>

      <!-- Student Details Table -->
      <div v-if="yearData && yearData.student_details" class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-purple-50">
          <h2 class="text-lg font-semibold text-gray-900">Student Settlement Details</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-purple-50">
            <thead class="bg-purple-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Class</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Outstanding</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-purple-50">
              <template v-for="student in yearData.student_details" :key="student.student_id">
                <tr class="hover:bg-purple-50/30 transition-colors">
                  <td class="px-6 py-4 font-medium text-gray-900">{{ student.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ student.class || 'N/A' }}</td>
                  <td class="px-6 py-4 text-center">
                    <span
                      :class="[
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                        student.status === 'Settled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ student.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right font-semibold text-gray-900">
                    ₦{{ (student.outstanding || 0).toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button
                      v-if="student.term_breakdown && student.term_breakdown.length > 0"
                      @click="toggleExpand(student.student_id)"
                      class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                    >
                      {{ expandedStudents.includes(student.student_id) ? 'Hide' : 'Show' }}
                    </button>
                  </td>
                </tr>

                <!-- Term Breakdown (Expandable) -->
                <tr v-if="expandedStudents.includes(student.student_id) && student.term_breakdown" class="bg-purple-50/50">
                  <td colspan="5" class="px-6 py-4">
                    <div class="space-y-3">
                      <div v-for="(term, idx) in student.term_breakdown" :key="idx" class="border-l-4 border-purple-300 pl-4">
                        <p class="font-semibold text-gray-900">{{ term.term_name }}</p>
                        <div class="mt-2 space-y-1 text-sm">
                          <div v-for="fee in term.breakdown" :key="fee.fee_type" class="flex justify-between">
                            <span class="text-gray-600 capitalize">{{ fee.fee_type.replace('_', ' ') }}</span>
                            <span class="text-gray-900 font-medium">₦{{ (fee.balance || 0).toLocaleString() }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- No Data Placeholder -->
      <div v-else class="bg-white rounded-2xl border border-dashed border-purple-200 p-12 text-center">
        <DollarSign class="w-12 h-12 text-purple-300 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">Select an academic year to view the year-end financial report</p>
      </div>

    </div>
  </div>
</template>
