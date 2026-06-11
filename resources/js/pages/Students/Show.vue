<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  User,
  Phone,
  MapPin,
  Calendar,
  GraduationCap,
  DollarSign,
  CreditCard,
  Wallet,
  AlertCircle,
  CheckCircle2,
  Download,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';
import StudentModal from './StudentModal.vue';

const props = defineProps<{
  student: any;
  currentTerm: any;
  financial: {
    expected: number;
    paid: number;
    balance: number;
    breakdown: {
      fee_type: string;
      expected: number;
      paid: number;
      balance: number;
    }[];
  }
  payments: any[];
  classes: any[];
}>();

const activeTab = ref('overview');
const showEditModal = ref(false);

// Computed for progress
const paymentPercentage = computed(() => {
  if (props.financial.expected === 0) {
    return 0;
  }
  return Math.round((props.financial.paid / props.financial.expected) * 100);
});

const isFullyPaid = computed(() => props.financial.balance <= 0);

// Format date helper
const formatDate = (date: string) => {
  if (!date) {
    return '—';
  }
  return new Date(date).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>

<template>
  <Head :title="`${student.first_name} ${student.last_name}`" />

  <!-- Full-bleed brand identity alignment framework -->
  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <!-- PROFILE CARD HEADER -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.02] border border-amber-100 overflow-hidden relative">
        <!-- Replaced hard indigo/purple banner gradient with the premium amber-gold horizon background -->
        <div class="h-32 bg-gradient-to-r from-amber-100 via-amber-200/70 to-orange-100 border-b border-amber-200/40"></div>
        
        <div class="relative px-6 pb-6">
          <div class="flex flex-col sm:flex-row sm:items-end -mt-12 gap-4">
            <!-- Custom Avatar Container -->
            <div class="w-24 h-24 rounded-2xl bg-white shadow-xl flex items-center justify-center border-4 border-white">
              <div class="w-full h-full rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center border border-amber-200/40">
                <span class="text-2xl font-black text-amber-900 tracking-wider">
                  {{ student.first_name?.charAt(0) }}{{ student.last_name?.charAt(0) }}
                </span>
              </div>
            </div>
            
            <div class="flex-1">
              <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                {{ student.first_name }} {{ student.last_name }}
              </h1>
              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-1.5">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500">
                  <GraduationCap class="w-4 h-4 text-amber-700" />
                  ID: {{ student.student_id }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100/80 text-amber-900 border border-amber-200/30 shadow-sm">
                  {{ student.class?.name || 'No Class Associated' }}
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                  <Calendar class="w-4 h-4 text-gray-400" />
                  Registered: {{ formatDate(student.admission_date) }}
                </span>
              </div>
            </div>
            
            <!-- Contextual Operational Action -->
            <div class="flex items-center gap-2">
              <a
                :href="`/exports/students/${student.id}/statement`"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl text-xs font-bold tracking-wide transition shadow-sm cursor-pointer"
              >
                <Download class="w-3.5 h-3.5 text-gray-900" />
                Download Statement
              </a>
              <button
                @click="showEditModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-100/80 hover:bg-amber-200/70 border border-amber-200/60 text-gray-900 rounded-xl text-xs font-bold tracking-wide transition shadow-sm cursor-pointer"
              >
                <User class="w-3.5 h-3.5 text-amber-800" />
                Edit Folders
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TABS SECTION NAVIGATION -->
      <div class="border-b border-amber-100">
        <nav class="flex space-x-6" aria-label="Tabs">
          <button
            @click="activeTab = 'overview'"
            :class="[
              'py-3.5 px-1 text-xs font-bold tracking-wider uppercase border-b-2 transition-all duration-200 focus:outline-none',
              activeTab === 'overview'
                ? 'border-amber-500 text-gray-900'
                : 'border-transparent text-gray-500 hover:text-gray-900'
            ]"
          >
            Overview
          </button>
          <button
            @click="activeTab = 'medical'"
            :class="[
              'py-3.5 px-1 text-xs font-bold tracking-wider uppercase border-b-2 transition-all duration-200 focus:outline-none',
              activeTab === 'medical'
                ? 'border-amber-500 text-gray-900'
                : 'border-transparent text-gray-500 hover:text-gray-900'
            ]"
          >
            Medical & Emergency
          </button>
          <Link
            :href="route('attendance.student', student)"
            class="py-3.5 px-1 text-xs font-bold tracking-wider uppercase border-b-2 border-transparent text-gray-500 hover:text-gray-900"
          >
            Attendance History
          </Link>
          <button
            @click="activeTab = 'financial'"
            :class="[
              'py-3.5 px-1 text-xs font-bold tracking-wider uppercase border-b-2 transition-all duration-200 focus:outline-none',
              activeTab === 'financial'
                ? 'border-amber-500 text-gray-900'
                : 'border-transparent text-gray-500 hover:text-gray-900'
            ]"
          >
            Financial Summary
          </button>
          <button
            @click="activeTab = 'payments'"
            :class="[
              'py-3.5 px-1 text-xs font-bold tracking-wider uppercase border-b-2 transition-all duration-200 focus:outline-none',
              activeTab === 'payments'
                ? 'border-amber-500 text-gray-900'
                : 'border-transparent text-gray-500 hover:text-gray-900'
            ]"
          >
            Payment History
          </button>
        </nav>
      </div>

      <!-- OVERVIEW TAB CONTENT -->
      <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Personal Info Sheet -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md shadow-amber-900/[0.01] border border-amber-100 p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2 mb-5 border-b border-amber-50 pb-3">
            <User class="w-4 h-4 text-amber-700" />
            Personal Identification Records
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div class="space-y-4">
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Full Legal Name</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ student.first_name }} {{ student.last_name }}</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Gender Profile</p>
                <p class="text-sm font-medium text-gray-900 capitalize mt-0.5">{{ student.gender || '—' }}</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Official Date of Birth</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(student.date_of_birth) }}</p>
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">System Identifier / ID</p>
                <p class="text-sm font-bold text-amber-900 font-mono mt-0.5">{{ student.student_id }}</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Assigned Cohort / Class</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ student.class?.name || '—' }}</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Admission Registry Date</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5">{{ formatDate(student.admission_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Guardian Info Card -->
        <div class="bg-white rounded-xl shadow-md shadow-amber-900/[0.01] border border-amber-100 p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2 mb-5 border-b border-amber-50 pb-3">
            <User class="w-4 h-4 text-amber-700" />
            Guardian & Contact Mapping
          </h2>
          <div class="space-y-4">
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Primary Responsible Parent</p>
              <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ student.parent_name || '—' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Emergency Phone Link</p>
              <div class="flex items-center gap-2 mt-1 bg-amber-50/50 border border-amber-100/60 rounded-xl px-3 py-2">
                <Phone class="w-3.5 h-3.5 text-amber-800" />
                <p class="text-xs font-bold text-gray-900 font-mono">{{ student.parent_phone || '—' }}</p>
              </div>
            </div>
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Residential Physical Address</p>
              <div class="flex items-start gap-2 mt-1">
                <MapPin class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" />
                <p class="text-xs text-gray-600 leading-relaxed">{{ student.address || '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MEDICAL & EMERGENCY TAB CONTENT -->
      <div v-if="activeTab === 'medical'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md shadow-amber-900/[0.01] border border-amber-100 p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2 mb-5 border-b border-amber-50 pb-3">
            <User class="w-4 h-4 text-amber-700" />
            Medical Records
          </h2>
          <div class="space-y-4">
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Known Allergies</p>
              <p class="text-sm font-medium text-gray-900 mt-0.5">{{ student.allergies || '—' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Medical Notes</p>
              <p class="text-sm text-gray-600 mt-0.5 leading-relaxed">{{ student.medical_notes || 'No medical notes on file' }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-md shadow-amber-900/[0.01] border border-amber-100 p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2 mb-5 border-b border-amber-50 pb-3">
            <Phone class="w-4 h-4 text-red-600" />
            Emergency Contact Information
          </h2>
          <div class="space-y-4">
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Emergency Contact Name</p>
              <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ student.emergency_contact_name || '—' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Relationship to Student</p>
              <p class="text-sm font-medium text-gray-900 mt-0.5">{{ student.emergency_contact_relationship || '—' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Emergency Phone</p>
              <div class="flex items-center gap-2 mt-1 bg-red-50 border border-red-100 rounded-xl px-3 py-2">
                <Phone class="w-3.5 h-3.5 text-red-600" />
                <p class="text-xs font-bold text-gray-900 font-mono">{{ student.emergency_contact_phone || '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FINANCIAL SUMMARY TAB CONTENT -->
      <div v-if="activeTab === 'financial'" class="space-y-6">
        <!-- Balance Row Matrix -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <!-- Expected Fees -->
          <div class="bg-white rounded-xl border border-amber-100 p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expected Term Fees</p>
                <p class="text-2xl font-black text-gray-900 mt-1">
                  {{ formatCurrencyCompact(financial.expected) }}
                </p>
              </div>
              <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-100/50">
                <Wallet class="w-5 h-5 text-amber-800" />
              </div>
            </div>
          </div>

          <!-- Total Paid -->
          <div class="bg-white rounded-xl border border-amber-100 p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Ledger Paid</p>
                <p class="text-2xl font-black text-green-600 mt-1">
                  {{ formatCurrencyCompact(financial.paid) }}
                </p>
              </div>
              <div class="p-2.5 bg-green-50 rounded-xl border border-green-100/20">
                <CreditCard class="w-5 h-5 text-green-600" />
              </div>
            </div>
          </div>

          <!-- Outstanding Balance -->
          <div class="bg-white rounded-xl border border-amber-100 p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Outstanding Liability</p>
                <p class="text-2xl font-black text-red-600 mt-1">
                  {{ formatCurrencyCompact(financial.balance) }}
                </p>
              </div>
              <div class="p-2.5 bg-red-50 rounded-xl border border-red-100/20">
                <DollarSign class="w-5 h-5 text-red-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Progress Tracker Card -->
        <div class="bg-white rounded-xl border border-amber-100 p-6 shadow-sm">
          <div class="flex justify-between items-center mb-2.5">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Collection Matrix Progress</span>
            <span class="text-xs font-black px-2 py-0.5 bg-amber-100 text-amber-900 rounded-md">{{ paymentPercentage }}% Completed</span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden border border-amber-50">
            <!-- Utilizing vivid green to denote absolute positive accounting execution status -->
            <div
              class="bg-gradient-to-r from-green-500 to-emerald-500 h-full rounded-full transition-all duration-500"
              :style="{ width: `${paymentPercentage}%` }"
            ></div>
          </div>
          <div class="mt-4 flex justify-between text-xs font-semibold text-gray-500">
            <span>Collected: {{ formatCurrencyCompact(financial.paid) }}</span>
            <span>Deficit Backlog: {{ formatCurrencyCompact(financial.balance) }}</span>
          </div>
        </div>

        <!-- Dynamic Feedback Callouts -->
        <!-- Fee Type Breakdown Cards -->
        <div v-if="financial.breakdown && financial.breakdown.length" class="space-y-3">
          <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
            <DollarSign class="w-4 h-4 text-amber-600" />
            Fee Breakdown by Type
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="item in financial.breakdown"
              :key="item.fee_type"
              class="bg-white rounded-xl border border-amber-100 p-4 shadow-sm"
            >
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">
                  {{ item.fee_type.split('_').map((w: string) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') }}
                </span>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold',
                    item.balance <= 0
                      ? 'bg-green-100 text-green-800 border border-green-200/40'
                      : 'bg-red-100 text-red-800 border border-red-200/40'
                  ]"
                >
                  {{ item.balance <= 0 ? 'PAID' : 'OUTSTANDING' }}
                </span>
              </div>
              <div class="grid grid-cols-3 gap-2 text-sm">
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-widest">Expected</p>
                  <p class="font-bold text-gray-900 mt-0.5">{{ formatCurrencyCompact(item.expected) }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-widest">Paid</p>
                  <p class="font-bold text-green-600 mt-0.5">{{ formatCurrencyCompact(item.paid) }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-widest">Balance</p>
                  <p :class="['font-bold mt-0.5', item.balance > 0 ? 'text-red-600' : 'text-green-600']">
                    {{ formatCurrencyCompact(item.balance) }}
                  </p>
                </div>
              </div>
              <!-- Progress bar per fee type -->
              <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div
                  class="bg-gradient-to-r from-green-500 to-emerald-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: `${item.expected > 0 ? Math.round((item.paid / item.expected) * 100) : 0}%` }"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <div v-if="!isFullyPaid" class="bg-amber-50 border border-amber-200 rounded-xl p-5 shadow-sm">
          <div class="flex gap-3.5">
            <AlertCircle class="w-5 h-5 text-amber-700 flex-shrink-0 mt-0.5" />
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">Outstanding Balance Arrears</h3>
              <p class="text-xs text-amber-800 font-medium mt-1.5 leading-relaxed">
                The accounting portfolio reports an open deficit of <strong class="font-black">{{ formatCurrencyCompact(financial.balance) }}</strong> verified against the {{ currentTerm?.name || 'current tracking term' }}. Kindly reconcile account definitions to maintain accurate records.
              </p>
            </div>
          </div>
        </div>
        
        <div v-else class="bg-green-50/60 border border-green-200/60 rounded-xl p-5 shadow-sm">
          <div class="flex gap-3.5">
            <CheckCircle2 class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
            <div>
              <h3 class="text-xs font-bold uppercase tracking-wider text-green-900">Settled Account</h3>
              <p class="text-xs text-green-700 font-medium mt-1.5">
                All assigned institutional fees for {{ currentTerm?.name || 'the present active session' }} have been successfully matched. General standing is complete.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- PAYMENT HISTORY LEDGER TAB CONTENT -->
      <div v-if="activeTab === 'payments'" class="bg-white rounded-xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/60">
              <tr>
                <th scope="col" class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Amount Paid</th>
                <th scope="col" class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Processing Date</th>
                <th scope="col" class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Assigned Session Term</th>
                <th scope="col" class="px-6 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Audit Reference Code</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50/60 bg-white">
              <tr v-for="payment in payments" :key="payment.id" class="hover:bg-amber-50/20 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                  GHS {{ payment.amount.toLocaleString() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-600">
                  {{ formatDate(payment.payment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200/40">
                    {{ payment.term?.name || '—' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-500 font-semibold tracking-tight">
                  {{ payment.receipt_number || '—' }}
                </td>
              </tr>
              <tr v-if="payments.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-xs font-medium text-gray-400 uppercase tracking-widest">
                  No verified historical ledger accounts mapped.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FLOATING ACTION LAYER (Utilizing high-contrast brand lime asset) -->
      <div class="fixed bottom-6 right-6 z-40">
        <a
          :href="`/payments?student_id=${student.id}`"
          class="inline-flex items-center gap-2 px-5 py-3 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-full shadow-xl transition-all duration-200 font-bold text-xs uppercase tracking-wider border border-lime-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500"
        >
          <CreditCard class="w-4 h-4 stroke-[2.5]" />
          Record Payment
        </a>
      </div>
    </div>

    <!-- MODAL INJECTION HOOK -->
    <StudentModal
      v-if="showEditModal"
      :student="student"
      :classes="classes"
      @close="showEditModal = false"
    />
  </div>
</template>