<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, TrendingUp, PieChart } from 'lucide-vue-next';
import { ref } from 'vue';

interface OverallStats {
  total_present: number;
  total_absent: number;
  total_late: number;
  total_excused: number;
  average_attendance: number;
}

interface DailyStat {
  date: string;
  present: number;
  absent: number;
  late: number;
  excused: number;
}

interface ClassStat {
  class: string;
  present: number;
  absent: number;
  late: number;
  excused: number;
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
  overallStats: OverallStats;
  dailyStats: DailyStat[];
  classStats: ClassStat[];
}>();

const selectedClassId = ref(props.selectedClass?.id || '');
const selectedTermId = ref(props.selectedTerm?.id || '');

const generateAnalytics = () => {
  router.get(route('attendance.analytics'), {
    class_id: selectedClassId.value,
    term_id: selectedTermId.value,
  });
};

const getTotal = (key: keyof OverallStats) => {
  if (key === 'average_attendance') return 0;
  return (props.overallStats[key] as number) || 0;
};

const totalRecords = () => {
  return getTotal('total_present') + getTotal('total_absent') + getTotal('total_late') + getTotal('total_excused');
};

const getPercentage = (value: number) => {
  const total = totalRecords();
  return total > 0 ? ((value / total) * 100).toFixed(1) : 0;
};
</script>

<template>
  <Head title="Attendance Analytics" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-blue-100 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Attendance Analytics</h1>
        <p class="text-sm text-gray-600 mt-1">Overview of attendance trends and statistics</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Select Class (Optional)</label>
            <select
              v-model="selectedClassId"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Classes</option>
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
              @click="generateAnalytics"
              class="w-full px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-sm transition"
            >
              Generate Analytics
            </button>
          </div>
        </div>
      </div>

      <!-- Overall Stats -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Average Attendance</p>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ overallStats.average_attendance }}%</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Present</p>
          <p class="text-3xl font-bold text-green-600 mt-2">{{ overallStats.total_present }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ getPercentage(overallStats.total_present) }}%</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Absent</p>
          <p class="text-3xl font-bold text-red-600 mt-2">{{ overallStats.total_absent }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ getPercentage(overallStats.total_absent) }}%</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Late</p>
          <p class="text-3xl font-bold text-yellow-600 mt-2">{{ overallStats.total_late }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ getPercentage(overallStats.total_late) }}%</p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
          <p class="text-xs text-gray-500 uppercase font-bold">Total Excused</p>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ overallStats.total_excused }}</p>
          <p class="text-xs text-gray-600 mt-1">{{ getPercentage(overallStats.total_excused) }}%</p>
        </div>
      </div>

      <!-- Daily Trends -->
      <div v-if="dailyStats.length > 0" class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
          <TrendingUp class="w-5 h-5 text-blue-600" />
          Daily Attendance Trend
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-blue-50 border-b border-blue-100">
              <tr>
                <th class="px-4 py-3 text-left font-bold text-gray-700">Date</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Present</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Absent</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Late</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Excused</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="stat in dailyStats" :key="stat.date" class="hover:bg-blue-50/30 transition">
                <td class="px-4 py-3 font-medium text-gray-900">{{ new Date(stat.date).toLocaleDateString() }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-900">
                    {{ stat.present }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-900">
                    {{ stat.absent }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-900">
                    {{ stat.late }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                    {{ stat.excused }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Class Comparison -->
      <div v-if="classStats.length > 0 && !selectedClassId" class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
          <BarChart3 class="w-5 h-5 text-blue-600" />
          Attendance by Class
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-blue-50 border-b border-blue-100">
              <tr>
                <th class="px-4 py-3 text-left font-bold text-gray-700">Class</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Present</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Absent</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Late</th>
                <th class="px-4 py-3 text-center font-bold text-gray-700">Excused</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="stat in classStats" :key="stat.class" class="hover:bg-blue-50/30 transition">
                <td class="px-4 py-3 font-medium text-gray-900">{{ stat.class }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-900">
                    {{ stat.present }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-900">
                    {{ stat.absent }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-900">
                    {{ stat.late }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                    {{ stat.excused }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
