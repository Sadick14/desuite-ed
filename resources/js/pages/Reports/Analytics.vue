<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';
import {
  TrendingUp,
  Users,
  Wallet,
  CreditCard,
  Download,
  ArrowRight,
  TrendingDown,
  PieChart,
  Percent
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';
import ExportDropdown from '@/components/ExportDropdown.vue';

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

type AnalyticsData = {
  summary: {
    totalStudents: number;
    totalRevenue: number;
    totalExpenses: number;
    netProfit: number;
    paymentRate: number;
  };
  revenueByTerm: Array<{ term: string; revenue: number; expected: number }>;
  expensesByCategory: Array<{ category: string; amount: number; percentage: number }>;
  studentsByLevel: Array<{ level: string; count: number }>;
  monthlyTrend: Array<{ month: string; revenue: number; expenses: number }>;
};

const props = defineProps<{
  analytics: AnalyticsData;
  terms: any[];
  academicYears: any[];
  filters?: { year: number | null; term: number | null };
}>();

const selectedYear = ref(props.filters?.year?.toString() || '');
const selectedTerm = ref(props.filters?.term?.toString() || '');

// Only show terms that belong to the selected academic year
const filteredTerms = computed(() => {
  if (!selectedYear.value) return props.terms;
  return props.terms.filter(t => {
    // term.academic_year_id matches directly, or check the nested relation
    const yearId = t.academic_year_id ?? t.academic_year?.id;
    return String(yearId) === String(selectedYear.value);
  });
});

// When year changes, reset term if it no longer belongs to the new year
watch(selectedYear, (newYear) => {
  if (selectedTerm.value) {
    const termStillValid = filteredTerms.value.some(
      t => String(t.id) === String(selectedTerm.value)
    );
    if (!termStillValid) {
      selectedTerm.value = '';
      // The term watcher below will fire and handle the request
      return;
    }
  }
  // If term was already empty, fetch now
  fetchAnalytics();
});

watch(selectedTerm, () => {
  fetchAnalytics();
});

function fetchAnalytics() {
  const params: Record<string, string> = {};
  if (selectedYear.value) params.year = selectedYear.value;
  if (selectedTerm.value) params.term = selectedTerm.value;

  router.get('/analytics', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}

// Computed values for simplified layout
const maxLevelCount = computed(() => {
  let max = 0;
  props.analytics.studentsByLevel.forEach(s => {
    if (s.count > max) max = s.count;
  });
  return max > 0 ? max : 1;
});

function getLevelBarWidthPercent(count: number) {
  return `${Math.round((count / maxLevelCount.value) * 100)}%`;
}

const profitMargin = computed(() => {
  if (props.analytics.summary.totalRevenue <= 0) return 0;
  return ((props.analytics.summary.netProfit / props.analytics.summary.totalRevenue) * 100).toFixed(1);
});

const costRatio = computed(() => {
  if (props.analytics.summary.totalRevenue <= 0) return 0;
  const ratio = (props.analytics.summary.totalExpenses / props.analytics.summary.totalRevenue) * 100;
  return Math.min(100, Math.max(0, Math.round(ratio)));
});

const revenuePerStudent = computed(() => {
  if (props.analytics.summary.totalStudents <= 0) return 0;
  return props.analytics.summary.totalRevenue / props.analytics.summary.totalStudents;
});

// Chart 1: Monthly Trend Line Chart Data
const monthlyTrendChartData = computed(() => ({
  labels: props.analytics.monthlyTrend.map(m => m.month),
  datasets: [
    {
      label: 'Revenue',
      data: props.analytics.monthlyTrend.map(m => m.revenue),
      borderColor: '#10b981', // emerald-500
      backgroundColor: 'rgba(16, 185, 129, 0.03)',
      borderWidth: 2,
      pointBackgroundColor: '#10b981',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: '#10b981',
      pointRadius: 4,
      pointHoverRadius: 6,
      tension: 0.4,
      fill: true,
    },
    {
      label: 'Expenses',
      data: props.analytics.monthlyTrend.map(m => m.expenses),
      borderColor: '#ef4444', // rose-500
      backgroundColor: 'rgba(239, 68, 68, 0.03)',
      borderWidth: 2,
      pointBackgroundColor: '#ef4444',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: '#ef4444',
      pointRadius: 4,
      pointHoverRadius: 6,
      tension: 0.4,
      fill: true,
    },
  ],
}));

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
      align: 'end' as const,
      labels: {
        boxWidth: 8,
        boxHeight: 8,
        padding: 15,
        font: { size: 10, weight: 'bold' as const, family: 'Inter, sans-serif' },
        color: '#787878',
      },
    },
    tooltip: {
      padding: 10,
      backgroundColor: '#1c1917',
      titleFont: { size: 11, weight: 'bold' as const, family: 'Inter, sans-serif' },
      bodyFont: { size: 10, family: 'Inter, sans-serif' },
      cornerRadius: 8,
      callbacks: {
        label: function (context: any) {
          return ` ${context.dataset.label}: GHS ${context.raw.toLocaleString()}`;
        }
      }
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: '#a8a8a8',
        font: { size: 9, family: 'Inter, sans-serif' },
        callback: function (val: any) {
          return val >= 1000 ? `GHS ${(val / 1000)}k` : `GHS ${val}`;
        }
      },
      grid: {
        color: 'rgba(120, 120, 120, 0.06)',
        drawTicks: false,
      },
      border: {
        dash: [4, 4],
        display: false
      }
    },
    x: {
      ticks: {
        color: '#a8a8a8',
        font: { size: 9, family: 'Inter, sans-serif' },
      },
      grid: {
        display: false,
      },
    },
  },
};

// Chart 2: Expenses Category Doughnut Chart Data
const expensesCategoryChartData = computed(() => ({
  labels: props.analytics.expensesByCategory.map(e => e.category),
  datasets: [
    {
      data: props.analytics.expensesByCategory.map(e => e.amount),
      backgroundColor: [
        '#10b981', // Emerald
        '#f59e0b', // Amber
        '#ef4444', // Rose
        '#8b5cf6', // Violet
        '#64748b', // Slate
      ],
      borderWidth: 2,
      borderColor: '#ffffff',
      hoverOffset: 4
    },
  ],
}));

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        boxWidth: 8,
        boxHeight: 8,
        padding: 12,
        font: { size: 9, weight: 'bold' as const, family: 'Inter, sans-serif' },
        color: '#787878',
      },
    },
    tooltip: {
      padding: 10,
      backgroundColor: '#1c1917',
      titleFont: { size: 11, weight: 'bold' as const, family: 'Inter, sans-serif' },
      bodyFont: { size: 10, family: 'Inter, sans-serif' },
      cornerRadius: 8,
      callbacks: {
        label: function (context: any) {
          const value = context.parsed;
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
          const percentage = ((value / total) * 100).toFixed(1);
          return ` GHS ${value.toLocaleString()} (${percentage}%)`;
        },
      },
    },
  },
  cutout: '65%',
};

// CSV Export
function exportAnalytics() {
  const data = {
    timestamp: new Date().toISOString(),
    summary: props.analytics.summary,
    revenueByTerm: props.analytics.revenueByTerm,
    expensesByCategory: props.analytics.expensesByCategory,
    studentsByLevel: props.analytics.studentsByLevel,
    monthlyTrend: props.analytics.monthlyTrend,
  };

  const csv = [
    '=== ANALYTICS SUMMARY ===',
    '',
    'Total Students,' + data.summary.totalStudents,
    'Total Revenue,' + data.summary.totalRevenue,
    'Total Expenses,' + data.summary.totalExpenses,
    'Net Profit,' + data.summary.netProfit,
    'Collection Rate,' + data.summary.paymentRate + '%',
    '',
    '=== REVENUE BY TERM ===',
    'Term,Revenue,Expected',
    ...data.revenueByTerm.map(r => `${r.term},${r.revenue},${r.expected}`),
    '',
    '=== EXPENSES BY CATEGORY ===',
    'Category,Amount,Percentage',
    ...data.expensesByCategory.map(e => `${e.category},${e.amount},${e.percentage}%`),
    '',
    '=== STUDENTS BY LEVEL ===',
    'Level,Count',
    ...data.studentsByLevel.map(s => `${s.level},${s.count}`),
    '',
    '=== MONTHLY TREND ===',
    'Month,Revenue,Expenses',
    ...data.monthlyTrend.map(m => `${m.month},${m.revenue},${m.expenses}`),
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `analytics_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  window.URL.revokeObjectURL(url);
}
</script>

<template>
  <Head title="Analytics Dashboard" />

  <div class="min-h-screen bg-stone-50/50 text-stone-900 antialiased selection:bg-amber-200">
    <div class="max-w-7xl mx-auto px-6 lg:px-12 py-10 space-y-8">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-stone-200/80 pb-6">
        <div>
          <span class="text-[10px] font-black uppercase tracking-widest text-amber-800 bg-amber-100/80 px-2.5 py-1 rounded-full">Reporting Hub</span>
          <h1 class="text-3xl font-black tracking-tight text-stone-900 mt-2">Analytics & Insights</h1>
          <p class="text-xs font-semibold text-stone-500 mt-1">Real-time indicators of institutional cash flows and enrollment trends.</p>
        </div>
        <div class="self-start sm:self-center">
          <ExportDropdown
            baseUrl="/exports/analytics"
            :filters="{
              year: selectedYear,
              term: selectedTerm
            }"
            label="Export Ledger"
          />
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl border border-stone-200 p-4 flex flex-col sm:flex-row gap-4 shadow-sm">
        <div class="flex-1">
          <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-1.5">Selected Academic Year</label>
          <select v-model="selectedYear" class="w-full border border-stone-200 rounded-xl px-3 py-2 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white transition-all">
            <option value="">All Academic Years</option>
            <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.name }}</option>
          </select>
        </div>
        <div class="flex-1">
          <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-1.5">Filter by Term</label>
          <select v-model="selectedTerm" class="w-full border border-stone-200 rounded-xl px-3 py-2 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white transition-all" :disabled="filteredTerms.length === 0">
            <option value="">{{ selectedYear ? 'All Terms in Year' : 'All Terms' }}</option>
            <option v-for="term in filteredTerms" :key="term.id" :value="term.id">{{ term.name }}</option>
          </select>
        </div>
      </div>

      <!-- Core Metrics Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Card 1: Financial Health Overview -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div class="space-y-2">
            <div class="flex justify-between items-start">
              <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Net Profit Margin</span>
              <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full" :class="analytics.summary.netProfit >= 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-rose-50 text-rose-800 border border-rose-100'">
                {{ profitMargin }}% margin
              </span>
            </div>
            <h2 class="text-3xl font-black text-stone-900">{{ formatCurrencyCompact(analytics.summary.netProfit) }}</h2>
            <p class="text-[10px] font-semibold text-stone-400 leading-relaxed">
              Consolidated earnings relative to expense footprints.
            </p>
          </div>

          <div class="space-y-3 pt-4 border-t border-stone-100">
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Revenue
              </span>
              <span class="text-stone-900">{{ formatCurrencyCompact(analytics.summary.totalRevenue) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Expenses
              </span>
              <span class="text-stone-900">{{ formatCurrencyCompact(analytics.summary.totalExpenses) }}</span>
            </div>
            <!-- Cost Ratio Slider -->
            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden flex">
              <div class="bg-emerald-500 h-2" :style="{ width: `${100 - costRatio}%` }"></div>
              <div class="bg-rose-500 h-2" :style="{ width: `${costRatio}%` }"></div>
            </div>
          </div>
        </div>

        <!-- Card 2: Collection Efficiency -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div class="space-y-2">
            <div class="flex justify-between items-start">
              <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Collection Rate</span>
              <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-100">
                Targeted Fees
              </span>
            </div>
            <h2 class="text-3xl font-black text-stone-900">{{ analytics.summary.paymentRate }}%</h2>
            <p class="text-[10px] font-semibold text-stone-400 leading-relaxed">
              Ratio of fee payments collected compared to total expectations.
            </p>
          </div>

          <div class="space-y-3 pt-4 border-t border-stone-100">
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px]">Collected</span>
              <span class="text-stone-900">{{ formatCurrencyCompact(analytics.summary.totalRevenue) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px]">Outstanding</span>
              <span class="text-stone-400">{{ formatCurrencyCompact(Math.max(0, analytics.summary.totalRevenue * (100 - analytics.summary.paymentRate) / (analytics.summary.paymentRate || 1))) }}</span>
            </div>
            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
              <div class="bg-emerald-500 h-2 rounded-full" :style="{ width: `${Math.min(100, Math.max(0, analytics.summary.paymentRate))}%` }"></div>
            </div>
          </div>
        </div>

        <!-- Card 3: Student Density -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div class="space-y-2">
            <div class="flex justify-between items-start">
              <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Enrolled Registry</span>
              <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-lime-50 text-lime-900 border border-lime-100 font-bold">
                Active Count
              </span>
            </div>
            <h2 class="text-3xl font-black text-stone-900">{{ analytics.summary.totalStudents }} Students</h2>
            <p class="text-[10px] font-semibold text-stone-400 leading-relaxed">
              Consolidated registry count of active students.
            </p>
          </div>

          <div class="space-y-3 pt-4 border-t border-stone-100">
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px]">Average Revenue/Student</span>
              <span class="text-stone-900">{{ formatCurrencyCompact(revenuePerStudent) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs font-bold">
              <span class="text-stone-500 uppercase tracking-wider text-[10px]">Active Grade Levels</span>
              <span class="text-stone-900">{{ analytics.studentsByLevel.length }} Levels</span>
            </div>
            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
              <div class="bg-amber-500 h-2 rounded-full" style="width: 100%"></div>
            </div>
          </div>
        </div>

      </div>

      <!-- Main Visualizations Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Column 1: Monthly Trend Line Chart -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm lg:col-span-2 space-y-6 flex flex-col justify-between">
          <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-800">
                <TrendingUp class="w-4 h-4" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Revenue & Expense Trend</h3>
                <p class="text-[10px] font-bold text-stone-400">Monthly cash flows comparison (last 6 months)</p>
              </div>
            </div>
          </div>

          <!-- ChartJS Line Chart -->
          <div class="h-64 relative">
            <Line :data="monthlyTrendChartData" :options="lineChartOptions" />
          </div>
        </div>

        <!-- Column 2: Expense Breakdown Pie/Doughnut Chart -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm lg:col-span-1 space-y-6 flex flex-col justify-between">
          <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
            <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-800">
              <Wallet class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Expense Footprints</h3>
              <p class="text-[10px] font-bold text-stone-400">Ranked expense distributions</p>
            </div>
          </div>

          <!-- ChartJS Doughnut Chart -->
          <div class="h-64 relative">
            <Doughnut :data="expensesCategoryChartData" :options="doughnutChartOptions" />
          </div>
        </div>

      </div>

      <!-- Bottom Visualizations Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Column 1: Student Level Density Horizontal Progress -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm lg:col-span-1 space-y-6">
          <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-800">
              <Users class="w-4 h-4 text-amber-900" />
            </div>
            <div>
              <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Enrollment Levels</h3>
              <p class="text-[10px] font-bold text-stone-400">Class level density distribution</p>
            </div>
          </div>

          <!-- List Layout -->
          <div class="space-y-4">
            <div v-for="item in analytics.studentsByLevel" :key="item.level" class="space-y-2">
              <div class="flex justify-between items-center text-xs font-bold text-stone-800">
                <span class="uppercase tracking-wider text-[10px] text-stone-500">{{ item.level }}</span>
                <span class="text-stone-950 font-black">{{ item.count }} Students</span>
              </div>
              <div class="w-full bg-stone-100 rounded-full h-1.5">
                <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: getLevelBarWidthPercent(item.count) }"></div>
              </div>
            </div>
            <!-- Empty state -->
            <div v-if="analytics.studentsByLevel.length === 0" class="text-center py-4 text-stone-400 font-bold uppercase tracking-wider text-[10px]">
              No recorded active student levels.
            </div>
          </div>
        </div>

        <!-- Column 2 & 3: Term comparative collections cards -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm lg:col-span-2 space-y-6">
          <div class="pb-4 border-b border-stone-100">
            <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Academic Term Collection Efficiency</h3>
            <p class="text-[10px] font-bold text-stone-400">Collected vs Expected Target balances across active terms</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="item in analytics.revenueByTerm" :key="item.term" class="bg-stone-50/50 border border-stone-200/60 rounded-2xl p-5 space-y-4 hover:border-amber-200 transition-all duration-300">
              <div class="flex justify-between items-center">
                <span class="text-xs font-black uppercase tracking-widest text-stone-900">{{ item.term }}</span>
                <span class="text-[9px] font-black text-emerald-800 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full uppercase tracking-wider">
                  {{ item.expected > 0 ? Math.round((item.revenue / item.expected) * 100) : 0 }}% Target Met
                </span>
              </div>
              
              <div class="grid grid-cols-2 gap-4 text-xs font-bold pt-2 border-t border-stone-100">
                <div>
                  <p class="text-[9px] text-stone-400 uppercase tracking-widest font-semibold">Collected</p>
                  <p class="text-stone-900 mt-0.5">GHS {{ item.revenue.toLocaleString() }}</p>
                </div>
                <div>
                  <p class="text-[9px] text-stone-400 uppercase tracking-widest font-semibold">Expected Target</p>
                  <p class="text-stone-500 mt-0.5">GHS {{ item.expected.toLocaleString() }}</p>
                </div>
              </div>

              <div class="w-full bg-stone-200/50 rounded-full h-1.5">
                <div 
                  class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" 
                  :style="{ width: `${Math.min(100, item.expected > 0 ? (item.revenue / item.expected) * 100 : 0)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>
