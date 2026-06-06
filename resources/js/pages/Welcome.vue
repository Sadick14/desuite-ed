<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
  Star,
  GraduationCap,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';


const page = usePage();


const isLoggedIn = computed(() => page.props.auth?.user);
</script>

<template>
  <Head title="DevSuite EDU - School Management System">
    <meta name="description" content="Modern school management system with smart payment recording, financial reporting, student enrollment, and intelligent promotion workflows." />
  </Head>

  <div class="h-screen w-screen overflow-hidden bg-gradient-to-b from-amber-50 via-white to-amber-50 flex flex-col">
    <!-- Navigation -->
    <nav class="border-b border-amber-100 bg-white/80 backdrop-blur-sm flex-shrink-0">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
          <div class="flex aspect-square size-8 items-center justify-center text-sidebar-foreground">
        <AppLogoIcon class="size-6 text-neutral-900 dark:text-neutral-100" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">DevSuite-Edu</span>
    </div>
        </div>
        <div class="flex items-center gap-4">
          <Link v-if="isLoggedIn" href="/dashboard" class="text-gray-600 hover:text-gray-900 transition">
            Dashboard
          </Link>
          <Link v-else href="/login" class="text-gray-600 hover:text-gray-900 transition">
            Sign In
          </Link>
          <Link v-if="!isLoggedIn" href="/register" class="px-4 py-2 rounded-lg bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold transition">
            Get Started
          </Link>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="flex-1 relative overflow-hidden">
      <div class="h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center w-full">
          <!-- Left Content -->
          <div class="space-y-6 order-2 lg:order-1">
            <!-- Title -->
            <div class="space-y-4">
              <h1 class="text-5xl sm:text-6xl font-bold text-gray-900 leading-tight">
                Manage school operations in
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                  minutes
                </span>
              </h1>
              <p class="text-xl text-gray-600 leading-relaxed max-w-xl">
                With a single dashboard, you can track students, manage finances, process promotions, and generate insights. Complete school management, simplified.
              </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
              <Link
                href="/register"
                class="inline-flex items-center justify-center px-6 py-3 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-lg font-semibold transition w-fit"
              >
                Register Now
              </Link>

            </div>

            <!-- Testimonials -->
            
          </div>

          <!-- Right Side - Product Image -->
          <div class="relative order-1 lg:order-2 h-full">
            <img
              src="/images/web2.jpg"
              alt="DevSuite EDU Dashboard"
              class="w-full h-full object-cover rounded-2xl"
            />
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
