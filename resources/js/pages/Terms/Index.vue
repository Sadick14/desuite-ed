<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  Calendar,
  CheckCircle,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type Term = {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  is_active: boolean;
  academic_year: {
    id: number;
    name: string;
  };
};

const props = defineProps<{
  terms: Term[];
  years: any[];
}>();

const showModal = ref(false);
const editing = ref<Term | null>(null);
const search = ref('');
const filterYear = ref('');

const form = useForm({
  academic_year_id: '',
  name: '',
  start_date: '',
  end_date: '',
  is_active: false,
});

// Filtered terms
const filteredTerms = computed(() => {
  let result = props.terms;
  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(t =>
      t.name.toLowerCase().includes(term) ||
      t.academic_year.name.toLowerCase().includes(term)
    );
  }
  if (filterYear.value) {
    result = result.filter(t => t.academic_year.id === parseInt(filterYear.value));
  }
  return result;
});

// Stats
const totalTerms = computed(() => filteredTerms.value.length);
const activeTerms = computed(() => filteredTerms.value.filter(t => t.is_active).length);
const uniqueYears = computed(() => new Set(filteredTerms.value.map(t => t.academic_year.id)).size);

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString() : '—';

function closeModal() {
  showModal.value = false;
  form.reset();
  form.is_active = false;
  editing.value = null;
}

function openCreate() {
  editing.value = null;
  form.reset();
  form.is_active = false;
  showModal.value = true;
}

function openEdit(term: Term) {
  editing.value = term;
  form.reset();
  form.academic_year_id = String(term.academic_year.id);
  form.name = term.name;
  form.start_date = term.start_date;
  form.end_date = term.end_date;
  form.is_active = term.is_active;
  showModal.value = true;
}

function submit() {
  const url = editing.value ? `/terms/${editing.value.id}` : '/terms';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this term? This action cannot be undone.')) {
    router.delete(`/terms/${id}`);
  }
}

function clearFilters() {
  search.value = '';
  filterYear.value = '';
}
</script>

<template>
  <Head title="Academic Terms" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Academic Terms</h1>
          <p class="text-sm text-gray-600 mt-1">Manage school terms and academic periods</p>
        </div>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl transition-all">
          <Plus class="w-4 h-4" /> Add Term
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input v-model="search" placeholder="Search by term or year..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition" />
          </div>
          <select v-model="filterYear" class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
            <option value="">All Years</option>
            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.name }}</option>
          </select>
          <button v-if="search || filterYear" @click="clearFilters" class="px-3 py-2 text-gray-600 hover:text-gray-900 transition">Clear</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Calendar class="w-5 h-5 text-amber-500" />
          <div><p class="text-xs text-gray-500 uppercase">Total Terms</p><p class="text-2xl font-bold">{{ totalTerms }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <CheckCircle class="w-5 h-5 text-green-500" />
          <div><p class="text-xs text-gray-500 uppercase">Active Terms</p><p class="text-2xl font-bold">{{ activeTerms }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Calendar class="w-5 h-5 text-amber-600" />
          <div><p class="text-xs text-gray-500 uppercase">Academic Years</p><p class="text-2xl font-bold">{{ uniqueYears }}</p></div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <table class="min-w-full divide-y divide-amber-100">
          <thead class="bg-amber-50/70 backdrop-blur-sm">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Term Name</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Academic Year</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Start Date</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">End Date</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-amber-50 bg-white">
            <tr v-for="term in filteredTerms" :key="term.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ term.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ term.academic_year.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(term.start_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(term.end_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="term.is_active" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">Active</span>
                <span v-else class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100/80 text-gray-700 border border-gray-200/30">Inactive</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                <button @click="openEdit(term)" class="text-green-600 hover:text-green-800"><Edit class="w-4 h-4" /></button>
                <button @click="destroy(term.id)" class="text-red-600 hover:text-red-800"><Trash2 class="w-4 h-4" /></button>
              </td>
            </tr>
            <tr v-if="filteredTerms.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">No terms found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-md w-full border border-amber-100">
          <div class="flex justify-between p-6 border-b border-amber-100/60">
            <h2 class="text-xl font-semibold text-gray-900">{{ editing ? 'Edit Term' : 'Create Term' }}</h2>
            <button @click="closeModal"><X class="w-5 h-5" /></button>
          </div>
          <form @submit.prevent="submit" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
              <select v-model="form.academic_year_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Select</option>
                <option v-for="y in years" :key="y.id" :value="String(y.id)">{{ y.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Term Name</label>
              <input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input v-model="form.start_date" type="date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input v-model="form.end_date" type="date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" id="active_check" class="rounded" />
              <label for="active_check" class="text-sm text-gray-700">Active (current term)</label>
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