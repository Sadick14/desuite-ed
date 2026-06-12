<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  Tag,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type Category = {
  id: number;
  name: string;
};

const props = defineProps<{
  categories: Category[];
}>();

// UI state
const showModal = ref(false);
const editing = ref<Category | null>(null);
const search = ref('');

const form = useForm({
  name: '',
});

// Filtered categories
const filteredCategories = computed(() => {
  if (!search.value) {
return props.categories;
}

  const term = search.value.toLowerCase();

  return props.categories.filter(c => c.name.toLowerCase().includes(term));
});

// Stats
const totalCategories = computed(() => filteredCategories.value.length);

// Modal actions
function closeModal() {
  showModal.value = false;
  form.reset();
  editing.value = null;
}

function openCreate() {
  editing.value = null;
  form.reset();
  showModal.value = true;
}

function openEdit(category: Category) {
  editing.value = category;
  form.reset();
  form.name = category.name;
  showModal.value = true;
}

function submit() {
  const url = editing.value ? `/expense-categories/${editing.value.id}` : '/expense-categories';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this category? This action cannot be undone.')) {
    router.delete(`/expense-categories/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Expense Categories" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            Expense Categories
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage expense categories for school accounting
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Category
        </button>
      </div>

      <!-- Search & Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <div class="relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by category name..."
              class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
            />
            <button
              v-if="search"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <Tag class="w-5 h-5 text-amber-500" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Categories</p>
            <p class="text-2xl font-bold">{{ totalCategories }}</p>
          </div>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category Name</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ category.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <button @click="openEdit(category)" class="text-green-600 hover:text-green-800">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="destroy(category.id)" class="text-red-600 hover:text-red-800">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredCategories.length === 0">
                <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                  No expense categories found. Click "Add Category" to create one.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-md w-full transform transition-all border border-amber-100">
            <div class="flex items-center justify-between p-6 border-b border-amber-100/60">
              <h2 class="text-xl font-semibold text-gray-900">
                {{ editing ? 'Edit Category' : 'Create Category' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <X class="w-5 h-5" />
              </button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g., Salaries, Utilities, Maintenance"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
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
                  class="flex-1 px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold disabled:opacity-50 flex items-center justify-center gap-2 transition"
                >
                  <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></span>
                  {{ form.processing ? 'Saving...' : (editing ? 'Update' : 'Create') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>