<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, MessageSquare, CheckCircle, AlertCircle, Clock, Trash2, RefreshCw, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    smsLogs: any;
    stats: {
        total: number;
        sent: number;
        failed: number;
        pending: number;
    };
}>();

const search = ref('');

const filteredLogs = computed(() => {
    if (!search.value) {
        return props.smsLogs.data;
    }

    const term = search.value.toLowerCase();

    return props.smsLogs.data.filter((log: any) =>
        log.recipient_name?.toLowerCase().includes(term) ||
        log.recipient_phone?.includes(term) ||
        log.message?.toLowerCase().includes(term)
    );
});

const getStatusBadge = (status: string) => {
    const badges: Record<string, { class: string; label: string }> = {
        sent: { class: 'bg-green-100 text-green-800 border-green-200', label: 'Sent' },
        failed: { class: 'bg-red-100 text-red-800 border-red-200', label: 'Failed' },
        pending: { class: 'bg-yellow-100 text-yellow-800 border-yellow-200', label: 'Pending' },
    };

    return badges[status] || badges.pending;
};

const getTypeBadge = (type: string) => {
    const types: Record<string, { class: string; label: string }> = {
        payment_confirmation: { class: 'bg-blue-100 text-blue-800 border-blue-200', label: 'Payment Confirmation' },
        balance_reminder: { class: 'bg-orange-100 text-orange-800 border-orange-200', label: 'Balance Reminder' },
        general: { class: 'bg-gray-100 text-gray-800 border-gray-200', label: 'General' },
        announcement: { class: 'bg-purple-100 text-purple-800 border-purple-200', label: 'Announcement' },
        attendance: { class: 'bg-pink-100 text-pink-800 border-pink-200', label: 'Attendance' },
    };

    return types[type] || { class: 'bg-gray-100 text-gray-800 border-gray-200', label: type };
};

const resend = (id: number) => {
    if (confirm('Are you sure you want to resend this SMS?')) {
        router.post(`/sms/${id}/resend`);
    }
};

const destroy = (id: number) => {
    if (confirm('Are you sure you want to delete this SMS log?')) {
        router.delete(`/sms/${id}`);
    }
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'SMS', href: '/sms' },
        ],
    }),
});
</script>

<template>
    <Head title="SMS Logs" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        SMS Communication
                    </h1>
                    <p class="text-gray-600 mt-1 text-sm">
                        Manage and track all SMS messages sent to parents and guardians
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        href="/sms/templates"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-50 transition-all cursor-pointer"
                    >
                        <MessageSquare class="w-4 h-4" />
                        Templates
                    </Link>
                    <Link
                        href="/sms/compose"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        Compose SMS
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-xl">
                            <MessageSquare class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-xl">
                            <CheckCircle class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Sent</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.sent.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-xl">
                            <AlertCircle class="w-5 h-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Failed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.failed.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-xl shadow-amber-900/[0.01] border border-amber-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 rounded-xl">
                            <Clock class="w-5 h-5 text-yellow-600" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-xl shadow-amber-900/[0.01]">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search SMS by recipient name, phone, or message..."
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                    />
                </div>
            </div>

            <!-- SMS Logs Table -->
            <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100">
                        <thead class="bg-amber-50/70 backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Recipient</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Message</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50 bg-white">
                            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ log.recipient_name || 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ log.recipient_phone }}</p>
                                        <p v-if="log.student" class="text-xs text-gray-400 mt-1">
                                            {{ log.student.first_name }} {{ log.student.last_name }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ log.message }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getTypeBadge(log.sms_type).class]">
                                        {{ getTypeBadge(log.sms_type).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border', getStatusBadge(log.status).class]">
                                        {{ getStatusBadge(log.status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(log.created_at).toLocaleDateString() }} {{ new Date(log.created_at).toLocaleTimeString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="log.status === 'failed'"
                                            @click="resend(log.id)"
                                            class="text-blue-600 hover:text-blue-800 transition-colors"
                                            title="Resend"
                                        >
                                            <RefreshCw class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="destroy(log.id)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredLogs.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <MessageSquare class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                                    No SMS messages found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="smsLogs.last_page > 1" class="px-6 py-4 border-t border-amber-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Showing {{ smsLogs.from }} to {{ smsLogs.to }} of {{ smsLogs.total }} results
                        </p>
                        <div class="flex gap-2">
                            <button
                                :disabled="!smsLogs.prev_page_url"
                                @click="router.get(smsLogs.prev_page_url)"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                Previous
                            </button>
                            <button
                                :disabled="!smsLogs.next_page_url"
                                @click="router.get(smsLogs.next_page_url)"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
