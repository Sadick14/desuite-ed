<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
  Users,
  GraduationCap,
  CreditCard,
  Wallet,
//   TrendingUp,
  TrendingDown,
  PlusCircle,
  BookOpen,
  DollarSign,
  Calendar,
  MoreHorizontal,
} from '@lucide/vue';
import { computed } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';

const props = defineProps<{
  stats: {
    students: number;
    classes: number;
    payments: number;
    expenses: number;
  };
  recentPayments: Array<{
    id: number;
    student: string;
    amount: number;
    date: string;
  }>;
  recentExpenses: Array<{
    id: number;
    description: string;
    amount: number;
    date: string;
  }>;
}>();

defineOptions({
  layout: () => ({
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: '/dashboard',
      },
    ],
  }),
});

// Computed financial metrics
const profit = computed(() => props.stats.payments - props.stats.expenses);
const profitMargin = computed(() =>
  props.stats.payments > 0
    ? ((profit.value / props.stats.payments) * 100).toFixed(1)
    : '0'
);

const avgPaymentPerStudent = computed(() => {
  const amount = props.stats.students > 0
    ? props.stats.payments / props.stats.students
    : 0;
  return formatCurrencyCompact(amount, 2);
});

// Date range compute block
const dateRange = computed(() => {
  const now = new Date();
  return `${now.toLocaleString('default', { month: 'long' })} ${now.getFullYear()}`;
});
</script>

<template>
  <Head title="Dashboard" />

  <!-- Full-bleed brand identity alignment background framework -->
  <div class="flex min-h-screen flex-1 flex-col gap-6 p-4 md:p-6 overflow-x-auto bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    
    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
          School Dashboard
        </h1>
        <p class="text-gray-600 mt-1 text-sm">
          Welcome back! Here's what's happening with your school today.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button class="px-3.5 py-2 text-xs font-bold border border-amber-200/60 rounded-xl bg-white/80 backdrop-blur-sm shadow-sm flex items-center gap-1.5 text-gray-700 hover:bg-amber-50/50 transition">
          <Calendar class="w-3.5 h-3.5 text-amber-700" />
          {{ dateRange }}
          <MoreHorizontal class="w-3.5 h-3.5 ml-1 text-gray-400" />
        </button>
      </div>
    </div>

    <!-- KPI CARDS ROW -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <!-- Students -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5 transition-all hover:shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Students</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.students.toLocaleString() }}</p>
            <p class="text-xs font-medium text-amber-700 bg-amber-100/50 rounded-md px-1.5 py-0.5 inline-block mt-2">Enrolled this term</p>
          </div>
          <div class="p-3 bg-amber-50 rounded-xl border border-amber-100/50">
            <Users class="w-5 h-5 text-amber-800" />
          </div>
        </div>
      </div>

      <!-- Classes -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5 transition-all hover:shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Active Classes</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.classes.toLocaleString() }}</p>
            <p class="text-xs font-medium text-gray-500 mt-2.5 block">
              Avg. <span class="font-bold text-gray-800">{{ stats.classes > 0 ? Math.round(stats.students / stats.classes) : 0 }}</span> students/class
            </p>
          </div>
          <div class="p-3 bg-amber-50 rounded-xl border border-amber-100/50">
            <GraduationCap class="w-5 h-5 text-amber-800" />
          </div>
        </div>
      </div>

      <!-- Payments (Revenue) -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5 transition-all hover:shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Revenue</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ formatCurrencyCompact(stats.payments) }}</p>
            <p class="text-xs font-medium text-green-700 bg-green-50 rounded-md px-1.5 py-0.5 inline-block mt-2">Year to date</p>
          </div>
          <div class="p-3 bg-green-50 rounded-xl border border-green-100/30">
            <CreditCard class="w-5 h-5 text-green-600" />
          </div>
        </div>
      </div>

      <!-- Expenses -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5 transition-all hover:shadow-md">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Expenses</p>
            <p class="text-3xl font-black text-gray-900 mt-2">{{ formatCurrencyCompact(stats.expenses) }}</p>
            <p class="text-xs font-medium text-red-700 bg-red-50 rounded-md px-1.5 py-0.5 inline-block mt-2">Operating costs</p>
          </div>
          <div class="p-3 bg-red-50 rounded-xl border border-red-100/30">
            <Wallet class="w-5 h-5 text-red-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- PROFIT SUMMARY CARD (Signature layout concept with light warm backdrop) -->
    <div class="bg-white rounded-2xl border border-amber-200 shadow-xl shadow-amber-900/[0.03] p-6 relative overflow-hidden">
      <!-- High-contrast decorative top color line to anchor the view -->
      <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-300 via-lime-400 to-emerald-400"></div>
      
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pt-1">
        <div>
          <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Net Accounting Profit</p>
          <p class="text-3xl font-black text-gray-900 mt-1">{{ formatCurrencyCompact(profit) }}</p>
          <div class="flex items-center gap-2 mt-2">
            <span class="text-xs font-bold px-2 py-0.5 bg-lime-400 text-gray-900 rounded-lg">Margin {{ profitMargin }}%</span>
          </div>
        </div>
        <div class="flex items-center gap-6 bg-amber-50/60 border border-amber-100/80 rounded-xl p-4 md:min-w-[400px] justify-around">
          <div class="text-center sm:text-right">
            <p class="text-gray-500 font-medium text-xs">Avg. Revenue / Student</p>
            <p class="text-lg font-bold text-gray-900 mt-0.5">{{ avgPaymentPerStudent }}</p>
          </div>
          <div class="h-8 w-px bg-amber-200/60"></div>
          <div class="text-center sm:text-right">
            <p class="text-gray-500 font-medium text-xs">Collection Rate</p>
            <p class="text-lg font-bold text-gray-900 mt-0.5">
              {{ stats.payments > 0 ? Math.min(100, Math.round((stats.payments / (stats.payments + stats.expenses)) * 100)) : 0 }}%
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT ACTIVITY PANELS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Payments -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-amber-50">
          <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
            <DollarSign class="w-4 h-4 text-green-600 stroke-[2.5]" /> Recent Payments
          </h3>
          <a href="/payments" class="text-xs font-bold text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
          <div v-for="payment in recentPayments.slice(0, 4)" :key="payment.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
            <div>
              <p class="font-semibold text-sm text-gray-900">{{ payment.student }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ new Date(payment.date).toLocaleDateString() }}</p>
            </div>
            <p class="font-bold text-sm text-green-600">+ {{ formatCurrencyCompact(payment.amount, 2) }}</p>
          </div>
          <div v-if="recentPayments.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent payments recorded</div>
        </div>
      </div>

      <!-- Recent Expenses -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-amber-50">
          <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
            <Wallet class="w-4 h-4 text-red-600 stroke-[2.5]" /> Recent Expenses
          </h3>
          <a href="/expenses" class="text-xs font-bold text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
          <div v-for="expense in recentExpenses.slice(0, 4)" :key="expense.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
            <div>
              <p class="font-semibold text-sm text-gray-900">{{ expense.description }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ new Date(expense.date).toLocaleDateString() }}</p>
            </div>
            <p class="font-bold text-sm text-red-600">- GHS {{ expense.amount.toLocaleString() }}</p>
          </div>
          <div v-if="recentExpenses.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent expenses logged</div>
        </div>
      </div>
    </div>

    <!-- QUICK ACTIONS SHEET -->
    <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
      <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Quick Operational Actions</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <a href="/students/" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-amber-50/40 border border-amber-100/40 hover:bg-amber-50 transition group text-center">
          <div class="p-2.5 rounded-xl bg-white border border-amber-100 group-hover:border-amber-300 transition shadow-sm">
            <PlusCircle class="w-5 h-5 text-amber-800" />
          </div>
          <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Add Student</span>
        </a>

        <a href="/classes" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-amber-50/40 border border-amber-100/40 hover:bg-amber-50 transition group text-center">
          <div class="p-2.5 rounded-xl bg-white border border-amber-100 group-hover:border-amber-300 transition shadow-sm">
            <BookOpen class="w-5 h-5 text-amber-800" />
          </div>
          <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Manage Classes</span>
        </a>

        <a href="/payments/" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-amber-50/40 border border-amber-100/40 hover:bg-amber-50 transition group text-center">
          <div class="p-2.5 rounded-xl bg-white border border-amber-100 group-hover:border-amber-300 transition shadow-sm">
            <DollarSign class="w-5 h-5 text-green-600" />
          </div>
          <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Record Payment</span>
        </a>

        <a href="/expenses/" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-amber-50/40 border border-amber-100/40 hover:bg-amber-50 transition group text-center">
          <div class="p-2.5 rounded-xl bg-white border border-amber-100 group-hover:border-amber-300 transition shadow-sm">
            <TrendingDown class="w-5 h-5 text-red-600" />
          </div>
          <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Add Expense</span>
        </a>

      </div>
    </div>
  </div>
</template>