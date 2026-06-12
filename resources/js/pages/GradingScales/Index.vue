<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, Search, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
  scales: any[];
}>();

const search = ref('');
const modalOpen = ref(false);
const editingScale = ref<any>(null);

const filteredScales = computed(() => {
  if (!search.value) {
return props.scales;
}

  const term = search.value.toLowerCase();

  return props.scales.filter(s =>
    s.name.toLowerCase().includes(term) ||
    s.description?.toLowerCase().includes(term)
  );
});

const form = useForm({
  name: '',
  description: '',
  boundaries: [
    { min_score: 80, max_score: 100, grade: 'A', remark: 'Excellent' },
    { min_score: 70, max_score: 79, grade: 'B', remark: 'Good' },
    { min_score: 60, max_score: 69, grade: 'C', remark: 'Fair' },
    { min_score: 50, max_score: 59, grade: 'D', remark: 'Pass' },
    { min_score: 0, max_score: 49, grade: 'F', remark: 'Fail' },
  ],
});

const openCreateModal = () => {
  editingScale.value = null;
  form.reset();
  form.boundaries = [
    { min_score: 80, max_score: 100, grade: 'A', remark: 'Excellent' },
    { min_score: 70, max_score: 79, grade: 'B', remark: 'Good' },
    { min_score: 60, max_score: 69, grade: 'C', remark: 'Fair' },
    { min_score: 50, max_score: 59, grade: 'D', remark: 'Pass' },
    { min_score: 0, max_score: 49, grade: 'F', remark: 'Fail' },
  ];
  modalOpen.value = true;
};

const openEditModal = (scale: any) => {
  editingScale.value = scale;
  form.name = scale.name;
  form.description = scale.description || '';
  form.boundaries = scale.boundaries ? [...scale.boundaries] : [];
  modalOpen.value = true;
};

const addBoundary = () => {
  form.boundaries.push({ min_score: 0, max_score: 100, grade: '', remark: '' });
};

const removeBoundary = (index: number) => {
  form.boundaries.splice(index, 1);
};

const save = () => {
  if (editingScale.value) {
    form.put(`/grading-scales/${editingScale.value.id}`, {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  } else {
    form.post('/grading-scales', {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  }
};

function destroy(id: number) {
  if (confirm('Delete this grading scale? This action cannot be undone.')) {
    form.delete(`/grading-scales/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Grading Scales" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-blue-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Grading Scales</h1>
          <p class="text-sm text-gray-600 mt-1">Manage grade definitions and boundaries</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Create Scale
        </button>
      </div>

      <!-- Search -->
      <div class="relative">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="search"
          type="text"
          placeholder="Search by name or description..."
          class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
        />
        <button
          v-if="search"
          @click="clearSearch"
          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Scales Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Description</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Grades</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="scale in filteredScales" :key="scale.id" class="hover:bg-blue-50/30 transition-colors">
                <td class="px-6 py-4 font-semibold text-gray-900">{{ scale.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ scale.description || '-' }}</td>
                <td class="px-6 py-4 text-center text-sm">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                    {{ scale.boundaries?.length || 0 }} grades
                  </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button
                    @click="openEditModal(scale)"
                    class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1"
                  >
                    <Edit class="w-4 h-4" />
                    <span class="text-xs font-medium">Edit</span>
                  </button>
                  <button
                    @click="destroy(scale.id)"
                    class="text-red-600 hover:text-red-800 inline-flex items-center gap-1"
                  >
                    <Trash2 class="w-4 h-4" />
                    <span class="text-xs font-medium">Delete</span>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredScales.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                  No grading scales found. Click "Create Scale" to add one.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal for Add/Edit Scale -->
      <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-blue-100 sticky top-0 bg-white">
            <h3 class="text-xl font-bold text-gray-900">{{ editingScale ? 'Edit Grading Scale' : 'Create Grading Scale' }}</h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-bold text-gray-900 mb-1">Scale Name</label>
              <input v-model="form.name" type="text" placeholder="e.g., Standard Scale" class="w-full border border-blue-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400 text-gray-900" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-bold text-gray-900 mb-1">Description (Optional)</label>
              <textarea v-model="form.description" rows="2" placeholder="Describe this scale..." class="w-full border border-blue-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400 text-gray-900"></textarea>
            </div>

            <!-- Grade Boundaries -->
            <div>
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-bold text-gray-900">Grade Boundaries</label>
                <button
                  type="button"
                  @click="addBoundary"
                  class="text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg font-medium transition"
                >
                  + Add Grade
                </button>
              </div>

              <div class="space-y-2">
                <div v-for="(boundary, idx) in form.boundaries" :key="idx" class="p-3 border border-blue-100 rounded-lg space-y-2">
                  <div class="grid grid-cols-4 gap-2">
                    <div>
                      <label class="text-xs font-medium text-gray-700">Min</label>
                      <input v-model.number="boundary.min_score" type="number" min="0" max="100" class="w-full px-2 py-1 border border-blue-200 rounded text-sm text-gray-900" />
                    </div>
                    <div>
                      <label class="text-xs font-medium text-gray-700">Max</label>
                      <input v-model.number="boundary.max_score" type="number" min="0" max="100" class="w-full px-2 py-1 border border-blue-200 rounded text-sm text-gray-900" />
                    </div>
                    <div>
                      <label class="text-xs font-medium text-gray-700">Grade</label>
                      <input v-model="boundary.grade" type="text" maxlength="2" class="w-full px-2 py-1 border border-blue-200 rounded text-sm text-gray-900" />
                    </div>
                    <div>
                      <label class="text-xs font-medium text-gray-700">Remark</label>
                      <input v-model="boundary.remark" type="text" class="w-full px-2 py-1 border border-blue-200 rounded text-sm text-gray-900" />
                    </div>
                  </div>
                  <button
                    v-if="form.boundaries.length > 1"
                    type="button"
                    @click="removeBoundary(idx)"
                    class="text-xs px-2 py-1 text-red-600 hover:bg-red-50 rounded"
                  >
                    Remove
                  </button>
                </div>
              </div>
              <p v-if="form.errors.boundaries" class="mt-2 text-sm text-red-600">{{ form.errors.boundaries }}</p>
            </div>
          </div>

          <div class="p-6 border-t border-blue-100 flex justify-end gap-3 sticky bottom-0 bg-blue-50">
            <button @click="modalOpen = false" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50">Cancel</button>
            <button @click="save" :disabled="form.processing || !form.name" class="px-4 py-2.5 bg-lime-400 hover:bg-lime-500 disabled:opacity-50 text-gray-900 rounded-xl font-bold text-sm shadow-sm">
              {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
