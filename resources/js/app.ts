import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import routePlugin from '@/lib/route';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) => {
        const pages = import.meta.glob<{ default: any }>('./pages/**/*.vue', { eager: true });
        
        return pages[`./pages/${name}.vue`];
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(routePlugin)
            .mount(el);
    },

    layout: ((name: string, page: any) => {
        // 1. If a component explicitly defines 'layout: null', respect it immediately!
        if (page.default?.layout === null) {
            return undefined;
        }

        // 2. Normalize the path name string to lowercase to eliminate casing bugs
        const normalizedPath = name.toLowerCase();

        switch (true) {
            case normalizedPath === 'welcome':
                return undefined;

            case normalizedPath.startsWith('auth/'):
                return undefined; // Force-strips layout for everything inside the Auth folder

            case normalizedPath.startsWith('settings/'):
            case normalizedPath.startsWith('teams/'):
                return [AppLayout, SettingsLayout];

            default:
                return AppLayout;
        }
    }) as any,
    progress: {
        color: '#4B5563',
    },
});
// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
