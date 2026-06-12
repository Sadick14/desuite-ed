<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  Layers,
  AlertCircle,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { formatCurrencyCompact } from '@/utils/format';

type Fee = {
  id: number;
  fee_type: string;
  amount: number;
  level: string;
  term: { id: number; name: string };
};

const LEVEL_OPTIONS = [
  { value: 'nursery', label: 'Nursery' },
  { value: 'kindergarten', label: 'Kindergarten' },
  { value: 'lower_primary', label: 'Lower Primary' },
  { value: 'upper_primary', label: 'Upper Primary' },
  { value: 'jhs', label: 'JHS' },
];

const getLevelLabel = (value: string) => {
  return LEVEL_OPTIONS.find(opt => opt.value === value)?.label || value;
};

const props = defineProps<{
  fees: Fee[];
  terms: any[];
}>();

const showModal = ref(false);
const editing = ref<Fee | null>(null);
const search = ref('');
const filterLevel = ref('');
const filterTerm = ref('');

const form = useForm({
  levels: [],
  level: '',
  term_id: '',
  fee_type: '',
  fee_name: '',
  amount: '',
  daily_rate: ''
});

const isFeedingFee = computed(() => form.fee_type === 'feeding_fees');

// Filtered fees
const filteredFees = computed(() => {
  let result = props.fees;

  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(fee =>
      fee.fee_type.toLowerCase().includes(term) ||
      fee.level.toLowerCase().includes(term) ||
      fee.term.name.toLowerCase().includes(term)
    );
  }

  if (filterLevel.value) {
    result = result.filter(fee => fee.level === filterLevel.value);
  }

  if (filterTerm.value) {
    result = result.filter(fee => fee.term.id === parseInt(filterTerm.value));
  }

  return result;
});

// Fee type badge colors
const getFeeTypeColor = (type: string) => {
  const colors: Record<string, string> = {
    school_fees: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    feeding_fees: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    registration_fees: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    others: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  };

  return colors[type] || colors.others;
};

// Format fee type for display
const formatFeeType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

function openCreate() {
  editing.value = null;
  form.reset();
  form.levels = [];
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  form.levels = [];
  editing.value = null;
}

function openEdit(fee: Fee) {
  editing.value = fee;
  form.reset();
  form.level = fee.level;
  form.levels = [];
  form.term_id = String(fee.term.id);
  form.fee_type = fee.fee_type;
  form.fee_name = (fee as any).fee_name || '';
  form.amount = fee.fee_type === 'feeding_fees' ? '' : String(fee.amount);
  form.daily_rate = (fee as any).daily_rate ? String((fee as any).daily_rate) : '';
  showModal.value = true;
}

function submit() {
  if (editing.value) {
    // Editing: require single level
    if (!form.level) {
      alert('Please select a level');

      return;
    }
  } else {
    // Creating: require multiple levels
    if (!form.levels || form.levels.length === 0) {
      alert('Please select at least one level');

      return;
    }
  }

  if (!form.term_id) {
    alert('Please select a term');

    return;
  }

  if (!form.fee_type) {
    alert('Please select a fee type');

    return;
  }

  if (isFeedingFee.value && !form.daily_rate) {
    alert('Please enter the daily feeding fee rate');

    return;
  }

  if (!isFeedingFee.value && !form.amount) {
    alert('Please enter an amount');

    return;
  }

  const url = editing.value ? `/fee-structures/${editing.value.id}` : '/fee-structures';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
    onError: (errors: any) => {
      console.error('Validation errors:', errors);
    },
  });
}

function destroy(id: number) {
  if (confirm('Delete this fee structure? This action cannot be undone.')) {
    router.delete(`/fee-structures/${id}`, {
      onSuccess: () => {
        closeModal();
      },
      onError: (error: any) => {
        console.error('Delete failed:', error);
        alert('Failed to delete fee structure. Please try again.');
      },
    });
  }
}

function clearFilters() {
  search.value = '';
  filterLevel.value = '';
  filterTerm.value = '';
}
</script>

<template>
  <Head title="Fee Structures" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Fee Structures</h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage school fees by level, term, and fee type
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Fee Structure
        </button>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by fee type, level, or term..."
              class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
            />
          </div>

          <select
            v-model="filterLevel"
            class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
          >
            <option value="">All Levels</option>
            <option v-for="opt in LEVEL_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>

          <select
            v-model="filterTerm"
            class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
          >
            <option value="">All Terms</option>
            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>

          <button
            v-if="search || filterLevel || filterTerm"
            @click="clearFilters"
            class="px-3 py-2 text-gray-600 hover:text-gray-900 transition"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Total Fees</p>
          <p class="text-2xl font-bold text-gray-900">{{ filteredFees.length }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Unique Levels</p>
          <p class="text-2xl font-bold text-gray-900">{{ new Set(filteredFees.map(f => f.level)).size }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Active Terms</p>
          <p class="text-2xl font-bold text-gray-900">{{ new Set(filteredFees.map(f => f.term.id)).size }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
          <p class="text-xs text-gray-500 uppercase">Total Revenue</p>
          <p class="text-2xl font-bold text-gray-900">{{ formatCurrencyCompact(filteredFees.reduce((sum, f) => sum + Number(f.amount), 0)) }}</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Level</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Fee Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount (GHS)</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="fee in filteredFees" :key="fee.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <Layers class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-medium text-gray-900">{{ getLevelLabel(fee.level) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ fee.term.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border w-fit', getFeeTypeColor(fee.fee_type)]">
                      {{ formatFeeType(fee.fee_type) }}
                    </span>
                    <span v-if="(fee as any).fee_name" class="text-xs text-gray-500 mt-1">{{ (fee as any).fee_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  <span v-if="fee.fee_type === 'feeding_fees'">
                    ₵{{ (Number((fee as any).daily_rate) || 0).toFixed(2) }}/day
                  </span>
                  <span v-else>
                    {{ formatCurrencyCompact(fee.amount, 2) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <button
                    @click="openEdit(fee)"
                    class="text-green-600 hover:text-green-800 transition"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button
                    @click="destroy(fee.id)"
                    class="text-red-600 hover:text-red-800 transition"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredFees.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  <AlertCircle class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                  No fee structures found. Try adjusting your filters or add a new fee.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

        <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-md w-full mx-4 border border-amber-100">
          <div class="flex items-center justify-between p-6 border-b border-amber-100/60">
            <h2 class="text-xl font-semibold text-gray-900">{{ editing ? 'Edit Fee Structure' : 'Create Fee Structure' }}</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-4">
            <!-- EDITING: Single Level Select -->
            <div v-if="editing">
              <label class="block text-sm font-medium text-gray-700 mb-1">Level *</label>
              <select
                v-model="form.level"
                disabled
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-600 cursor-not-allowed"
              >
                <option value="">Select Level</option>
                <option v-for="opt in LEVEL_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Cannot change level when editing</p>
            </div>

            <!-- CREATING: Multi-Select Levels -->
            <div v-else>
              <label class="block text-sm font-medium text-gray-700 mb-2">Levels (Select one or more) *</label>
              <div class="grid grid-cols-2 gap-2">
                <label v-for="opt in LEVEL_OPTIONS" :key="opt.value" class="flex items-center gap-2 p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-amber-50">
                  <input
                    type="checkbox"
                    :value="opt.value"
                    v-model="form.levels"
                    class="w-4 h-4 text-amber-500 rounded focus:ring-2 focus:ring-amber-500"
                  />
                  <span class="text-sm text-gray-700">{{ opt.label }}</span>
                </label>
              </div>
              <p v-if="form.levels.length > 0" class="text-xs text-amber-600 mt-2">
                Selected: {{ form.levels.map(l => LEVEL_OPTIONS.find(o => o.value === l)?.label).join(', ') }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Term *</label>
              <select
                v-model="form.term_id"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              >
                <option value="">Select Term</option>
                <option v-for="t in terms" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fee Type *</label>
              <select
                v-model="form.fee_type"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              >
                <option value="">Select Fee Type</option>
                <option value="school_fees">School Fees</option>
                <option value="feeding_fees">Feeding Fees</option>
                <option value="registration_fees">Registration Fees</option>
                <option value="others">Others</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Custom Fee Name (Optional)</label>
              <input
                v-model="form.fee_name"
                type="text"
                placeholder="e.g., 'Building Fund', 'PTA Dues'"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              />
              <p class="text-xs text-gray-500 mt-1">Leave blank to use the default fee type name</p>
            </div>

            <!-- Regular fee amount -->
            <div v-if="!isFeedingFee">
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHS) *</label>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              />
            </div>

            <!-- Feeding fee daily rate -->
            <div v-else>
              <label class="block text-sm font-medium text-gray-700 mb-1">Daily Rate (GHS/day) *</label>
              <input
                v-model="form.daily_rate"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              />
              <p class="text-xs text-gray-500 mt-1">Daily rate × days attended = weekly feeding fee (Mon-Fri)</p>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="flex-1 px-4 py-2 border border-amber-100 rounded-lg text-gray-700 hover:bg-amber-50/30 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="flex-1 px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold transition disabled:opacity-50"
              >
                {{ form.processing ? 'Saving...' : (editing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>