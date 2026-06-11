<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  X,
  Search,
  Percent,
  Settings,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type AssessmentSetting = {
  id: number;
  academic_year_id: number;
  term_id: number;
  class_assessment_weight: number;
  mid_term_weight: number;
  quiz_weight: number;
  final_exam_weight: number;
  academic_year?: { id: number; name: string };
  term?: { id: number; name: string };
};

const props = defineProps<{
  assessmentSettings: AssessmentSetting[];
  academicYears: any[];
  terms: any[];
}>();

const showModal = ref(false);
const editing = ref<AssessmentSetting | null>(null);
const search = ref('');

const form = useForm({
  academic_year_id: null as number | null,
  term_id: null as number | null,
  class_assessment_weight: 30,
  mid_term_weight: 20,
  quiz_weight: 10,
  final_exam_weight: 40,
});

const filteredSettings = computed(() => {
  if (!search.value) return props.assessmentSettings;
  const term = search.value.toLowerCase();
  return props.assessmentSettings.filter(s =>
    (s.academic_year?.name && s.academic_year.name.toLowerCase().includes(term)) ||
    (s.term?.name && s.term.name.toLowerCase().includes(term))
  );
});

const totalSettings = computed(() => filteredSettings.value.length);

function openCreate() {
  editing.value = null;
  form.reset();
  form.class_assessment_weight = 30;
  form.mid_term_weight = 20;
  form.quiz_weight = 10;
  form.final_exam_weight = 40;
  if (props.academicYears.length > 0) {
    form.academic_year_id = props.academicYears[0].id;
  }
  if (props.terms.length > 0) {
    form.term_id = props.terms[0].id;
  }
  showModal.value = true;
}

function openEdit(setting: AssessmentSetting) {
  editing.value = setting;
  form.reset();
  form.academic_year_id = setting.academic_year_id;
  form.term_id = setting.term_id;
  form.class_assessment_weight = setting.class_assessment_weight;
  form.mid_term_weight = setting.mid_term_weight;
  form.quiz_weight = setting.quiz_weight;
  form.final_exam_weight = setting.final_exam_weight;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  editing.value = null;
}

function submit() {
  const url = editing.value ? `/assessment-settings/${editing.value.id}` : '/assessment-settings';
  const method = editing.value ? 'put' : 'post';

  form[method](url, {
    onSuccess: closeModal,
    preserveScroll: true,
  });
}

function destroy(id: number) {
  if (confirm('Delete this assessment setting? This action cannot be undone.')) {
    router.delete(`/assessment-settings/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}

const totalWeight = computed(() => {
  return form.class_assessment_weight + form.mid_term_weight + form.quiz_weight + form.final_exam_weight;
});
</script>

<template>
  <Head title="Assessment Settings" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            Assessment Settings
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Configure weight distribution for different assessment types
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Setting
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-1">
          <div class="relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by academic year or term..."
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
          <Settings class="w-5 h-5 text-amber-600" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Settings</p>
            <p class="text-2xl font-bold">{{ totalSettings }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70 backdrop-blur-sm">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Academic Year</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Class Assessment</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Mid-Term</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Quiz</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Final Exam</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="setting in filteredSettings" :key="setting.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ setting.academic_year?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">
                    {{ setting.term?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ setting.class_assessment_weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ setting.mid_term_weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ setting.quiz_weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ setting.final_exam_weight }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <button @click="openEdit(setting)" class="text-green-600 hover:text-green-800">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button @click="destroy(setting.id)" class="text-red-600 hover:text-red-800">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredSettings.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  No assessment settings found. Click "Add Setting" to create one.
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
          <div class="relative bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] max-w-lg w-full transform transition-all border border-amber-100">
            <div class="flex items-center justify-between p-6 border-b border-amber-100/60">
              <h2 class="text-xl font-semibold text-gray-900">
                {{ editing ? 'Edit Assessment Setting' : 'Create Assessment Setting' }}
              </h2>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <X class="w-5 h-5" />
              </button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
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
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Term *</label>
                  <select
                    v-model="form.term_id"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  >
                    <option value="">Select Term</option>
                    <option v-for="term in terms" :key="term.id" :value="term.id">
                      {{ term.name }}
                    </option>
                  </select>
                  <p v-if="form.errors.term_id" class="text-red-500 text-xs mt-1">{{ form.errors.term_id }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Class Assessment Weight *</label>
                  <input
                    v-model.number="form.class_assessment_weight"
                    type="number"
                    required
                    min="0"
                    max="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.class_assessment_weight" class="text-red-500 text-xs mt-1">{{ form.errors.class_assessment_weight }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Mid-Term Weight *</label>
                  <input
                    v-model.number="form.mid_term_weight"
                    type="number"
                    required
                    min="0"
                    max="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.mid_term_weight" class="text-red-500 text-xs mt-1">{{ form.errors.mid_term_weight }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quiz Weight *</label>
                  <input
                    v-model.number="form.quiz_weight"
                    type="number"
                    required
                    min="0"
                    max="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.quiz_weight" class="text-red-500 text-xs mt-1">{{ form.errors.quiz_weight }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Final Exam Weight *</label>
                  <input
                    v-model.number="form.final_exam_weight"
                    type="number"
                    required
                    min="0"
                    max="100"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                  <p v-if="form.errors.final_exam_weight" class="text-red-500 text-xs mt-1">{{ form.errors.final_exam_weight }}</p>
                </div>
              </div>

              <div class="p-4 rounded-lg border" :class="totalWeight === 100 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium" :class="totalWeight === 100 ? 'text-green-700' : 'text-red-700'">
                    Total Weight: {{ totalWeight }}%
                  </span>
                  <span class="text-xs" :class="totalWeight === 100 ? 'text-green-600' : 'text-red-600'">
                    {{ totalWeight === 100 ? 'Perfect!' : 'Must equal 100%' }}
                  </span>
                </div>
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
                  :disabled="form.processing || totalWeight !== 100"
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
