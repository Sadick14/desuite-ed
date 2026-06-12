<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

type StudentMark = {
  id: number;
  student_id: number;
  course_id: number;
  term_id: number;
  student: { 
    first_name: string; 
    last_name: string; 
    school_class_id?: number;
    [key: string]: any;
  };
  course: { name: string; [key: string]: any };
  class_test_1: number | null;
  class_test_2: number | null;
  class_test_3: number | null;
  assignment: number | null;
  classwork: number | null;
  project: number | null;
  ca_total: number | null;
  exam_score: number | null;
  final_score: number | null;
  letter_grade: string | null;
  status: string;
  [key: string]: any;
};

const props = defineProps<{
  marks: StudentMark[];
  terms: any[];
  classes: any[];
  selectedTermId?: string | number;
  selectedClassId?: string | number;
}>();

const search = ref('');
const filterTerm = ref(props.selectedTermId || '');
const filterClass = ref(props.selectedClassId || '');
const showSelector = ref(!props.selectedTermId || !props.selectedClassId);
const selectedCourse = ref('');

// Track unique students to show grades button
const uniqueStudents = computed(() => {
  const map = new Map();
  props.marks.forEach(m => {
    if (!map.has(m.student_id)) {
      map.set(m.student_id, m.student);
    }
  });

  return Array.from(map.values());
});

const filteredMarks = computed(() => {
  let result = props.marks;

  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(m =>
      `${m.student.first_name} ${m.student.last_name}`.toLowerCase().includes(term)
    );
  }

  if (filterTerm.value) {
result = result.filter(m => m.term_id === Number(filterTerm.value));
}

  if (filterClass.value) {
result = result.filter(m => m.student.school_class_id === Number(filterClass.value));
}

  if (selectedCourse.value) {
result = result.filter(m => m.course_id === Number(selectedCourse.value));
}

  return result;
});

const courses = computed(() => {
  const unique = new Map();
  props.marks.forEach(m => {
    if (!unique.has(m.course_id)) {
      unique.set(m.course_id, m.course);
    }
  });

  return Array.from(unique.values());
});

const statusColors: Record<string, string> = {
  draft: 'bg-amber-100/80 text-amber-900 border-amber-200',
  submitted: 'bg-blue-100/80 text-blue-900 border-blue-200',
  approved: 'bg-green-100/80 text-green-900 border-green-200',
};

function startEnteringMarks() {
  if (filterTerm.value && filterClass.value && selectedCourse.value) {
    router.get(`/student-marks/create?term_id=${filterTerm.value}&class_id=${filterClass.value}&course_id=${selectedCourse.value}`);
  }
}

function deleteMarks(id: number) {
  if (confirm('Delete this record? This action cannot be undone.')) {
    router.delete(`/student-marks/${id}`);
  }
}
</script>

<template>
  <Head title="Student Marks" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-blue-100/60 pb-5">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Student Marks</h1>
          <p class="text-sm text-gray-600 mt-1">Manage student grades per class and course</p>
        </div>
      </div>

      <!-- Quick Entry Section -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Quick Entry - Select Class & Course</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Term Selection -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">Select Term</label>
            <select
              v-model="filterTerm"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">-- Choose Term --</option>
              <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>

          <!-- Class Selection -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">Select Class</label>
            <select
              v-model="filterClass"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">-- Choose Class --</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Course Selection -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">Select Course</label>
            <select
              v-model="selectedCourse"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">-- Choose Course --</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Action Button -->
          <div class="flex items-end">
            <button
              @click="startEnteringMarks"
              :disabled="!filterTerm || !filterClass || !selectedCourse"
              class="w-full px-4 py-2 bg-lime-400 hover:bg-lime-500 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-semibold rounded-lg transition flex items-center justify-center gap-2"
            >
              <Plus class="w-4 h-4" />
              Enter
            </button>
          </div>
        </div>
      </div>

      <!-- Students Quick Access -->
      <div v-if="uniqueStudents.length > 0" class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-4">Students - View All Grades</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <router-link
            v-for="student in uniqueStudents"
            :key="student.id"
            :href="`/students/${student.id}/grades`"
            class="p-3 border border-blue-100 rounded-lg hover:bg-blue-50 transition flex flex-col items-center text-center gap-2"
          >
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700">
              {{ student.first_name.charAt(0) }}{{ student.last_name.charAt(0) }}
            </div>
            <span class="text-xs font-semibold text-gray-900 line-clamp-2">{{ student.first_name }} {{ student.last_name }}</span>
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by student name..."
              class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <select
            v-model="selectedCourse"
            class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Courses</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>

      <!-- Marks Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Course</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">CA Score</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Exam</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Final Score</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Grade</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="m in filteredMarks" :key="m.id" class="hover:bg-blue-50/30">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ m.student.first_name }} {{ m.student.last_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ m.course.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                  {{ m.class_test_1 ? m.class_test_1.toFixed(1) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                  {{ m.exam_score ? m.exam_score.toFixed(1) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold">
                  {{ m.final_score ? m.final_score.toFixed(2) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span v-if="m.letter_grade" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900">
                    {{ m.letter_grade }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', statusColors[m.status]]">
                    {{ m.status.charAt(0).toUpperCase() + m.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button @click="deleteMarks(m.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredMarks.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  No marks found. Use the form above to enter marks for a class and course.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
