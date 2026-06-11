<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, MessageSquare, Edit, Plus } from 'lucide-vue-next';

const props = defineProps<{
    templates: any[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'SMS', href: '/sms' },
            { title: 'Templates', href: '/sms/templates' },
        ],
    }),
});
</script>

<template>
    <Head title="SMS Templates" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
                <div class="flex items-center gap-3">
                    <Link
                        href="/sms"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                            SMS Templates
                        </h1>
                        <p class="text-gray-600 mt-1 text-sm">
                            Manage reusable SMS templates for common communications
                        </p>
                    </div>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid gap-4">
                <div v-for="template in templates" :key="template.id" class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                    <div class="p-5 border-b border-amber-100 bg-amber-50/50">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-100 rounded-xl">
                                    <FileText class="w-5 h-5 text-amber-700" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ template.name }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200 mt-1">
                                        {{ template.type }}
                                    </span>
                                </div>
                            </div>
                            <span v-if="template.is_active" class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-semibold rounded-full border border-green-200">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ template.message }}</p>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-2">Available variables:</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="variable in template.variables" :key="variable" class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded border border-gray-200">
                                    {{ variable }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="templates.length === 0" class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] p-12 text-center">
                <MessageSquare class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No templates yet</h3>
                <p class="text-gray-500">Create reusable SMS templates to save time!</p>
            </div>
        </div>
    </div>
</template>
