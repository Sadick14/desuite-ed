<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  Eye,
  Search,
  X,
  Calendar,
  User,
  Activity,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

type AuditLog = {
  id: number;
  user_id: number;
  user: { id: number; name: string; email: string } | null;
  action: string;
  model: string;
  record_id: number | null;
  changes: Record<string, any> | null;
  created_at: string;
};

const props = defineProps<{
  logs: AuditLog[];
}>();

// Filters
const search = ref('');
const filterAction = ref('');
const filterModel = ref('');
const filterDate = ref('');

// Unique action types and models for filters
const actionOptions = computed(() => {
  const actions = new Set(props.logs.map(log => log.action));
  return Array.from(actions).sort();
});

const modelOptions = computed(() => {
  const models = new Set(props.logs.map(log => log.model));
  return Array.from(models).sort();
});

// Filtered logs
const filteredLogs = computed(() => {
  let result = props.logs;
  if (search.value) {
    const term = search.value.toLowerCase();
    result = result.filter(log =>
      log.user?.name?.toLowerCase().includes(term) ||
      log.model.toLowerCase().includes(term) ||
      log.action.toLowerCase().includes(term)
    );
  }
  if (filterAction.value) {
    result = result.filter(log => log.action === filterAction.value);
  }
  if (filterModel.value) {
    result = result.filter(log => log.model === filterModel.value);
  }
  if (filterDate.value) {
    const targetDate = new Date(filterDate.value).toDateString();
    result = result.filter(log => new Date(log.created_at).toDateString() === targetDate);
  }
  return result;
});

// Stats
const totalLogs = computed(() => filteredLogs.value.length);
const uniqueModels = computed(() => new Set(filteredLogs.value.map(l => l.model)).size);

// Helper
const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};

const getActionBadgeClass = (action: string) => {
  const classes: Record<string, string> = {
    created: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    updated: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    deleted: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  };
  return classes[action] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

function clearFilters() {
  search.value = '';
  filterAction.value = '';
  filterModel.value = '';
  filterDate.value = '';
}
</script>

<template>
  <Head title="Audit Logs" />

  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
            Audit Logs
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Complete history of system activities
          </p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by user, model, action..."
              class="w-full pl-9 pr-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600"
            />
          </div>
          <select v-model="filterAction" class="px-3 py-2 border rounded-lg dark:bg-gray-800">
            <option value="">All Actions</option>
            <option v-for="act in actionOptions" :key="act" :value="act">{{ act }}</option>
          </select>
          <select v-model="filterModel" class="px-3 py-2 border rounded-lg dark:bg-gray-800">
            <option value="">All Models</option>
            <option v-for="mod in modelOptions" :key="mod" :value="mod">{{ mod }}</option>
          </select>
          <input v-model="filterDate" type="date" class="px-3 py-2 border rounded-lg dark:bg-gray-800" />
          <button
            v-if="search || filterAction || filterModel || filterDate"
            @click="clearFilters"
            class="px-3 py-2 text-gray-600 hover:text-gray-800"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border p-4 flex items-center gap-3">
          <Activity class="w-5 h-5 text-indigo-500" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Events</p>
            <p class="text-2xl font-bold">{{ totalLogs }}</p>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border p-4 flex items-center gap-3">
          <Calendar class="w-5 h-5 text-purple-500" />
          <div>
            <p class="text-xs text-gray-500 uppercase">Affected Models</p>
            <p class="text-2xl font-bold">{{ uniqueModels }}</p>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Record ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-gray-400" />
                    <span class="text-sm">{{ log.user?.name || 'System' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getActionBadgeClass(log.action)]">
                    {{ log.action }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ log.model }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ log.record_id ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(log.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <a :href="`/audit-logs/${log.id}`" class="text-indigo-600 hover:text-indigo-800">
                    <Eye class="w-4 h-4" />
                  </a>
                </td>
              </tr>
              <tr v-if="filteredLogs.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  No audit logs found for the selected filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>