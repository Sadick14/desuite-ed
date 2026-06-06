<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
  ArrowRight,
  Users,
  GraduationCap,
  Calendar,
  AlertCircle,
  Check,
  Loader2,
  History,
  Download,
  X,
  Grid,
  CheckSquare,
  RefreshCw,
  Search,
  ArrowLeft
} from 'lucide-vue-next';

interface Student {
  id: number;
  student_id: number;
  student_name: string;
  student_id_code: string;
  class_name: string;
  level: string;
  status?: string;
}

interface EnrollmentHistory {
  academic_year: string;
  class_name: string;
  level: string;
  status: string;
  enrolled_date: string;
}

interface SchoolClass {
  id: number;
  name: string;
  level: string;
  academic_year_id: number;
  enrollments_count: number;
}

interface AcademicYear {
  id: number;
  name: string;
  is_active: boolean;
  classes: SchoolClass[];
}

const props = defineProps<{
  academicYears: AcademicYear[];
  currentYear: AcademicYear;
  activeTerm?: { id: number; name: string; is_active: boolean } | null;
}>();

const isTerm3Active = computed(() => {
  return props.activeTerm?.name?.toLowerCase().includes('term 3') || false;
});

function quickAutoPromote() {
  if (!selectedFromClass.value || !selectedToClass.value || !selectedToYear.value) {
    alert('Please ensure you have selected the From Class, To Year, and Default Target Class first.');
    return;
  }
  initPromotionRules();
  students.value.forEach(student => {
    promotionRules.value[student.student_id] = {
      action: 'promote',
      target_class_id: selectedToClass.value || null
    };
  });
  currentStep.value = 3;
}

// Wizard Navigation
const currentStep = ref(1);
const loading = ref(false);
const successMessage = ref('');

// Step 1: Cohort Selection
const selectedFromYear = ref<number>(props.currentYear?.id || 0);
const selectedFromClass = ref<number>(0);
const selectedToYear = ref<number>(0);
const selectedToClass = ref<number>(0); // Default target class

// Student Search Filter in Step 2
const searchQuery = ref('');

// Students Data
const students = ref<Student[]>([]);
const loadingStudents = ref(false);

// Step 2: Promotion Rules Map
// Maps student_id to { action: 'promote' | 'retain' | 'withdraw', target_class_id: number | null }
interface PromoRule {
  action: 'promote' | 'retain' | 'withdraw';
  target_class_id: number | null;
}
const promotionRules = ref<Record<number, PromoRule>>({});
const selectedStudentIds = ref<number[]>([]);

// Enrollment History Modal
const showHistory = ref(false);
const selectedStudentForHistory = ref<Student | null>(null);
const enrollmentHistory = ref<EnrollmentHistory[]>([]);

// Computeds
const fromYearData = computed(() =>
  props.academicYears.find(ay => ay.id === selectedFromYear.value)
);

const toYearData = computed(() =>
  props.academicYears.find(ay => ay.id === selectedToYear.value)
);

const fromClasses = computed(() => {
  return fromYearData.value?.classes || [];
});

const toClasses = computed(() => {
  return toYearData.value?.classes || [];
});

// Auto-fill To Year with next year if possible
watch(selectedFromYear, (newFromYear) => {
  if (newFromYear) {
    const sortedYears = [...props.academicYears].sort((a, b) => a.id - b.id);
    const currentIndex = sortedYears.findIndex(ay => ay.id === newFromYear);
    if (currentIndex !== -1 && currentIndex + 1 < sortedYears.length) {
      selectedToYear.value = sortedYears[currentIndex + 1].id;
    } else {
      selectedToYear.value = 0;
    }
  }
});

// Auto-fill Default Target Class based on level matching
watch([selectedFromClass, selectedToYear], () => {
  if (selectedFromClass.value && selectedToYear.value) {
    const sourceClass = fromClasses.value.find(c => c.id === selectedFromClass.value);
    if (sourceClass) {
      // Find classes with similar level or name in Target Year
      const matchingClass = toClasses.value.find(
        c => c.name.toLowerCase() === sourceClass.name.toLowerCase()
      ) || toClasses.value.find(
        c => c.level === sourceClass.level
      ) || toClasses.value[0];
      
      selectedToClass.value = matchingClass?.id || 0;
    }
  } else {
    selectedToClass.value = 0;
  }
});

// Fetch students when source year/class is updated
watch([selectedFromYear, selectedFromClass], async () => {
  if (selectedFromYear.value && selectedFromClass.value) {
    loadingStudents.value = true;
    try {
      const response = await fetch(
        `/api/enrollments/${selectedFromYear.value}/${selectedFromClass.value}`
      );
      students.value = await response.json();
      initPromotionRules();
    } catch (error) {
      console.error('Failed to fetch students:', error);
      students.value = [];
    } finally {
      loadingStudents.value = false;
    }
  } else {
    students.value = [];
    promotionRules.value = {};
  }
});

// Sync default target class with promotion rule targets
watch(selectedToClass, (newClassId) => {
  students.value.forEach(student => {
    const rule = promotionRules.value[student.student_id];
    if (rule && rule.action === 'promote') {
      rule.target_class_id = newClassId || null;
    }
  });
});

// Find equivalent target class for retention (repeating the class in the new year)
const equivalentClassForSource = computed(() => {
  if (!selectedFromClass.value || !selectedToYear.value) return null;
  const sourceClass = fromClasses.value.find(c => c.id === selectedFromClass.value);
  if (!sourceClass) return null;
  
  // Find class with same name in target year
  const sameName = toClasses.value.find(c => c.name.toLowerCase() === sourceClass.name.toLowerCase());
  if (sameName) return sameName.id;

  // Find class with same level in target year
  const sameLevel = toClasses.value.find(c => c.level === sourceClass.level);
  if (sameLevel) return sameLevel.id;

  return null;
});

function initPromotionRules() {
  const rules: Record<number, PromoRule> = {};
  students.value.forEach(student => {
    rules[student.student_id] = {
      action: 'promote',
      target_class_id: selectedToClass.value || null
    };
  });
  promotionRules.value = rules;
  selectedStudentIds.value = [];
}

// Student Individual Action Mutators
function setStudentAction(studentId: number, action: 'promote' | 'retain' | 'withdraw') {
  if (!promotionRules.value[studentId]) return;
  promotionRules.value[studentId].action = action;
  if (action === 'promote') {
    promotionRules.value[studentId].target_class_id = selectedToClass.value || null;
  } else if (action === 'retain') {
    promotionRules.value[studentId].target_class_id = equivalentClassForSource.value || null;
  } else {
    promotionRules.value[studentId].target_class_id = null;
  }
}

function setStudentTargetClass(studentId: number, classId: number) {
  if (!promotionRules.value[studentId]) return;
  promotionRules.value[studentId].target_class_id = classId || null;
}

// Bulk Actions Logic
const filteredStudents = computed(() => {
  if (!searchQuery.value.trim()) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(
    s => s.student_name.toLowerCase().includes(q) || s.student_id_code.toLowerCase().includes(q)
  );
});

const isAllFilteredSelected = computed(() => {
  return filteredStudents.value.length > 0 && 
         filteredStudents.value.every(s => selectedStudentIds.value.includes(s.student_id));
});

function toggleSelectAllFiltered() {
  if (isAllFilteredSelected.value) {
    // Remove all filtered students from selection
    const filteredIds = filteredStudents.value.map(s => s.student_id);
    selectedStudentIds.value = selectedStudentIds.value.filter(id => !filteredIds.includes(id));
  } else {
    // Add all filtered students to selection
    const filteredIds = filteredStudents.value.map(s => s.student_id);
    const merged = new Set([...selectedStudentIds.value, ...filteredIds]);
    selectedStudentIds.value = Array.from(merged);
  }
}

function applyBulkAction(action: 'promote' | 'retain' | 'withdraw') {
  selectedStudentIds.value.forEach(id => {
    setStudentAction(id, action);
  });
  selectedStudentIds.value = [];
}

// Statistics in Step 3
const summaryStats = computed(() => {
  let promoted = 0;
  let retained = 0;
  let withdrawn = 0;

  students.value.forEach(s => {
    const rule = promotionRules.value[s.student_id];
    if (rule) {
      if (rule.action === 'promote') promoted++;
      else if (rule.action === 'retain') retained++;
      else if (rule.action === 'withdraw') withdrawn++;
    }
  });

  return { promoted, retained, withdrawn };
});

// View Student History
async function viewEnrollmentHistory(student: Student) {
  selectedStudentForHistory.value = student;
  try {
    const response = await fetch(`/api/student/${student.student_id}/enrollment-history`);
    enrollmentHistory.value = await response.json();
  } catch (error) {
    console.error('Failed to fetch enrollment history:', error);
    enrollmentHistory.value = [];
  }
  showHistory.value = true;
}

// Navigation Controls
function nextStep() {
  if (currentStep.value === 1) {
    if (!selectedFromYear.value || !selectedFromClass.value || !selectedToYear.value) {
      alert('Please select the source and destination academic years along with the source class.');
      return;
    }
    if (selectedFromYear.value === selectedToYear.value) {
      alert('Source and target academic years must be different.');
      return;
    }
    if (students.value.length === 0) {
      alert('No active student enrollments found in the selected source class.');
      return;
    }
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    // Check if at least one promote/retain rule has target class
    const missingTarget = students.value.some(student => {
      const rule = promotionRules.value[student.student_id];
      return rule && (rule.action === 'promote' || rule.action === 'retain') && !rule.target_class_id;
    });
    if (missingTarget) {
      alert('One or more students selected for promotion or retention do not have a target class assigned.');
      return;
    }
    currentStep.value = 3;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

// Execute Bulk Promotion
function executePromotion() {
  loading.value = true;
  
  // Format payload
  const promotionsPayload = students.value.map(s => {
    const rule = promotionRules.value[s.student_id];
    return {
      student_id: s.student_id,
      action: rule.action,
      target_class_id: rule.target_class_id
    };
  });

  router.post(
    route('promotions.bulk'),
    {
      from_academic_year_id: selectedFromYear.value,
      to_academic_year_id: selectedToYear.value,
      promotions: promotionsPayload
    },
    {
      onSuccess: () => {
        successMessage.value = 'Student promotion processed successfully!';
        setTimeout(() => (successMessage.value = ''), 4000);
        // Reset state
        students.value = [];
        promotionRules.value = {};
        selectedFromClass.value = 0;
        selectedToClass.value = 0;
        currentStep.value = 1;
        loading.value = false;
      },
      onError: () => {
        loading.value = false;
      }
    }
  );
}

// CSV Export
function exportPromotedStudents() {
  if (students.value.length === 0) return;

  const csvContent = [
    ['Student Name', 'ID Code', 'Action', 'From Class', 'Destination Class', 'Date'].join(','),
    ...students.value.map(s => {
      const rule = promotionRules.value[s.student_id];
      const targetClass = toClasses.value.find(c => c.id === rule?.target_class_id);
      const actionLabel = rule ? rule.action.toUpperCase() : 'UNKNOWN';
      const destLabel = rule?.action === 'withdraw' ? 'EXITED REGISTRY' : (targetClass ? targetClass.name : 'N/A');
      return [
        `"${s.student_name}"`,
        s.student_id_code,
        actionLabel,
        s.class_name,
        destLabel,
        new Date().toLocaleDateString()
      ].join(',');
    })
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `promotion_report_${fromClasses.value.find(c => c.id === selectedFromClass.value)?.name || 'class'}_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  window.URL.revokeObjectURL(url);
}
</script>

<template>
  <Head title="Student Promotions" />

  <div class="min-h-screen bg-stone-50/50 text-stone-900 antialiased selection:bg-amber-200">
    <div class="max-w-7xl mx-auto px-6 lg:px-12 py-10 space-y-8">

      <!-- Header -->
      <div class="border-b border-stone-200/80 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="text-[10px] font-black uppercase tracking-widest text-amber-800 bg-amber-100/80 px-2.5 py-1 rounded-full">Registry Module</span>
          <h1 class="text-3xl font-black tracking-tight text-stone-900 mt-2">Interactive Student Promotions</h1>
          <p class="text-xs font-semibold text-stone-500 mt-1">Manage academic year transit routes, class upgrades, student retentions, and withdrawal status logs.</p>
        </div>
        <div v-if="currentStep > 1" class="flex gap-2">
          <button @click="exportPromotedStudents" class="px-4 py-2 border border-stone-200 text-stone-700 bg-white hover:bg-stone-50 text-xs font-black uppercase tracking-wider rounded-xl transition flex items-center gap-2 shadow-sm">
            <Download class="w-3.5 h-3.5 text-stone-500" />
            Export Draft
          </button>
        </div>
      </div>

      <!-- Alert -->
      <div v-if="successMessage" class="bg-emerald-950 text-emerald-100 border border-emerald-900 rounded-xl p-4 flex gap-3 shadow-lg transition-all">
        <Check class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5 stroke-[3]" />
        <p class="text-xs font-bold uppercase tracking-wider">{{ successMessage }}</p>
      </div>

      <!-- Progress Stepper -->
      <div class="flex items-center justify-between mb-8 max-w-3xl mx-auto">
        <div v-for="step in [1, 2, 3]" :key="step" class="flex items-center flex-1 last:flex-initial">
          <div class="flex items-center gap-3">
            <div :class="[
              'w-9 h-9 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300',
              currentStep === step 
                ? 'bg-amber-950 text-white shadow-md ring-4 ring-amber-950/15 scale-110' 
                : currentStep > step 
                  ? 'bg-lime-400 text-amber-950 font-bold' 
                  : 'bg-stone-200 text-stone-500'
            ]">
              <Check v-if="currentStep > step" class="w-4 h-4 stroke-[3]" />
              <span v-else>{{ step }}</span>
            </div>
            <span :class="[
              'text-xs uppercase tracking-widest font-black hidden sm:inline transition-all duration-300',
              currentStep === step ? 'text-amber-950 font-bold' : 'text-stone-400'
            ]">
              {{ step === 1 ? 'Cohort Setup' : step === 2 ? 'Configure Actions' : 'Review & Execute' }}
            </span>
          </div>
          <div v-if="step < 3" :class="[
            'h-0.5 flex-1 mx-4 transition-all duration-500',
            currentStep > step ? 'bg-lime-400' : 'bg-stone-200'
          ]"></div>
        </div>
      </div>

      <!-- STEP 1: COHORT SELECTION -->
      <div v-if="currentStep === 1" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- Source Cohort Setup -->
          <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
              <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-800 font-bold">
                <Users class="w-4 h-4 text-amber-900" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Source Class</h3>
                <p class="text-[10px] font-bold text-stone-400">Select class and academic year to promote from</p>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-2">From Academic Year</label>
                <select v-model.number="selectedFromYear" class="w-full border border-stone-200 rounded-xl px-3.5 py-3 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white transition-all">
                  <option :value="0" disabled>Select academic year...</option>
                  <option v-for="year in academicYears" :key="year.id" :value="year.id">
                    {{ year.name }} {{ year.is_active ? '(Active Year)' : '' }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-2">From Class</label>
                <select v-model.number="selectedFromClass" :disabled="!selectedFromYear" class="w-full border border-stone-200 rounded-xl px-3.5 py-3 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white disabled:opacity-50 disabled:bg-stone-50 transition-all">
                  <option :value="0" disabled>Select class...</option>
                  <option v-for="cls in fromClasses" :key="cls.id" :value="cls.id">
                    {{ cls.name }} ({{ cls.levelLabel }} • {{ cls.enrollments_count }} Students)
                  </option>
                </select>
              </div>
            </div>

            <!-- Loader / Results Preview -->
            <div v-if="selectedFromClass && selectedFromYear" class="pt-4 border-t border-stone-100">
              <div v-if="loadingStudents" class="flex items-center gap-2.5 py-2">
                <Loader2 class="w-4 h-4 text-amber-700 animate-spin" />
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wide">Scanning class records...</span>
              </div>
              <div v-else-if="students.length > 0" class="flex items-center justify-between py-2">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-wider">
                  {{ students.length }} Students Registered
                </span>
                <span class="text-xs text-stone-400 font-semibold">Ready for configuration</span>
              </div>
              <div v-else class="flex items-center gap-2 text-amber-700 bg-amber-50 rounded-xl p-3">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span class="text-xs font-bold uppercase tracking-wider">No active student enrollments found in this class.</span>
              </div>
            </div>
          </div>

          <!-- Destination Cohort Setup -->
          <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
              <div class="w-8 h-8 rounded-lg bg-lime-50 flex items-center justify-center text-lime-800 font-bold">
                <GraduationCap class="w-4 h-4 text-amber-900" />
              </div>
              <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Destination Class</h3>
                <p class="text-[10px] font-bold text-stone-400">Select target academic year and default promotion class</p>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-2">To Academic Year</label>
                <select v-model.number="selectedToYear" :disabled="!selectedFromYear" class="w-full border border-stone-200 rounded-xl px-3.5 py-3 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white disabled:opacity-50 disabled:bg-stone-50 transition-all">
                  <option :value="0" disabled>Select target year...</option>
                  <option v-for="year in academicYears" :key="year.id" :value="year.id" :disabled="year.id === selectedFromYear">
                    {{ year.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-black uppercase tracking-wider text-stone-400 mb-2">Default Target Class (for Promotions)</label>
                <select v-model.number="selectedToClass" :disabled="!selectedToYear" class="w-full border border-stone-200 rounded-xl px-3.5 py-3 bg-stone-50/50 text-xs font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white disabled:opacity-50 disabled:bg-stone-50 transition-all">
                  <option :value="0" disabled>Select class...</option>
                  <option v-for="cls in toClasses" :key="cls.id" :value="cls.id">
                    {{ cls.name }} ({{ cls.levelLabel }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Helpful tips -->
            <div class="pt-4 border-t border-stone-100 flex gap-3 text-stone-500">
              <AlertCircle class="w-4 h-4 text-stone-400 shrink-0 mt-0.5" />
              <p class="text-[11px] font-semibold leading-relaxed">
                By default, students will be set to promote to this target class. You can customize actions (like retaining or withdrawing) or select different destination classes for individual students in the next step.
              </p>
            </div>
          </div>
        </div>

        <!-- End of Year Auto-Promote Quick Action -->
        <div v-if="isTerm3Active && selectedFromClass && selectedToClass && students.length > 0" class="bg-lime-50/60 border border-lime-200 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
          <div class="space-y-1 text-center sm:text-left">
            <span class="text-[9px] font-black uppercase tracking-wider text-lime-800 bg-lime-200 px-2 py-0.5 rounded-full">End of Year Alert</span>
            <h4 class="text-xs font-black uppercase tracking-widest text-lime-950">Active Term is Term 3 (End of Year)</h4>
            <p class="text-[11px] text-lime-800 font-semibold leading-relaxed">
              Bypass individual overrides and auto-promote all {{ students.length }} students to the target class directly.
            </p>
          </div>
          <button 
            @click="quickAutoPromote"
            class="px-5 py-3 bg-lime-400 hover:bg-lime-500 text-amber-950 text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md shadow-lime-500/20 whitespace-nowrap"
          >
            ⚡ Quick Auto-Promote Class
          </button>
        </div>

        <!-- Next Button -->
        <div class="flex justify-between items-center pt-4">
          <div class="text-xs font-semibold text-stone-400">
            Current Active Term: <span class="font-black text-stone-700">{{ activeTerm?.name || 'None' }}</span>
          </div>
          <button 
            @click="nextStep"
            :disabled="!selectedFromClass || !selectedToClass || students.length === 0"
            class="px-6 py-3 bg-amber-950 hover:bg-amber-900 disabled:opacity-30 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md shadow-stone-900/5 flex items-center gap-2"
          >
            Configure Promotion Rules
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- STEP 2: CONFIGURE ACTIONS -->
      <div v-if="currentStep === 2" class="space-y-6">
        
        <!-- Summary Strip and Bulk Bar -->
        <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm space-y-4">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Set Promotion Rules</h3>
              <p class="text-[11px] font-semibold text-stone-500 mt-1">
                advancing from <span class="font-black text-amber-950">{{ fromClasses.find(c => c.id === selectedFromClass)?.name }}</span> 
                to <span class="font-black text-amber-950">{{ toClasses.find(c => c.id === selectedToClass)?.name }}</span> 
                for <span class="font-black text-amber-950">{{ toYearData?.name }}</span>
              </p>
            </div>

            <!-- Search Field -->
            <div class="relative w-full md:w-64">
              <Search class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5" />
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search students..." 
                class="w-full pl-9 pr-4 py-2 border border-stone-200 bg-stone-50/50 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-950 focus:bg-white"
              />
            </div>
          </div>

          <!-- Bulk Operations Bar -->
          <div class="bg-stone-50 border border-stone-200/60 rounded-xl p-3.5 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex items-center gap-2">
              <CheckSquare class="w-4 h-4 text-stone-500" />
              <span class="text-xs font-black text-stone-700 uppercase tracking-wider">
                {{ selectedStudentIds.length }} of {{ filteredStudents.length }} Selected
              </span>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Mark selected as:</span>
              <div class="inline-flex rounded-lg border border-stone-200 bg-white p-0.5">
                <button 
                  @click="applyBulkAction('promote')"
                  :disabled="selectedStudentIds.length === 0"
                  class="px-3 py-1.5 rounded-md text-[10px] uppercase font-black tracking-wider text-emerald-700 hover:bg-emerald-50 disabled:opacity-20 transition"
                >
                  Promote
                </button>
                <button 
                  @click="applyBulkAction('retain')"
                  :disabled="selectedStudentIds.length === 0"
                  class="px-3 py-1.5 rounded-md text-[10px] uppercase font-black tracking-wider text-amber-700 hover:bg-amber-50 disabled:opacity-20 transition"
                >
                  Retain
                </button>
                <button 
                  @click="applyBulkAction('withdraw')"
                  :disabled="selectedStudentIds.length === 0"
                  class="px-3 py-1.5 rounded-md text-[10px] uppercase font-black tracking-wider text-rose-700 hover:bg-rose-50 disabled:opacity-20 transition"
                >
                  Withdraw
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Student Table -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-stone-50/50 border-b border-stone-100 text-[10px] font-black uppercase tracking-widest text-stone-500">
                  <th class="py-4 px-6 w-12 text-center">
                    <input 
                      type="checkbox" 
                      :checked="isAllFilteredSelected" 
                      @change="toggleSelectAllFiltered"
                      class="rounded border-stone-300 text-amber-950 focus:ring-amber-950"
                    />
                  </th>
                  <th class="py-4 px-4">Student Name</th>
                  <th class="py-4 px-4">Student ID</th>
                  <th class="py-4 px-4 text-center">Promotion Action</th>
                  <th class="py-4 px-4">Target Class Destination</th>
                  <th class="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100 text-xs">
                <tr 
                  v-for="student in filteredStudents" 
                  :key="student.id" 
                  :class="[
                    'transition-colors',
                    selectedStudentIds.includes(student.student_id) ? 'bg-amber-50/10' : 'hover:bg-stone-50/30'
                  ]"
                >
                  <td class="py-4 px-6 text-center">
                    <input 
                      type="checkbox" 
                      v-model="selectedStudentIds" 
                      :value="student.student_id"
                      class="rounded border-stone-300 text-amber-950 focus:ring-amber-950"
                    />
                  </td>
                  <td class="py-4 px-4 font-black uppercase tracking-wide text-stone-900">
                    {{ student.student_name }}
                  </td>
                  <td class="py-4 px-4 text-stone-500 font-mono">
                    {{ student.student_id_code }}
                  </td>
                  <td class="py-4 px-4">
                    <div class="flex justify-center">
                      <div class="inline-flex rounded-lg border border-stone-200 p-0.5 bg-stone-50">
                        <button 
                          type="button"
                          @click="setStudentAction(student.student_id, 'promote')" 
                          :class="[
                            'px-3 py-1 rounded-md text-[10px] uppercase font-black tracking-wider transition-all duration-200',
                            promotionRules[student.student_id]?.action === 'promote'
                              ? 'bg-emerald-600 text-white shadow-sm font-black'
                              : 'text-stone-500 hover:text-stone-900'
                          ]"
                        >
                          Promote
                        </button>
                        <button 
                          type="button"
                          @click="setStudentAction(student.student_id, 'retain')" 
                          :class="[
                            'px-3 py-1 rounded-md text-[10px] uppercase font-black tracking-wider transition-all duration-200',
                            promotionRules[student.student_id]?.action === 'retain'
                              ? 'bg-amber-600 text-white shadow-sm font-black'
                              : 'text-stone-500 hover:text-stone-900'
                          ]"
                        >
                          Retain
                        </button>
                        <button 
                          type="button"
                          @click="setStudentAction(student.student_id, 'withdraw')" 
                          :class="[
                            'px-3 py-1 rounded-md text-[10px] uppercase font-black tracking-wider transition-all duration-200',
                            promotionRules[student.student_id]?.action === 'withdraw'
                              ? 'bg-rose-600 text-white shadow-sm font-black'
                              : 'text-stone-500 hover:text-stone-900'
                          ]"
                        >
                          Withdraw
                        </button>
                      </div>
                    </div>
                  </td>
                  <td class="py-4 px-4">
                    <!-- Target class picker -->
                    <div v-if="promotionRules[student.student_id]?.action === 'promote'">
                      <select 
                        :value="promotionRules[student.student_id]?.target_class_id"
                        @change="e => setStudentTargetClass(student.student_id, Number((e.target as HTMLSelectElement).value))"
                        class="border border-stone-200 rounded-lg px-2 py-1 bg-white text-[11px] font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-950 transition-all"
                      >
                        <option :value="0" disabled>Select Class...</option>
                        <option v-for="cls in toClasses" :key="cls.id" :value="cls.id">
                          {{ cls.name }} ({{ cls.levelLabel }})
                        </option>
                      </select>
                    </div>

                    <div v-else-if="promotionRules[student.student_id]?.action === 'retain'">
                      <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 bg-amber-50 border border-amber-200 px-2 py-1 rounded-md">
                        {{ toClasses.find(c => c.id === promotionRules[student.student_id]?.target_class_id)?.name || 'Same Grade Equivalent' }} (Retained)
                      </span>
                    </div>

                    <div v-else>
                      <span class="text-[10px] font-black uppercase tracking-wider text-rose-800 bg-rose-50 border border-rose-200 px-2 py-1 rounded-md">
                        Exiting Registry
                      </span>
                    </div>
                  </td>
                  <td class="py-4 px-6 text-right">
                    <button 
                      @click="viewEnrollmentHistory(student)"
                      class="p-2 border border-stone-200 text-stone-400 hover:text-stone-900 hover:bg-stone-50 rounded-lg transition" 
                      title="View System Enrollment History"
                    >
                      <History class="w-3.5 h-3.5" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between pt-4">
          <button 
            @click="prevStep"
            class="px-5 py-2.5 border border-stone-200 text-stone-700 bg-white hover:bg-stone-50 text-xs font-black uppercase tracking-wider rounded-xl transition flex items-center gap-2 shadow-sm"
          >
            <ArrowLeft class="w-4 h-4" />
            Cohort Selection
          </button>
          
          <button 
            @click="nextStep"
            class="px-6 py-3 bg-amber-950 hover:bg-amber-900 text-white text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md shadow-stone-900/5 flex items-center gap-2"
          >
            Review Promotions
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- STEP 3: REVIEW & EXECUTE -->
      <div v-if="currentStep === 3" class="space-y-6">
        
        <!-- Summary Cards Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-6 space-y-2 shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">Outcome: Advance</span>
            <p class="text-3xl font-black text-emerald-950">{{ summaryStats.promoted }}</p>
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Students to be Promoted</p>
          </div>

          <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-6 space-y-2 shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">Outcome: Keep Class</span>
            <p class="text-3xl font-black text-amber-950">{{ summaryStats.retained }}</p>
            <p class="text-xs font-bold text-amber-800 uppercase tracking-wide">Students to be Retained</p>
          </div>

          <div class="bg-rose-50 border border-rose-200/80 rounded-2xl p-6 space-y-2 shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-wider text-rose-800 bg-rose-100 px-2 py-0.5 rounded-full">Outcome: De-Register</span>
            <p class="text-3xl font-black text-rose-950">{{ summaryStats.withdrawn }}</p>
            <p class="text-xs font-bold text-rose-800 uppercase tracking-wide">Students to be Withdrawn</p>
          </div>
        </div>

        <!-- Detailed Review Sheet -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 space-y-4">
          <div>
            <h3 class="text-xs font-black uppercase tracking-widest text-stone-900">Promotion Verification Log</h3>
            <p class="text-[10px] font-semibold text-stone-400 mt-0.5">Please review the list below carefully before committing changes to the registry database.</p>
          </div>

          <div class="border border-stone-100 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-stone-50 border-b border-stone-100 text-[10px] font-black uppercase tracking-widest text-stone-400">
                  <th class="py-3 px-4">Student</th>
                  <th class="py-3 px-4">Original Class</th>
                  <th class="py-3 px-4">Action</th>
                  <th class="py-3 px-4">Target Class Destination</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100 text-xs">
                <tr v-for="student in students" :key="student.id" class="hover:bg-stone-50/50">
                  <td class="py-3 px-4 font-black uppercase text-stone-900">
                    {{ student.student_name }}
                    <span class="block text-[9px] font-semibold text-stone-400 font-mono mt-0.5">{{ student.student_id_code }}</span>
                  </td>
                  <td class="py-3 px-4 text-stone-600 font-bold uppercase tracking-wider">
                    {{ student.class_name }}
                  </td>
                  <td class="py-3 px-4">
                    <span 
                      :class="[
                        'text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-md border',
                        promotionRules[student.student_id]?.action === 'promote' 
                          ? 'bg-emerald-50 text-emerald-800 border-emerald-200' 
                          : promotionRules[student.student_id]?.action === 'retain'
                            ? 'bg-amber-50 text-amber-800 border-amber-200'
                            : 'bg-rose-50 text-rose-800 border-rose-200'
                      ]"
                    >
                      {{ promotionRules[student.student_id]?.action }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-black uppercase tracking-wide">
                    <span v-if="promotionRules[student.student_id]?.action === 'withdraw'" class="text-stone-400">
                      N/A (Exiting School)
                    </span>
                    <span v-else class="text-stone-800">
                      {{ toClasses.find(c => c.id === promotionRules[student.student_id]?.target_class_id)?.name }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Warning Disclaimer -->
        <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-4 flex gap-3">
          <AlertCircle class="w-5 h-5 text-amber-800 mt-0.5 shrink-0" />
          <div>
            <h4 class="text-xs font-black uppercase tracking-widest text-amber-950">Database Operation Notice</h4>
            <p class="text-[11px] font-semibold text-amber-900 mt-1 leading-relaxed">
              Executing this promotion will automatically create new enrollments for the target Academic Year, close out the current term records, and update the student registries. This process is secure and runs as an atomic operation, but should be validated before execution.
            </p>
          </div>
        </div>

        <!-- Action Controls -->
        <div class="flex justify-between pt-4">
          <button 
            @click="prevStep"
            :disabled="loading"
            class="px-5 py-2.5 border border-stone-200 text-stone-700 bg-white hover:bg-stone-50 disabled:opacity-50 text-xs font-black uppercase tracking-wider rounded-xl transition flex items-center gap-2 shadow-sm"
          >
            <ArrowLeft class="w-4 h-4" />
            Rules Configuration
          </button>
          
          <button 
            @click="executePromotion"
            :disabled="loading"
            class="px-6 py-3 bg-lime-400 hover:bg-lime-500 disabled:opacity-40 text-amber-950 text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md shadow-lime-500/10 flex items-center gap-2"
          >
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin text-amber-950" />
            Commit Cohort Promotion
          </button>
        </div>
      </div>

      <!-- MODAL: ENROLLMENT AUDIT HISTORY -->
      <div v-if="showHistory" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-stone-900/40 backdrop-blur-sm" @click="showHistory = false"></div>
          
          <div class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full max-h-[80vh] overflow-y-auto border border-stone-200 flex flex-col">
            <div class="sticky top-0 bg-white border-b border-stone-100 p-6 flex justify-between items-center z-10">
              <div>
                <h2 class="text-xs font-black uppercase tracking-widest text-amber-950">Enrollment Matrix History Audit</h2>
                <p class="text-[11px] text-stone-500 font-bold mt-0.5">{{ selectedStudentForHistory?.student_name }}</p>
              </div>
              <button @click="showHistory = false" class="text-stone-400 hover:text-stone-950 transition p-1 bg-stone-50 hover:bg-stone-100 rounded-lg">
                <X class="w-4 h-4" />
              </button>
            </div>

            <div class="p-6 space-y-4 flex-1">
              <div v-if="enrollmentHistory.length === 0" class="text-center py-8">
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider">No logged track iterations found</p>
              </div>
              
              <div v-else class="space-y-6 relative before:absolute before:inset-y-1 before:left-2.5 before:w-0.5 before:bg-stone-100">
                <div v-for="(enrollment, idx) in enrollmentHistory" :key="idx" class="flex gap-4 relative">
                  <div class="w-5 h-5 rounded-full bg-white border-4 border-amber-950 shrink-0 z-10 mt-1"></div>
                  
                  <div class="flex-1 bg-stone-50/50 border border-stone-200/60 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between items-start">
                      <p class="text-xs font-black uppercase tracking-wide text-amber-950">{{ enrollment.class_name }}</p>
                      <span :class="[
                        'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded border', 
                        enrollment.status === 'Active' 
                          ? 'bg-emerald-50 text-emerald-950 border-emerald-200' 
                          : 'bg-stone-100 text-stone-600 border-stone-200'
                      ]">
                        {{ enrollment.status }}
                      </span>
                    </div>
                    
                    <p class="text-[11px] font-semibold text-stone-500">
                      <span class="capitalize">{{ enrollment.level }}</span> • {{ enrollment.academic_year }}
                    </p>
                    
                    <p class="text-[10px] text-stone-400 font-mono">{{ enrollment.enrolled_date }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>