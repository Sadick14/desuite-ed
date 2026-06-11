<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Download, ChevronLeft, CheckCircle2, XCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    student: any;
    terms: any[];
    academicYears: any[];
    grades: any[];
    selectedTermId: number | null;
    selectedAcademicYearId: number | null;
    summary: any;
}>();

const selectedTermId = ref(props.selectedTermId);
const selectedAcademicYearId = ref(props.selectedAcademicYearId);

const handleFilterChange = () => {
    window.location.href = route('reports.show', {
        student: props.student,
        term_id: selectedTermId.value,
        academic_year_id: selectedAcademicYearId.value,
    });
};

const downloadReport = () => {
    window.location.href = route('reports.download', {
        student: props.student,
        term_id: selectedTermId.value,
        academic_year_id: selectedAcademicYearId.value,
    });
};
</script>

<template>
    <Head :title="`Report - ${student.first_name} ${student.last_name}`" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <div class="flex items-center gap-3 border-b border-amber-100/60 pb-5">
                <Link href="/reports" class="p-2 bg-white border border-amber-200 rounded-lg hover:bg-amber-50">
                    <ChevronLeft class="w-4 h-4 text-gray-700" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">Report Card</h1>
                    <p class="text-gray-500">{{ student.first_name }} {{ student.last_name }}</p>
                </div>
                <button 
                    @click="downloadReport" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm transition"
                >
                    <Download class="w-4 h-4" />
                    Download PDF
                </button>
            </div>
            
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-1.5">Academic Year</label>
                        <select 
                            v-model="selectedAcademicYearId" 
                            @change="handleFilterChange" 
                            class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm"
                        >
                            <option value="">All Years</option>
                            <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                {{ year.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-1.5">Term</label>
                        <select 
                            v-model="selectedTermId" 
                            @change="handleFilterChange" 
                            class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm"
                        >
                            <option value="">All Terms</option>
                            <option v-for="term in terms" :key="term.id" :value="term.id">
                                {{ term.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Student Info</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium">{{ student.first_name }} {{ student.last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Student ID</span>
                            <span class="font-medium">{{ student.student_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Class</span>
                            <span class="font-medium">{{ student.schoolClass?.name || '-' }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-amber-50 rounded-2xl border border-amber-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Summary</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center">
                            <p class="text-2xl font-black text-amber-700">{{ summary.percentage }}%</p>
                            <p class="text-gray-600">Percentage</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-gray-900">{{ summary.averageScore }}</p>
                            <p class="text-gray-600">Average</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-green-700">{{ summary.passedSubjects }}</p>
                            <p class="text-gray-600">Passed</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-red-700">{{ summary.failedSubjects }}</p>
                            <p class="text-gray-600">Failed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                <div class="bg-amber-50/70 px-6 py-4 border-b border-amber-100">
                    <h3 class="font-bold text-gray-900">Grades</h3>
                </div>
                <div v-if="grades.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100">
                        <thead class="bg-amber-50/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Exam</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Percentage</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Grade</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100">
                            <tr v-for="grade in grades" :key="grade.id" class="hover:bg-amber-50/30">
                                <td class="px-6 py-4 text-sm font-medium">{{ grade.exam.course.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ grade.exam.name }}</td>
                                <td class="px-6 py-4 text-sm font-bold">{{ grade.score }} / {{ grade.exam.max_score }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-amber-700">{{ grade.percentage }}%</td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-bold',
                                            grade.letter_grade === 'A+' || grade.letter_grade === 'A' ? 'bg-lime-100 text-lime-800' :
                                            grade.letter_grade === 'B' ? 'bg-emerald-100 text-emerald-800' :
                                            grade.letter_grade === 'C' ? 'bg-yellow-100 text-yellow-800' :
                                            grade.letter_grade === 'D' ? 'bg-orange-100 text-orange-800' :
                                            'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ grade.letter_grade }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span 
                                        :class="[
                                            'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold',
                                            grade.is_passing 
                                                ? 'bg-green-100 text-green-800 border border-green-200' 
                                                : 'bg-red-100 text-red-800 border border-red-200'
                                        ]"
                                    >
                                        <CheckCircle2 v-if="grade.is_passing" class="w-3 h-3" />
                                        <XCircle v-else class="w-3 h-3" />
                                        {{ grade.is_passing ? 'PASS' : 'FAIL' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ grade.remarks || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <FileText class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                    <p class="text-gray-500">No grades available for selected period</p>
                </div>
            </div>
        </div>
    </div>
</template>
