<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, DollarSign, Users, Zap, Download } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  years: any[];
  selectedYear: any;
  terms: any[];
  selectedTerm: any;
  outstandingStudents: any[];
  totalOutstanding: number;
  studentCount: number;
  blockedStudentCount: number;
}>();

const selectedYearId = ref(props.selectedYear?.id || '');
const selectedTermId = ref(props.selectedTerm?.id || '');

function filterByYear() {
  const url = new URL(window.location.href);
  url.searchParams.set('year_id', selectedYearId.value);

  if (selectedTermId.value) {
    url.searchParams.set('term_id', selectedTermId.value);
  }

  router.visit(url.toString().replace(window.location.origin, ''));
}

function filterByTerm() {
  const url = new URL(window.location.href);
  url.searchParams.set('year_id', selectedYearId.value);
  url.searchParams.set('term_id', selectedTermId.value);
  router.visit(url.toString().replace(window.location.origin, ''));
}

function downloadReport() {
  window.open(`/exports/fee-collection?year_id=${selectedYearId.value}`);
}
</script>

<template>
  <Head title="Collections Dashboard" />

  <div class="min-h-screen bg-gradient-to-b from-red-50 via-white to-red-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-red-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Collections Dashboard</h1>
        <p class="text-sm text-gray-600 mt-1">Monitor and manage outstanding student fees</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Academic Year</label>
            <select
              v-model="selectedYearId"
              @change="filterByYear"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-gray-900"
            >
              <option value="">-- Select Year --</option>
              <option v-for="year in years" :key="year.id" :value="year.id">
                {{ year.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Term (Optional)</label>
            <select
              v-model="selectedTermId"
              @change="filterByTerm"
              :disabled="terms.length === 0"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-gray-900 disabled:bg-gray-100"
            >
              <option value="">-- All Terms --</option>
              <option v-for="term in terms" :key="term.id" :value="term.id">
                {{ term.name }}
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="downloadReport"
              v-if="selectedYear"
              class="w-full px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
            >
              <Download class="w-4 h-4" />
              Download Report
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="selectedYear" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Total Outstanding</p>
              <p class="text-2xl font-bold text-red-600 mt-2">₦{{ (totalOutstanding || 0).toLocaleString() }}</p>
            </div>
            <AlertCircle class="w-8 h-8 text-red-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Total Students</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">{{ studentCount }}</p>
            </div>
            <Users class="w-8 h-8 text-blue-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Students w/ Debt</p>
              <p class="text-2xl font-bold text-red-600 mt-2">{{ blockedStudentCount }}</p>
            </div>
            <Zap class="w-8 h-8 text-orange-500 opacity-20" />
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-red-100 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-500 uppercase font-semibold">Collection Rate</p>
              <p class="text-2xl font-bold text-gray-900 mt-2">
                {{ studentCount > 0 ? (((studentCount - blockedStudentCount) / studentCount * 100).toFixed(1)) : 0 }}%
              </p>
            </div>
            <DollarSign class="w-8 h-8 text-green-500 opacity-20" />
          </div>
        </div>
      </div>

      <!-- Outstanding Students Table -->
      <div class="bg-white rounded-2xl border border-red-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-red-50">
          <h2 class="text-lg font-semibold text-gray-900">Outstanding Balances</h2>
        </div>

        <div v-if="outstandingStudents.length === 0" class="px-6 py-12 text-center">
          <p class="text-gray-500">No students with outstanding balances</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-red-50">
            <thead class="bg-red-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Class</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Outstanding Balance</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Breakdown</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-red-50">
              <tr v-for="student in outstandingStudents" :key="student.id" class="hover:bg-red-50/30 transition-colors">
                <td class="px-6 py-4 font-medium text-gray-900">{{ student.full_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ student.class?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-right">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                    ₦{{ (student.current_balance || student.outstanding_balance || 0).toLocaleString() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div v-if="student.term_breakdown && student.term_breakdown.length > 0" class="space-y-1">
                    <div v-for="(term, idx) in student.term_breakdown.slice(0, 2)" :key="idx" class="text-xs">
                      {{ term.term_name || 'Term' }}: ₦{{ (term.balance || 0).toLocaleString() }}
                    </div>
                    <div v-if="student.term_breakdown.length > 2" class="text-xs text-gray-400">
                      +{{ student.term_breakdown.length - 2 }} more
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <a
                    :href="`/finance/students/${student.id}/financial`"
                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                  >
                    View History
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>
