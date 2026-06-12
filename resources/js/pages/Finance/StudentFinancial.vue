<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { DollarSign, AlertCircle, CheckCircle, BarChart3 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  student: any;
  financialHistory: any[];
  currentBalance: any;
}>();

const expandedYear = ref<number | null>(null);

function toggleExpand(yearId: number) {
  expandedYear.value = expandedYear.value === yearId ? null : yearId;
}
</script>

<template>
  <Head :title="`${student.full_name} - Financial Statement`" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header with Student Info -->
      <div class="border-b border-blue-100/60 pb-5">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ student.full_name }}</h1>
            <p class="text-sm text-gray-600 mt-1">ID: {{ student.student_id }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">Class</p>
            <p class="text-lg font-semibold text-gray-900">{{ student.class?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>

      <!-- Current Balance Card -->
      <div v-if="currentBalance && currentBalance.balance > 0" class="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl border border-red-100 p-6">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-semibold text-gray-700 uppercase">Current Outstanding Balance</p>
            <p class="text-4xl font-bold text-red-600 mt-2">₦{{ (currentBalance.balance || 0).toLocaleString() }}</p>
            <p class="text-sm text-gray-600 mt-2">Student cannot proceed to next term until paid</p>
          </div>
          <AlertCircle class="w-12 h-12 text-red-500 flex-shrink-0 opacity-30" />
        </div>
      </div>

      <div v-else-if="currentBalance && currentBalance.balance === 0" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100 p-6">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-semibold text-gray-700 uppercase">All Fees Settled</p>
            <p class="text-2xl font-bold text-green-600 mt-2">✓ Paid in Full</p>
            <p class="text-sm text-gray-600 mt-2">Student is eligible for next term/year enrollment</p>
          </div>
          <CheckCircle class="w-12 h-12 text-green-500 flex-shrink-0 opacity-30" />
        </div>
      </div>

      <!-- Fee Type Breakdown (Current Term) -->
      <div v-if="currentBalance && currentBalance.breakdown && currentBalance.breakdown.length > 0" class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Term Breakdown</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="fee in currentBalance.breakdown" :key="fee.fee_type" class="border border-blue-100 rounded-lg p-4">
            <p class="text-sm text-gray-600 capitalize">{{ fee.fee_type.replace('_', ' ') }}</p>
            <div class="mt-3 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Expected:</span>
                <span class="font-medium text-gray-900">₦{{ (fee.expected || 0).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Paid:</span>
                <span class="font-medium text-green-600">₦{{ (fee.paid || 0).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-sm border-t border-blue-50 pt-2">
                <span class="text-gray-600 font-semibold">Balance:</span>
                <span :class="['font-semibold', fee.balance > 0 ? 'text-red-600' : 'text-green-600']">
                  ₦{{ (fee.balance || 0).toLocaleString() }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial History by Year -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Financial History</h2>

        <div v-if="financialHistory.length === 0" class="bg-white rounded-2xl border border-dashed border-blue-200 p-12 text-center">
          <BarChart3 class="w-12 h-12 text-blue-300 mx-auto mb-4" />
          <p class="text-gray-500 text-lg">No financial history found</p>
        </div>

        <div v-for="(year, idx) in financialHistory" :key="idx" class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
          <!-- Year Header -->
          <button
            @click="toggleExpand(idx)"
            class="w-full px-6 py-4 flex items-center justify-between hover:bg-blue-50/50 transition-colors border-b border-blue-50"
          >
            <div class="flex items-center gap-4 flex-1 text-left">
              <div>
                <p class="font-semibold text-gray-900">{{ year.year_name }}</p>
                <p class="text-sm text-gray-600">{{ year.year_status }} Year</p>
              </div>
            </div>
            <div class="text-right mr-4">
              <p class="text-sm text-gray-600">Outstanding</p>
              <p :class="['text-lg font-bold', year.total_outstanding > 0 ? 'text-red-600' : 'text-green-600']">
                ₦{{ (year.total_outstanding || 0).toLocaleString() }}
              </p>
            </div>
            <span v-if="expandedYear === idx" class="text-blue-600">▼</span>
            <span v-else class="text-gray-400">▶</span>
          </button>

          <!-- Year Details (Expandable) -->
          <div v-if="expandedYear === idx" class="px-6 py-4 bg-blue-50/30 border-t border-blue-50">
            <div v-if="year.term_breakdown && year.term_breakdown.length > 0" class="space-y-4">
              <div v-for="term in year.term_breakdown" :key="term.term_name" class="border-l-4 border-blue-300 pl-4">
                <p class="font-semibold text-gray-900">{{ term.term_name }}</p>
                <p class="text-sm text-gray-600 mt-1">Outstanding: <span class="font-medium text-red-600">₦{{ (term.balance || 0).toLocaleString() }}</span></p>

                <!-- Fee Type Details -->
                <div class="mt-3 space-y-2 text-sm">
                  <div v-for="fee in term.breakdown" :key="fee.fee_type" class="flex justify-between pl-4 py-1 bg-white rounded border border-blue-100 px-2">
                    <span class="text-gray-600 capitalize">{{ fee.fee_type.replace('_', ' ') }}</span>
                    <span class="text-gray-900 font-medium">
                      Balance: ₦{{ (fee.balance || 0).toLocaleString() }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-gray-500 text-sm">
              No outstanding balance for this year
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
