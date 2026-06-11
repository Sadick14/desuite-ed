<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Clock, AlertCircle, Calendar, ChevronLeft } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    student: any;
    academicYears: any[];
    selectedYearId: number | null;
    attendanceRecords: any[];
    stats: { present: number; absent: number; excused: number; late: number };
    attendanceRate: number;
}>();

const selectedYear = ref(props.selectedYearId);
</script>

<template>
    <Head :title="`Attendance - ${student.first_name} ${student.last_name}`" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <div class="flex items-center gap-3 border-b border-amber-100/60 pb-5">
                <Link href="/students" class="p-2 bg-white border border-amber-200 rounded-lg hover:bg-amber-50">
                    <ChevronLeft class="w-4 h-4 text-gray-700" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ student.first_name }} {{ student.last_name }}</h1>
                    <p class="text-gray-500 text-sm">Attendance History</p>
                </div>
                <select v-model="selectedYear" @change="router.get(route('attendance.student', student), { academic_year_id: selectedYear })" class="px-3 py-2 border border-amber-200 rounded-lg">
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.name }}</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
                    <CheckCircle2 class="w-10 h-10 text-green-600 mx-auto mb-2" />
                    <p class="text-3xl font-black text-green-700">{{ stats.present }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Present</p>
                </div>
                
                <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
                    <XCircle class="w-10 h-10 text-red-600 mx-auto mb-2" />
                    <p class="text-3xl font-black text-red-700">{{ stats.absent }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Absent</p>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
                    <Clock class="w-10 h-10 text-yellow-600 mx-auto mb-2" />
                    <p class="text-3xl font-black text-yellow-700">{{ stats.late }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Late</p>
                </div>
                
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-center">
                    <AlertCircle class="w-10 h-10 text-gray-600 mx-auto mb-2" />
                    <p class="text-3xl font-black text-gray-700">{{ stats.excused }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Excused</p>
                </div>
                
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                    <div class="text-3xl font-black text-amber-700 mb-1">{{ attendanceRate }}%</div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Attendance</p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100">
                        <thead class="bg-amber-50/70">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50 bg-white">
                            <tr v-for="record in attendanceRecords" :key="record.id" class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ new Date(record.attendance_date).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="record.status === 'present'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                        <CheckCircle2 class="w-3 h-3 mr-1" />
                                        Present
                                    </span>
                                    <span v-else-if="record.status === 'absent'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                        <XCircle class="w-3 h-3 mr-1" />
                                        Absent
                                    </span>
                                    <span v-else-if="record.status === 'late'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <Clock class="w-3 h-3 mr-1" />
                                        Late
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                        <AlertCircle class="w-3 h-3 mr-1" />
                                        Excused
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ record.notes }}
                                </td>
                            </tr>
                            <tr v-if="attendanceRecords.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    <Calendar class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                    No attendance records for this academic year
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
