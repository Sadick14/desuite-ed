<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  FileText,
  Download,
  BarChart3,
  Users,
  Receipt,
  Wallet,
  Calendar,
  Printer,
  File,
  X,
} from 'lucide-vue-next';

const props = defineProps<{
  terms: any[];
  academicYears: any[];
  expenseCategories: any[];
  classes: any[];
}>();

const reportType = ref('financial_summary');
const format = ref('pdf');
const filters = ref<any>({});

const reportOptions = [
  { value: 'financial_summary', label: 'Financial Summary', icon: BarChart3 },
  { value: 'student_enrollment', label: 'Student Enrollment', icon: Users },
  { value: 'fee_collection', label: 'Fee Collection', icon: Receipt },
  { value: 'expense_report', label: 'Expense Report', icon: Wallet },
  { value: 'payment_history', label: 'Payment History', icon: FileText },
];

// Dynamic filters based on report type
const dynamicFilters = computed(() => {
  const filtersMap = {
    financial_summary: [
      { key: 'term_id', label: 'Term', type: 'select', options: props.terms, optionLabel: (t: any) => `${t.name} (${t.academic_year?.name})`, optionValue: 'id' },
      { key: 'academic_year_id', label: 'Academic Year', type: 'select', options: props.academicYears, optionLabel: 'name', optionValue: 'id' },
    ],
    student_enrollment: [
      { key: 'class_id', label: 'Class', type: 'select', options: props.classes, optionLabel: 'name', optionValue: 'id' },
    ],
    fee_collection: [
      { key: 'term_id', label: 'Term', type: 'select', options: props.terms, optionLabel: (t: any) => `${t.name} (${t.academic_year?.name})`, optionValue: 'id' },
      { key: 'academic_year_id', label: 'Academic Year', type: 'select', options: props.academicYears, optionLabel: 'name', optionValue: 'id' },
    ],
    expense_report: [
      { key: 'category_id', label: 'Category', type: 'select', options: props.expenseCategories, optionLabel: 'name', optionValue: 'id' },
      { key: 'start_date', label: 'Start Date', type: 'date' },
      { key: 'end_date', label: 'End Date', type: 'date' },
    ],
    payment_history: [
      { key: 'student_id', label: 'Student', type: 'text', placeholder: 'Student ID or Name' }, // Would need autocomplete
      { key: 'term_id', label: 'Term', type: 'select', options: props.terms, optionLabel: (t: any) => `${t.name} (${t.academic_year?.name})`, optionValue: 'id' },
      { key: 'date_from', label: 'From Date', type: 'date' },
      { key: 'date_to', label: 'To Date', type: 'date' },
    ],
  };
  return filtersMap[reportType.value] || [];
});

async function downloadReport() {
  let payloadFilters = { ...filters.value };
  Object.keys(payloadFilters).forEach(key => {
    if (!payloadFilters[key]) delete payloadFilters[key];
  });
  const response = await fetch('/reports/generate', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
    body: JSON.stringify({
      report_type: reportType.value,
      format: format.value,
      filters: payloadFilters,
    }),
  });
  if (response.ok) {
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `report_${reportType.value}.${format.value === 'excel' ? 'xlsx' : format.value}`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } else {
    alert('Failed to generate report');
  }
}
</script>

<template>
  <Head title="Reports" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-amber-100/60 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">System Reports</h1>
        <p class="text-gray-600 mt-2">
          Generate and download comprehensive reports across all modules
        </p>
      </div>

      <!-- Report Type Grid -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <button
          v-for="opt in reportOptions"
          :key="opt.value"
          @click="reportType = opt.value"
          :class="[
            'flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all',
            reportType === opt.value
              ? 'border-amber-500 bg-amber-50 text-amber-700'
              : 'border-amber-100 hover:border-amber-300'
          ]"
        >
          <component :is="opt.icon" class="w-6 h-6" />
          <span class="text-sm font-medium">{{ opt.label }}</span>
        </button>
      </div>

      <!-- Main Form Card -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-6 space-y-6">

        <!-- Dynamic Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="filter in dynamicFilters" :key="filter.key" class="space-y-1">
            <label class="text-sm font-medium text-gray-700">
              {{ filter.label }}
            </label>
            <select
              v-if="filter.type === 'select'"
              v-model="filters[filter.key]"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
            >
              <option value="">-- Any --</option>
              <option v-for="opt in filter.options" :key="opt[filter.optionValue]" :value="opt[filter.optionValue]">
                {{ typeof filter.optionLabel === 'function' ? filter.optionLabel(opt) : opt[filter.optionLabel] }}
              </option>
            </select>
            <input
              v-else-if="filter.type === 'text'"
              v-model="filters[filter.key]"
              type="text"
              :placeholder="filter.placeholder"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
            />
            <input
              v-else-if="filter.type === 'date'"
              v-model="filters[filter.key]"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Format Selection & Generate -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
          <div class="flex gap-2">
            <button
              v-for="fmt in ['csv', 'pdf']"
              :key="fmt"
              @click="format = fmt"
              :class="[
                'inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-sm font-semibold',
                format === fmt
                  ? 'bg-amber-100/80 text-amber-900 border border-amber-200/30'
                  : 'bg-gray-100/80 text-gray-700 border border-gray-200/30'
              ]"
            >
              <component :is="fmt === 'pdf' ? File : FileText" class="w-4 h-4" />
              {{ fmt.toUpperCase() }}
            </button>
          </div>
          <button
            @click="downloadReport"
            class="inline-flex items-center gap-2 px-6 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold transition-all"
          >
            <Download class="w-4 h-4" />
            Generate Report
          </button>
        </div>

        <!-- Info / Notes -->
        <div class="text-sm text-gray-600 border-t border-amber-100/60 pt-4">
          <p>✓ Reports are generated asynchronously and downloaded directly.</p>
          <p>✓ Filter selections are optional — leave blank for full data.</p>
          <p>✓ Excel and CSV exports contain raw data; PDF includes formatted summary and charts.</p>
        </div>
      </div>

      <!-- Quick Help Card -->
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 shadow-xl shadow-amber-900/[0.01]">
        <h3 class="font-medium text-amber-900 flex items-center gap-2">
          <Printer class="w-5 h-5" />
          Available Report Types
        </h3>
        <ul class="mt-2 text-sm text-amber-800 space-y-1 ml-7 list-disc">
          <li><strong>Financial Summary</strong> – Expected vs paid across terms/years</li>
          <li><strong>Student Enrollment</strong> – List of students by class</li>
          <li><strong>Fee Collection</strong> – Expected vs collected per fee type/class</li>
          <li><strong>Expense Report</strong> – All expenses with category filters</li>
          <li><strong>Payment History</strong> – Detailed payment log for students</li>
        </ul>
      </div>

    </div>
  </div>
</template>