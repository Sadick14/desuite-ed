<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  Plus,
  Search,
  Pencil,
  Trash2,
  CreditCard,
  Eye,
  X,
} from 'lucide-vue-next';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';
import StudentModal from './StudentModal.vue';
import ExportDropdown from '@/components/ExportDropdown.vue';

// Quick payment handler
const recordQuickPayment = (studentId: number) => {
  router.get('/payments', { student_id: studentId });
};

const props = defineProps<{
  students: {
    data: any[];
    links: any[];
  };
  classes: any[];
  selectedClassId?: number | null;
}>();

// UI STATE
const showModal = ref(false);
const editingStudent = ref<any | null>(null);
const search = ref('');
const selectedClassId = ref(props.selectedClassId);

const handleFiltersChange = () => {
  router.get('/students', { 
    search: search.value,
    class_id: selectedClassId.value
  }, {
    preserveState: true,
    replace: true,
  });
};

// Debounced search
watchDebounced(
  search,
  handleFiltersChange,
  { debounce: 300 }
);

// OPEN CREATE
const openCreate = () => {
  editingStudent.value = null;
  showModal.value = true;
};

// OPEN EDIT
const openEdit = (student: any) => {
  editingStudent.value = student;
  showModal.value = true;
};

// DELETE
const deleteStudent = (id: number) => {
  if (confirm('Delete this student? This action cannot be undone.')) {
    router.delete(`/students/${id}`);
  }
};

// Format date for display
const formatDate = (date: string | null) => {
  if (!date){ 
    return '—'; 
}

  return new Date(date).toLocaleDateString();
};

// Helper to clean pagination labels (remove HTML)
const cleanLabel = (label: string) => {
  return label.replace(/&laquo;|&raquo;|&lsaquo;|&rsaquo;/g, '').trim();
};
</script>

<template>
  <Head title="Students" />

  <!-- Full-bleed brand identity alignment framework -->
  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <!-- HEADER SECTION -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            Students
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage student records, registrations, and payments
          </p>
        </div>
        
        <div class="flex items-center gap-2">
          <ExportDropdown
            baseUrl="/exports/students"
            :filters="{ search: search }"
            label="Export Students"
          />
          <button
            @click="openCreate"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 cursor-pointer"
          >
            <Plus class="w-4 h-4 stroke-[2.5]" />
            Register Student
          </button>
        </div>
      </div>

      <!-- SEARCH & FILTERS SECTION -->
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1 shadow-sm rounded-lg">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search class="h-4 w-4 text-gray-400" />
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Search by name, ID, parent, or phone..."
            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
          />
          <div v-if="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button @click="search = ''" class="text-gray-400 hover:text-gray-600 transition">
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>
        <div class="sm:w-64">
          <select 
            v-model="selectedClassId" 
            @change="handleFiltersChange"
            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
          >
            <option :value="null">All Classes</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
          </select>
        </div>
      </div>

      <!-- DESKTOP TABLE (Clean Floating Sheet Concept) -->
      <div class="hidden md:block border border-amber-100 rounded-xl overflow-hidden bg-white shadow-xl shadow-amber-900/[0.02] overflow-x-auto">
        <table class="min-w-full divide-y divide-amber-100">
          <!-- Swapped pure indigo background for subtle warm header styling -->
          <thead class="bg-amber-50/70 backdrop-blur-sm">
            <tr>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Student
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Class
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Parent / Guardian
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Phone
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Date of Birth
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Admission Date
              </th>
              <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-amber-50 bg-white">
            <tr v-for="student in students.data" :key="student.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center">
                  <!-- Custom Brand Avatar Fill -->
                  <div class="flex-shrink-0 h-9 w-9 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200/60 flex items-center justify-center border border-amber-200/40">
                    <span class="text-amber-900 font-bold text-xs uppercase tracking-wider">
                      {{ student.first_name?.charAt(0) }}{{ student.last_name?.charAt(0) }}
                    </span>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-semibold text-gray-900">
                      {{ student.first_name }} {{ student.last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      ID: {{ student.student_id }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/30">
                  {{ student.class?.name || '—' }}
                </span>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-sm font-medium text-gray-800">
                {{ student.parent_name || '—' }}
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-600">
                {{ student.parent_phone || '—' }}
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-600">
                {{ formatDate(student.date_of_birth) }}
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-600">
                {{ formatDate(student.admission_date) }}
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <a
                  :href="`/students/${student.id}`"
                  class="inline-flex items-center gap-1 text-gray-600 hover:text-indigo-600 transition-colors"
                  title="View student details"
                >
                  <Eye class="w-4 h-4" />
                  <span class="hidden sm:inline font-semibold text-xs">View</span>
                </a>
                <button
                  @click="recordQuickPayment(student.id)"
                  class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 transition-colors"
                  title="Record payment for this student"
                >
                  <CreditCard class="w-4 h-4" />
                  <span class="hidden sm:inline font-semibold text-xs">Payment</span>
                </button>
                <button
                  @click="openEdit(student)"
                  class="text-green-600 hover:text-green-800 transition-colors"
                >
                  <Pencil class="w-4 h-4" />
                </button>
                <button
                  @click="deleteStudent(student.id)"
                  class="text-red-500 hover:text-red-700 transition-colors"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="students.data.length === 0">
              <td colspan="7" class="px-6 py-16 text-center text-gray-500 font-medium">
                No students found. Try adjusting your search or register a new student.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- MOBILE CARD VIEW (Optimized with Floating Glass aesthetic) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
        <div
          v-for="student in students.data"
          :key="student.id"
          class="bg-white rounded-xl border border-amber-100 p-4 shadow-md shadow-amber-900/[0.01] hover:shadow-lg transition-all"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200/50 flex items-center justify-center border border-amber-200/30">
                <span class="text-amber-900 font-bold text-sm">
                  {{ student.first_name?.charAt(0) }}{{ student.last_name?.charAt(0) }}
                </span>
              </div>
              <div>
                <h3 class="font-bold text-gray-900">
                  {{ student.first_name }} {{ student.last_name }}
                </h3>
                <p class="text-xs text-gray-500 font-medium">ID: {{ student.student_id }}</p>
              </div>
            </div>
            <div class="flex gap-2">
              <button @click="openEdit(student)" class="p-1 text-green-600 hover:text-green-800 transition-colors">
                <Pencil class="w-4 h-4" />
              </button>
              <button @click="deleteStudent(student.id)" class="p-1 text-red-500 hover:text-red-700 transition-colors">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="mt-4 space-y-1.5 text-sm border-t border-amber-50 pt-3">
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">Class:</span>
              <span class="font-semibold text-gray-900">{{ student.class?.name || '—' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">Parent:</span>
              <span class="font-semibold text-gray-800">{{ student.parent_name || '—' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">Phone:</span>
              <span class="font-mono text-gray-600 text-xs">{{ student.parent_phone || '—' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">DOB:</span>
              <span class="text-gray-600">{{ formatDate(student.date_of_birth) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">Admission:</span>
              <span class="text-gray-600">{{ formatDate(student.admission_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 font-medium">Gender:</span>
              <span class="font-medium text-gray-800 capitalize">{{ student.gender || '—' }}</span>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-amber-50 flex items-center justify-between">
            <a
              :href="`/students/${student.id}`"
              class="inline-flex items-center gap-1 text-xs font-bold text-gray-600 hover:text-indigo-600 transition-colors"
            >
              <Eye class="w-4 h-4" />
              View Folder
            </a>
            <button
              @click="recordQuickPayment(student.id)"
              class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
            >
              <CreditCard class="w-4 h-4" />
              Record Payment
            </button>
          </div>
        </div>
        <div v-if="students.data.length === 0" class="col-span-full text-center py-12 text-gray-500 font-medium">
          No students found.
        </div>
      </div>

      <!-- PAGINATION SECTION -->
      <div v-if="students.links && students.links.length > 3" class="flex justify-center mt-8">
        <nav class="inline-flex rounded-xl shadow-sm -space-x-px bg-white p-1 border border-amber-100" aria-label="Pagination">
          <template v-for="(link, idx) in students.links" :key="idx">
            <component
              :is="link.url ? 'a' : 'span'"
              :href="link.url"
              v-html="link.label"
              :class="[
                'relative inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-lg transition-all mx-0.5',
                link.active 
                  ? 'z-10 bg-lime-400 text-gray-900 border-transparent shadow-sm'
                  : 'text-gray-600 hover:bg-amber-50 border-transparent',
                !link.url && 'cursor-not-allowed opacity-40',
              ]"
              v-text="cleanLabel(link.label)"
            />
          </template>
        </nav>
      </div>

    </div>

    <!-- MODAL POPUP WRAPPER -->
    <StudentModal
      v-if="showModal"
      :student="editingStudent"
      :classes="classes"
      @close="showModal = false"
    />
  </div>
</template>

<style scoped>
/* Unified webkit layout reset configurations */
input focus {
  box-shadow: none;
}
</style>