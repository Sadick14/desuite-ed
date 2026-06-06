<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  ArrowLeft,
  User,
  Calendar,
  Database,
  Hash,
  GitBranch,
  FileText,
  CheckCircle,
} from 'lucide-vue-next';

const props = defineProps<{
  log: {
    id: number;
    user_id: number;
    user: { id: number; name: string; email: string } | null;
    action: string;
    model: string;
    record_id: number | null;
    changes: Record<string, any> | null;
    created_at: string;
  };
}>();

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
</script>

<template>
  <Head :title="`Audit Log #${log.id}`" />

  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link href="/audit-logs" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Audit Log Details
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Event #{{ log.id }}
          </p>
        </div>
      </div>

      <!-- Main card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border overflow-hidden">
        <div class="p-6 space-y-6">
          <!-- Basic info grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start gap-3">
              <User class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 uppercase">User</p>
                <p class="font-medium">{{ log.user?.name || 'System' }}</p>
                <p class="text-sm text-gray-500">{{ log.user?.email }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <Calendar class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 uppercase">Timestamp</p>
                <p class="font-medium">{{ formatDate(log.created_at) }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <Database class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 uppercase">Model</p>
                <p class="font-medium">{{ log.model }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <Hash class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 uppercase">Record ID</p>
                <p class="font-mono">{{ log.record_id ?? '—' }}</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <GitBranch class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 uppercase">Action</p>
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getActionBadgeClass(log.action)]">
                  {{ log.action }}
                </span>
              </div>
            </div>
          </div>

          <!-- Changes section -->
          <div v-if="log.changes && Object.keys(log.changes).length > 0" class="border-t pt-4">
            <h3 class="text-lg font-semibold flex items-center gap-2 mb-3">
              <FileText class="w-5 h-5" />
              {{ log.action === 'updated' ? 'Changes' : 'Data' }}
            </h3>
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800">
                  <tr>
                    <th class="px-4 py-2 text-left">Field</th>
                    <th v-if="log.action === 'updated'" class="px-4 py-2 text-left">Old Value</th>
                    <th class="px-4 py-2 text-left">{{ log.action === 'updated' ? 'New Value' : 'Value' }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="(change, field) in log.changes" :key="field">
                    <td class="px-4 py-2 font-mono text-xs">{{ field }}</td>
                    <td v-if="log.action === 'updated' && typeof change === 'object' && change !== null" class="px-4 py-2">
                      <span class="inline-block max-w-xs break-words">
                        {{ change.old ?? '—' }}
                      </span>
                    </td>
                    <td class="px-4 py-2">
                      <span class="inline-block max-w-xs break-words font-medium" :class="log.action === 'created' ? 'text-green-700 dark:text-green-300' : 'text-green-700 dark:text-green-300'">
                        {{ typeof change === 'object' && change !== null ? (change.new ?? '—') : (change ?? '—') }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- No changes message -->
          <div v-else class="border-t pt-4 text-center text-gray-500">
            <CheckCircle class="w-12 h-12 mx-auto mb-2 text-green-500" />
            <p>No field changes recorded for this event.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
