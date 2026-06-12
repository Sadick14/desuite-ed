<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  Plus,
  Trash2,
  Edit,
  Copy,
  X,
  Search,
  School,
  BookOpen,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

type Class = {
  id: number;
  name: string;
  level: string;
  level_label: string;
  courses: any[];
};

const props = defineProps<{
  classes: Class[];
}>();

// UI state
const search = ref('');

// Filter classes based on search
const filteredClasses = computed(() => {
  if (!search.value) {
return props.classes;
}

  const term = search.value.toLowerCase();

  return props.classes.filter(c =>
    c.name.toLowerCase().includes(term) ||
    c.level_label.toLowerCase().includes(term)
  );
});

// Stats
const totalClasses = computed(() => filteredClasses.value.length);
const courseCount = computed(() => {
  const courseSet = new Set();
  props.classes.forEach(c => {
    c.courses?.forEach((course: any) => {
      courseSet.add(course.id);
    });
  });

  return courseSet.size;
});

function duplicate(id: number) {
  router.post(`/classes/${id}/duplicate`);
}

function destroy(id: number) {
  if (confirm('Delete this class? This action cannot be undone.')) {
    router.delete(`/classes/${id}`);
  }
}

function clearSearch() {
  search.value = '';
}
</script>

<template>
  <Head title="Classes" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-blue-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Classes</h1>
          <p class="text-sm text-gray-600 mt-1">Manage school classes and their assigned courses</p>
        </div>
        <a
          href="/classes/create"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all"
        >
          <Plus class="w-4 h-4" />
          Create Class
        </a>
      </div>

      <!-- Search & Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <div class="relative shadow-sm rounded-lg">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by class name or level..."
              class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
            <button
              v-if="search"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 flex items-center gap-3">
          <School class="w-5 h-5 text-blue-500" />
          <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Classes</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalClasses }}</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-4 flex items-center gap-3">
          <BookOpen class="w-5 h-5 text-blue-600" />
          <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Courses</p>
            <p class="text-2xl font-bold text-gray-900">{{ courseCount }}</p>
          </div>
        </div>
      </div>

      <!-- Classes Table -->
      <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Class Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Level</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Assigned Courses</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
              <tr v-for="c in filteredClasses" :key="c.id" class="hover:bg-blue-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ c.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100/80 text-blue-900 border border-blue-200/30">
                    {{ c.level_label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div v-if="c.courses?.length" class="flex flex-wrap gap-1">
                    <span
                      v-for="course in c.courses.slice(0, 3)"
                      :key="course.id"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-100/60 text-amber-900 border border-amber-200/30"
                    >
                      {{ course.name }}
                    </span>
                    <span v-if="c.courses.length > 3" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                      +{{ c.courses.length - 3 }} more
                    </span>
                  </div>
                  <span v-else class="text-gray-400 italic">No courses assigned</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <a :href="`/classes/${c.id}/edit`" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                    <Edit class="w-4 h-4" />
                    <span class="text-xs font-medium">Edit</span>
                  </a>
                  <button @click="duplicate(c.id)" class="text-green-600 hover:text-green-800 inline-flex items-center gap-1">
                    <Copy class="w-4 h-4" />
                    <span class="text-xs font-medium">Duplicate</span>
                  </button>
                  <button @click="destroy(c.id)" class="text-red-600 hover:text-red-800 inline-flex items-center gap-1">
                    <Trash2 class="w-4 h-4" />
                    <span class="text-xs font-medium">Delete</span>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredClasses.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                  No classes found. Click "Create Class" to add one.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>