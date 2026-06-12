<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Calendar, AlertCircle, Save } from 'lucide-vue-next';
import { ref, computed } from 'vue';

type AttendanceStatus = 'present' | 'absent' | 'excused' | 'late';

interface Student {
  id: number;
  first_name: string;
  last_name: string;
  student_id: string;
  school_class_id: number;
}

interface SchoolClass {
  id: number;
  name: string;
  level: string;
  students?: Student[];
}

interface AttendanceRecord {
  id: number;
  student_id: number;
  school_class_id: number;
  status: AttendanceStatus;
  notes: string | null;
}

const props = defineProps<{
  classes: SchoolClass[];
  selectedClass: SchoolClass | null;
  activeTerm: any;
  attendanceRecords: AttendanceRecord[];
  attendanceDate: string;
}>();

const attendanceDate = ref(props.attendanceDate);
const selectedClassId = ref(props.selectedClass?.id || '');
const attendanceStatus = ref<Record<number, AttendanceStatus>>({});

// Initialize attendance status from records
const initializeStatus = () => {
  const records: Record<number, AttendanceStatus> = {};
  props.attendanceRecords.forEach(record => {
    records[record.student_id] = record.status;
  });
  attendanceStatus.value = records;
};

initializeStatus();

const selectedClass = computed(() =>
  props.classes.find(c => c.id === Number(selectedClassId.value))
);

const students = computed(() => selectedClass.value?.students || []);

const toggleStatus = (studentId: number) => {
  const statuses: AttendanceStatus[] = ['present', 'absent', 'late', 'excused'];
  const currentStatus = attendanceStatus.value[studentId];
  const currentIndex = statuses.indexOf(currentStatus);
  attendanceStatus.value[studentId] = statuses[(currentIndex + 1) % statuses.length];
};

const getStatusColor = (status: AttendanceStatus) => {
  const colors: Record<AttendanceStatus, string> = {
    present: 'bg-green-100 text-green-900 border-green-300',
    absent: 'bg-red-100 text-red-900 border-red-300',
    late: 'bg-yellow-100 text-yellow-900 border-yellow-300',
    excused: 'bg-blue-100 text-blue-900 border-blue-300',
  };
  return colors[status];
};

const getStatusIcon = (status: AttendanceStatus) => {
  const icons: Record<AttendanceStatus, string> = {
    present: '✓',
    absent: '✗',
    late: '⏰',
    excused: 'ℹ',
  };
  return icons[status];
};

const saveAttendance = () => {
  const attendances = students.value
    .filter(s => attendanceStatus.value[s.id])
    .map(s => ({
      student_id: s.id,
      status: attendanceStatus.value[s.id],
    }));

  if (attendances.length === 0) {
    alert('Please mark attendance for at least one student');
    return;
  }

  router.post(route('attendance.bulkStore'), {
    attendance_date: attendanceDate.value,
    class_id: selectedClassId.value,
    attendances,
  });
};
</script>

<template>
  <Head title="Attendance" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="border-b border-blue-100 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Student Attendance</h1>
        <p class="text-sm text-gray-600 mt-1">{{ activeTerm?.name }} - Mark attendance for students</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-blue-100 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row gap-4 items-end">
          <div class="flex-1">
            <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Select Class</label>
            <select
              v-model="selectedClassId"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            >
              <option value="">-- Select a Class --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }} ({{ cls.students?.length || 0 }} students)
              </option>
            </select>
          </div>

          <div class="flex-1">
            <label class="block text-xs uppercase font-bold text-gray-500 tracking-wider mb-2">Attendance Date</label>
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="attendanceDate"
                type="date"
                class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              />
            </div>
          </div>

          <button
            v-if="selectedClass"
            @click="saveAttendance"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-sm transition"
          >
            <Save class="w-4 h-4" />
            Save Attendance
          </button>
        </div>
      </div>

      <!-- Attendance Table -->
      <div v-if="selectedClass" class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-blue-100 bg-blue-50">
          <h2 class="text-lg font-bold text-gray-900">{{ selectedClass.name }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ students.length }} students</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-blue-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="student in students" :key="student.id" class="hover:bg-blue-50/30 transition">
                <td class="px-6 py-4">
                  <span class="font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ student.student_id }}</td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="toggleStatus(student.id)"
                    :class="[
                      'inline-flex items-center justify-center w-16 h-10 rounded-lg border-2 font-bold transition cursor-pointer',
                      getStatusColor(attendanceStatus[student.id] || 'absent')
                    ]"
                  >
                    {{ getStatusIcon(attendanceStatus[student.id] || 'absent') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-blue-100 flex items-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-100 border-2 border-green-300 rounded"></div>
            <span class="text-gray-600">Present</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-red-100 border-2 border-red-300 rounded"></div>
            <span class="text-gray-600">Absent</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-yellow-100 border-2 border-yellow-300 rounded"></div>
            <span class="text-gray-600">Late</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-blue-100 border-2 border-blue-300 rounded"></div>
            <span class="text-gray-600">Excused</span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-2xl border border-blue-100 p-12 text-center shadow-sm">
        <AlertCircle class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-600 font-medium">Select a class to mark attendance</p>
      </div>
    </div>
  </div>
</template>
