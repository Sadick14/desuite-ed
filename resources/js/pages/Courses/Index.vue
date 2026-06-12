<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { BookOpen, Plus, Edit, Trash2, GraduationCap } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  courses: any;
}>();

const modalOpen = ref(false);
const editingCourse = ref<any>(null);

const form = useForm({
  name: '',
  code: '',
  description: '',
  level: '',
});

const openCreateModal = () => {
  editingCourse.value = null;
  form.reset();
  modalOpen.value = true;
};

const openEditModal = (course: any) => {
  editingCourse.value = course;
  form.name = course.name;
  form.code = course.code;
  form.description = course.description;
  form.level = course.level;
  modalOpen.value = true;
};

const save = () => {
  if (editingCourse.value) {
    form.put(`/courses/${editingCourse.value.id}`, {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  } else {
    form.post('/courses', {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  }
};

const deleteCourse = (course: any) => {
  if (confirm('Are you sure you want to delete this course?')) {
    form.delete(`/courses/${course.id}`);
  }
};
</script>

<template>
  <Head title="Courses" />
  
  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Courses</h1>
          <p class="text-sm text-gray-600 mt-1">Global course pool - assign courses to classes during class creation</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl font-bold text-sm shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Add Course
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-amber-100">
            <thead class="bg-amber-50/70">
              <tr>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Level</th>
                <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-amber-50 bg-white">
              <tr v-for="course in courses.data" :key="course.id" class="hover:bg-amber-50/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <BookOpen class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-medium text-gray-900">{{ course.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ course.code }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ course.level || '-' }}</td>
                <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                  <button @click="openEditModal(course)" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold border border-amber-200 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition-colors">
                    <Edit class="w-3 h-3" />
                    Edit
                  </button>
                  <button @click="deleteCourse(course)" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold border border-red-200 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
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
            Showing {{ courses.from }} to {{ courses.to }} of {{ courses.total }} courses
          </p>
        </div>
      </div>
      
      <!-- Modal for Add/Edit Course -->
      <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="p-6 border-b border-amber-100">
            <h3 class="text-xl font-bold text-gray-900">{{ editingCourse ? 'Edit Course' : 'Add Course' }}</h3>
          </div>
          
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Course Name</label>
              <input v-model="form.name" type="text" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Code</label>
                <input v-model="form.code" type="text" class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Level (Optional)</label>
                <input v-model="form.level" type="text" placeholder="e.g., JHS, Primary, etc." class="w-full border border-amber-200 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-lime-400 focus:border-lime-400" />
              </div>
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
