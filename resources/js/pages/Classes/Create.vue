<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  courses: any[];
}>();

const form = useForm({
  name: '',
  level: '',
  selected_courses: [] as number[],
});

const selectedCount = computed(() => form.selected_courses.length);

const levelOptions = [
  { value: 'nursery', label: 'Nursery' },
  { value: 'kindergarten', label: 'Kindergarten' },
  { value: 'lower_primary', label: 'Lower Primary (P1-P3)' },
  { value: 'upper_primary', label: 'Upper Primary (P4-P6)' },
  { value: 'jhs', label: 'JHS' },
];

function toggleCourse(courseId: number) {
  if (form.selected_courses.includes(courseId)) {
    form.selected_courses = form.selected_courses.filter(id => id !== courseId);
  } else {
    form.selected_courses = [...form.selected_courses, courseId];
  }
}

function selectAll() {
  form.selected_courses = [...props.courses.map(c => c.id)];
}

function clearAll() {
  form.selected_courses = [];
}

function submit() {
  form.post('/classes', {
    onSuccess: () => {
      router.visit('/classes');
    }
  });
}

function goBack() {
  router.visit('/classes');
}
</script>

<template>
  <Head title="Create Class" />

  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-blue-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <button @click="goBack" class="p-2 hover:bg-gray-100 rounded-lg transition">
          <ArrowLeft class="w-5 h-5 text-gray-600" />
        </button>
        <h1 class="text-3xl font-bold text-gray-900">Create New Class</h1>
      </div>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden space-y-6 p-6">

          <!-- Class Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Class Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., Grade 10 Alpha, Form 2B"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
              :disabled="form.processing"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Class Level -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Class Level</label>
            <select
              v-model="form.level"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
              :disabled="form.processing"
            >
              <option value="">-- Select Level --</option>
              <option v-for="opt in levelOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
            <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
          </div>

          <!-- Course Selection -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <label class="block text-sm font-semibold text-gray-900">Assign Courses to This Class</label>
              <div class="flex gap-2">
                <button
                  type="button"
                  @click="selectAll"
                  class="text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg font-medium transition"
                >
                  Select All
                </button>
                <button
                  type="button"
                  @click="clearAll"
                  class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition"
                >
                  Clear
                </button>
              </div>
            </div>

            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
              <label
                v-for="course in courses"
                :key="course.id"
                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition"
                :class="{ 'bg-blue-50 border-blue-300': form.selected_courses.includes(course.id) }"
              >
                <input
                  type="checkbox"
                  :checked="form.selected_courses.includes(course.id)"
                  @change="toggleCourse(course.id)"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ course.name }}</p>
                  <p v-if="course.code" class="text-xs text-gray-500">{{ course.code }}</p>
                </div>
              </label>
            </div>

            <!-- Selection Summary -->
            <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <AlertCircle class="w-4 h-4 text-blue-600 flex-shrink-0" />
              <p class="text-sm text-blue-900">
                <span class="font-semibold">{{ selectedCount }}</span>
                <span v-if="selectedCount === 1">course selected</span>
                <span v-else>courses selected</span>
              </p>
            </div>

            <p v-if="form.errors['selected_courses']" class="mt-2 text-sm text-red-600">
              {{ form.errors['selected_courses'] }}
            </p>
          </div>

        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-6">
          <button
            type="button"
            @click="goBack"
            class="flex-1 px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition"
            :disabled="form.processing"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing || !form.name || !form.level || selectedCount === 0"
            class="flex-1 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 disabled:opacity-50 disabled:cursor-not-allowed text-gray-900 font-semibold rounded-xl transition flex items-center justify-center gap-2"
          >
            <Check class="w-4 h-4" />
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Class</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</template>
