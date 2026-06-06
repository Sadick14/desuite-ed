<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
  X,
  ChevronLeft,
  ChevronRight,
  Check,
  GraduationCap
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps<{
  student?: any;
  classes: any[];
  errors?: Record<string, string>;
}>();

const emit = defineEmits(['close']);

const step = ref(1);
const loading = ref(false);
const formErrors = ref<Record<string, string>>({});

const form = useForm({
  school_class_id: '',
  first_name: '',
  last_name: '',
  gender: 'male',
  date_of_birth: '',
  parent_name: '',
  parent_phone: '',
  address: '',
  admission_date: '',
});

const resetForm = () => {
  form.reset();
  formErrors.value = {};
};

// Pre-fill form when editing
watch(() => props.student, (newStudent) => {
  if (newStudent) {
    form.school_class_id = newStudent.school_class_id || '';
    form.first_name = newStudent.first_name || '';
    form.last_name = newStudent.last_name || '';
    form.gender = newStudent.gender || 'male';
    form.date_of_birth = newStudent.date_of_birth || '';
    form.parent_name = newStudent.parent_name || '';
    form.parent_phone = newStudent.parent_phone || '';
    form.address = newStudent.address || '';
    form.admission_date = newStudent.admission_date || '';
    formErrors.value = {};
  } else {
    resetForm();
  }
}, { immediate: true });

// Clear errors when step changes
watch(step, () => {
  formErrors.value = {};
});

// Step validation
const canContinue = computed(() => {
  if (step.value === 1){
    return !!form.school_class_id;
  }

  if (step.value === 2) {
    return !!form.first_name && !!form.last_name &&
           !!form.gender && !!form.date_of_birth;
  }

  if (step.value === 3) {
    return !!form.parent_name && !!form.parent_phone;
  }

  if (step.value === 4) {
    return !!form.admission_date;
  }

  return true;
});

// Next step
const next = () => {
  if (step.value < 4 && canContinue.value) {
    step.value++;
  }
};

// Previous step
const back = () => {
  if (step.value > 1) {
    step.value--;
  }
};

// Submit form
const submit = () => {
  const url = props.student ? `/students/${props.student.id}` : '/students';
  const method = props.student ? 'put' : 'post';

  form[method](url, {
    onSuccess: () => {
      resetForm();
      emit('close');
    },

    onError: (errors: Record<string, string>) => {
      formErrors.value = errors;
      if (errors.school_class_id) {
        step.value = 1;
      } else if (errors.first_name || errors.last_name || errors.gender || errors.date_of_birth) {
        step.value = 2;
      } else if (errors.parent_name || errors.parent_phone) {
        step.value = 3;
      } else if (errors.admission_date) {
        step.value = 4;
      }
    },
  });
};

// Close modal and reset form
const closeModal = () => {
  resetForm();
  step.value = 1;
  emit('close');
};

// Close on escape key
const onEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    closeModal();
  }
};
onMounted(() => document.addEventListener('keydown', onEscape));
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="emit('close')">
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-amber-950/20 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl border border-amber-100 max-w-lg w-full transform transition-all overflow-hidden">
          
          <button
            @click="closeModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 transition-colors p-1"
          >
            <X class="w-4 h-4" />
          </button>

          <div class="px-6 pt-6 pb-4 border-b border-amber-50">
            <h2 class="text-lg font-black text-gray-900 tracking-tight">
              {{ student ? 'Modify Record Folder' : 'Register New Student Profile' }}
            </h2>
            <p class="text-xs text-gray-400 font-medium mt-1">
              Input valid legal references below to optimize the system indexing profile.
            </p>
          </div>

          <div class="px-6 pt-4">
            <div class="flex items-center justify-between">
              <div
                v-for="i in 4"
                :key="i"
                class="flex flex-col items-center flex-1"
              >
                <div
                  :class="[
                    'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                    step > i ? 'bg-gradient-to-br from-emerald-400 to-green-500 text-white shadow-sm' : 
                    step === i ? 'bg-amber-900 text-white ring-4 ring-amber-100' : 
                    'bg-amber-50 text-amber-900/40 border border-amber-200/40'
                  ]"
                >
                  <Check v-if="step > i" class="w-3.5 h-3.5 stroke-[2.5]" />
                  <span v-else>{{ i }}</span>
                </div>
                
                <div class="h-[3px] w-full bg-amber-50 mt-2.5 rounded-full overflow-hidden">
                  <div
                    v-if="step > i"
                    class="h-full bg-green-500 transition-all duration-300"
                    style="width: 100%"
                  ></div>
                  <div
                    v-else-if="step === i"
                    class="h-full bg-amber-600 transition-all duration-300"
                    style="width: 50%"
                  ></div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between text-[10px] font-black uppercase tracking-wider text-gray-400 mt-2 px-1">
              <span>Cohort</span>
              <span>Personal</span>
              <span>Guardian</span>
              <span>Admission</span>
            </div>
          </div>

          <div class="p-6 space-y-4">
            
            <div v-if="step === 1" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                  <GraduationCap class="inline w-3.5 h-3.5 mr-1 text-amber-700" /> Academic Cohort Selection *
                </label>
                <select
                  v-model="form.school_class_id"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2.5 bg-amber-50/20 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white"
                >
                  <option value="">Select registry cohort configuration</option>
                  <option v-for="c in classes" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </select>
                <p v-if="formErrors.school_class_id" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.school_class_id }}</p>
              </div>
            </div>

            <div v-if="step === 2" class="space-y-4">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">First Legal Name *</label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    placeholder="E.g., John"
                  />
                  <p v-if="formErrors.first_name" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.first_name }}</p>
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Last Surname *</label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    placeholder="E.g., Doe"
                  />
                  <p v-if="formErrors.last_name" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.last_name }}</p>
                </div>
              </div>
              
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender Vector Mapping *</label>
                <div class="flex gap-6">
                  <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                    <input type="radio" v-model="form.gender" value="male" class="text-amber-900 focus:ring-amber-500" />
                    <span>Male Mapping</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                    <input type="radio" v-model="form.gender" value="female" class="text-amber-900 focus:ring-amber-500" />
                    <span>Female Mapping</span>
                  </label>
                </div>
                <p v-if="formErrors.gender" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.gender }}</p>
              </div>
              
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Official Birth Date *</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                />
                <p v-if="formErrors.date_of_birth" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.date_of_birth }}</p>
              </div>
            </div>

            <div v-if="step === 3" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Primary Responsible Guardian *</label>
                <input
                  v-model="form.parent_name"
                  type="text"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="Full Legal Name"
                />
                <p v-if="formErrors.parent_name" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.parent_name }}</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Emergency Mobile Communications *</label>
                <input
                  v-model="form.parent_phone"
                  type="tel"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-mono font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="+233 XX XXX XXXX"
                />
                <p v-if="formErrors.parent_phone" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.parent_phone }}</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Residential Location Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="Street reference, structural address identifiers"
                ></textarea>
                <p v-if="formErrors.address" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.address }}</p>
              </div>
            </div>

            <div v-if="step === 4" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Registry Admission Date *</label>
                <input
                  v-model="form.admission_date"
                  type="date"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                />
                <p v-if="formErrors.admission_date" class="text-red-600 text-xs mt-1.5 font-medium">{{ formErrors.admission_date }}</p>
              </div>
              
              <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4 text-xs font-medium">
                <p class="font-bold text-amber-900 uppercase tracking-wider text-[10px]">Verify data compilation ledger:</p>
                <ul class="mt-2.5 space-y-1.5 text-gray-600">
                  <li><strong class="text-gray-400 font-bold">Assigned Cohort:</strong> {{ classes.find(c => c.id == form.school_class_id)?.name || '—' }}</li>
                  <li><strong class="text-gray-400 font-bold">Identity Mapping:</strong> {{ form.first_name }} {{ form.last_name }}</li>
                  <li><strong class="text-gray-400 font-bold">Guardian Sync:</strong> {{ form.parent_name }} ({{ form.parent_phone }})</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t border-amber-50 flex justify-between bg-amber-50/10">
            <button
              v-if="step > 1"
              @click="back"
              class="px-3.5 py-2 text-xs font-bold text-gray-700 border border-amber-200/60 rounded-xl hover:bg-amber-50 transition-colors focus:outline-none flex items-center gap-1"
            >
              <ChevronLeft class="w-3.5 h-3.5 stroke-[2.5]" /> Back
            </button>
            
            <div class="flex gap-2 ml-auto">
              <button
                @click="closeModal"
                class="px-4 py-2 text-xs font-bold text-gray-500 border border-transparent rounded-xl hover:bg-amber-50/50 transition-colors focus:outline-none"
              >
                Cancel Work
              </button>

              <button
                v-if="step < 4"
                @click="next"
                :disabled="!canContinue"
                class="px-4 py-2 bg-amber-900 text-white rounded-xl hover:bg-amber-950 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold transition-all flex items-center gap-1 focus:outline-none"
              >
                Next Step <ChevronRight class="w-3.5 h-3.5 stroke-[2.5]" />
              </button>

              <button
                v-else
                @click="submit"
                :disabled="loading"
                class="px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed text-xs font-black uppercase tracking-wider border border-lime-500/20 transition-all flex items-center gap-1 focus:outline-none shadow-md shadow-lime-500/[0.08]"
              >
                Commit Changes
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </Teleport>
</template>