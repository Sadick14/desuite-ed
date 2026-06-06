<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    GraduationCap,
    CreditCard,
    Receipt,
    Wallet,
    Building2,
    Settings,
    BookOpen,
    FolderGit2,
    ChevronDown,
    BarChart3,
    Clock,
    ArrowRight,
} from '@lucide/vue';

import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import type { NavItem } from '@/types';

const dashboardUrl = computed(() => '/dashboard');

const navigationGroups = computed(() => [
    {
        title: 'Core',
        icon: LayoutGrid,
        items: [
            { title: 'Dashboard', href: dashboardUrl.value, icon: LayoutGrid },
            { title: 'Students', href: '/students', icon: Users },
            { title: 'Payments', href: '/payments', icon: CreditCard },
            { title: 'Expenses', href: '/expenses', icon: Wallet },
        ],
    },
    {
        title: 'Finance',
        icon: CreditCard,
        items: [
            { title: 'Analytics', href: '/analytics', icon: BarChart3 },
            { title: 'Reports', href: '/reports', icon: BarChart3 },
            { title: 'Fee Structures', href: '/fee-structures', icon: Receipt },
            { title: 'Expense Categories', href: '/expense-categories', icon: FolderGit2 },
        ],
    },
    {
        title: 'System',
        icon: Settings,
        items: [
            { title: 'School Settings', href: '/school', icon: Building2 },
            { title: 'Academic Years', href: '/academic-years', icon: Clock },
            { title: 'Classes', href: '/classes', icon: GraduationCap },
            { title: 'Terms', href: '/terms', icon: BookOpen },
            { title: 'Audit Logs', href: '/audit-logs', icon: BookOpen },
            { title: 'Promotions', href: '/promotions', icon: ArrowRight },
        ],
    },
]);

const openGroups = ref<Record<string, boolean>>({
    Core: true,
    Finance: false,
    System: false,
});

function toggleGroup(title: string) {
    openGroups.value[title] = !openGroups.value[title];
}

function isActive(href: string) {
    return window.location.pathname === href;
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-amber-100 bg-white">
        <SidebarHeader class="border-b border-amber-100 px-0 bg-white">
            <SidebarMenu class="px-3">
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="h-12">
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-0 bg-white">
            <div class="space-y-2 py-4">
                <!-- Render each collapsible group -->
                <div v-for="group in navigationGroups" :key="group.title" class="px-3 py-1">
                    <Collapsible
                        :open="openGroups[group.title]"
                        @update:open="toggleGroup(group.title)"
                        class="group/collapsible"
                    >
                        <CollapsibleTrigger as-child>
                            <button
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-amber-50"
                                :class="{
                                    'bg-amber-50 text-gray-900': openGroups[group.title],
                                    'text-gray-600 hover:text-gray-900': !openGroups[group.title],
                                }"
                            >
                                <div class="flex items-center gap-2.5">
                                    <component :is="group.icon" class="h-4 w-4 flex-shrink-0" />
                                    <span class="truncate">{{ group.title }}</span>
                                </div>
                                <ChevronDown
                                    class="h-4 w-4 flex-shrink-0 transition-transform duration-200"
                                    :class="{ 'rotate-180': openGroups[group.title] }"
                                />
                            </button>
                        </CollapsibleTrigger>

                        <CollapsibleContent class="overflow-hidden data-[state=closed]:animate-collapse data-[state=open]:animate-expand">
                            <SidebarMenu class="border-l border-amber-100 ml-3 pl-3.5 space-y-1 pt-2">
                                <SidebarMenuItem v-for="item in group.items" :key="item.title">
                                    <SidebarMenuButton
                                        as-child
                                        :isActive="isActive(item.href)"
                                        class="relative px-3 py-1.5 text-sm transition-colors"
                                        :class="{
                                            'bg-lime-100 text-gray-900 font-medium': isActive(item.href),
                                            'text-gray-600 hover:text-gray-900 hover:bg-amber-50': !isActive(item.href),
                                        }"
                                    >
                                        <Link :href="item.href" class="flex items-center gap-2.5 w-full">
                                            <component :is="item.icon" class="h-4 w-4 flex-shrink-0" />
                                            <span class="truncate">{{ item.title }}</span>
                                            <div
                                                v-if="isActive(item.href)"
                                                class="ml-auto h-1.5 w-1.5 rounded-full bg-lime-500"
                                            ></div>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </CollapsibleContent>
                    </Collapsible>
                </div>
            </div>
        </SidebarContent>

        <SidebarFooter class="border-t border-amber-100 px-0 bg-white">
            <div class="px-3 py-3 space-y-3">
                <NavFooter :items="[]" />
                <NavUser />
            </div>
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>