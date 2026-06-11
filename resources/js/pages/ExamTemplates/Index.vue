<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  FilePlus2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type ExamTemplate = {
  id: number;
  name: string;
  exam_type: string;
  weight: number;
  max_score: number;
  pass_score: number;
  level: string | null;
  description: string | null;
  created_at: string;
};

const props = defineProps<{
  examTemplates: ExamTemplate[];
}>();

const showModal = ref(false);
const editing = ref<ExamTemplate | null>(null);
const search = ref('');

const form = useForm({
  name: '',
  exam_type: 'class_assessment',
  weight: 30,
  max_score: 100,
  pass_score: 50,
  level: null as string | null,
  description: null as string | null,
});

const filteredTemplates = computed(() => {
  if (!search.value) return props.examTemplates;
  const term = search.value.toLowerCase();
  return props.examTemplates.filter(t =>
    t.name.toLowerCase().includes(term) ||
    t.description?.toLowerCase().includes(term)
  );
});

const totalTemplates = computed(() => filteredTemplates.value.length);

const levelOptions = [
  { value: null, label: 'All Levels' },
  { value: 'nursery', label: 'Nursery' },
  { value: 'kindergarten', label: 'Kindergarten' },
  { value: 'lower_primary', label: 'Lower Primary' },
  { value: 'upper_primary', label: 'Upper Primary' },
  { value: 'jhs', label: 'JHS' },
];

const examTypeOptions = [
  { value: 'class_assessment', label: 'Class Assessment' },
  { value: 'mid_term', label: 'Mid-Term' },
  { value: 'quiz', label: 'Quiz' },
  { value: 'final_exam', label: 'Final Exam' },
];

function openCreate() {
  editing.value = null;
  form.reset();
  form.exam_type = 'class_assessment';
  form.weight = 30;
  form.max_score = 100;
  form.pass_score = 50;
  form.level = null;
  showModal.value = true;
}

function openEdit(template: ExamTemplate) {
  editing.value = template;
  form.reset();
  form.name = template.name;
  form.exam_type = template.exam_type;
  form.weight = template.weight;
  form.max_score = template.max_score;
  form.pass_score = template.pass_score;
  form.level = template.level;
  form.description = template.description;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  editing.value = null;
}

function submit() {
  const url = editing.value ? `/exam-templates/${editing.value.id}` : '/exam-templates';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this exam template? This action cannot be undone.')) {
    router.delete(`/exam-templates/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Exam Templates" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            Exam Templates
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage reusable exam templates
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Template
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-1">
          <div class="relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search templates..."
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
          <FilePlus2 class="w-5 h-5 text-amber-600" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Templates</p>
            <p class="text-2xl font-bold">{{ totalTemplates }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Weight</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Max Score</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Level</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="template in filteredTemplates" :key="template.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ template.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">
                    {{ examTypeOptions.find(opt => opt.value === template.exam_type)?.label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ template.weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ template.max_score }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="template.level" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100/80 text-blue-900 border border-blue-200/30">
                    {{ levelOptions.find(opt => opt.value === template.level)?.label }}
                  </span>
                  <span v-else class="text-sm text-gray-400">All Levels</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <button @click="openEdit(template)" class="text-green-600 hover:text-green-800">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="destroy(template.id)" class="text-red-600 hover:text-red-800">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredTemplates.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  No exam templates found. Click "Add Template" to create one!
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
          <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-md w-full transform transition-all border border-amber-100">
            <div class="flex items-center justify-between p-6 border-b border-amber-100/60">
              <h2 class="text-xl font-semibold text-gray-900">
                {{ editing ? 'Edit Exam Template' : 'Create Exam Template' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <X class="w-5 h-5" />
              </button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g., Class Assessment 1"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                  <select
                    v-model="form.exam_type"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  >
                    <option v-for="opt in examTypeOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                  <p v-if="form.errors.exam_type" class="text-red-500 text-xs mt-1">{{ form.errors.exam_type }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                  <select
                    v-model="form.level"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  >
                    <option v-for="opt in levelOptions" :key="String(opt.value)" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Weight *</label>
                  <input
                    v-model.number="form.weight"
                    type="number"
                    required
                    min="0"
                    max="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.weight" class="text-red-500 text-xs mt-1">{{ form.errors.weight }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Max Score</label>
                  <input
                    v-model.number="form.max_score"
                    type="number"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Pass Score</label>
                  <input
                    v-model.number="form.pass_score"
                    type="number"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                ></textarea>
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
