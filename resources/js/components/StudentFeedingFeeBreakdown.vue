<script setup lang="ts">
import { ref } from 'vue';
import { TrendingDown, AlertCircle, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{
  feedingFeeData: {
    total_owed: number;
    total_paid: number;
    carryforward: number;
    outstanding: number;
    weekly_breakdown: Array<{
      week: number;
      days_attended: number;
      amount_owed: number;
      amount_paid: number;
      carryforward: number;
      outstanding: number;
    }>;
  } | null;
}>();

const showWeeklyDetails = ref(false);
</script>

<template>
  <div v-if="props.feedingFeeData" class="bg-white rounded-2xl border border-amber-100 p-6 shadow-sm">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Feeding Fee Tracking</h3>
        <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-semibold">Attendance-Based</span>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-orange-50 rounded-lg border border-orange-100">
          <p class="text-xs text-gray-600 uppercase font-semibold">Total Owed</p>
          <p class="text-2xl font-bold text-orange-600 mt-2">₵{{ (props.feedingFeeData.total_owed || 0).toFixed(2) }}</p>
        </div>

        <div class="p-4 bg-green-50 rounded-lg border border-green-100">
          <p class="text-xs text-gray-600 uppercase font-semibold">Total Paid</p>
          <p class="text-2xl font-bold text-green-600 mt-2">₵{{ (props.feedingFeeData.total_paid || 0).toFixed(2) }}</p>
        </div>

        <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
          <p class="text-xs text-gray-600 uppercase font-semibold">Carryforward</p>
          <p class="text-2xl font-bold text-blue-600 mt-2">₵{{ (props.feedingFeeData.carryforward || 0).toFixed(2) }}</p>
          <p class="text-xs text-gray-600 mt-1">Overpaid (moves forward)</p>
        </div>

        <div :class="['p-4 rounded-lg border', props.feedingFeeData.outstanding > 0 ? 'bg-red-50 border-red-100' : 'bg-green-50 border-green-100']">
          <p class="text-xs text-gray-600 uppercase font-semibold">Outstanding</p>
          <p :class="['text-2xl font-bold mt-2', props.feedingFeeData.outstanding > 0 ? 'text-red-600' : 'text-green-600']">
            ₵{{ (props.feedingFeeData.outstanding || 0).toFixed(2) }}
          </p>
          <p v-if="props.feedingFeeData.outstanding === 0" class="text-xs text-gray-600 mt-1 flex items-center gap-1">
            <CheckCircle class="w-3 h-3" /> All paid
          </p>
        </div>
      </div>

      <!-- Weekly Breakdown -->
      <div>
        <button
          @click="showWeeklyDetails = !showWeeklyDetails"
          class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition"
        >
          <TrendingDown class="w-4 h-4" />
          Weekly Details {{ showWeeklyDetails ? '▼' : '▶' }}
        </button>

        <div v-if="showWeeklyDetails && props.feedingFeeData.weekly_breakdown.length > 0" class="mt-4 space-y-3">
          <div v-for="week in props.feedingFeeData.weekly_breakdown" :key="week.week" class="p-3 border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <p class="font-semibold text-gray-900">Week {{ week.week }}</p>
              <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ week.days_attended }} days</span>
            </div>
            <div class="grid grid-cols-4 gap-2 text-xs">
              <div>
                <p class="text-gray-600">Owed</p>
                <p class="font-medium text-gray-900">₵{{ week.amount_owed.toFixed(2) }}</p>
              </div>
              <div>
                <p class="text-gray-600">Paid</p>
                <p class="font-medium text-green-600">₵{{ week.amount_paid.toFixed(2) }}</p>
              </div>
              <div>
                <p class="text-gray-600">Carry Forward</p>
                <p class="font-medium text-blue-600">₵{{ week.carryforward.toFixed(2) }}</p>
              </div>
              <div>
                <p class="text-gray-600">Outstanding</p>
                <p :class="['font-medium', week.outstanding > 0 ? 'text-red-600' : 'text-green-600']">₵{{ week.outstanding.toFixed(2) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info Box -->
      <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex gap-3">
        <AlertCircle class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
        <div class="text-sm text-blue-900">
          <p class="font-semibold">How it works:</p>
          <p class="mt-1">Feeding fees are charged based on days you attend school (Mon-Fri). If you pay more than owed, the excess carries forward to the next week. Payment is automatically allocated to oldest outstanding balances first.</p>
        </div>
      </div>
    </div>
  </div>
</template>
