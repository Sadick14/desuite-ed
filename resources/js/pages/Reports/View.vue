<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Download, FileText, ArrowLeft, Printer } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/utils/format';

const props = defineProps<{
  payments: any[];
  studentSummary: any[];
  classSummary: any[];
  feeTypeSummary: any[];
  allStudents: any[];
  filterLabel: string;
  reportType: string;
  filterId: number;
}>();

const downloadingPdf = ref(false);
const downloadingCsv = ref(false);
const activeTab = ref('overview');

const form = useForm({
  report_type: props.reportType,
  term_id: props.reportType === 'term' ? props.filterId : null,
  academic_year_id: props.reportType === 'academic_year' ? props.filterId : null,
});

const handlePrint = () => {
  window.print();
};

const downloadPdf = async () => {
  downloadingPdf.value = true;
  try {
    const response = await fetch('/reports/download-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        report_type: props.reportType,
        term_id: props.reportType === 'term' ? props.filterId : null,
        academic_year_id: props.reportType === 'academic_year' ? props.filterId : null,
      }),
    });

    const data = await response.json();
    generateClientPdf(data);
  } finally {
    downloadingPdf.value = false;
  }
};

const downloadCsv = () => {
  form.post('/reports/download-csv', {
    preserveState: false,
  });
};

const generateClientPdf = (data: any) => {
  // Using html2pdf library approach (we'll use a simple table-based approach)
  const html = generatePdfHtml(data);
  const element = document.createElement('div');
  element.innerHTML = html;
  element.style.display = 'none';
  document.body.appendChild(element);

  window.print();

  setTimeout(() => {
    document.body.removeChild(element);
  }, 1000);
};

const formatCurrencyFull = (amount: number) => {
  return `${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const generatePdfHtml = (data: any) => {
  const now = new Date().toLocaleDateString();

  return `
    <html>
      <head>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          h1 { text-align: center; color: #333; }
          h2 { color: #4a5568; border-bottom: 2px solid #4a5568; padding-bottom: 10px; margin-top: 30px; }
          .header { text-align: center; margin-bottom: 30px; }
          .report-date { text-align: center; color: #666; margin-bottom: 10px; font-size: 12px; }
          table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
          th { background-color: #4a5568; color: white; padding: 10px; text-align: left; }
          td { padding: 8px; border-bottom: 1px solid #ddd; }
          tr:nth-child(even) { background-color: #f9fafb; }
          .total-row { font-weight: bold; background-color: #e2e8f0; }
          .amount { text-align: right; }
          .page-break { page-break-after: always; }
          @media print { body { margin: 0; } }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Financial Report</h1>
          <p class="report-date">Period: ${data.filterLabel}</p>
          <p class="report-date">Generated: ${now}</p>
        </div>

        <!-- Fee Type Summary -->
        <h2>Fee Type Summary</h2>
        <table>
          <thead>
            <tr>
              <th>Fee Type</th>
              <th class="amount">Expected (GHS)</th>
              <th class="amount">Paid (GHS)</th>
              <th class="amount">Balance (GHS)</th>
              <th class="amount">Transactions</th>
            </tr>
          </thead>
          <tbody>
            ${data.feeTypeSummary.map((ft: any) => `
              <tr>
                <td>${ft.fee_type_label}</td>
                <td class="amount">${formatCurrencyFull(ft.expected)}</td>
                <td class="amount">${formatCurrencyFull(ft.paid)}</td>
                <td class="amount">${formatCurrencyFull(ft.balance)}</td>
                <td class="amount">${ft.transaction_count}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div class="page-break"></div>

        <!-- Class Summary -->
        <h2>Class Summary</h2>
        <table>
          <thead>
            <tr>
              <th>Class</th>
              <th class="amount">Students</th>
              <th class="amount">Expected (GHS)</th>
              <th class="amount">Paid (GHS)</th>
              <th class="amount">Balance (GHS)</th>
            </tr>
          </thead>
          <tbody>
            ${data.classSummary.map((cs: any) => `
              <tr>
                <td>${cs.class_name}</td>
                <td class="amount">${cs.student_count}</td>
                <td class="amount">${formatCurrencyFull(cs.expected)}</td>
                <td class="amount">${formatCurrencyFull(cs.paid)}</td>
                <td class="amount">${formatCurrencyFull(cs.balance)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div class="page-break"></div>

        <!-- Student Summary -->
        <h2>Student Payment Summary</h2>
        <table>
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Student ID</th>
              <th>Class</th>
              <th class="amount">Expected (GHS)</th>
              <th class="amount">Paid (GHS)</th>
              <th class="amount">Balance (GHS)</th>
            </tr>
          </thead>
          <tbody>
            ${data.studentSummary.map((ss: any) => `
              <tr>
                <td>${ss.student_name}</td>
                <td>${ss.student_id_code}</td>
                <td>${ss.class}</td>
                <td class="amount">${ss.expected.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="amount">${ss.paid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="amount">${ss.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>

        <div class="page-break"></div>

        <!-- Transaction Log -->
        <h2>Payment Transaction Log</h2>
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Student</th>
              <th>Fee Type</th>
              <th class="amount">Amount (GHS)</th>
              <th>Method</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
            ${data.payments.map((p: any) => `
              <tr>
                <td>${new Date(p.payment_date).toLocaleDateString()}</td>
                <td>${p.student.first_name} ${p.student.last_name}</td>
                <td>${formatFeeType(p.payment_type)}</td>
                <td class="amount">${p.amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${p.payment_method.toUpperCase()}</td>
                <td>${p.receipt_number}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </body>
    </html>
  `;
};

const formatFeeType = (type: string) => {
  return {
    school_fees: 'School Fees',
    feeding_fees: 'Feeding Fees',
    registration_fees: 'Registration Fees',
    others: 'Other Fees',
  }[type] || type;
};

const goBack = () => {
  router.get('/reports');
};

// Calculate totals
const getTotalStats = () => {
  const totalExpected = props.feeTypeSummary.reduce((sum, ft) => sum + ft.expected, 0);
  const totalPaid = props.feeTypeSummary.reduce((sum, ft) => sum + ft.paid, 0);
  const totalBalance = props.feeTypeSummary.reduce((sum, ft) => sum + ft.balance, 0);

  return { totalExpected, totalPaid, totalBalance };
};

const stats = getTotalStats();
</script>

<template>
  <AppLayout>
    <Head title="Financial Report" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Financial Report
          </h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">
            {{ filterLabel }}
          </p>
        </div>
        <button
          @click="goBack"
          class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition"
        >
          <ArrowLeft class="w-4 h-4" />
          Back to Reports
        </button>
      </div>

      <!-- Download Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex gap-3">
        <button
          @click="downloadPdf"
          :disabled="downloadingPdf"
          class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition"
        >
          <FileText class="w-4 h-4" />
          {{ downloadingPdf ? 'Preparing...' : 'Download PDF' }}
        </button>
        <button
          @click="downloadCsv"
          :disabled="downloadingCsv"
          class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition"
        >
          <Download class="w-4 h-4" />
          Download CSV
        </button>
        <button
          @click="handlePrint"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"
        >
          <Printer class="w-4 h-4" />
          Print
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Expected</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
            GHS {{ stats.totalExpected.toLocaleString() }}
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Paid</p>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
            GHS {{ stats.totalPaid.toLocaleString() }}
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <p class="text-sm text-gray-500 dark:text-gray-400">Outstanding Balance</p>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
            GHS {{ stats.totalBalance.toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-8">
          <button
            @click="activeTab = 'overview'"
            :class="[
              'py-3 px-1 text-sm font-medium transition-colors',
              activeTab === 'overview'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Fee Type Summary
          </button>
          <button
            @click="activeTab = 'classes'"
            :class="[
              'py-3 px-1 text-sm font-medium transition-colors',
              activeTab === 'classes'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            By Class
          </button>
          <button
            @click="activeTab = 'students'"
            :class="[
              'py-3 px-1 text-sm font-medium transition-colors',
              activeTab === 'students'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            By Student
          </button>
          <button
            @click="activeTab = 'transactions'"
            :class="[
              'py-3 px-1 text-sm font-medium transition-colors',
              activeTab === 'transactions'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Transactions
          </button>
        </nav>
      </div>

      <!-- Fee Type Summary Tab -->
      <div v-if="activeTab === 'overview'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fee Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Expected</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paid</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transactions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="ft in feeTypeSummary" :key="ft.fee_type">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ ft.fee_type_label }}</td>
                <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">
                  GHS {{ ft.expected.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold text-green-600 dark:text-green-400">
                  GHS {{ ft.paid.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold" :class="ft.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                  GHS {{ ft.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">{{ ft.transaction_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Class Summary Tab -->
      <div v-if="activeTab === 'classes'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Students</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Expected</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paid</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="cs in classSummary" :key="cs.class_id">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ cs.class_name }}</td>
                <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">{{ cs.student_count }}</td>
                <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">
                  GHS {{ cs.expected.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold text-green-600 dark:text-green-400">
                  GHS {{ cs.paid.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold" :class="cs.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                  GHS {{ cs.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Student Summary Tab -->
      <div v-if="activeTab === 'students'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Expected</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paid</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="ss in studentSummary" :key="ss.student_id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ ss.student_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 font-mono">{{ ss.student_id_code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ ss.class }}</td>
                <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">
                  GHS {{ ss.expected.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold text-green-600 dark:text-green-400">
                  GHS {{ ss.paid.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-semibold" :class="ss.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                  GHS {{ ss.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Transactions Tab -->
      <div v-if="activeTab === 'transactions'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fee Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Receipt</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="p in payments" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                  {{ new Date(p.payment_date).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ p.student.first_name }} {{ p.student.last_name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatFeeType(p.payment_type) }}</td>
                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                  GHS {{ p.amount.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="['px-2 py-1 rounded text-xs font-medium',
                    p.payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' :
                    p.payment_method === 'momo' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' :
                    'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                  ]">
                    {{ p.payment_method.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 font-mono">{{ p.receipt_number }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
  </AppLayout>
</template>
