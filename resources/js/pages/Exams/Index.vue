<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Edit, Trash2, Calendar } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  exams: any;
  courses: any[];
  terms: any[];
  academicYears: any[];
}>();

const modalOpen = ref(false);
const editingExam = ref<any>(null);

const form = useForm({
    name: '',
    exam_type: 'general',
    weight: 100,
    course_id: null as number | null,
    term_id: null as number | null,
    academic_year_id: null as number | null,
    max_score: 100,
    pass_score: 50,
    date: '',
    description: '',
});

const openCreateModal = () => {
  editingExam.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (exam: any) => {
  editingExam.value = exam;
  form.name = exam.name;
  form.exam_type = exam.exam_type || 'general';
  form.weight = exam.weight || 100;
  form.course_id = exam.course_id;
  form.term_id = exam.term_id;
  form.academic_year_id = exam.academic_year_id;
  form.max_score = exam.max_score;
  form.pass_score = exam.pass_score;
  form.date = exam.date;
  form.description = exam.description;
  modalOpen.value = true;
};

const save = () => {
  if (editingExam.value) {
    form.put(route('exams.update', editingExam.value.id), {
      onSuccess: () => { modalOpen.value = false; }
    });
  } else {
    form.post(route('exams.store'), {
      onSuccess: () => { modalOpen.value = false; }
    });
  }
};

const deleteExam = (exam: any) => {
  if (confirm('Are you sure you want to delete this exam?')) {
    form.delete(route('exams.destroy', exam.id));
  }
};
</script>

<template>
  <Head title="Exams" />
  
  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div class="flex items-center gap-3">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Exams</h1>
        </div>
        <button 
          @click="openCreateModal" 
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Exam
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70">
              <tr>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Exam</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Weight (%)</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Course</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Max Score</th>
                <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="exam in exams.data" :key="exam.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <FileText class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-medium text-gray-900">{{ exam.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    {{ exam.exam_type }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ exam.weight }}%</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ exam.course?.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ exam.term?.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ exam.date }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ exam.max_score }}</td>
                <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                  <button @click="openEditModal(exam)" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold border border-amber-200 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition-colors">
                    <Edit class="w-3 h-3" />
                    Edit
                  </button>
                  <button @click="deleteExam(exam)" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold border border-red-200 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                    <Trash2 class="w-3 h-3" />
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="px-6 py-4 border-t border-amber-100 bg-amber-50/30">
          <p class="text-sm text-gray-500">
            Showing {{ exams.from }} to {{ exams.to }} of {{ exams.total }} exams
          </p>
        </div>
      </div>
      
      <!-- Modal for Add/Edit Exam -->
      <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-amber-100">
            <h3 class="text-xl font-bold text-gray-900">{{ editingExam ? 'Edit Exam' : 'Add Exam' }}</h3>
          </div>
          
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Exam Name</label>
              <input v-model="form.name" type="text" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
            </div>
            
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Exam Type</label>
              <select v-model="form.exam_type" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400">
                <option value="class_assessment">Class Assessment</option>
                <option value="mid_term">Mid-Term Exam</option>
                <option value="final">Final Exam</option>
                <option value="quiz">Quiz</option>
                <option value="general">General</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Weight (%)</label>
              <input v-model.number="form.weight" type="number" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" min="0" max="100" />
            </div>
            
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Course</label>
              <select v-model="form.course_id" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400">
                <option value="">Select Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
              </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Academic Year</label>
                <select v-model="form.academic_year_id" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400">
                  <option value="">Select Year</option>
                  <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Term</label>
                <select v-model="form.term_id" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400">
                  <option value="">Select Term</option>
                  <option v-for="term in terms" :key="term.id" :value="term.id">{{ term.name }}</option>
                </select>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Max Score</label>
                <input v-model.number="form.max_score" type="number" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Pass Score</label>
                <input v-model.number="form.pass_score" type="number" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Date</label>
              <input v-model="form.date" type="date" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
            </div>
            
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400"></textarea>
            </div>
          </div>
          
          <div class="p-6 border-t border-amber-100 flex justify-end gap-3">
            <button @click="modalOpen = false" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50">Cancel</button>
            <button @click="save" :disabled="form.processing" class="px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm">
              {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
