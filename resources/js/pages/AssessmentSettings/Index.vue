<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, Edit } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  settings: any[];
  terms: any[];
  scales: any[];
}>();

const modalOpen = ref(false);
const editingSetting = ref<any>(null);

const form = useForm({
  term_id: '',
  grading_scale_id: '',
  ca_weight: 40,
  exam_weight: 60,
  ca_max_marks: 100,
  exam_max_marks: 100,
});

const openCreateModal = () => {
  editingSetting.value = null;
  form.reset();
  form.term_id = '';
  form.grading_scale_id = '';
  form.ca_weight = 40;
  form.exam_weight = 60;
  form.ca_max_marks = 100;
  form.exam_max_marks = 100;
  modalOpen.value = true;
};

const openEditModal = (setting: any) => {
  editingSetting.value = setting;
  form.grading_scale_id = setting.grading_scale_id;
  form.ca_weight = setting.ca_weight;
  form.exam_weight = setting.exam_weight;
  form.ca_max_marks = setting.ca_max_marks;
  form.exam_max_marks = setting.exam_max_marks;
  modalOpen.value = true;
};

const save = () => {
  if (editingSetting.value) {
    form.put(`/assessment-settings/${editingSetting.value.id}`, {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  } else {
    form.post('/assessment-settings', {
      onSuccess: () => {
 modalOpen.value = false; 
}
    });
  }
};

function destroy(id: number) {
  if (confirm('Delete this assessment setting?')) {
    form.delete(`/assessment-settings/${id}`);
  }
}
</script>

<template>
  <Head title="Assessment Settings" />

  <div class="min-h-screen bg-gradient-to-b from-purple-50 via-white to-purple-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-purple-100/60 pb-5">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Assessment Settings</h1>
          <p class="text-sm text-gray-600 mt-1">Configure grading weights and scales per term</p>
        </div>
        <button @click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl transition-all">
          <Plus class="w-4 h-4" />
          New Setting
        </button>
      </div>

      <!-- Settings Cards Grid -->
      <div class="grid gap-4 md:grid-cols-2">
        <div v-for="s in settings" :key="s.id" class="bg-white rounded-2xl border border-purple-100 p-6 shadow-sm hover:shadow-md transition">

          <!-- Term Header -->
          <div class="border-b border-purple-100 pb-4 mb-4">
            <h3 class="text-lg font-bold text-gray-900">{{ s.term.name }}</h3>
            <p class="text-xs text-gray-500 mt-1">Grading Scale: <span class="font-semibold">{{ s.grading_scale.name }}</span></p>
          </div>

          <!-- Configuration Details -->
          <div class="space-y-3 mb-6">
            <!-- CA Weight -->
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Continuous Assessment (CA)</span>
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-blue-600">{{ s.ca_weight }}%</span>
                <span class="text-xs text-gray-500">(max {{ s.ca_max_marks }})</span>
              </div>
            </div>

            <!-- Exam Weight -->
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Final Examination</span>
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-blue-600">{{ s.exam_weight }}%</span>
                <span class="text-xs text-gray-500">(max {{ s.exam_max_marks }})</span>
              </div>
            </div>

            <!-- Weight Verification -->
            <div class="bg-purple-50/50 rounded-lg p-3 border border-purple-100/50">
              <p class="text-xs text-purple-800 font-semibold">
                <span v-if="Number(s.ca_weight) + Number(s.exam_weight) === 100" class="text-green-700">
                  ✓ Weights sum correctly to 100%
                </span>
                <span v-else class="text-red-700">
                  ✗ Weights sum to {{ s.ca_weight + s.exam_weight }}%
                </span>
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 pt-4 border-t border-purple-100">
            <button @click="openEditModal(s)" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg transition">
              <Edit class="w-4 h-4" />
              Edit
            </button>
            <button @click="destroy(s.id)" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-semibold rounded-lg transition">
              <Trash2 class="w-4 h-4" />
              Delete
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="settings.length === 0" class="col-span-2 bg-white rounded-2xl border border-dashed border-purple-200 p-12 text-center">
          <p class="text-gray-500 mb-4">No assessment settings configured yet.</p>
          <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-xl transition">
            <Plus class="w-4 h-4" />
            Create First Setting
          </button>
        </div>
      </div>

      <!-- Modal for Add/Edit Setting -->
      <div v-if="modalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b border-purple-100 sticky top-0 bg-white">
            <h3 class="text-xl font-bold text-gray-900">{{ editingSetting ? 'Edit Assessment Setting' : 'Create Assessment Setting' }}</h3>
          </div>

          <div class="p-6 space-y-4">
            <!-- Term Selection (disabled in edit mode) -->
            <div v-if="!editingSetting">
              <label class="block text-sm font-bold text-gray-900 mb-1">Select Term *</label>
              <select
                v-model="form.term_id"
                class="w-full border border-purple-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900"
              >
                <option value="">-- Select a Term --</option>
                <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <p v-if="form.errors.term_id" class="mt-1 text-sm text-red-600">{{ form.errors.term_id }}</p>
            </div>

            <!-- Term Display (edit mode) -->
            <div v-if="editingSetting">
              <label class="block text-sm font-bold text-gray-900 mb-1">Term (Cannot be changed)</label>
              <div class="px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-900">
                {{ editingSetting.term.name }}
              </div>
            </div>

            <!-- Grading Scale Selection -->
            <div>
              <label class="block text-sm font-bold text-gray-900 mb-1">Grading Scale *</label>
              <select
                v-model="form.grading_scale_id"
                class="w-full border border-purple-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900"
              >
                <option value="">-- Select a Scale --</option>
                <option v-for="s in scales" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <p v-if="form.errors.grading_scale_id" class="mt-1 text-sm text-red-600">{{ form.errors.grading_scale_id }}</p>
            </div>

            <!-- Weights Section -->
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100 space-y-3">
              <h4 class="font-semibold text-gray-900 text-sm">Assessment Weights</h4>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs font-bold text-gray-900 mb-1 block">CA Weight (%)</label>
                  <input
                    v-model.number="form.ca_weight"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full border border-purple-200 rounded-lg px-2 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-purple-500"
                  />
                </div>

                <div>
                  <label class="text-xs font-bold text-gray-900 mb-1 block">Exam Weight (%)</label>
                  <input
                    v-model.number="form.exam_weight"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full border border-purple-200 rounded-lg px-2 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-purple-500"
                  />
                </div>
              </div>

              <div :class="['p-2 rounded text-xs font-semibold text-center', form.ca_weight + form.exam_weight === 100 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                {{ form.ca_weight + form.exam_weight === 100 ? '✓ Weights sum to 100%' : `✗ Weights sum to ${form.ca_weight + form.exam_weight}% (must be 100%)` }}
              </div>
            </div>

            <!-- Max Marks Section -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 space-y-3">
              <h4 class="font-semibold text-gray-900 text-sm">Maximum Marks</h4>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs font-bold text-gray-900 mb-1 block">CA Max</label>
                  <input
                    v-model.number="form.ca_max_marks"
                    type="number"
                    min="0"
                    class="w-full border border-blue-200 rounded-lg px-2 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="text-xs font-bold text-gray-900 mb-1 block">Exam Max</label>
                  <input
                    v-model.number="form.exam_max_marks"
                    type="number"
                    min="0"
                    class="w-full border border-blue-200 rounded-lg px-2 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-purple-100 flex justify-end gap-3 sticky bottom-0 bg-purple-50">
            <button @click="modalOpen = false" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-bold text-sm hover:bg-gray-50">Cancel</button>
            <button
              @click="save"
              :disabled="form.processing || form.ca_weight + form.exam_weight !== 100 || (!editingSetting && !form.term_id)"
              class="px-4 py-2.5 bg-lime-400 hover:bg-lime-500 disabled:opacity-50 text-gray-900 rounded-lg font-bold text-sm shadow-sm"
            >
              {{ form.processing ? 'Saving...' : (editingSetting ? 'Update' : 'Create') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
