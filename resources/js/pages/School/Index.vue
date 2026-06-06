<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  Building2,
  Mail,
  Phone,
  MapPin,
  Image,
  Save,
  X,
  Upload,
} from 'lucide-vue-next';

const props = defineProps<{
  school: {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo: string | null;
  } | null;
}>();

// Form handling
const form = useForm({
  name: props.school?.name || '',
  email: props.school?.email || '',
  phone: props.school?.phone || '',
  address: props.school?.address || '',
  logo: null as File | null,
});

// Logo preview
const logoPreview = ref<string | null>(props.school?.logo || null);
const uploadError = ref<string | null>(null);

// Handle file selection
const handleLogoUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    uploadError.value = 'Please upload an image file (JPEG, PNG, etc.)';
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    uploadError.value = 'Logo must be less than 2MB';
    return;
  }

  uploadError.value = null;
  form.logo = file;

  // Preview
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target?.result as string;
  };
  reader.readAsDataURL(file);
};

// Remove logo
const removeLogo = () => {
  form.logo = null;
  logoPreview.value = null;
  uploadError.value = null;
  // Clear file input
  const fileInput = document.getElementById('logo_input') as HTMLInputElement;
  if (fileInput) fileInput.value = '';
};

// Submit form
function submit() {
  if (!props.school) {
    // Create new school (initial setup)
    form.post('/school', {
      preserveScroll: true,
      onSuccess: () => {
        // handle success if needed
      },
    });
  } else {
    // Update existing school – use PUT directly
    form.put(`/school/${props.school.id}`, {
      preserveScroll: true,
    });
  }
}
</script>

<template>
  <Head title="School Settings" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-3 border-b border-amber-100/60 pb-5">
        <Building2 class="w-8 h-8 text-amber-600" />
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            School Settings
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage your school profile and contact information
          </p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 overflow-hidden">
        <form @submit.prevent="submit" class="p-6 space-y-6">

          <!-- Logo Section -->
          <div class="border-b border-amber-100/60 pb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              School Logo
            </label>
            <div class="flex items-center gap-6">
              <div class="relative">
                <div v-if="logoPreview" class="w-24 h-24 rounded-xl overflow-hidden bg-gray-100 border-2 border-amber-200">
                  <img :src="logoPreview" alt="School logo" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-24 h-24 rounded-xl bg-gray-100 border-2 border-dashed border-amber-200 flex items-center justify-center">
                  <Image class="w-8 h-8 text-gray-400" />
                </div>
                <button
                  v-if="logoPreview"
                  type="button"
                  @click="removeLogo"
                  class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
              <div class="flex-1">
                <label class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100/70 text-amber-900 rounded-lg cursor-pointer hover:bg-amber-100 transition">
                  <Upload class="w-4 h-4" />
                  <span class="text-sm font-medium">Upload Logo</span>
                  <input
                    id="logo_input"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleLogoUpload"
                  />
                </label>
                <p class="text-xs text-gray-600 mt-2">
                  Recommended: Square image, max 2MB (JPEG, PNG)
                </p>
                <p v-if="uploadError" class="text-xs text-red-500 mt-1">{{ uploadError }}</p>
              </div>
            </div>
          </div>

          <!-- School Details -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                School Name *
              </label>
              <div class="relative">
                <Building2 class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g., Sunshine Academy"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
              </div>
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Email Address
              </label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="contact@school.edu"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
              </div>
              <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Phone Number
              </label>
              <div class="relative">
                <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  v-model="form.phone"
                  type="tel"
                  placeholder="+233 XX XXX XXXX"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
              </div>
              <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Address
              </label>
              <div class="relative">
                <MapPin class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <textarea
                  v-model="form.address"
                  rows="3"
                  placeholder="Street, City, Country"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                ></textarea>
              </div>
              <p v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-amber-100/60">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 px-6 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold disabled:opacity-50 transition-all"
            >
              <Save class="w-4 h-4" />
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>

          <!-- Success Message (optional) -->
          <div v-if="$page.props.flash?.success" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            {{ $page.props.flash.success }}
          </div>
        </form>
      </div>

      <!-- Info Card (for single-school context) -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4">
        <p class="text-sm text-blue-700 dark:text-blue-300">
          <strong>Note:</strong> Your school runs as a single institution. All data (students, classes, fees) belongs to this school.
        </p>
      </div>
    </div>
  </div>
</template>