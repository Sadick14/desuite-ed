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
  CheckCircle,
  XCircle,
  Clock,
  Award,
  MessageSquare,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';

const props = defineProps<{
  role?: string;
  stats: any;
  recentPayments?: Array<any>;
  recentExpenses?: Array<any>;
  recentGrades?: Array<any>;
  recentAttendance?: Array<any>;
  myGrades?: Array<any>;
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
const profit = computed(() => {
  if (props.stats.payments !== undefined && props.stats.expenses !== undefined) {
    return props.stats.payments - props.stats.expenses;
  }
  return 0;
});
const profitMargin = computed(() => {
  if (props.stats.payments !== undefined && props.stats.payments > 0) {
    return ((profit.value / props.stats.payments) * 100).toFixed(1);
  }
  return '0';
});

const avgPaymentPerStudent = computed(() => {
  if (props.stats.students > 0 && props.stats.payments !== undefined) {
    const amount = props.stats.payments / props.stats.students;
    return formatCurrencyCompact(amount, 2);
  }
  return formatCurrencyCompact(0);
});

// Date range compute block
const dateRange = computed(() => {
  const now = new Date();
  return `${now.toLocaleString('default', { month: 'long' })} ${now.getFullYear()}`;
});

// Get welcome text based on role
const welcomeText = computed(() => {
  switch (props.role) {
    case 'teacher':
      return 'Here is your teaching schedule and class updates.';
    case 'student':
      return 'Here is your academic progress and upcoming assignments.';
    case 'parent':
      return 'Here is your child’s academic progress and updates.';
    default:
      return "Welcome back! Here's what's happening with your school today.";
  }
});

const dashboardTitle = computed(() => {
  switch (props.role) {
    case 'teacher':
      return 'Teacher Dashboard';
    case 'student':
      return 'Student Dashboard';
    case 'parent':
      return 'Parent Dashboard';
    default:
      return 'School Dashboard';
  }
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
          {{ dashboardTitle }}
        </h1>
        <p class="text-gray-600 mt-1 text-sm">
          {{ welcomeText }}
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

    <!-- ADMIN/OWNER/MEMBER DASHBOARD -->
    <template v-if="!['teacher', 'student', 'parent'].includes(role || 'member')">
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

      <!-- PROFIT SUMMARY CARD -->
      <div class="bg-white rounded-2xl border border-amber-200 shadow-xl shadow-amber-900/[0.03] p-6 relative overflow-hidden">
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
            <div v-for="payment in recentPayments?.slice(0, 4)" :key="payment.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
              <div>
                <p class="font-semibold text-sm text-gray-900">{{ payment.student }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ new Date(payment.date).toLocaleDateString() }}</p>
              </div>
              <p class="font-bold text-sm text-green-600">+ {{ formatCurrencyCompact(payment.amount, 2) }}</p>
            </div>
            <div v-if="!recentPayments || recentPayments.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent payments recorded</div>
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
            <div v-for="expense in recentExpenses?.slice(0, 4)" :key="expense.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
              <div>
                <p class="font-semibold text-sm text-gray-900">{{ expense.description }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ new Date(expense.date).toLocaleDateString() }}</p>
              </div>
              <p class="font-bold text-sm text-red-600">- GHS {{ expense.amount.toLocaleString() }}</p>
            </div>
            <div v-if="!recentExpenses || recentExpenses.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent expenses logged</div>
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
          <a href="/sms" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-amber-50/40 border border-amber-100/40 hover:bg-amber-50 transition group text-center">
            <div class="p-2.5 rounded-xl bg-white border border-amber-100 group-hover:border-amber-300 transition shadow-sm">
              <MessageSquare class="w-5 h-5 text-blue-600" />
            </div>
            <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Send SMS</span>
          </a>
        </div>
      </div>
    </template>

    <!-- TEACHER DASHBOARD -->
    <template v-else-if="role === 'teacher'">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Students</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.students.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100/50">
              <GraduationCap class="w-5 h-5 text-amber-800" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Active Classes</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.classes.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-100/50">
              <BookOpen class="w-5 h-5 text-blue-800" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Today's Attendance</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.attendance.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-green-50 rounded-xl border border-green-100/50">
              <CheckCircle class="w-5 h-5 text-green-800" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Grades Entered</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.grades.toLocaleString() }}</p>
            </div>
            <div class="p-3 bg-lime-50 rounded-xl border border-lime-100/50">
              <Award class="w-5 h-5 text-lime-800" />
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-center mb-4 pb-2 border-b border-amber-50">
            <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
              <Award class="w-4 h-4 text-lime-600 stroke-[2.5]" /> Recent Grades
            </h3>
            <a href="/grades" class="text-xs font-bold text-indigo-600 hover:underline">View all</a>
          </div>
          <div class="space-y-3">
            <div v-for="grade in recentGrades?.slice(0, 4)" :key="grade.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
              <div>
                <p class="font-semibold text-sm text-gray-900">{{ grade.student }} - {{ grade.exam }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ new Date(grade.date).toLocaleDateString() }}</p>
              </div>
              <p class="font-bold text-sm text-gray-900">{{ grade.score }}%</p>
            </div>
            <div v-if="!recentGrades || recentGrades.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent grades</div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-center mb-4 pb-2 border-b border-amber-50">
            <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
              <CheckCircle class="w-4 h-4 text-blue-600 stroke-[2.5]" /> Recent Attendance
            </h3>
            <a href="/attendance" class="text-xs font-bold text-indigo-600 hover:underline">View all</a>
          </div>
          <div class="space-y-3">
            <div v-for="att in recentAttendance?.slice(0, 4)" :key="att.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
              <div>
                <p class="font-semibold text-sm text-gray-900">{{ att.student }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ new Date(att.date).toLocaleDateString() }}</p>
              </div>
              <span :class="
                att.status === 'present' ? 'text-green-600' :
                att.status === 'absent' ? 'text-red-600' : 'text-amber-600'
              " class="font-bold text-sm">
                {{ att.status }}
              </span>
            </div>
            <div v-if="!recentAttendance || recentAttendance.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent attendance</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
        <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <a href="/attendance" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-blue-50/40 border border-blue-100/40 hover:bg-blue-50 transition group text-center">
            <div class="p-2.5 rounded-xl bg-white border border-blue-100 group-hover:border-blue-300 transition shadow-sm">
              <CheckCircle class="w-5 h-5 text-blue-800" />
            </div>
            <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Mark Attendance</span>
          </a>
          <a href="/grades" class="flex flex-col items-center gap-2.5 p-4 rounded-xl bg-lime-50/40 border border-lime-100/40 hover:bg-lime-50 transition group text-center">
            <div class="p-2.5 rounded-xl bg-white border border-lime-100 group-hover:border-lime-300 transition shadow-sm">
              <Award class="w-5 h-5 text-lime-800" />
            </div>
            <span class="text-xs font-bold text-gray-700 group-hover:text-gray-900">Enter Grades</span>
          </a>
        </div>
      </div>
    </template>

    <!-- STUDENT/PARENT DASHBOARD -->
    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Attendance Rate</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.attendancePercentage }}%</p>
            </div>
            <div class="p-3 bg-green-50 rounded-xl border border-green-100/50">
              <CheckCircle class="w-5 h-5 text-green-800" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Average Grade</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ Number(stats.gradeAverage).toFixed(1) }}%</p>
            </div>
            <div class="p-3 bg-lime-50 rounded-xl border border-lime-100/50">
              <Award class="w-5 h-5 text-lime-800" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Upcoming Exams</p>
              <p class="text-3xl font-black text-gray-900 mt-2">{{ stats.upcomingExams }}</p>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100/50">
              <Clock class="w-5 h-5 text-amber-800" />
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-amber-50">
          <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm uppercase tracking-wider">
            <Award class="w-4 h-4 text-lime-600 stroke-[2.5]" /> My Recent Grades
          </h3>
        </div>
        <div class="space-y-3">
          <div v-for="grade in myGrades?.slice(0, 5)" :key="grade.id" class="flex justify-between items-center border-b border-amber-50/60 pb-2.5 last:border-0 last:pb-0">
            <div>
              <p class="font-semibold text-sm text-gray-900">{{ grade.exam }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ new Date(grade.date).toLocaleDateString() }}</p>
            </div>
            <p class="font-bold text-sm text-gray-900">{{ grade.score }}%</p>
          </div>
          <div v-if="!myGrades || myGrades.length === 0" class="text-center text-sm font-medium text-gray-400 py-6">No recent grades</div>
        </div>
      </div>
    </template>
  </div>
</template>