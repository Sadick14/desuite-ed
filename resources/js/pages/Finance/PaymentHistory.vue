<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CreditCard, Calendar, DollarSign } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  payments: any;
  years: any[];
  selectedYear: any;
  selectedStudent: any;
  totalAmount: number;
}>();

const selectedYearId = ref(props.selectedYear?.id || '');

function filterByYear() {
  const url = new URL(window.location.href);

  if (selectedYearId.value) {
    url.searchParams.set('year_id', selectedYearId.value);
  } else {
    url.searchParams.delete('year_id');
  }

  router.visit(url.toString().replace(window.location.origin, ''));
}

function formatPaymentMethod(method: string) {
  const methods: Record<string, string> = {
    cash: 'Cash',
    momo: 'Mobile Money',
    bank: 'Bank Transfer',
  };

  return methods[method] || method;
}

function formatPaymentType(type: string) {
  const types: Record<string, string> = {
    school_fees: 'School Fees',
    feeding_fees: 'Feeding Fees',
    registration_fees: 'Registration',
    others: 'Others',
  };

  return types[type] || type;
}
</script>

<template>
  <Head title="Payment History" />

  <div class="min-h-screen bg-gradient-to-b from-green-50 via-white to-green-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-green-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
        <p class="text-sm text-gray-600 mt-1">Track all student payments and receipts</p>
      </div>

      <!-- Filter & Stats -->
      <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Filter by Academic Year</label>
            <select
              v-model="selectedYearId"
              @change="filterByYear"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900"
            >
              <option value="">-- All Years --</option>
              <option v-for="year in years" :key="year.id" :value="year.id">
                {{ year.name }}
              </option>
            </select>
          </div>
          <div class="flex flex-col justify-end">
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-50 rounded-lg">
              <DollarSign class="w-5 h-5 text-green-600" />
              <span class="text-sm font-semibold text-gray-900">
                Total Collected: <span class="text-green-600">₦{{ (totalAmount || 0).toLocaleString() }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Records Table -->
      <div class="bg-white rounded-2xl border border-green-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-green-50">
            <thead class="bg-green-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Term</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Fee Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Method</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Receipt</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-green-50">
              <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-green-50/30 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ new Date(payment.created_at).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-900">{{ payment.student?.full_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ payment.term?.name }}</td>
                <td class="px-6 py-4 text-sm">
                  <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                    {{ formatPaymentType(payment.payment_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatPaymentMethod(payment.payment_method) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900">₦{{ (payment.amount || 0).toLocaleString() }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ payment.receipt_number }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="payments.data.length > 0" class="px-6 py-4 border-t border-green-50 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} payments
          </div>
          <div class="flex gap-2">
            <button
              v-if="payments.prev_page_url"
              @click="router.visit(payments.prev_page_url)"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
              Previous
            </button>
            <button
              v-if="payments.next_page_url"
              @click="router.visit(payments.next_page_url)"
              class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium"
            >
              Next
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
