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

type AcademicYear = {
  id: number;
  name: string;
  ended_at: string | null;
  terms_count: number;
  terms?: any[];
};

const props = defineProps<{
  years: AcademicYear[];
}>();

const showModal = ref(false);
const editing = ref<AcademicYear | null>(null);
const editingTerms = ref(false);
const search = ref('');

const form = useForm({
  name: '',
  terms: [] as any[],
});

// Filtered academic years
const filteredYears = computed(() => {
  if (!search.value) {
return props.years;
}

  const term = search.value.toLowerCase();

  return props.years.filter(y => y.name.toLowerCase().includes(term));
});

// Stats
const totalYears = computed(() => filteredYears.value.length);
const activeYears = computed(() => filteredYears.value.filter((y: any) => y.terms?.some((t: any) => t.is_active)).length);

function isYearActive(year: AcademicYear) {
  return year.terms?.some((t: any) => t.is_active) || false;
}
const totalTerms = computed(() => props.years.reduce((sum, y) => sum + y.terms_count, 0));

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString() : '—';

function closeModal() {
  showModal.value = false;
  editingTerms.value = false;
  form.reset();
  editing.value = null;
}

function openCreate() {
  editing.value = null;
  form.reset();
  showModal.value = true;
}

function formatDateForInput(dateString: string | null): string {
  if (!dateString) {
return '';
}

  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function openEdit(year: AcademicYear) {
  editing.value = year;
  form.reset();
  form.name = year.name;
  form.terms = year.terms
    ? year.terms.map((term: any) => ({
        ...term,
        start_date: formatDateForInput(term.start_date),
        end_date: formatDateForInput(term.end_date),
      }))
    : [];
  showModal.value = true;
}

function addTerm() {
  form.terms.push({ name: '', start_date: '', end_date: '', is_active: false });
}

function removeTerm(index: number) {
  form.terms.splice(index, 1);
}

function activateTerm(index: number) {
  form.terms.forEach((term: any, idx: number) => {
    term.is_active = idx === index;
  });
}

function submit() {
  const url = editing.value ? `/academic-years/${editing.value.id}` : '/academic-years';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this academic year? This action cannot be undone.')) {
    router.delete(`/academic-years/${id}`);
  }
}

function endYear(id: number) {
  if (confirm('Mark this academic year as complete? All data will be archived and a new year can be created. This action cannot be undone.')) {
    router.post(`/academic-years/${id}/end`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Academic Years" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Academic Years</h1>
          <p class="text-sm text-gray-600 mt-1">Manage academic years and their terms</p>
        </div>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl transition-all">
          <Plus class="w-4 h-4" /> Add Year
        </button>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
        <div class="flex gap-3">
          <div class="flex-1 relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input v-model="search" placeholder="Search by year name..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition" />
          </div>
          <button v-if="search" @click="clearSearch" class="px-3 py-2 text-gray-600 hover:text-gray-900 transition">Clear</button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Calendar class="w-5 h-5 text-amber-500" />
          <div><p class="text-xs text-gray-500 uppercase">Total Years</p><p class="text-2xl font-bold">{{ totalYears }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <CheckCircle class="w-5 h-5 text-green-500" />
          <div><p class="text-xs text-gray-500 uppercase">Active Year</p><p class="text-2xl font-bold">{{ activeYears }}</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Calendar class="w-5 h-5 text-amber-600" />
          <div><p class="text-xs text-gray-500 uppercase">Total Terms</p><p class="text-2xl font-bold">{{ totalTerms }}</p></div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <table class="min-w-full divide-y divide-amber-100">
          <thead class="bg-amber-50/70 backdrop-blur-sm">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Year Name</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Terms</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-amber-50 bg-white">
            <tr v-for="year in filteredYears" :key="year.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ year.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ year.terms_count }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="year.ended_at" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100/80 text-gray-700 border border-gray-200/30">Ended</span>
                <span v-else-if="isYearActive(year)" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">Active</span>
                <span v-else class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100/80 text-blue-900 border border-blue-200/30">Inactive</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                <button @click="openEdit(year)" class="text-green-600 hover:text-green-800"><Edit class="w-4 h-4" /></button>
                <button
                  v-if="!year.ended_at"
                  @click="endYear(year.id)"
                  class="text-orange-600 hover:text-orange-800 text-xs font-medium"
                  title="Mark this year as complete"
                >
                  End Year
                </button>
                <button @click="destroy(year.id)" class="text-red-600 hover:text-red-800"><Trash2 class="w-4 h-4" /></button>
              </td>
            </tr>
            <tr v-if="filteredYears.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">No academic years found.</td>
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
            <h2 class="text-xl font-semibold text-gray-900">{{ editing ? 'Edit Academic Year' : 'Create Academic Year' }}</h2>
            <button @click="closeModal"><X class="w-5 h-5" /></button>
          </div>
          <form @submit.prevent="submit" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Year Name</label>
              <input v-model="form.name" type="text" placeholder="e.g., 2024-2025" required class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
            </div>

            <!-- Terms Section -->
            <div class="border-t border-amber-100 pt-4">
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">Terms</label>
                <button
                  v-if="!editingTerms"
                  type="button"
                  @click="editingTerms = true"
                  class="text-xs px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition"
                >
                  Edit Terms
                </button>
                <div v-else class="flex gap-1">
                  <button
                    type="button"
                    @click="addTerm"
                    class="text-xs px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded transition"
                  >
                    + Add Term
                  </button>
                  <button
                    type="button"
                    @click="editingTerms = false"
                    class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition"
                  >
                    Done
                  </button>
                </div>
              </div>

              <div v-if="form.terms.length === 0" class="text-xs text-gray-500 py-2">No terms added yet</div>

              <!-- View Mode -->
              <div v-if="!editingTerms" class="space-y-2">
                <div v-for="(term, idx) in form.terms" :key="idx" :class="['p-3 border rounded-lg', term.is_active ? 'border-green-300 bg-green-50' : 'border-amber-100 bg-amber-50/50']">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ term.name }}</p>
                      <p class="text-xs text-gray-600 mt-1">{{ term.start_date }} to {{ term.end_date }}</p>
                    </div>
                    <span v-if="term.is_active" class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                      Active
                    </span>
                  </div>
                </div>
              </div>

              <!-- Edit Mode -->
              <div v-else class="space-y-2">
                <div v-for="(term, idx) in form.terms" :key="idx" :class="['p-3 border rounded-lg mb-2 space-y-2', term.is_active ? 'border-green-300 bg-green-50/50' : 'border-amber-100/50 bg-amber-50/30']">
                <div class="flex items-start justify-between gap-2">
                  <input
                    v-model="term.name"
                    type="text"
                    placeholder="Term name (e.g., Term 1)"
                    class="flex-1 text-xs border border-amber-200 rounded px-2 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-amber-500"
                  />
                  <span v-if="term.is_active" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 whitespace-nowrap">
                    Active
                  </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <input
                    v-model="term.start_date"
                    type="date"
                    class="text-xs border border-amber-200 rounded px-2 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-amber-500"
                  />
                  <input
                    v-model="term.end_date"
                    type="date"
                    class="text-xs border border-amber-200 rounded px-2 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-amber-500"
                  />
                </div>
                <div class="flex items-center justify-between gap-2">
                  <button
                    v-if="!term.is_active"
                    type="button"
                    @click="activateTerm(idx)"
                    class="text-xs px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded font-medium transition"
                  >
                    Activate
                  </button>
                  <div v-else class="text-xs px-2 py-1"></div>
                  <button
                    type="button"
                    @click="removeTerm(idx)"
                    class="text-xs text-red-600 hover:text-red-800 font-medium"
                  >
                    Remove
                  </button>
                </div>
                </div>
              </div>
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