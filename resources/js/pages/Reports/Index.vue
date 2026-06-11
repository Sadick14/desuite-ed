<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { FileText, Users } from 'lucide-vue-next';

const props = defineProps<{
    academicYears: any[];
    classes: any[];
    students: any[];
    selectedClassId: number | null;
    selectedTermId: number | null;
}>();

const selectedClassId = ref(props.selectedClassId);

const handleClassChange = () => {
    window.location.href = route('reports.index', {
        class_id: selectedClassId.value,
    });
};
</script>

<template>
    <Head title="Reports" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <div class="border-b border-amber-100/60 pb-5">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <FileText class="text-amber-600" />
                    Report Cards
                </h1>
                <p class="text-gray-500 mt-1">Generate and download student report cards</p>
            </div>
            
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-1.5">Select Class</label>
                        <select 
                            v-model="selectedClassId" 
                            @change="handleClassChange" 
                            class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm"
                        >
                            <option value="" disabled>Choose a class...</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div v-if="selectedClassId && students.length > 0" class="space-y-3">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                    <div class="bg-amber-50/70 px-6 py-4 border-b border-amber-100">
                        <h3 class="font-bold text-gray-900">{{ students.length }} Students</h3>
                    </div>
                    <div class="divide-y divide-amber-100">
                        <div 
                            v-for="student in students" 
                            :key="student.id" 
                            class="px-6 py-4 flex items-center justify-between hover:bg-amber-50/30"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                                    <Users class="w-5 h-5 text-amber-700" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</p>
                                    <p class="text-sm text-gray-500">{{ student.student_id }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link 
                                    :href="route('reports.show', student)" 
                                    class="px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-sm font-bold hover:bg-amber-100 transition"
                                >
                                    View Report
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else-if="selectedClassId && students.length === 0" class="bg-white rounded-2xl border border-amber-100 p-8 text-center">
                <Users class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                <p class="text-gray-500">No students found in this class</p>
            </div>
            
        </div>
    </div>
</template>
