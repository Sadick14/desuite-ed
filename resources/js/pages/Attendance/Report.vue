<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface AttendanceStat {
  student_id: number;
  student_name: string;
  student_number: string;
  present: number;
  absent: number;
  late: number;
  excused: number;
  total_days: number;
  attendance_percentage: number;
}

interface SchoolClass {
  id: number;
  name: string;
}

const props = defineProps<{
  classes: SchoolClass[];
  selectedClass: SchoolClass | null;
  selectedTerm: any;
  terms: any[];
  activeTerm: any;
  attendanceStats: AttendanceStat[];
}>();

const selectedClassId = ref(props.selectedClass?.id || '');
const selectedTermId = ref(props.selectedTerm?.id || '');

const generateReport = () => {
  router.get(route('attendance.report'), {
    class_id: selectedClassId.value,
    term_id: selectedTermId.value,
  });
};

const getPercentageColor = (percentage: number) => {
  if (percentage >= 90) return 'text-green-600';
  if (percentage >= 75) return 'text-blue-600';
  if (percentage >= 60) return 'text-yellow-600';
  return 'text-red-600';
};

const getPercentageBg = (percentage: number) => {
  if (percentage >= 90) return 'bg-green-50';
  if (percentage >= 75) return 'bg-blue-50';
  if (percentage >= 60) return 'bg-yellow-50';
  return 'bg-red-50';
};

const averageAttendance = computed(() => {
  if (props.attendanceStats.length === 0) return 0;
  const total = props.attendanceStats.reduce((sum, s) => sum + s.attendance_percentage, 0);
  return (total / props.attendanceStats.length).toFixed(2);
});

const studentsSorted = computed(() => {
  return [...props.attendanceStats].sort((a, b) => b.attendance_percentage - a.attendance_percentage);
});
</script>

<template>
  <Head title="Attendance Report" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-blue-100 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Attendance Report</h1>
        <p class="text-sm text-gray-600 mt-1">Detailed attendance statistics by student</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Select Class</label>
            <select
              v-model="selectedClassId"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">-- Select a Class --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Select Term</label>
            <select
              v-model="selectedTermId"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="term in terms" :key="term.id" :value="term.id">
                {{ term.name }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="generateReport"
              class="w-full px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-sm transition"
            >
              Generate Report
            </button>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div v-if="selectedClass && attendanceStats.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Students</p>
          <p class="text-3xl font-bold text-gray-900 mt-2">{{ attendanceStats.length }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Class Average</p>
          <p :class="['text-3xl font-bold mt-2', getPercentageColor(Number(averageAttendance))]">
            {{ averageAttendance }}%
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Present</p>
          <p class="text-3xl font-bold text-green-600 mt-2">
            {{ attendanceStats.reduce((sum, s) => sum + s.present, 0) }}
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Absent</p>
          <p class="text-3xl font-bold text-red-600 mt-2">
            {{ attendanceStats.reduce((sum, s) => sum + s.absent, 0) }}
          </p>
        </div>
      </div>

      <!-- Attendance Table -->
      <div v-if="selectedClass && attendanceStats.length > 0" class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-blue-100 bg-blue-50">
          <h2 class="text-lg font-bold text-gray-900">{{ selectedClass.name }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ selectedTerm.name }}</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Present</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Absent</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Late</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Excused</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Total Days</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Attendance %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="stat in studentsSorted" :key="stat.student_id" class="hover:bg-blue-50/30 transition">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900">{{ stat.student_name }}</p>
                    <p class="text-xs text-gray-500">{{ stat.student_number }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-900">
                    {{ stat.present }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-900">
                    {{ stat.absent }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-900">
                    {{ stat.late }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-900">
                    {{ stat.excused }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center font-semibold text-gray-900">{{ stat.total_days }}</td>
                <td class="px-6 py-4 text-right">
                  <div :class="['inline-flex items-center px-4 py-2 rounded-lg font-bold', getPercentageBg(stat.attendance_percentage)]">
                    <span :class="getPercentageColor(stat.attendance_percentage)">
                      {{ stat.attendance_percentage }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-2xl border border-blue-100 p-12 text-center shadow-sm">
        <BarChart3 class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-600 font-medium">Select a class to view attendance report</p>
      </div>
    </div>
  </div>
</template>
