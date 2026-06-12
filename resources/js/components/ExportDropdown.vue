<script setup lang="ts">
import { Download, ChevronDown, FileSpreadsheet, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    baseUrl: string;
    filters?: Record<string, any>;
    label?: string;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon';
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
    label: 'Export',
    variant: 'outline',
    size: 'sm',
});

// Compute the download URLs with serialized queries
const getExportUrl = (format: 'pdf' | 'csv') => {
    const params = new URLSearchParams();
    params.append('format', format);

    if (props.filters) {
        Object.entries(props.filters).forEach(([key, val]) => {
            if (val !== null && val !== undefined && val !== '') {
                params.append(key, String(val));
            }
        });
    }

    return `${props.baseUrl}?${params.toString()}`;
};

const pdfUrl = computed(() => getExportUrl('pdf'));
const csvUrl = computed(() => getExportUrl('csv'));

const triggerExport = (url: string) => {
    window.location.href = url;
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button :variant="variant" :size="size" class="gap-2 cursor-pointer">
                <Download class="size-4 opacity-70" />
                <span>{{ label }}</span>
                <ChevronDown class="size-3.5 opacity-50" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel>Export Options</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="triggerExport(pdfUrl)" class="flex items-start gap-3 p-2.5 cursor-pointer">
                <div class="flex items-center justify-center p-1.5 rounded-md bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400">
                    <FileText class="size-4" />
                </div>
                <div class="flex flex-col">
                    <span class="font-medium text-xs">PDF Document</span>
                    <span class="text-[10px] text-muted-foreground">Best for printing & sharing</span>
                </div>
            </DropdownMenuItem>
            <DropdownMenuItem @click="triggerExport(csvUrl)" class="flex items-start gap-3 p-2.5 cursor-pointer">
                <div class="flex items-center justify-center p-1.5 rounded-md bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400">
                    <FileSpreadsheet class="size-4" />
                </div>
                <div class="flex flex-col">
                    <span class="font-medium text-xs">CSV Spreadsheet</span>
                    <span class="text-[10px] text-muted-foreground">Best for Excel data analysis</span>
                </div>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
