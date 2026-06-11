<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  DollarSign,
  Calendar,
  Folder,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import ExportDropdown from '@/components/ExportDropdown.vue';
import { formatCurrencyCompact } from '@/utils/format';

type Expense = {
  id: number;
  title: string;
  description: string | null;
  amount: number;
  expense_date: string;
  payment_method: 'cash' | 'momo' | 'bank';
  category: {
    id: number;
    name: string;
  } | null;
  created_at: string;
};

const props = defineProps<{
  expenses: Expense[];
  categories: Array<{ id: number; name: string }>;
}>();

const showModal = ref(false);
const editing = ref<Expense | null>(null);
const search = ref('');
const filterCategory = ref('');

const form = useForm({
  expense_category_id: '',
  title: '',
  description: '',
  amount: '',
  expense_date: '',
  payment_method: 'cash',
});

const filteredExpenses = computed(() => {
  let result = props.expenses;

  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(e =>
      e.title.toLowerCase().includes(term) ||
      e.description?.toLowerCase().includes(term) ||
      e.category?.name?.toLowerCase().includes(term)
    );
  }

  if (filterCategory.value) {
    result = result.filter(e => e.category && e.category.id === parseInt(filterCategory.value));
  }

  return result;
});

const stats = computed(() => ({
  total: filteredExpenses.value.length,
  amount: filteredExpenses.value.reduce((sum, e) => sum + e.amount, 0),
  avgAmount: filteredExpenses.value.length > 0
    ? (filteredExpenses.value.reduce((sum, e) => sum + e.amount, 0) / filteredExpenses.value.length).toFixed(2)
    : '0',
}));

const formatDate = (date: string) => new Date(date).toLocaleDateString();

function closeModal() {
  showModal.value = false;
  form.reset();
  editing.value = null;
}

function openCreate() {
  editing.value = null;
  form.reset();
  form.payment_method = 'cash';
  showModal.value = true;
}

function openEdit(expense: Expense) {
  editing.value = expense;
  form.reset();
  form.expense_category_id = expense.category ? String(expense.category.id) : '';
  form.title = expense.title;
  form.description = expense.description || '';
  form.amount = String(expense.amount);
  form.expense_date = expense.expense_date;
  form.payment_method = expense.payment_method;
  showModal.value = true;
}

function submit() {
  if (!form.expense_category_id) {
    alert('Please select a category');

    return;
  }

  if (!form.title) {
    alert('Please enter a title');

    return;
  }

  if (!form.amount) {
    alert('Please enter an amount');

    return;
  }

  if (!form.expense_date) {
    alert('Please select a date');
    
    return;
  }

  const url = editing.value ? `/expenses/${editing.value.id}` : '/expenses';
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
  if (confirm('Delete this expense? This action cannot be undone.')) {
    router.delete(`/expenses/${id}`);
  }
}

function clearFilters() {
  search.value = '';
  filterCategory.value = '';
}
</script>

<template>
  <Head title="Expenses" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Expenses</h1>
          <p class="text-sm text-gray-600 mt-1">Track and manage school expenses</p>
        </div>
        <div class="flex items-center gap-2">
          <ExportDropdown
            baseUrl="/exports/expenses"
            :filters="{
              category_id: filterCategory,
              search: search
            }"
            label="Export Expenses"
          />
          <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl transition-all cursor-pointer">
            <Plus class="w-4 h-4" /> Add Expense
          </button>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input v-model="search" placeholder="Search expenses..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition" />
          </div>
          <select v-model="filterCategory" class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <button v-if="search || filterCategory" @click="clearFilters" class="px-3 py-2 text-gray-600 hover:text-gray-900 transition">Clear</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Folder class="w-5 h-5 text-amber-500" />
          <div><p class="text-xs text-gray-500 uppercase">Total Expenses</p><p class="text-2xl font-bold">{{ stats.total }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <DollarSign class="w-5 h-5 text-red-500" />
          <div><p class="text-xs text-gray-500 uppercase">Total Amount</p><p class="text-2xl font-bold">{{ formatCurrencyCompact(stats.amount) }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Calendar class="w-5 h-5 text-green-500" />
          <div><p class="text-xs text-gray-500 uppercase">Average</p><p class="text-2xl font-bold">{{ formatCurrencyCompact(parseFloat(stats.avgAmount), 2) }}</p></div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <table class="min-w-full divide-y divide-amber-100">
          <thead class="bg-amber-50/70 backdrop-blur-sm">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Method</th>
              <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-amber-50 bg-white">
            <tr v-for="expense in filteredExpenses" :key="expense.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ expense.title }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span v-if="expense.category" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">
                  {{ expense.category.name }}
                </span>
                <span v-else class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100/80 text-gray-700 border border-gray-200/30">
                  No category
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ formatCurrencyCompact(expense.amount, 2) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(expense.expense_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="capitalize px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-green-100/80 text-green-900 border border-green-200/30': expense.payment_method === 'bank',
                    'bg-purple-100/80 text-purple-900 border border-purple-200/30': expense.payment_method === 'momo',
                    'bg-amber-100/80 text-amber-900 border border-amber-200/30': expense.payment_method === 'cash',
                  }">
                  {{ expense.payment_method }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                <button @click="openEdit(expense)" class="text-green-600 hover:text-green-800 transition"><Edit class="w-4 h-4" /></button>
                <button @click="destroy(expense.id)" class="text-red-600 hover:text-red-800 transition"><Trash2 class="w-4 h-4" /></button>
              </td>
            </tr>
            <tr v-if="filteredExpenses.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">No expenses found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-md w-full mx-4 border border-amber-100">
          <div class="flex justify-between p-6 border-b border-amber-100/60">
            <h2 class="text-xl font-semibold text-gray-900">{{ editing ? 'Edit Expense' : 'Create Expense' }}</h2>
            <button @click="closeModal"><X class="w-5 h-5" /></button>
          </div>
          <form @submit.prevent="submit" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select v-model="form.expense_category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Select category</option>
                <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input v-model="form.title" type="text" placeholder="e.g., Office Supplies" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
              <textarea v-model="form.description" placeholder="Additional details..." class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none" rows="2"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount (GHS)</label>
              <input v-model="form.amount" type="number" step="0.01" placeholder="0.00" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
              <input v-model="form.expense_date" type="date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select v-model="form.payment_method" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="cash">Cash</option>
                <option value="momo">Mobile Money</option>
                <option value="bank">Bank Transfer</option>
              </select>
            </div>
            <div class="flex gap-3 pt-4">
              <button type="button" @click="closeModal" class="flex-1 px-4 py-2 border border-amber-100 rounded-lg text-gray-700 hover:bg-amber-50/30 transition">Cancel</button>
              <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold transition disabled:opacity-50">
                {{ form.processing ? 'Saving...' : (editing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>