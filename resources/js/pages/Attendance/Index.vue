<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Calendar, Users, AlertCircle, CheckCircle2, Clock, XCircle, ChevronRight, ChevronLeft, MessageSquare } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    academicYears: any[];
    currentYear: any;
    terms: any[];
    currentTerm: any;
    classes: any[];
    students: any[];
    attendanceRecords: Record<string, any>;
    selectedClassId: number | null;
    selectedTermId: number | null;
    selectedDate: string;
}>();

const form = useForm({
    school_class_id: props.selectedClassId,
    term_id: props.selectedTermId,
    attendance_date: props.selectedDate,
    attendance: [] as Array<{ student_id: number; status: string; notes: string | null }>,
    send_sms_alerts: false,
});

const selectAllPresent = () => {
    form.attendance = props.students.map(student => ({
        student_id: student.id,
        status: 'present',
        notes: null,
    }));
};

const selectAllAbsent = () => {
    form.attendance = props.students.map(student => ({
        student_id: student.id,
        status: 'absent',
        notes: null,
    }));
};

const setAttendance = (studentId: number, status: string) => {
    const index = form.attendance.findIndex(r => r.student_id === studentId);
    if (index !== -1) {
        form.attendance[index].status = status;
    } else {
        form.attendance.push({ student_id: studentId, status, notes: null });
    }
};

const setNotes = (studentId: number, notes: string) => {
    const index = form.attendance.findIndex(r => r.student_id === studentId);
    if (index !== -1) {
        form.attendance[index].notes = notes;
    }
};

const getAttendanceStatus = (studentId: number) => {
    const record = form.attendance.find(r => r.student_id === studentId);
    if (record) {
        return record.status;
    }
    if (props.attendanceRecords[studentId]) {
        return props.attendanceRecords[studentId].status;
    }
    return null;
};

const getAttendanceNotes = (studentId: number) => {
    const record = form.attendance.find(r => r.student_id === studentId);
    if (record && record.notes) {
        return record.notes;
    }
    if (props.attendanceRecords[studentId]) {
        return props.attendanceRecords[studentId].notes;
    }
    return null;
};

const stats = computed(() => {
    return {
        present: props.students.filter(s => getAttendanceStatus(s.id) === 'present').length,
        absent: props.students.filter(s => getAttendanceStatus(s.id) === 'absent').length,
        excused: props.students.filter(s => getAttendanceStatus(s.id) === 'excused').length,
        late: props.students.filter(s => getAttendanceStatus(s.id) === 'late').length,
    };
});

const goToPreviousDay = () => {
    const currentDate = new Date(form.attendance_date);
    currentDate.setDate(currentDate.getDate() - 1);
    updateDate(currentDate);
};

const goToNextDay = () => {
    const currentDate = new Date(form.attendance_date);
    currentDate.setDate(currentDate.getDate() + 1);
    updateDate(currentDate);
};

const updateDate = (date: Date) => {
    form.attendance_date = date.toISOString().split('T')[0];
    router.get(route('attendance.index'), {
        class_id: form.school_class_id,
        term_id: form.term_id,
        date: form.attendance_date,
    }, { preserveScroll: false });
};

const handleClassChange = () => {
    router.get(route('attendance.index'), {
        class_id: form.school_class_id,
        term_id: form.term_id,
        date: form.attendance_date,
    }, { preserveScroll: false });
};

const handleTermChange = () => {
    router.get(route('attendance.index'), {
        class_id: form.school_class_id,
        term_id: form.term_id,
        date: form.attendance_date,
    }, { preserveScroll: false });
};
</script>

<template>
    <Head title="Attendance Tracking" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Attendance Tracking</h1>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Academic Year</label>
                        <select class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm bg-amber-50/50" disabled>
                            <option>{{ currentYear?.name }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Term</label>
                        <select v-model="form.term_id" @change="handleTermChange" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm">
                            <option value="" disabled>Select Term</option>
                            <option v-for="term in terms" :key="term.id" :value="term.id">
                                {{ term.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Class</label>
                        <select v-model="form.school_class_id" @change="handleClassChange" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm">
                            <option value="" disabled>Select Class</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Date</label>
                        <div class="flex items-center gap-2">
                            <button @click="goToPreviousDay" class="p-2 border border-amber-200 rounded-lg hover:bg-amber-50 text-amber-700">
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <input type="date" v-model="form.attendance_date" @change="updateDate(new Date(($event.target as HTMLInputElement).value))" class="flex-1 border border-amber-200 rounded-xl px-3 py-2.5 text-sm" />
                            <button @click="goToNextDay" class="p-2 border border-amber-200 rounded-lg hover:bg-amber-50 text-amber-700">
                                <ChevronRight class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="selectedClassId && selectedTermId" class="space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-green-50 border border-green-100 rounded-xl p-4 flex items-center gap-3">
                        <CheckCircle2 class="w-6 h-6 text-green-600" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Present</p>
                            <p class="text-xl font-black text-gray-900">{{ stats.present }}</p>
                        </div>
                    </div>
                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 flex items-center gap-3">
                        <XCircle class="w-6 h-6 text-red-600" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Absent</p>
                            <p class="text-xl font-black text-gray-900">{{ stats.absent }}</p>
                        </div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 flex items-center gap-3">
                        <Clock class="w-6 h-6 text-yellow-600" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Late</p>
                            <p class="text-xl font-black text-gray-900">{{ stats.late }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex items-center gap-3">
                        <AlertCircle class="w-6 h-6 text-gray-600" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Excused</p>
                            <p class="text-xl font-black text-gray-900">{{ stats.excused }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="selectAllPresent" class="px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-bold">
                        Mark All Present
                    </button>
                    <button @click="selectAllAbsent" class="px-3 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-bold">
                        Mark All Absent
                    </button>
                </div>
                
                <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-amber-100">
                            <thead class="bg-amber-50/70">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50 bg-white">
                                <tr v-for="student in students" :key="student.id" class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <Users class="w-4 h-4 text-amber-500" />
                                            <span class="text-sm font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</span>
                                            <span class="text-xs text-gray-500">({{ student.student_id }})</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button @click="setAttendance(student.id, 'present')" :class="['px-2 py-1 text-xs font-bold rounded border', getAttendanceStatus(student.id) === 'present' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-white text-gray-600 border-gray-200']">
                                                Present
                                            </button>
                                            <button @click="setAttendance(student.id, 'late')" :class="['px-2 py-1 text-xs font-bold rounded border', getAttendanceStatus(student.id) === 'late' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-white text-gray-600 border-gray-200']">
                                                Late
                                            </button>
                                            <button @click="setAttendance(student.id, 'excused')" :class="['px-2 py-1 text-xs font-bold rounded border', getAttendanceStatus(student.id) === 'excused' ? 'bg-gray-100 text-gray-700 border-gray-200' : 'bg-white text-gray-600 border-gray-200']">
                                                Excused
                                            </button>
                                            <button @click="setAttendance(student.id, 'absent')" :class="['px-2 py-1 text-xs font-bold rounded border', getAttendanceStatus(student.id) === 'absent' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-white text-gray-600 border-gray-200']">
                                                Absent
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" :value="getAttendanceNotes(student.id)" @input="setNotes(student.id, ($event.target as HTMLInputElement).value)" class="px-2 py-1 border border-amber-200 rounded text-sm" placeholder="Notes..." />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-gray-500">
                                        <Link :href="route('students.show', student)" class="text-amber-700 hover:text-amber-900">
                                            View Student
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.send_sms_alerts" class="w-4 h-4 text-amber-700 border-gray-300 rounded focus:ring-amber-500" />
                            <span class="text-sm font-medium text-gray-700">
                                <MessageSquare class="w-4 h-4 inline mr-1" />
                                Send SMS alerts for absent students
                            </span>
                        </label>
                        
                        <button @click="form.post(route('attendance.store'))" :disabled="form.processing" class="px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            Save Attendance
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-else class="bg-white rounded-2xl border border-amber-100 p-8 text-center">
                <Users class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500 text-sm">Select a class and term to get started</p>
            </div>
        </div>
    </div>
</template>
