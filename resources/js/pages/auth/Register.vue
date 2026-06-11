<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login, home } from '@/routes';


defineOptions({ layout: undefined });

defineProps<{
    passwordRules: string;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function submit() {
    form.post('/register', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Register" />

    <div class="h-screen w-screen overflow-hidden bg-gradient-to-b from-amber-50 via-white to-amber-50 flex flex-col">
        
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

        <div class="flex-1 max-w-7xl w-full mx-auto px-6 lg:px-8 flex items-center justify-between gap-12 overflow-hidden">
            
            <div class="w-full lg:max-w-md h-full overflow-y-auto py-8 pr-2 flex items-center flex-shrink-0 style-scrollbar">
                <div class="w-full">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Create Account</h1>
                        <p class="text-gray-600 mt-2">Join DevSuite EDU and start managing your school</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-semibold text-gray-700">Full Name</Label>
                            <Input id="name" type="text" required autofocus autocomplete="name" placeholder="John Doe"
                                   class="h-10 bg-white border-gray-300" v-model="form.name" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-semibold text-gray-700">Email Address</Label>
                            <Input id="email" type="email" required autocomplete="email" placeholder="you@school.edu"
                                   class="h-10 bg-white border-gray-300" v-model="form.email" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-semibold text-gray-700">Password</Label>
                            <div class="relative">
                                <Input :type="showPassword ? 'text' : 'password'" id="password" required autocomplete="new-password"
                                       placeholder="••••••••" class="h-10 bg-white border-gray-300 pr-10" v-model="form.password" />
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordRules" class="text-xs text-gray-500">{{ passwordRules }}</p>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-sm font-semibold text-gray-700">Confirm Password</Label>
                            <div class="relative">
                                <Input :type="showPasswordConfirmation ? 'text' : 'password'" id="password_confirmation" required
                                       autocomplete="new-password" placeholder="••••••••" class="h-10 bg-white border-gray-300 pr-10"
                                       v-model="form.password_confirmation" />
                                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <Eye v-if="!showPasswordConfirmation" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <Button type="submit" :disabled="form.processing"
                                class="w-full h-10 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-lg transition mt-6"
                                data-test="register-user-button">
                            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-gray-900 border-t-transparent rounded-full animate-spin mr-2"></span>
                            {{ form.processing ? 'Creating account...' : 'Create Account' }}
                        </Button>
                    </form>

                    <div class="mt-6 text-center text-sm text-gray-600">
                        Already have an account?
                        <TextLink :href="login()" class="text-indigo-600 font-semibold hover:underline">Sign in</TextLink>
                    </div>
                </div>
            </div>

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
/* Only target the localized form container's scrollbar if needed */
.style-scrollbar::-webkit-scrollbar { display: none; }
</style>