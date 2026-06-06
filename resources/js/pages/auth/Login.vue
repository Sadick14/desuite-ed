<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register, home } from '@/routes';
import { request } from '@/routes/password';


defineOptions({ layout: null }); // Prevents any global layout

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post('/login', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Log in" />

    <!-- Unified full-bleed background layout -->
    <div class="h-screen w-screen overflow-hidden bg-gradient-to-b from-amber-50 via-white to-amber-50 flex flex-col">
        
        <!-- Header spans full width across the top of the background -->
        <header class="border-b border-amber-100 bg-white/40 backdrop-blur-sm flex-shrink-0 z-10">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex items-center justify-between">
                <Link :href="home()" class="flex items-center gap-2 hover:opacity-80 transition">
                    <div class="flex aspect-square size-8 items-center justify-center text-sidebar-foreground">
        <AppLogoIcon class="size-6 text-neutral-900 dark:text-neutral-100" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">DevSuite-Edu</span>
    </div>
                </Link>
                <Link :href="home()" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
                    <ArrowLeft class="w-4 h-4" />
                    <span class="text-sm font-medium">Back</span>
                </Link>
            </div>
        </header>

        <!-- Main floating layout content area -->
        <div class="flex-1 max-w-7xl w-full mx-auto px-6 lg:px-8 flex items-center justify-between gap-12 overflow-hidden">
            
            <!-- LEFT AREA: The structural Form Card -->
            <div class="w-full lg:max-w-md h-full overflow-y-auto py-8 pr-2 flex items-center flex-shrink-0 style-scrollbar">
                <div class="w-full">
                    <!-- Status Message (e.g., password reset confirmation) -->
                    <div v-if="status"
                         class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-center text-sm font-medium text-green-700">
                        {{ status }}
                    </div>

                    <!-- Form Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
                        <p class="text-gray-600 mt-2">Sign in to your account to continue</p>
                    </div>

                    <!-- Login Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email Field -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-semibold text-gray-700">Email Address</Label>
                            <Input id="email" type="email" required autofocus autocomplete="email" placeholder="you@school.edu"
                                   class="h-10 bg-white border-gray-300" v-model="form.email" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-semibold text-gray-700">Password</Label>
                                <TextLink v-if="canResetPassword" :href="request()" class="text-xs text-indigo-600 hover:underline">
                                    Forgot password?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Input :type="showPassword ? 'text' : 'password'" id="password" required autocomplete="current-password"
                                       placeholder="••••••••" class="h-10 bg-white border-gray-300 pr-10" v-model="form.password" />
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center space-x-2">
                            <Checkbox id="remember" v-model:checked="form.remember" />
                            <Label for="remember" class="text-sm text-gray-600 cursor-pointer">Remember me for 30 days</Label>
                        </div>

                        <!-- Submit Button -->
                        <Button type="submit" :disabled="form.processing"
                                class="w-full h-10 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-lg transition mt-6"
                                data-test="login-button">
                            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin mr-2"></span>
                            {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        </Button>
                    </form>

                    <!-- Sign Up Link -->
                    <div class="mt-6 text-center text-sm text-gray-600">
                        Don't have an account?
                        <TextLink :href="register()" class="text-indigo-600 font-semibold hover:underline">Create account</TextLink>
                    </div>
                </div>
            </div>

            <!-- RIGHT AREA: Floating Dashboard Mockup Image -->
            <div class="relative order-1 lg:order-2 h-full">
                <img
                    src="/images/web2.jpg"
                    alt="DevSuite EDU Dashboard"
                    class="w-full h-full object-cover"
                />
            </div>

        </div>
    </div>
</template>

<style scoped>
.style-scrollbar::-webkit-scrollbar { display: none; }
</style>