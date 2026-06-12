<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Download } from 'lucide-vue-next';
import { ref, computed } from 'vue';

type StudentMark = {
  id: number;
  term_id: number;
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
  remark: string | null;
  status: string;
  [key: string]: any;
};

const props = defineProps<{
  student: any;
  terms: any[];
  marks: StudentMark[];
  selectedTerm?: any;
}>();

const selectedTermId = ref(props.selectedTerm?.id || '');

const termMarks = computed(() => {
  if (!selectedTermId.value) {
return [];
}

  return props.marks.filter(m => m.term_id === Number(selectedTermId.value));
});

const summary = computed(() => {
  if (termMarks.value.length === 0) {
    return {
      totalCourses: 0,
      approvedCourses: 0,
      averageScore: 0,
      highestScore: 0,
      lowestScore: 0,
    };
  }

  const approved = termMarks.value.filter(m => m.status === 'approved');
  const scores = approved.map(m => m.final_score).filter(s => s !== null);

  return {
    totalCourses: termMarks.value.length,
    approvedCourses: approved.length,
    averageScore: scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(2) : 0,
    highestScore: scores.length > 0 ? Math.max(...scores).toFixed(2) : 0,
    lowestScore: scores.length > 0 ? Math.min(...scores).toFixed(2) : 0,
  };
});

const statusColors: Record<string, string> = {
  draft: 'bg-amber-100/80 text-amber-900 border-amber-200',
  submitted: 'bg-blue-100/80 text-blue-900 border-blue-200',
  approved: 'bg-green-100/80 text-green-900 border-green-200',
};

const gradeColors: Record<string, string> = {
  A: 'bg-green-100 text-green-900',
  B: 'bg-blue-100 text-blue-900',
  C: 'bg-yellow-100 text-yellow-900',
  D: 'bg-orange-100 text-orange-900',
  E: 'bg-red-100 text-red-900',
  F: 'bg-red-200 text-red-900',
};

function goBack() {
  router.visit('/student-marks');
}

function downloadReport() {
  const content = generateReportContent();
  const element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
  element.setAttribute('download', `${props.student.first_name}_${props.student.last_name}_grades.txt`);
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
}

function generateReportContent() {
  const term = props.terms.find(t => t.id === Number(selectedTermId.value));
  let content = `STUDENT GRADE REPORT\n`;
  content += `${'='.repeat(100)}\n\n`;
  content += `Student: ${props.student.first_name} ${props.student.last_name}\n`;
  content += `ID: ${props.student.student_id}\n`;
  content += `Class: ${props.student.class?.name}\n`;
  content += `Term: ${term?.name}\n`;
  content += `Date: ${new Date().toLocaleDateString()}\n\n`;

  content += `DETAILED COURSE GRADES\n`;
  content += `${'='.repeat(130)}\n`;
  content += `Course Name          | T1  | T2  | T3  | Asgn| CW  | Proj| CA/100| Exam/100| Final | Grade| Remark\n`;
  content += `${'-'.repeat(130)}\n`;

  termMarks.value.forEach(mark => {
    const courseName = (mark.course.name || '-').padEnd(20);
    const t1 = (mark.class_test_1?.toFixed(1) || '-').padEnd(4);
    const t2 = (mark.class_test_2?.toFixed(1) || '-').padEnd(4);
    const t3 = (mark.class_test_3?.toFixed(1) || '-').padEnd(4);
    const assign = (mark.assignment?.toFixed(1) || '-').padEnd(4);
    const cw = (mark.classwork?.toFixed(1) || '-').padEnd(4);
    const proj = (mark.project?.toFixed(1) || '-').padEnd(4);
    const ca = (mark.ca_total?.toFixed(1) || '-').padEnd(6);
    const exam = (mark.exam_score?.toFixed(1) || '-').padEnd(8);
    const final = (mark.final_score?.toFixed(2) || '-').padEnd(5);
    const grade = (mark.letter_grade || '-').padEnd(5);
    const remark = mark.remark || '-';

    content += `${courseName}| ${t1}| ${t2}| ${t3}| ${assign}| ${cw}| ${proj}| ${ca}| ${exam}| ${final}| ${grade}| ${remark}\n`;
  });

  content += `\n${'='.repeat(130)}\n`;
  content += `SUMMARY\n`;
  content += `${'-'.repeat(130)}\n`;
  content += `Total Courses:        ${summary.value.totalCourses}\n`;
  content += `Approved:             ${summary.value.approvedCourses}\n`;
  content += `Average Final Score:  ${summary.value.averageScore}\n`;
  content += `Highest Score:        ${summary.value.highestScore}\n`;
  content += `Lowest Score:         ${summary.value.lowestScore}\n`;
  content += `\nCA Structure (100 marks total):\n`;
  content += `Test 1: /10, Test 2: /10, Test 3: /10, Assignment: /20, Classwork: /30, Project: /20\n`;
  content += `\nLegend:\n`;
  content += `T1-T3 = Test 1-3 (/10 each), Asgn = Assignment (/20), CW = Classwork (/30), Proj = Project (/20)\n`;
  content += `CA/100 = Sum of all 6 CA components (0-100 score), Exam/100 = Exam Score (0-100)\n`;
  content += `Final = (CA/100 × CA_weight%) + (Exam/100 × Exam_weight%)\n`;

  return content;
}
</script>

<template>
  <Head title="Student Grades" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between gap-4 border-b border-blue-100/60 pb-5">
        <div class="flex items-center gap-3">
          <button @click="goBack" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <ArrowLeft class="w-5 h-5 text-gray-600" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ student.first_name }} {{ student.last_name }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ student.class?.name }} | ID: {{ student.student_id }}</p>
          </div>
        </div>
        <button
          @click="downloadReport"
          :disabled="!selectedTermId || termMarks.length === 0"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-400 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Download class="w-4 h-4" />
          Download Report
        </button>
      </div>

      <!-- Term Selector -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <label class="block text-sm font-semibold text-gray-900 mb-3">Select Term to View Grades</label>
        <select
          v-model="selectedTermId"
          class="w-full md:w-64 px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">-- Choose Term --</option>
          <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>

      <!-- Summary Stats -->
      <div v-if="selectedTermId && termMarks.length > 0" class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-semibold">Total Courses</p>
          <p class="text-2xl font-bold text-gray-900 mt-2">{{ summary.totalCourses }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-semibold">Approved</p>
          <p class="text-2xl font-bold text-green-600 mt-2">{{ summary.approvedCourses }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-semibold">Average Score</p>
          <p class="text-2xl font-bold text-blue-600 mt-2">{{ summary.averageScore }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-semibold">Highest</p>
          <p class="text-2xl font-bold text-green-600 mt-2">{{ summary.highestScore }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-semibold">Lowest</p>
          <p class="text-2xl font-bold text-red-600 mt-2">{{ summary.lowestScore }}</p>
        </div>
      </div>

      <!-- Courses Table -->
      <div v-if="selectedTermId" class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div v-if="termMarks.length === 0" class="p-12 text-center text-gray-500">
          <p>No grades recorded for this term yet.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100 text-sm">
            <thead class="bg-blue-50/70 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Course</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T1/10</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T2/10</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">T3/10</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">Asgn/20</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">CW/30</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase bg-amber-50">Proj/20</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-amber-800 uppercase bg-amber-100">CA /100</th>
                <th class="px-2 py-3 text-center text-xs font-bold text-blue-800 uppercase bg-blue-100">Exam /100</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Final</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Grade</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Remark</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="mark in termMarks" :key="mark.id" class="hover:bg-blue-50/30">
                <td class="px-4 py-3 font-semibold text-gray-900">
                  {{ mark.course.name }}
                </td>
                <!-- CA Components: Test1, Test2, Test3, Assignment, Classwork, Project (/100 total) -->
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.class_test_1 ? mark.class_test_1.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.class_test_2 ? mark.class_test_2.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.class_test_3 ? mark.class_test_3.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.assignment ? mark.assignment.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.classwork ? mark.classwork.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm bg-amber-50/50">
                  {{ mark.project ? mark.project.toFixed(1) : '-' }}
                </td>
                <td class="px-2 py-3 text-center text-sm font-bold text-amber-900 bg-amber-100/50">
                  {{ mark.ca_total ? mark.ca_total.toFixed(1) : '-' }}/100
                </td>
                <!-- Exam Score -->
                <td class="px-2 py-3 text-center text-sm font-bold text-blue-900 bg-blue-100/50">
                  {{ mark.exam_score ? mark.exam_score.toFixed(1) : '-' }}/100
                </td>
                <!-- Final Score -->
                <td class="px-4 py-3 text-center text-sm font-bold text-gray-900">
                  {{ mark.final_score ? mark.final_score.toFixed(2) : '-' }}
                </td>
                <!-- Grade -->
                <td class="px-4 py-3 text-center">
                  <span
                    v-if="mark.letter_grade"
                    :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-bold', gradeColors[mark.letter_grade]]"
                  >
                    {{ mark.letter_grade }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <!-- Remark -->
                <td class="px-4 py-3 text-sm text-gray-600">
                  {{ mark.remark || '-' }}
                </td>
                <!-- Status -->
                <td class="px-4 py-3 text-center">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', statusColors[mark.status]]">
                    {{ mark.status.charAt(0).toUpperCase() + mark.status.slice(1) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Legend -->
        <div class="bg-gray-50 px-6 py-4 border-t border-blue-100 text-xs text-gray-600 space-y-2">
          <div class="font-semibold text-gray-900">CA Structure (100 marks total):</div>
          <div class="grid grid-cols-2 md:grid-cols-6 gap-2 ml-4">
            <div>Test 1: /10</div>
            <div>Test 2: /10</div>
            <div>Test 3: /10</div>
            <div>Assignment: /20</div>
            <div>Classwork: /30</div>
            <div>Project: /20</div>
          </div>
          <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><span class="inline-block w-3 h-3 bg-amber-50 border border-amber-200 rounded mr-2"></span>CA Components (sum of 6)</div>
            <div><span class="inline-block w-3 h-3 bg-blue-100 border border-blue-200 rounded mr-2"></span>Exam Score (0-100)</div>
            <div><span class="font-semibold">CA /100</span> = Sum of all 6 CA components</div>
            <div><span class="font-semibold">Final</span> = (CA × CA_wt%) + (Exam × Exam_wt%)</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
