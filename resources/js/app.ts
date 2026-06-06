import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name, page) => {
        // 1. If a component explicitly defines 'layout: null', respect it immediately!
        if (page.default?.layout === null) {
            return null;
        }

        // 2. Normalize the path name string to lowercase to eliminate casing bugs
        const normalizedPath = name.toLowerCase();

        switch (true) {
            case normalizedPath === 'welcome':
                return null;
                
            case normalizedPath.startsWith('auth/'):
                return null; // Force-strips layout for everything inside the Auth folder
                
            case normalizedPath.startsWith('settings/'):
            case normalizedPath.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
                
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});
// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
