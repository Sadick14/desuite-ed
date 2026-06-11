<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  Scale,
  BookOpen,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type GradeScaleRule = {
  id: number;
  academic_year_id: number | null;
  grade: string;
  min_score: number;
  max_score: number;
  remark: string;
  is_template: boolean;
  level: string | null;
  template_name: string | null;
  academic_year?: { id: number; name: string };
};

const props = defineProps<{
  gradeScaleRules: GradeScaleRule[];
  academicYears: any[];
}>();

const showModal = ref(false);
const editing = ref<GradeScaleRule | null>(null);
const search = ref('');

const levelOptions = [
  { value: null, label: 'All Levels' },
  { value: 'nursery', label: 'Nursery' },
  { value: 'kindergarten', label: 'Kindergarten' },
  { value: 'lower_primary', label: 'Lower Primary' },
  { value: 'upper_primary', label: 'Upper Primary' },
  { value: 'jhs', label: 'JHS' },
];

const form = useForm({
  academic_year_id: null as number | null,
  grade: '',
  min_score: null as number | null,
  max_score: null as number | null,
  remark: '',
  is_template: false,
  level: null as string | null,
  template_name: null as string | null,
});

const filteredRules = computed(() => {
  if (!search.value) return props.gradeScaleRules;
  const term = search.value.toLowerCase();
  return props.gradeScaleRules.filter(r =>
    r.grade.toLowerCase().includes(term) ||
    r.remark.toLowerCase().includes(term) ||
    (r.academic_year?.name && r.academic_year.name.toLowerCase().includes(term))
  );
});

const totalRules = computed(() => filteredRules.value.length);
const uniqueGrades = computed(() => new Set(filteredRules.value.map(r => r.grade)).size);

function openCreate() {
  editing.value = null;
  form.reset();
  if (props.academicYears.length > 0) {
    form.academic_year_id = props.academicYears[0].id;
  }
  showModal.value = true;
}

function openEdit(rule: GradeScaleRule) {
  editing.value = rule;
  form.reset();
  form.academic_year_id = rule.academic_year_id;
  form.grade = rule.grade;
  form.min_score = rule.min_score;
  form.max_score = rule.max_score;
  form.remark = rule.remark;
  form.is_template = rule.is_template;
  form.level = rule.level;
  form.template_name = rule.template_name;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  editing.value = null;
}

function submit() {
  const url = editing.value ? `/grade-scale-rules/${editing.value.id}` : '/grade-scale-rules';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this grade scale rule? This action cannot be undone.')) {
    router.delete(`/grade-scale-rules/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Grade Scale Rules" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            Grade Scale Rules
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Define grading scales and remarks for academic years
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Rule
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <div class="relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by grade or remark..."
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
          <Scale class="w-5 h-5 text-amber-500" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Rules</p>
            <p class="text-2xl font-bold">{{ totalRules }}</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 flex items-center gap-3 shadow-xl shadow-amber-900/[0.01]">
          <BookOpen class="w-5 h-5 text-amber-600" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Unique Grades</p>
            <p class="text-2xl font-bold">{{ uniqueGrades }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name/Year</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Grade</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Min Score</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Max Score</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Remark</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="rule in filteredRules" :key="rule.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="rule.is_template" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100/80 text-blue-900 border border-blue-200/30">
                    Template
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">
                    Year-Specific
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ rule.is_template ? rule.template_name : rule.academic_year?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100/80 text-purple-900 border border-purple-200/30">
                    {{ rule.grade }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ rule.min_score }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ rule.max_score }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ rule.remark }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <button @click="openEdit(rule)" class="text-green-600 hover:text-green-800">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="destroy(rule.id)" class="text-red-600 hover:text-red-800">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredRules.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  No grade scale rules found. Click "Add Rule" to create one.
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
                {{ editing ? 'Edit Grade Scale Rule' : 'Create Grade Scale Rule' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <X class="w-5 h-5" />
              </button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
              <div class="flex items-center gap-2">
                <input
                  id="is-template"
                  type="checkbox"
                  v-model="form.is_template"
                  class="h-4 w-4 rounded border-gray-300 text-lime-600 focus:ring-lime-500"
                />
                <label for="is-template" class="text-sm font-medium text-gray-700">Is this a reusable template?</label>
              </div>

              <div v-if="!form.is_template">
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year *</label>
                <select
                  v-model="form.academic_year_id"
                  required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                >
                  <option value="">Select Academic Year</option>
                  <option v-for="year in academicYears" :key="year.id" :value="year.id">
                    {{ year.name }}
                  </option>
                </select>
                <p v-if="form.errors.academic_year_id" class="text-red-500 text-xs mt-1">{{ form.errors.academic_year_id }}</p>
              </div>

              <div v-if="form.is_template">
                <label class="block text-sm font-medium text-gray-700 mb-1">Template Name *</label>
                <input
                  v-model="form.template_name"
                  type="text"
                  required
                  placeholder="e.g., Standard Grading Scale"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
                <p v-if="form.errors.template_name" class="text-red-500 text-xs mt-1">{{ form.errors.template_name }}</p>

                <div class="mt-3">
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grade *</label>
                <input
                  v-model="form.grade"
                  type="text"
                  required
                  placeholder="e.g., A, B+, Pass"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
                <p v-if="form.errors.grade" class="text-red-500 text-xs mt-1">{{ form.errors.grade }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Min Score *</label>
                  <input
                    v-model.number="form.min_score"
                    type="number"
                    required
                    min="0"
                    max="100"
                    placeholder="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.min_score" class="text-red-500 text-xs mt-1">{{ form.errors.min_score }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Max Score *</label>
                  <input
                    v-model.number="form.max_score"
                    type="number"
                    required
                    min="0"
                    max="100"
                    placeholder="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.max_score" class="text-red-500 text-xs mt-1">{{ form.errors.max_score }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remark *</label>
                <input
                  v-model="form.remark"
                  type="text"
                  required
                  placeholder="e.g., Excellent, Very Good"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
                <p v-if="form.errors.remark" class="text-red-500 text-xs mt-1">{{ form.errors.remark }}</p>
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
