<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Users, FileText } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
  classes: any[];
  terms: any[];
  exams: any[];
  courses: any[];
  students: any[];
  grades: any;
  selectedClassId: number | null;
  selectedExamId: number | null;
}>();

const selectedClassId = ref(props.selectedClassId);
const selectedExamId = ref(props.selectedExamId);

const form = useForm({
  exam_id: props.selectedExamId,
  grades: {} as Record<string, { score: number | null; remarks: string | null }>,
});

const selectedExam = computed(() => {
  return props.exams.find(e => e.id === selectedExamId.value);
});

const handleFiltersChange = () => {
  router.get(route('grades.index'), {
    class_id: selectedClassId.value,
    exam_id: selectedExamId.value,
  }, { preserveState: true, preserveScroll: true });
};

const save = () => {
  form.exam_id = selectedExamId.value!;
  form.post(route('grades.store'));
};
</script>

<template>
  <Head title="Grades" />
  
  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div class="flex items-center gap-3">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Grades</h1>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-1.5">Class</label>
            <select v-model="selectedClassId" @change="handleFiltersChange" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm">
              <option value="" disabled>Select Class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-1.5">Exam</label>
            <select v-model="selectedExamId" @change="handleFiltersChange" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 text-sm" :disabled="!selectedClassId">
              <option value="" disabled>Select Exam</option>
              <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                {{ exam.name }} ({{ exam.exam_type }} - {{ exam.weight }}%) - {{ exam.course?.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="selectedClassId && selectedExamId" class="space-y-6">
        <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-5 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <FileText class="w-6 h-6 text-amber-600" />
            <div>
              <p class="font-bold text-gray-900">{{ selectedExam?.name }}</p>
              <p class="text-sm text-gray-500">
                {{ selectedExam?.exam_type }} • {{ selectedExam?.weight }}% weight • {{ selectedExam?.course?.name }} • Max Score: {{ selectedExam?.max_score }} • Pass: {{ selectedExam?.pass_score }}
              </p>
            </div>
          </div>
          <button @click="save" :disabled="form.processing" class="px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm">
            {{ form.processing ? 'Saving...' : 'Save Grades' }}
          </button>
        </div>
        
        <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-amber-100">
              <thead class="bg-amber-50/70">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Score</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Remarks</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-amber-50 bg-white">
                <tr v-for="student in students" :key="student.id" class="hover:bg-amber-50/30 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <Users class="w-4 h-4 text-amber-500" />
                      <span class="text-sm font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</span>
                      <span class="text-xs text-gray-500">({{ student.student_id }})</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      :value="grades[student.id]?.score"
                      @input="form.grades[student.id] = { ...form.grades[student.id], score: ($event.target as HTMLInputElement).value ? Number(($event.target as HTMLInputElement).value) : null, remarks: form.grades[student.id]?.remarks || grades[student.id]?.remarks || null }"
                      type="number"
                      :max="selectedExam?.max_score"
                      class="w-24 px-2 py-1.5 border border-amber-200 rounded-lg text-sm"
                    />
                    <span class="ml-2 text-sm text-gray-500">/ {{ selectedExam?.max_score }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span 
                      v-if="grades[student.id]?.score"
                      :class="[
                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold',
                        grades[student.id]?.score >= selectedExam?.pass_score 
                          ? 'bg-green-100 text-green-800 border border-green-200' 
                          : 'bg-red-100 text-red-800 border border-red-200'
                      ]"
                    >
                      <CheckCircle2 class="w-3 h-3" />
                      {{ grades[student.id]?.score >= selectedExam?.pass_score ? 'Pass' : 'Fail' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <input
                      :value="grades[student.id]?.remarks"
                      @input="form.grades[student.id] = { ...form.grades[student.id], remarks: ($event.target as HTMLInputElement).value, score: form.grades[student.id]?.score !== undefined ? form.grades[student.id]?.score : grades[student.id]?.score }"
                      type="text"
                      class="w-full max-w-xs px-2 py-1.5 border border-amber-200 rounded-lg text-sm"
                      placeholder="Add remarks..."
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
      </div>
      
      <div v-else class="bg-white rounded-2xl border border-amber-100 p-8 text-center">
        <FileText class="w-16 h-16 mx-auto text-gray-300 mb-4" />
        <p class="text-gray-500 text-sm">Select a class and exam to record grades</p>
      </div>
    </div>
  </div>
</template>
