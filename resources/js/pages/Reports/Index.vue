<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Filter, Download } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
  academicYears: any[];
  activeYear?: any;
  currentTerm?: any;
  classes: any[];
  students: any[];
  selectedYearId: number | null;
  selectedTermId: number | null;
  selectedClassId: number | null;
}>();

const selectedYear = ref(props.selectedYearId || '');
const selectedTerm = ref(props.selectedTermId || '');
const selectedClass = ref(props.selectedClassId || '');

const termsForSelectedYear = computed(() => {
  if (!selectedYear.value) {
return [];
}

  const yearId = parseInt(selectedYear.value.toString());

  return props.academicYears.find((y: any) => y.id === yearId)?.terms || [];
});

watch(selectedYear, () => {
  if (termsForSelectedYear.value.length > 0) {
    selectedTerm.value = termsForSelectedYear.value[0].id;
  } else {
    selectedTerm.value = '';
  }
});

const currentClass = computed(() => {
  if (!selectedClass.value) {
return null;
}

  return props.classes.find((c: any) => c.id === parseInt(selectedClass.value.toString()));
});

const classStudents = computed(() => {
  const classId = selectedClass.value ? parseInt(selectedClass.value.toString()) : null;

  if (!classId) {
return [];
}

  return props.classes.find((c: any) => c.id === classId)?.students || [];
});

function getStudentAverageMarks(student: any) {
  if (!student.marks || student.marks.length === 0) {
    return { ca: '-', exam: '-', final: '-', grade: '-', status: 'No marks' };
  }

  const totalCA = student.marks.reduce((sum: number, m: any) => sum + (m.ca_total || 0), 0);
  const totalExam = student.marks.reduce((sum: number, m: any) => sum + (m.exam_score || 0), 0);
  const totalFinal = student.marks.reduce((sum: number, m: any) => sum + (m.final_score || 0), 0);

  const avgCA = (totalCA / student.marks.length).toFixed(1);
  const avgExam = (totalExam / student.marks.length).toFixed(1);
  const avgFinal = (totalFinal / student.marks.length).toFixed(1);

  const grades = student.marks.map((m: any) => m.letter_grade).filter((g: any) => g);
  const mostCommonGrade = grades.length > 0 ? grades[0] : '-';

  const passCount = student.marks.filter((m: any) => (m.final_score || 0) >= 50).length;
  const status = passCount === student.marks.length ? 'Pass' : passCount > 0 ? 'Mixed' : 'Fail';

  return {
    ca: avgCA,
    exam: avgExam,
    final: avgFinal,
    grade: mostCommonGrade,
    status
  };
}

function filterReports() {
  const params = new URLSearchParams();

  if (selectedYear.value) {
params.append('year_id', selectedYear.value.toString());
}

  if (selectedTerm.value) {
params.append('term_id', selectedTerm.value.toString());
}

  if (selectedClass.value) {
params.append('class_id', selectedClass.value.toString());
}

  router.visit(`/reports?${params.toString()}`);
}

function downloadClassReport() {
  if (!selectedClass.value) {
return;
}

  window.print();
}
</script>

<template>
  <Head title="Reports" />

  <div class="min-h-screen bg-gradient-to-b from-green-50 via-white to-green-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between border-b border-green-100/60 pb-5">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Student Reports</h1>
          <p class="text-sm text-gray-600 mt-1">Class-wise and individual student reports</p>
        </div>
        <button
          v-if="selectedClass"
          @click="downloadClassReport"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition"
        >
          <Download class="w-4 h-4" />
          Print Report
        </button>
      </div>

      <!-- Filter Section -->
      <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <!-- Academic Year -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Academic Year</label>
            <select
              v-model="selectedYear"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900"
            >
              <option value="">-- Select Year --</option>
              <option v-for="year in academicYears" :key="year.id" :value="year.id">
                {{ year.name }}
              </option>
            </select>
          </div>

          <!-- Term -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Term</label>
            <select
              v-model="selectedTerm"
              :disabled="!selectedYear"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 disabled:opacity-50"
            >
              <option value="">-- Select Term --</option>
              <option v-for="term in termsForSelectedYear" :key="term.id" :value="term.id">
                {{ term.name }}
              </option>
            </select>
          </div>

          <!-- Class -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Class</label>
            <select
              v-model="selectedClass"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900"
            >
              <option value="">-- Select Class --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }} ({{ cls.students?.length || 0 }} students)
              </option>
            </select>
          </div>

          <!-- Filter Button -->
          <button
            @click="filterReports"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition"
          >
            <Filter class="w-4 h-4" />
            Filter
          </button>
        </div>
      </div>

      <!-- Spreadsheet View -->
      <div v-if="selectedClass && classStudents.length > 0" class="bg-white rounded-2xl border border-green-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-green-100 text-sm">
            <thead class="bg-green-50/70 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left font-bold text-gray-700 uppercase bg-green-100/50">S/N</th>
                <th class="px-4 py-3 text-left font-bold text-gray-700 uppercase bg-green-100/50">Student Name</th>
                <th class="px-4 py-3 text-left font-bold text-gray-700 uppercase bg-green-100/50">Admission #</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase">CA</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase">Exam</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase">Final</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase">Grade</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-green-50">
              <tr v-for="(student, idx) in classStudents" :key="student.id" class="hover:bg-green-50/30 transition-colors">
                <td class="px-4 py-3 font-semibold text-gray-900">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">
                  {{ student.first_name }} {{ student.last_name }}
                </td>
                <td class="px-4 py-3 text-gray-600">{{ student.admission_number }}</td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ getStudentAverageMarks(student).ca }}</td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ getStudentAverageMarks(student).exam }}</td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ getStudentAverageMarks(student).final }}</td>
                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ getStudentAverageMarks(student).grade }}</td>
                <td class="px-4 py-3 text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                      getStudentAverageMarks(student).status === 'Pass'
                        ? 'bg-green-100 text-green-900'
                        : getStudentAverageMarks(student).status === 'No marks'
                          ? 'bg-yellow-100 text-yellow-900'
                          : 'bg-red-100 text-red-900'
                    ]"
                  >
                    {{ getStudentAverageMarks(student).status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-6 py-4 border-t border-green-100 bg-green-50/30">
          <p class="text-sm text-gray-600">
            Showing {{ classStudents.length }} student(s) in {{ currentClass?.name }}
          </p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-2xl border border-dashed border-green-200 p-12 text-center">
        <p class="text-gray-500 mb-4">Select academic year, term, and class to view the class report spreadsheet</p>
      </div>

    </div>
  </div>
</template>
