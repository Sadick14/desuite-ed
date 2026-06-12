<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  term: any;
  course: any;
  class: any;
  setting: any;
  students: any[];
}>();

const schoolClass = props.class;

const marks = useForm({
  term_id: props.term.id,
  course_id: props.course.id,
  class_id: schoolClass.id,
  marks: props.students.map(s => ({
    student_id: s.id,
    class_test_1: null,
    class_test_2: null,
    class_test_3: null,
    assignment: null,
    classwork: null,
    project: null,
    exam_score: null,
  }))
});

const hasCAScore = (mark: any) => {
  return (mark.class_test_1 !== null && mark.class_test_1 !== '') &&
         (mark.class_test_2 !== null && mark.class_test_2 !== '') &&
         (mark.class_test_3 !== null && mark.class_test_3 !== '') &&
         (mark.assignment !== null && mark.assignment !== '') &&
         (mark.classwork !== null && mark.classwork !== '') &&
         (mark.project !== null && mark.project !== '');
};

const completedCount = computed(() => {
  return marks.marks.filter(m =>
    hasCAScore(m) &&
    (m.exam_score !== null && m.exam_score !== '')
  ).length;
});

const incompleteStudents = computed(() => {
  return marks.marks.filter(m =>
    !hasCAScore(m) ||
    (m.exam_score === null || m.exam_score === '')
  ).map((m, idx) => `${props.students[idx].first_name} ${props.students[idx].last_name}`);
});

const progress = computed(() => {
  return Math.round((completedCount.value / marks.marks.length) * 100);
});

function submit() {
  marks.post('/student-marks', {
    onSuccess: () => {
      router.visit('/student-marks');
    }
  });
}

function goBack() {
  router.visit('/student-marks');
}
</script>

<template>
  <Head :title="`${schoolClass.name} - ${course.name} Marks`" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <button @click="goBack" class="p-2 hover:bg-gray-100 rounded-lg transition">
          <ArrowLeft class="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ schoolClass.name }} - {{ course.name }}</h1>
          <p class="text-sm text-gray-600 mt-1">{{ term.name }} | {{ students.length }} students</p>
        </div>
      </div>

      <!-- Info Box -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <AlertCircle class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
        <div class="text-sm text-blue-900">
          <p class="font-semibold">Assessment Configuration</p>
          <p class="mt-1">CA Weight: {{ setting.ca_weight }}% | Exam Weight: {{ setting.exam_weight }}% | Max CA: {{ setting.ca_max_marks }} | Max Exam: {{ setting.exam_max_marks }}</p>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="bg-white rounded-xl border border-blue-100 p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-semibold text-gray-700">Progress: {{ completedCount }}/{{ marks.marks.length }} students complete</span>
          <span class="text-lg font-bold text-blue-600">{{ progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div :style="{ width: progress + '%' }" class="bg-blue-600 h-2 rounded-full transition-all"></div>
        </div>

        <!-- Requirements -->
        <div class="mt-3 text-xs text-gray-600 space-y-1">
          <p class="font-semibold">Requirements per student:</p>
          <p class="flex items-center gap-2">
            <span v-if="completedCount > 0" class="text-green-600">✓</span>
            <span v-else class="text-red-600">✗</span>
            <span>CA (/100): T1+T2+T3 (/10 each) + Assignment (/20) + Classwork (/30) + Project (/20)</span>
          </p>
          <p class="flex items-center gap-2">
            <span v-if="completedCount > 0" class="text-green-600">✓</span>
            <span v-else class="text-red-600">✗</span>
            Exam score (/100) - required
          </p>
        </div>
      </div>

      <!-- Marks Table -->
      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-100">
              <thead class="bg-blue-50/70 sticky top-0">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T1/10</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T2/10</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T3/10</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">Asgn/20</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">CW/30</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">Proj/20</th>
                  <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-blue-100">Exam/100</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-blue-50">
                <tr v-for="(m, idx) in marks.marks" :key="m.student_id" class="hover:bg-blue-50/30">
                  <td class="px-4 py-3 font-medium text-gray-900 text-sm whitespace-nowrap">
                    {{ students[idx].first_name }} {{ students[idx].last_name }}
                  </td>
                  <!-- CA Components (6 fields, /100 total) -->
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.class_test_1" type="number" step="0.5" min="0" max="10" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.class_test_2" type="number" step="0.5" min="0" max="10" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.class_test_3" type="number" step="0.5" min="0" max="10" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.assignment" type="number" step="0.5" min="0" max="20" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.classwork" type="number" step="0.5" min="0" max="30" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <td class="px-2 py-3 bg-amber-50/50">
                    <input v-model.number="m.project" type="number" step="0.5" min="0" max="20" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                  <!-- Exam -->
                  <td class="px-2 py-3 border-l-2 border-blue-300 bg-blue-50/50">
                    <input v-model.number="m.exam_score" type="number" step="0.5" min="0" max="100" placeholder="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg bg-white text-sm text-center font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Error Messages -->
        <div v-if="incompleteStudents.length > 0 && completedCount === 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <p class="text-sm font-semibold text-red-900 mb-2">⚠️ Required: All 6 CA components and Exam score must be entered</p>
          <p class="text-xs text-red-700">Each student needs:</p>
          <ul class="text-xs text-red-700 list-disc list-inside mt-1">
            <li>All CA components: Test 1, Test 2, Test 3, Assignment, Classwork, and Project</li>
            <li>Exam score (required)</li>
          </ul>
        </div>

        <div v-else-if="incompleteStudents.length > 0" class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
          <p class="text-sm font-semibold text-amber-900 mb-2">⚠️ Incomplete: {{ incompleteStudents.length }} student(s) missing data</p>
          <p class="text-xs text-amber-700">Missing either CA scores or exam scores</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-6">
          <button type="button" @click="goBack" class="flex-1 px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition">
            Cancel
          </button>
          <button
            type="submit"
            :disabled="marks.processing || completedCount === 0"
            class="flex-1 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-semibold rounded-xl transition flex items-center justify-center gap-2"
          >
            <Check class="w-4 h-4" />
            <span v-if="marks.processing">Saving...</span>
            <span v-else-if="completedCount === 0">Save Marks ({{ completedCount }}/{{ marks.marks.length }} ready)</span>
            <span v-else>Save Marks ({{ completedCount }}/{{ marks.marks.length }} complete)</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
