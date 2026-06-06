<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Search,
  X,
  Calendar,
  DollarSign,
  User,
  CreditCard,
  AlertCircle,
  ArrowLeft,
  ArrowRight,
  Check,
  Landmark,
  Smartphone,
  Coins,
  UserCheck,
  FileText,
  Printer,
  Share2,
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';
import ExportDropdown from '@/components/ExportDropdown.vue';

// --- Type definitions ---
type Payment = {
  id: number;
  amount: number;
  payment_type: string;
  payment_method: string;
  receipt_number: string;
  payment_date: string;
  student_id: number;
  term_id: number;
  student: { first_name: string; last_name: string };
  term: { name: string; academic_year?: { name: string } };
};

type FeeStructure = {
  id: number;
  fee_type: string;
  amount: number;
  level: string;
  term_id: number;
  term: { id: number; name: string };
};

type Student = {
  id: number;
  first_name: string;
  last_name: string;
  student_id: string;
  school_class_id: number;
  class?: { id: number; name: string; level: string };
  parent_name: string;
  parent_phone: string;
  balances?: {
    expected: number;
    paid: number;
    balance: number;
    breakdown: Array<{
      fee_type: string;
      expected: number;
      paid: number;
      balance: number;
    }>;
  };
};

const props = defineProps<{
  payments: { data: Payment[] } | Payment[];
  students: Student[];
  terms: any[];
  feeStructures: FeeStructure[];
  activeTerm: any;
  school: {
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo: string | null;
  } | null;
}>();

const schoolLogoUrl = computed(() => {
  if (!props.school?.logo) return null;
  if (props.school.logo.startsWith('http') || props.school.logo.startsWith('data:')) {
    return props.school.logo;
  }
  return props.school.logo.startsWith('/storage') ? props.school.logo : `/storage/${props.school.logo}`;
});

// Handle both paginated and non-paginated payments
const paymentsArray = computed(() => {
  if (Array.isArray(props.payments)) {
    return props.payments;
  }
  return props.payments?.data || [];
});

// UI state
const showModal = ref(false);
const modalState = ref<'recording' | 'receipt'>('recording');
const currentStep = ref(1); // Track multi-step wizard
const editing = ref<Payment | null>(null);
const search = ref('');
const filterTerm = ref('');
const filterType = ref('');
const filterMethod = ref('');
const studentSearch = ref('');
const selectedClassFilter = ref(''); // Filter students by class

// Store the last created payment AND the selected student for receipt
const lastPayment = ref<Payment | null>(null);
const lastStudent = ref<Student | null>(null);

const form = useForm({
  student_id: '',
  term_id: '',
  amount: '',
  payment_type: '',
  payment_method: 'cash',
  payment_date: new Date().toISOString().split('T')[0],
});

// Auto-open modal if student_id in URL query
onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  const studentIdParam = params.get('student_id');

  if (studentIdParam) {
    const student = props.students.find(s => s.id === Number(studentIdParam));
    if (student) {
      form.student_id = student.id;
      selectedClassFilter.value = String(student.school_class_id);
      showModal.value = true;
      currentStep.value = 1;
      // Clean up the URL
      window.history.replaceState({}, '', '/payments');
    }
  }
});

// Get unique classes for filter dropdown
const uniqueClasses = computed(() => {
  const classes = new Map<number, { id: number; name: string }>();
  props.students.forEach(s => {
    if (s.class && !classes.has(s.class.id)) {
      classes.set(s.class.id, s.class);
    }
  });
  return Array.from(classes.values()).sort((a, b) => a.name.localeCompare(b.name));
});

// Student typeahead with class filtering
const filteredStudents = computed(() => {
  // First, filter by class if selected
  let students = props.students;
  if (selectedClassFilter.value) {
    students = students.filter(
      s => String(s.class?.id) === selectedClassFilter.value
    );
  }

  // If no search, show first 10 (or all if filtered by class)
  if (!studentSearch.value.trim()) {
    return students.slice(0, 10);
  }

  // Score-based search
  const term = studentSearch.value.toLowerCase();
  const matches = students
    .map(s => {
      const name = `${s.first_name} ${s.last_name}`.toLowerCase();
      const id = s.student_id?.toLowerCase();

      let score = 0;

      if (name.startsWith(term)) {
        score += 100;
      } else if (name.includes(term)) {
        score += 50;
      }

      if (id?.startsWith(term)) {
        score += 100;
      } else if (id?.includes(term)) {
        score += 50;
      }

      return { student: s, score };
    })
    .filter(m => m.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, 15);

  return matches.map(m => m.student);
});

// ---------- Fee structure helpers ----------
const selectedStudent = computed(() =>
  props.students.find(s => s.id == form.student_id)
);

const availableFeeStructures = computed(() => {
  if (!selectedStudent.value || !props.activeTerm) return [];
  const studentLevel = selectedStudent.value.class?.level;
  return props.feeStructures.filter(fs =>
    fs.level === studentLevel && fs.term_id === props.activeTerm.id
  );
});

// Calculate paid amount for each fee type for the selected student in active term
const feeCardsWithBalance = computed(() => {
  if (!selectedStudent.value || !props.activeTerm) return [];

  if (selectedStudent.value.balances?.breakdown) {
    const studentLevel = selectedStudent.value.class?.level;
    return selectedStudent.value.balances.breakdown.map((b: any) => {
      const fs = props.feeStructures.find(
        f => f.fee_type === b.fee_type && f.level === studentLevel && f.term_id === props.activeTerm.id
      );
      return {
        id: fs?.id || Math.random(),
        fee_type: b.fee_type,
        amount: b.expected,
        paid: b.paid,
        balance: b.balance,
      };
    });
  }

  return availableFeeStructures.value.map(fs => {
    const paidAmount = paymentsArray.value
      .filter(p =>
        Number(p.student_id) === Number(form.student_id) &&
        Number(p.term_id) === props.activeTerm.id &&
        p.payment_type === fs.fee_type
      )
      .reduce((sum, p) => sum + p.amount, 0);

    return {
      ...fs,
      paid: paidAmount,
      balance: Math.max(0, fs.amount - paidAmount),
    };
  });
});

// Format fee type for display
const formatFeeType = (type: string) => {
  if (!type) return '';
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const selectedFeeCard = computed(() => {
  if (!form.payment_type) return null;
  return feeCardsWithBalance.value.find(c => c.fee_type === form.payment_type);
});

const isOverpaying = computed(() => {
  if (!selectedFeeCard.value || form.payment_type === 'feeding_fees') return false;
  const amt = parseFloat(form.amount);
  if (isNaN(amt)) return false;
  return amt > selectedFeeCard.value.balance + 0.01;
});

// Step navigation validation
const canGoToStep2 = computed(() => !!form.student_id);

const canGoToStep3 = computed(() => {
  if (!form.payment_type) return false;
  const amountNum = parseFloat(form.amount);
  if (isNaN(amountNum) || amountNum <= 0) return false;
  return !isOverpaying.value;
});

// When a fee card is selected, auto-fill the remaining balance
watch(() => form.payment_type, (newType) => {
  const card = feeCardsWithBalance.value.find(c => c.fee_type === newType);
  if (card) {
    form.amount = String(card.balance);
  }
});

// When student is selected, set term to active term and reset fee type
watch(() => form.student_id, (newId) => {
  if (newId && props.activeTerm) {
    form.term_id = props.activeTerm.id;
    form.payment_type = '';
    form.amount = '';
    studentSearch.value = '';
  }
});

// ---------- Filter & stats ----------
const selectedTermId = computed(() => {
  if (!filterTerm.value) return '';
  const term = props.terms.find(t => t.name === filterTerm.value);
  return term ? term.id : '';
});

const filteredPayments = computed(() => {
  let result = paymentsArray.value;
  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(p =>
      `${p.student.first_name} ${p.student.last_name}`.toLowerCase().includes(term) ||
      p.receipt_number?.toLowerCase().includes(term)
    );
  }
  if (filterTerm.value) result = result.filter(p => p.term.name === filterTerm.value);
  if (filterType.value) result = result.filter(p => p.payment_type === filterType.value);
  if (filterMethod.value) result = result.filter(p => p.payment_method === filterMethod.value);
  return result;
});

const totalPayments = computed(() => filteredPayments.value.length);
const totalAmount = computed(() => filteredPayments.value.reduce((sum, p) => sum + p.amount, 0));
const averagePayment = computed(() => totalPayments.value ? totalAmount.value / totalPayments.value : 0);
const uniqueStudents = computed(() => new Set(filteredPayments.value.map(p => p.student.first_name + p.student.last_name)).size);

// Badge helpers
const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    school_fees: 'bg-blue-100/80 text-blue-900 border-blue-200/30',
    feeding_fees: 'bg-green-100/80 text-green-900 border-green-200/30',
    registration_fees: 'bg-purple-100/80 text-purple-900 border-purple-200/30',
    others: 'bg-gray-100/80 text-gray-700 border-gray-200/30',
  };
  return classes[type] || classes.others;
};

const getMethodBadgeClass = (method: string) => {
  const classes: Record<string, string> = {
    cash: 'bg-emerald-100/80 text-emerald-900 border-emerald-200/30',
    momo: 'bg-orange-100/80 text-orange-900 border-orange-200/30',
    bank: 'bg-slate-100/80 text-slate-900 border-slate-200/30',
  };
  return classes[method] || classes.cash;
};

// ---------- Actions ----------
function openCreate() {
  editing.value = null;
  form.reset();
  form.payment_date = new Date().toISOString().split('T')[0];
  form.payment_method = 'cash';
  studentSearch.value = '';
  selectedClassFilter.value = '';
  modalState.value = 'recording';
  currentStep.value = 1;
  lastPayment.value = null;
  lastStudent.value = null;
  showModal.value = true;
}

function selectStudent(student: Student) {
  form.student_id = student.id;
  studentSearch.value = '';
}

function payFullBalance(card: any) {
  form.amount = String(card.balance);
}

function submit() {
  form.clearErrors();

  if (!form.student_id) {
    form.setError('student_id', 'Please select a student.');
  }
  if (!form.payment_type) {
    form.setError('payment_type', 'Please select a fee type.');
  }
  if (!form.amount || parseFloat(form.amount) <= 0) {
    form.setError('amount', 'Please enter a valid amount.');
  } else if (isOverpaying.value) {
    form.setError('amount', `Payment would exceed remaining balance of GHS ${selectedFeeCard.value?.balance}.`);
  }

  if (form.hasErrors) {
    return;
  }

  form.post('/payments', {
    onSuccess: (page: any) => {
      // Store the newly created payment and the associated student for receipt
      lastPayment.value = page.props.payment as Payment;
      // Also store the full student object (from props) for receipt
      const student = props.students.find(s => s.id === form.student_id);
      lastStudent.value = student || null;
      modalState.value = 'receipt';
      form.reset(); // reset after storing, so form is clean for next payment
    },
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this payment? This action cannot be undone.')) {
    router.delete(`/payments/${id}`);
  }
}

function printReceipt() {
  window.print();
}

function openWhatsApp() {
  if (!lastPayment.value || !lastStudent.value) return;
  const student = lastStudent.value;
  const message = `Payment of GHS ${lastPayment.value.amount} received for ${student.first_name} ${student.last_name} - ${formatFeeType(lastPayment.value.payment_type)} - ${lastPayment.value.term.name}. Receipt: ${lastPayment.value.receipt_number}. Thank you.`;
  const encodedMsg = encodeURIComponent(message);
  const phone = student.parent_phone?.replace(/\D/g, '');
  if (phone) {
    window.open(`https://wa.me/${phone}?text=${encodedMsg}`, '_blank');
  } else {
    alert('Parent phone number not available.');
  }
}

// Copy SMS Message
function copySMS() {
  if (!lastPayment.value || !lastStudent.value) return;
  const student = lastStudent.value;
  const message = `Payment of GHS ${lastPayment.value.amount} received for ${student.first_name} ${student.last_name} - ${formatFeeType(lastPayment.value.payment_type)} - ${lastPayment.value.term.name}. Receipt: ${lastPayment.value.receipt_number}. Thank you.`;
  navigator.clipboard.writeText(message);
  alert('SMS message copied to clipboard!');
}

function newPayment() {
  modalState.value = 'recording';
  form.reset();
  form.payment_date = new Date().toISOString().split('T')[0];
  form.payment_method = 'cash';
  studentSearch.value = '';
  selectedClassFilter.value = '';
  currentStep.value = 1;
  lastPayment.value = null;
  lastStudent.value = null;
}

function clearFilters() {
  search.value = '';
  filterTerm.value = '';
  filterType.value = '';
  filterMethod.value = '';
}
</script>

<template>
  <Head title="Payments" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Payments</h1>
          <p class="text-sm text-gray-600 mt-1">Track and manage all student fee payments</p>
        </div>
        <div class="flex items-center gap-2">
          <ExportDropdown
            baseUrl="/exports/payments"
            :filters="{
              term_id: selectedTermId,
              payment_type: filterType,
              payment_method: filterMethod,
              search: search
            }"
            label="Export Payments"
          />
          <button @click="openCreate" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all cursor-pointer">
            <Plus class="w-4 h-4" />
            Record Payment
          </button>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input v-model="search" type="text" placeholder="Search by student name or receipt number..." class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition" />
          </div>
          <div class="flex flex-wrap gap-3">
            <select v-model="filterTerm" class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
              <option value="">All Terms</option>
              <option v-for="t in terms" :key="t.id" :value="t.name">{{ t.name }}</option>
            </select>
            <select v-model="filterType" class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
              <option value="">All Types</option>
              <option value="school_fees">School Fees</option>
              <option value="feeding_fees">Feeding Fees</option>
              <option value="registration_fees">Registration</option>
              <option value="others">Others</option>
            </select>
            <select v-model="filterMethod" class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
              <option value="">All Methods</option>
              <option value="cash">Cash</option>
              <option value="momo">Mobile Money</option>
              <option value="bank">Bank</option>
            </select>
            <button v-if="search || filterTerm || filterType || filterMethod" @click="clearFilters" class="px-3 py-2 text-gray-600 hover:text-gray-900 transition">Clear</button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Total Payments</p>
          <p class="text-2xl font-bold">{{ totalPayments }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Total Amount</p>
          <p class="text-2xl font-bold text-green-600">{{ formatCurrencyCompact(totalAmount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Average Payment</p>
          <p class="text-2xl font-bold text-amber-600">{{ formatCurrencyCompact(averagePayment, 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Students Paid</p>
          <p class="text-2xl font-bold text-amber-700">{{ uniqueStudents }}</p>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Method</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Receipt</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="p in filteredPayments" :key="p.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-medium text-gray-900">{{ p.student.first_name }} {{ p.student.last_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ p.term.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getTypeBadgeClass(p.payment_type)]">
                    {{ formatFeeType(p.payment_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getMethodBadgeClass(p.payment_method)]">
                    {{ p.payment_method.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ formatCurrencyCompact(p.amount, 2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ p.receipt_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ new Date(p.payment_date).toLocaleDateString() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button @click="destroy(p.id)" class="text-red-600 hover:text-red-800">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredPayments.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  <AlertCircle class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                  No payments found. Try adjusting filters or record a new payment.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Teleport -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto print:hidden" @click.self="showModal = false">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-amber-100 overflow-hidden" @click.stop>
            
            <!-- RECORDING STATE -->
            <div v-if="modalState === 'recording'">
              <!-- Header -->
              <div class="flex items-center justify-between p-6 border-b border-amber-100">
                <div>
                  <h2 class="text-xl font-bold text-gray-900">Record Student Payment</h2>
                  <p class="text-xs text-gray-500 mt-0.5">Active Term: {{ activeTerm?.name }}</p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition">
                  <X class="w-5 h-5" />
                </button>
              </div>

              <!-- Stepper Progress Bar -->
              <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/50">
                <div class="flex items-center justify-between max-w-md mx-auto">
                  <!-- Step 1 -->
                  <div class="flex flex-col items-center">
                    <div :class="[
                      'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                      currentStep === 1
                        ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 ring-4 ring-amber-500/10'
                        : currentStep > 1
                          ? 'bg-lime-500 text-white'
                          : 'bg-gray-200 text-gray-500'
                    ]">
                      <Check v-if="currentStep > 1" class="w-4 h-4" />
                      <span v-else>1</span>
                    </div>
                    <span class="text-[10px] font-bold mt-1 text-gray-500">Student</span>
                  </div>

                  <!-- Connector 1 -->
                  <div class="flex-1 h-0.5 mx-2 bg-gray-200 relative">
                    <div :class="[
                      'absolute inset-0 bg-lime-500 transition-all duration-500',
                      currentStep > 1 ? 'w-full' : 'w-0'
                    ]"></div>
                  </div>

                  <!-- Step 2 -->
                  <div class="flex flex-col items-center">
                    <div :class="[
                      'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                      currentStep === 2
                        ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 ring-4 ring-amber-500/10'
                        : currentStep > 2
                          ? 'bg-lime-500 text-white'
                          : 'bg-gray-200 text-gray-500'
                    ]">
                      <Check v-if="currentStep > 2" class="w-4 h-4" />
                      <span v-else>2</span>
                    </div>
                    <span class="text-[10px] font-bold mt-1 text-gray-500">Fee & Amount</span>
                  </div>

                  <!-- Connector 2 -->
                  <div class="flex-1 h-0.5 mx-2 bg-gray-200 relative">
                    <div :class="[
                      'absolute inset-0 bg-lime-500 transition-all duration-500',
                      currentStep > 2 ? 'w-full' : 'w-0'
                    ]"></div>
                  </div>

                  <!-- Step 3 -->
                  <div class="flex flex-col items-center">
                    <div :class="[
                      'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                      currentStep === 3
                        ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 ring-4 ring-amber-500/10'
                        : 'bg-gray-200 text-gray-500'
                    ]">
                      <span>3</span>
                    </div>
                    <span class="text-[10px] font-bold mt-1 text-gray-500">Method & Review</span>
                  </div>
                </div>
              </div>

              <form @submit.prevent="submit" class="p-6 space-y-6">
                <!-- ==================== STEP 1: STUDENT SELECTION ==================== -->
                <div v-if="currentStep === 1" class="space-y-4">
                  <!-- Filter by Class -->
                  <div>
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Filter by Class (Optional)</label>
                    <select
                      v-model="selectedClassFilter"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-sm"
                    >
                      <option value="">All Classes</option>
                      <option v-for="cls in uniqueClasses" :key="cls.id" :value="String(cls.id)">
                        {{ cls.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Student Input / Search -->
                  <div>
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Student Search *</label>
                    <div v-if="!form.student_id" class="relative">
                      <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                          v-model="studentSearch"
                          type="text"
                          placeholder="Type student name or ID to search..."
                          class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition"
                          @keydown.escape="studentSearch = ''"
                        />
                      </div>
                      <div v-if="filteredStudents.length > 0" class="absolute top-full left-0 right-0 mt-1 border border-amber-100 rounded-xl bg-white shadow-xl z-20 max-h-60 overflow-y-auto">
                        <button
                          v-for="s in filteredStudents"
                          :key="s.id"
                          type="button"
                          @click="selectStudent(s)"
                          class="w-full text-left px-4 py-3 hover:bg-amber-50/50 border-b border-amber-50 last:border-b-0 transition flex items-center justify-between gap-3"
                        >
                          <div class="flex-1 min-w-0">
                            <div class="font-bold text-gray-900 truncate text-sm">
                              {{ s.first_name }} {{ s.last_name }}
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                              <span class="font-mono text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100">{{ s.student_id }}</span>
                              <span>•</span>
                              <span class="font-medium text-gray-600">{{ s.class?.name }}</span>
                            </div>
                          </div>
                          <div class="text-right text-[11px] font-bold text-amber-800 bg-amber-100/60 px-2.5 py-1 rounded-full whitespace-nowrap">
                            {{ s.class?.name }}
                          </div>
                        </button>
                      </div>
                    </div>

                    <!-- Selected Student Card -->
                    <div v-else class="bg-amber-50/60 p-5 rounded-xl border border-amber-200/60 shadow-sm flex items-start justify-between gap-4">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg border border-amber-200">
                          {{ selectedStudent?.first_name.charAt(0) }}{{ selectedStudent?.last_name.charAt(0) }}
                        </div>
                        <div>
                          <div class="font-bold text-gray-900 text-base">{{ selectedStudent?.first_name }} {{ selectedStudent?.last_name }}</div>
                          <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-amber-800 bg-amber-100/50 px-1.5 py-0.5 rounded">{{ selectedStudent?.student_id }}</span>
                            <span>•</span>
                            <span class="font-semibold">{{ selectedStudent?.class?.name }}</span>
                          </div>
                        </div>
                      </div>
                      <button type="button" @click="form.student_id = ''" class="text-gray-400 hover:text-red-500 transition p-1 rounded-lg hover:bg-white shadow-sm border border-transparent hover:border-gray-100">
                        <X class="w-5 h-5" />
                      </button>
                    </div>
                    <p v-if="form.errors.student_id" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.student_id }}</p>
                  </div>

                  <!-- Financial Summary for Student in Step 1 -->
                  <div v-if="form.student_id && selectedStudent?.balances" class="bg-white border border-amber-100 rounded-xl p-4 shadow-sm">
                    <h3 class="text-xs uppercase font-bold text-amber-800 tracking-wider mb-3">Overall Term Balance Summary</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                      <div class="p-3 bg-gray-50 rounded-lg">
                        <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider block">Expected</span>
                        <span class="text-sm font-bold text-gray-900 mt-1 block">{{ formatCurrencyCompact(selectedStudent.balances.expected) }}</span>
                      </div>
                      <div class="p-3 bg-green-50/50 rounded-lg">
                        <span class="text-[10px] text-green-700 font-bold uppercase tracking-wider block">Total Paid</span>
                        <span class="text-sm font-bold text-green-600 mt-1 block">{{ formatCurrencyCompact(selectedStudent.balances.paid) }}</span>
                      </div>
                      <div class="p-3 bg-amber-50/50 rounded-lg">
                        <span class="text-[10px] text-amber-800 font-bold uppercase tracking-wider block">Outstanding</span>
                        <span :class="['text-sm font-bold mt-1 block', selectedStudent.balances.balance > 0 ? 'text-red-600' : 'text-green-600']">
                          {{ formatCurrencyCompact(selectedStudent.balances.balance) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ==================== STEP 2: FEE TYPE & AMOUNT ==================== -->
                <div v-else-if="currentStep === 2" class="space-y-6 animate-fade-in">
                  <!-- Selected Student Mini Header -->
                  <div class="flex items-center justify-between bg-amber-50/30 p-3 rounded-lg border border-amber-100/50 text-xs">
                    <div class="flex items-center gap-2">
                      <UserCheck class="w-4 h-4 text-amber-600" />
                      <span class="font-bold text-gray-900">{{ selectedStudent?.first_name }} {{ selectedStudent?.last_name }}</span>
                      <span class="text-gray-400">|</span>
                      <span class="font-medium text-gray-600">{{ selectedStudent?.class?.name }}</span>
                    </div>
                    <span class="font-mono font-bold text-amber-800 bg-amber-100/50 px-2 py-0.5 rounded">{{ selectedStudent?.student_id }}</span>
                  </div>

                  <!-- Fee Type Selection Grid -->
                  <div v-if="feeCardsWithBalance.length">
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-3">Select Fee Type *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <button
                        v-for="card in feeCardsWithBalance"
                        :key="card.id"
                        type="button"
                        @click="form.payment_type = card.fee_type"
                        :class="[
                          'text-left p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden group shadow-sm',
                          form.payment_type === card.fee_type
                            ? 'border-amber-500 bg-amber-50/60 ring-2 ring-amber-500/10'
                            : 'border-amber-100/60 bg-white hover:border-amber-300'
                        ]"
                      >
                        <div class="font-bold flex items-center justify-between text-sm">
                          <span class="text-gray-900">{{ formatFeeType(card.fee_type) }}</span>
                          <span v-if="card.fee_type === 'feeding_fees'" class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md font-bold">Flexible</span>
                        </div>
                        
                        <div v-if="card.fee_type !== 'feeding_fees'" class="grid grid-cols-3 gap-2 mt-3 text-xs border-t border-dashed border-gray-100 pt-2">
                          <div>
                            <span class="text-gray-500 block">Expected</span>
                            <div class="font-bold text-gray-900 mt-0.5">{{ formatCurrencyCompact(card.amount, 0) }}</div>
                          </div>
                          <div>
                            <span class="text-gray-500 block">Paid</span>
                            <div class="font-bold text-green-600 mt-0.5">{{ formatCurrencyCompact(card.paid, 0) }}</div>
                          </div>
                          <div>
                            <span class="text-gray-500 block">Outstanding</span>
                            <div :class="['font-bold mt-0.5', card.balance > 0 ? 'text-red-600' : 'text-green-600']">
                              {{ formatCurrencyCompact(card.balance, 0) }}
                            </div>
                          </div>
                        </div>

                        <!-- Feeding Fee Suggestion -->
                        <div v-else class="mt-2 text-xs space-y-1 border-t border-dashed border-gray-100 pt-2">
                          <div class="flex items-center justify-between text-gray-600">
                            <span>Daily Rate:</span>
                            <span class="font-bold text-gray-900">{{ formatCurrencyCompact(card.amount, 0) }}</span>
                          </div>
                          <div class="flex items-center justify-between text-gray-500 text-[10px]">
                            <span>Weekly (5 days):</span>
                            <span class="font-semibold">{{ formatCurrencyCompact(card.amount * 5, 0) }}</span>
                          </div>
                        </div>

                        <div v-if="card.balance > 0 && card.fee_type !== 'feeding_fees'" class="absolute right-0 bottom-0 scale-75 origin-bottom-right">
                          <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-l-md text-[10px] font-bold">Outstanding</span>
                        </div>
                      </button>
                    </div>
                    <p v-if="form.errors.payment_type" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.payment_type }}</p>
                  </div>

                  <!-- Amount Input Field -->
                  <div v-if="form.payment_type" class="space-y-2">
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider">Amount to Pay (GHS) *</label>
                    <div class="flex gap-2 relative rounded-xl shadow-sm">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold text-sm">GHS</span>
                      </div>
                      <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        required
                        class="w-full pl-12 pr-32 py-3 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-lg font-bold text-gray-900"
                      />
                      <button
                        v-if="form.payment_type !== 'feeding_fees' && selectedFeeCard"
                        type="button"
                        @click="payFullBalance(selectedFeeCard)"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-bold rounded-lg transition"
                      >
                        Full Balance
                      </button>
                    </div>
                    <p v-if="form.payment_type === 'feeding_fees'" class="text-xs text-gray-500 mt-1">
                      💡 Override with actual payment amount as needed.
                    </p>
                    <p v-if="isOverpaying" class="text-amber-700 text-xs mt-1.5 flex items-center gap-1.5 font-semibold bg-amber-50 border border-amber-200 p-2.5 rounded-xl">
                      <AlertCircle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                      Warning: Amount exceeds remaining balance of {{ formatCurrencyCompact(selectedFeeCard?.balance || 0, 2) }} for this fee.
                    </p>
                    <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.amount }}</p>
                  </div>
                </div>

                <!-- ==================== STEP 3: METHOD, DATE & REVIEW ==================== -->
                <div v-else-if="currentStep === 3" class="space-y-6">
                  <!-- Selected Student + Fee Mini Header -->
                  <div class="bg-amber-50/30 p-3 rounded-lg border border-amber-100/50 text-xs space-y-1">
                    <div class="flex items-center justify-between font-bold text-gray-900">
                      <span>{{ selectedStudent?.first_name }} {{ selectedStudent?.last_name }}</span>
                      <span class="font-mono text-amber-800">{{ selectedStudent?.student_id }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600 font-medium">
                      <span>Fee Type: {{ formatFeeType(form.payment_type) }}</span>
                      <span>Amount: GHS {{ parseFloat(form.amount || 0).toFixed(2) }}</span>
                    </div>
                  </div>

                  <!-- Payment Method Selection -->
                  <div class="space-y-3">
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider">Payment Method *</label>
                    <div class="grid grid-cols-3 gap-3">
                      <!-- Cash -->
                      <button
                        type="button"
                        @click="form.payment_method = 'cash'"
                        :class="[
                          'py-3 px-4 rounded-xl border-2 font-bold transition flex flex-col items-center justify-center gap-2 text-xs',
                          form.payment_method === 'cash'
                            ? 'border-lime-500 bg-lime-50/50 text-lime-800'
                            : 'border-amber-100/60 bg-white hover:border-amber-200 text-gray-600'
                        ]"
                      >
                        <Coins class="w-5 h-5 text-emerald-500" />
                        Cash
                      </button>
                      <!-- Momo -->
                      <button
                        type="button"
                        @click="form.payment_method = 'momo'"
                        :class="[
                          'py-3 px-4 rounded-xl border-2 font-bold transition flex flex-col items-center justify-center gap-2 text-xs',
                          form.payment_method === 'momo'
                            ? 'border-lime-500 bg-lime-50/50 text-lime-800'
                            : 'border-amber-100/60 bg-white hover:border-amber-200 text-gray-600'
                        ]"
                      >
                        <Smartphone class="w-5 h-5 text-orange-500" />
                        Mobile Money
                      </button>
                      <!-- Bank -->
                      <button
                        type="button"
                        @click="form.payment_method = 'bank'"
                        :class="[
                          'py-3 px-4 rounded-xl border-2 font-bold transition flex flex-col items-center justify-center gap-2 text-xs',
                          form.payment_method === 'bank'
                            ? 'border-lime-500 bg-lime-50/50 text-lime-800'
                            : 'border-amber-100/60 bg-white hover:border-amber-200 text-gray-600'
                        ]"
                      >
                        <Landmark class="w-5 h-5 text-slate-600" />
                        Bank Transfer
                      </button>
                    </div>
                  </div>

                  <!-- Payment Date Selector -->
                  <div class="space-y-2">
                    <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider">Payment Date *</label>
                    <div class="relative rounded-xl shadow-sm">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Calendar class="w-4 h-4 text-gray-400" />
                      </div>
                      <input
                        v-model="form.payment_date"
                        type="date"
                        required
                        class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm text-gray-900"
                      />
                    </div>
                  </div>

                  <!-- Checkout Review Box -->
                  <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3 shadow-inner">
                    <h4 class="text-xs uppercase font-extrabold text-gray-600 tracking-wider border-b border-gray-200 pb-2">Review Payment Information</h4>
                    <dl class="grid grid-cols-2 gap-y-2 text-xs">
                      <dt class="text-gray-500 font-semibold">Student Name:</dt>
                      <dd class="text-gray-900 font-bold text-right">{{ selectedStudent?.first_name }} {{ selectedStudent?.last_name }}</dd>

                      <dt class="text-gray-500 font-semibold">Fee Type Category:</dt>
                      <dd class="text-gray-900 font-bold text-right">{{ formatFeeType(form.payment_type) }}</dd>

                      <dt class="text-gray-500 font-semibold">Academic Period:</dt>
                      <dd class="text-gray-900 font-bold text-right">{{ activeTerm?.name }}</dd>

                      <dt class="text-gray-500 font-semibold">Payment Method:</dt>
                      <dd class="text-gray-900 font-bold text-right capitalize">{{ form.payment_method }}</dd>

                      <dt class="text-gray-500 font-semibold">Date of Record:</dt>
                      <dd class="text-gray-900 font-bold text-right">{{ form.payment_date }}</dd>

                      <dt class="text-amber-800 font-bold text-sm border-t border-dashed border-gray-200 pt-2 mt-1">Amount to Record:</dt>
                      <dd class="text-lime-700 font-extrabold text-right text-base border-t border-dashed border-gray-200 pt-2 mt-1">
                        GHS {{ parseFloat(form.amount || 0).toFixed(2) }}
                      </dd>
                    </dl>
                  </div>
                </div>

                <!-- Navigation Controls -->
                <div class="flex gap-3 pt-4 border-t border-amber-100">
                  <button
                    v-if="currentStep > 1"
                    type="button"
                    @click="currentStep--"
                    class="flex-1 px-4 py-2.5 border border-amber-200 hover:bg-amber-50/30 text-gray-700 font-bold rounded-xl transition text-sm flex items-center justify-center gap-2"
                  >
                    <ArrowLeft class="w-4 h-4" />
                    Back
                  </button>
                  <button
                    v-else
                    type="button"
                    @click="showModal = false"
                    class="flex-1 px-4 py-2.5 border border-amber-200 hover:bg-amber-50/30 text-gray-700 font-bold rounded-xl transition text-sm flex items-center justify-center"
                  >
                    Cancel
                  </button>

                  <button
                    v-if="currentStep < 3"
                    type="button"
                    @click="currentStep++"
                    :disabled="(currentStep === 1 && !canGoToStep2) || (currentStep === 2 && !canGoToStep3)"
                    class="flex-1 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold disabled:opacity-50 disabled:cursor-not-allowed transition text-sm flex items-center justify-center gap-2"
                  >
                    Next
                    <ArrowRight class="w-4 h-4" />
                  </button>
                  <button
                    v-else
                    type="submit"
                    :disabled="form.processing || isOverpaying"
                    class="flex-1 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold disabled:opacity-50 transition text-sm flex items-center justify-center gap-2"
                  >
                    <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
                    <Check class="w-4 h-4" v-else />
                    Record Payment
                  </button>
                </div>
              </form>
            </div>

            <!-- RECEIPT STATE -->
            <div v-else-if="modalState === 'receipt'">
              <!-- Receipt Action Bar -->
              <div class="flex flex-wrap items-center justify-between p-6 border-b border-amber-100 bg-amber-50/30 gap-3">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                  <Check class="w-5 h-5 text-lime-600" />
                  Payment Recorded Successfully
                </h2>
                <div class="flex items-center gap-2">
                  <button @click="printReceipt" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <Printer class="w-3.5 h-3.5" />
                    Print A4
                  </button>
                  <button @click="openWhatsApp" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <Share2 class="w-3.5 h-3.5" />
                    WhatsApp
                  </button>
                  <button @click="copySMS" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition">
                    Copy SMS
                  </button>
                </div>
              </div>

              <!-- Screen Preview of the A4 Receipt -->
              <div class="p-6 max-h-[60vh] overflow-y-auto bg-gray-50 border-b border-amber-100">
                <div class="bg-white border border-gray-200 rounded-xl shadow-md p-6 max-w-lg mx-auto font-sans text-xs text-gray-800 space-y-6">
                  
                  <!-- Preview Header -->
                  <div class="flex justify-between items-center border-b border-amber-500 pb-4 gap-4">
                    <div class="flex items-center gap-3">
                      <div v-if="schoolLogoUrl" class="w-10 h-10 rounded-lg overflow-hidden bg-gray-50 border border-amber-100 flex-shrink-0">
                        <img :src="schoolLogoUrl" alt="School logo" class="w-full h-full object-cover" />
                      </div>
                      <div>
                        <h4 class="font-bold text-gray-900 text-xs tracking-wide uppercase">{{ props.school?.name || 'DEVSUITE INTERNATIONAL SCHOOL' }}</h4>
                        <p class="text-[9px] text-gray-500 mt-0.5">{{ props.school?.phone || '+233 (0) 24 123 4567' }}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block text-[9px]">Receipt Preview</span>
                      <span class="font-mono font-bold text-gray-800 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded text-[10px]">
                        {{ lastPayment?.receipt_number }}
                      </span>
                    </div>
                  </div>

                  <!-- Preview Metadata -->
                  <div class="grid grid-cols-2 gap-4 bg-amber-50/20 p-3 rounded-lg text-[10px]">
                    <div>
                      <p class="text-amber-800 font-bold uppercase tracking-wider text-[9px] mb-1">Student Details</p>
                      <p class="text-gray-900 font-semibold">{{ lastStudent?.first_name }} {{ lastStudent?.last_name }}</p>
                      <p class="text-gray-500 font-mono mt-0.5">ID: {{ lastStudent?.student_id }}</p>
                      <p class="text-gray-900 mt-0.5">Class: {{ lastStudent?.class?.name }}</p>
                    </div>
                    <div>
                      <p class="text-amber-800 font-bold uppercase tracking-wider text-[9px] mb-1">Payment Info</p>
                      <p class="text-gray-900"><span class="text-gray-500">Date:</span> {{ lastPayment?.payment_date }}</p>
                      <p class="text-gray-900 mt-0.5 capitalize"><span class="text-gray-500">Method:</span> {{ lastPayment?.payment_method }}</p>
                      <p class="text-gray-900 mt-0.5"><span class="text-gray-500">Period:</span> {{ lastPayment?.term?.name }}</p>
                    </div>
                  </div>

                  <!-- Preview Details Table -->
                  <table class="w-full text-left text-[11px]">
                    <thead>
                      <tr class="bg-amber-600 text-white">
                        <th class="py-1.5 px-2 font-semibold rounded-l">Description</th>
                        <th class="py-1.5 px-2 text-right font-semibold rounded-r">Amount</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                      <tr>
                        <td class="py-2 px-2">
                          <span class="font-semibold text-gray-900">{{ formatFeeType(lastPayment?.payment_type || '') }}</span>
                          <span class="text-[9px] text-gray-500 block">Term: {{ lastPayment?.term?.name }}</span>
                        </td>
                        <td class="py-2 px-2 text-right font-bold text-gray-900">
                          GHS {{ parseFloat(lastPayment?.amount || 0).toFixed(2) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- Preview Balances -->
                  <div class="flex justify-end pt-2">
                    <div class="w-48 space-y-1.5 border-t border-gray-100 pt-2 text-[10px]">
                      <div class="flex justify-between">
                        <span class="text-gray-500">Expected:</span>
                        <span class="text-gray-900 font-medium">GHS {{ parseFloat(lastStudent?.balances?.expected || 0).toFixed(2) }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-500">Total Paid:</span>
                        <span class="text-gray-900 font-medium">GHS {{ parseFloat(lastStudent?.balances?.paid || 0).toFixed(2) }}</span>
                      </div>
                      <div class="flex justify-between bg-amber-50/50 p-1.5 rounded font-bold">
                        <span class="text-amber-800">Remaining:</span>
                        <span :class="[ (lastStudent?.balances?.balance || 0) > 0 ? 'text-red-600' : 'text-green-600' ]">
                          GHS {{ parseFloat(lastStudent?.balances?.balance || 0).toFixed(2) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Footer Actions -->
              <div class="flex items-center justify-end p-6 border-t border-amber-100 bg-amber-50/10 gap-3">
                <button @click="newPayment" class="px-4 py-2 border border-amber-100 hover:bg-amber-50/50 text-gray-700 font-semibold rounded-xl transition text-sm">
                  Record Another
                </button>
                <button @click="showModal = false" class="px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl shadow-sm transition text-sm">
                  Close Window
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>

  <!-- Print-only A4 Receipt Container -->
  <div class="hidden print:block receipt-print-container">
    <div class="w-full bg-white font-sans text-gray-800">
      <!-- School Letterhead Header -->
      <div class="flex justify-between items-center border-b-2 border-amber-500 pb-6 mb-6 gap-4">
        <div class="flex items-center gap-4">
          <div v-if="schoolLogoUrl" class="w-16 h-16 rounded-xl overflow-hidden bg-gray-50 border border-amber-200 flex-shrink-0">
            <img :src="schoolLogoUrl" alt="School logo" class="w-full h-full object-cover" />
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-wide uppercase">{{ props.school?.name || 'DEVSUITE INTERNATIONAL SCHOOL' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ props.school?.address || 'P.O. Box GP 1234, Accra, Ghana' }}</p>
            <p class="text-sm text-gray-500">
              <span v-if="props.school?.phone">Tel: {{ props.school.phone }}</span>
              <span v-if="props.school?.phone && props.school?.email"> | </span>
              <span v-if="props.school?.email">Email: {{ props.school.email }}</span>
            </p>
          </div>
        </div>
        <div class="text-right">
          <div class="text-3xl font-extrabold text-amber-600 tracking-tight">OFFICIAL RECEIPT</div>
          <div class="text-sm font-mono mt-2 bg-amber-50 px-3 py-1 rounded-lg border border-amber-100 inline-block text-amber-800 font-semibold">
            {{ lastPayment?.receipt_number }}
          </div>
        </div>
      </div>

      <!-- Receipt Metadata Grid -->
      <div class="grid grid-cols-2 gap-8 mb-8 bg-amber-50/30 p-4 rounded-xl border border-amber-100/50">
        <div>
          <h3 class="text-xs uppercase font-bold text-amber-700 tracking-wider mb-2">Student Information</h3>
          <table class="w-full text-sm">
            <tr>
              <td class="py-1 text-gray-500 font-medium w-24">Name:</td>
              <td class="py-1 text-gray-900 font-semibold">{{ lastStudent?.first_name }} {{ lastStudent?.last_name }}</td>
            </tr>
            <tr>
              <td class="py-1 text-gray-500 font-medium">Student ID:</td>
              <td class="py-1 text-gray-900 font-mono">{{ lastStudent?.student_id }}</td>
            </tr>
            <tr>
              <td class="py-1 text-gray-500 font-medium">Class:</td>
              <td class="py-1 text-gray-900 font-semibold">{{ lastStudent?.class?.name }}</td>
            </tr>
          </table>
        </div>
        <div>
          <h3 class="text-xs uppercase font-bold text-amber-700 tracking-wider mb-2">Payment Details</h3>
          <table class="w-full text-sm">
            <tr>
              <td class="py-1 text-gray-500 font-medium w-32">Date of Issue:</td>
              <td class="py-1 text-gray-900 font-semibold">{{ lastPayment?.payment_date }}</td>
            </tr>
            <tr>
              <td class="py-1 text-gray-500 font-medium">Payment Method:</td>
              <td class="py-1 text-gray-900 font-semibold capitalize">{{ lastPayment?.payment_method }}</td>
            </tr>
            <tr>
              <td class="py-1 text-gray-500 font-medium">Academic Period:</td>
              <td class="py-1 text-gray-900 font-semibold">{{ lastPayment?.term?.name }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Transaction Table -->
      <table class="w-full border-collapse mb-8">
        <thead>
          <tr class="bg-amber-600 text-white text-sm">
            <th class="py-3 px-4 text-left font-bold rounded-l-lg">No.</th>
            <th class="py-3 px-4 text-left font-bold">Item Description</th>
            <th class="py-3 px-4 text-right font-bold rounded-r-lg">Amount Paid</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr class="text-sm text-gray-700">
            <td class="py-4 px-4">1</td>
            <td class="py-4 px-4 font-medium">
              {{ formatFeeType(lastPayment?.payment_type || '') }} Payment
              <span class="text-xs text-gray-500 block mt-0.5 font-normal">Academic Term: {{ lastPayment?.term?.name }}</span>
            </td>
            <td class="py-4 px-4 text-right font-bold text-gray-900">
              GHS {{ parseFloat(lastPayment?.amount || 0).toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Balances Summary Column -->
      <div class="flex justify-end mb-12">
        <div class="w-80">
          <div class="flex justify-between py-2 border-b border-gray-200 text-sm">
            <span class="text-gray-500 font-medium">Expected for Term:</span>
            <span class="text-gray-900 font-semibold">GHS {{ parseFloat(lastStudent?.balances?.expected || 0).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-gray-200 text-sm">
            <span class="text-gray-500 font-medium">Total Paid to Date:</span>
            <span class="text-gray-900 font-semibold">GHS {{ parseFloat(lastStudent?.balances?.paid || 0).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between py-3 bg-amber-50/50 px-3 rounded-lg mt-2 text-base font-bold">
            <span class="text-amber-800">Remaining Balance:</span>
            <span :class="[ (lastStudent?.balances?.balance || 0) > 0 ? 'text-red-600' : 'text-green-600' ]">
              GHS {{ parseFloat(lastStudent?.balances?.balance || 0).toFixed(2) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Signature Area -->
      <div class="grid grid-cols-2 gap-12 pt-8 border-t border-dashed border-gray-300">
        <div>
          <p class="text-sm text-gray-600 italic">Thank you for your payment!</p>
          <p class="text-xs text-gray-400 mt-2">This is an official computer-generated receipt.</p>
        </div>
        <div class="text-right">
          <div class="inline-block border-t border-gray-400 pt-2 px-12 text-center">
            <p class="text-sm font-semibold text-gray-900">School Representative</p>
            <p class="text-xs text-gray-500 mt-1">Signature & Stamp</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  /* Hide screen-only dashboard nodes */
  body > * {
    display: none !important;
  }
  
  /* Teleport container has fixed classes that we must override */
  .fixed.inset-0 {
    display: none !important;
  }

  /* Show only the A4 printable receipt */
  .receipt-print-container {
    display: block !important;
    position: absolute;
    left: 0;
    top: 0;
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: 0;
    background: white !important;
    color: black !important;
    box-sizing: border-box;
  }

  /* Color accuracy inside prints */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background-color: transparent !important;
  }

  /* Exclude backgrounds on specific printable elements if needed */
  .bg-amber-50\/30 {
    background-color: #fffbeb !important;
  }

  .bg-amber-600 {
    background-color: #d97706 !important;
    color: white !important;
  }

  .text-white {
    color: white !important;
  }
}
</style>