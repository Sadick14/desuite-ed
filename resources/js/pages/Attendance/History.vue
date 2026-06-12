<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Calendar, Filter, Trash2, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { formatDate } from '@/utils/format';

interface AttendanceRecord {
  id: number;
  student_id: number;
  school_class_id: number;
  attendance_date: string;
  status: 'present' | 'absent' | 'excused' | 'late';
  notes: string | null;
  student: {
    first_name: string;
    last_name: string;
    student_id: string;
  };
  schoolClass: {
    name: string;
  };
}

interface SchoolClass {
  id: number;
  name: string;
}

const props = defineProps<{
  attendance: {
    data: AttendanceRecord[];
    links: any;
    meta: any;
  };
  classes: SchoolClass[];
  activeTerm: any;
  filters: Record<string, any>;
}>();

const search = ref('');
const classFilter = ref(props.filters.class_id || '');
const studentFilter = ref(props.filters.student_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
  const filters: Record<string, any> = {};

  if (classFilter.value) filters.class_id = classFilter.value;
  if (studentFilter.value) filters.student_id = studentFilter.value;
  if (startDate.value) filters.start_date = startDate.value;
  if (endDate.value) filters.end_date = endDate.value;
  if (statusFilter.value) filters.status = statusFilter.value;

  router.get(route('attendance.history'), filters, { replace: true });
};

const clearFilters = () => {
  classFilter.value = '';
  studentFilter.value = '';
  startDate.value = '';
  endDate.value = '';
  statusFilter.value = '';
  router.get(route('attendance.history'));
};

const deleteAttendance = (id: number) => {
  if (confirm('Are you sure you want to delete this attendance record?')) {
    router.delete(route('attendance.destroy', { attendance: id }));
  }
};

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    present: 'bg-green-100 text-green-900 border-green-200',
    absent: 'bg-red-100 text-red-900 border-red-200',
    late: 'bg-yellow-100 text-yellow-900 border-yellow-200',
    excused: 'bg-blue-100 text-blue-900 border-blue-200',
  };
  return classes[status] || classes.absent;
};
</script>

<template>
  <Head title="Attendance History" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-blue-100 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Attendance History</h1>
        <p class="text-sm text-gray-600 mt-1">View and manage past attendance records</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <div class="space-y-4">
          <div class="flex items-center gap-2 mb-4">
            <Filter class="w-4 h-4 text-gray-600" />
            <h3 class="font-bold text-gray-900">Filters</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
              <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Class</label>
              <select
                v-model="classFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">All Classes</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                  {{ cls.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Start Date</label>
              <input
                v-model="startDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">End Date</label>
              <input
                v-model="endDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Status</label>
              <select
                v-model="statusFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">All Statuses</option>
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
                <option value="excused">Excused</option>
              </select>
            </div>

            <div class="flex gap-2 items-end">
              <button
                @click="applyFilters"
                class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition"
              >
                Apply
              </button>
              <button
                v-if="classFilter || startDate || endDate || statusFilter"
                @click="clearFilters"
                class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg transition"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-blue-50 border-b border-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Class</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="record in props.attendance.data" :key="record.id" class="hover:bg-blue-50/30 transition">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900">{{ record.student?.first_name }} {{ record.student?.last_name }}</p>
                    <p class="text-xs text-gray-500">{{ record.student?.student_id }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ record.schoolClass?.name || '-' }}</td>
                <td class="px-6 py-4 text-gray-600">{{ formatDate(record.attendance_date) }}</td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadgeClass(record.status)]">
                    {{ record.status.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ record.notes || '-' }}</td>
                <td class="px-6 py-4 text-right">
                  <button
                    @click="deleteAttendance(record.id)"
                    class="text-red-600 hover:text-red-800 transition"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="props.attendance.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  No attendance records found. Try adjusting filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
