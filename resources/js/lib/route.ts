import { usePage } from '@inertiajs/vue3';

interface RouteParams {
    [key: string]: string | number | boolean | object | null | undefined;
}

function resolveRoute(name: string, params?: RouteParams | any): string {
    const page = usePage();
    const routes = (page.props as any)?.routes || {};

    if (!routes[name]) {
        console.warn(`Route "${name}" not found`);
        return '/';
    }

    let url = routes[name];

    // Replace route parameters
    if (params) {
        if (typeof params === 'object') {
            Object.keys(params).forEach((key) => {
                const value = params[key];
                // Skip null or undefined values
                if (value === null || value === undefined) {
                    return;
                }
                // Handle model objects - use their ID if available
                const paramValue = typeof value === 'object' && value !== null && 'id' in value
                    ? (value as any).id
                    : String(value);
                url = url.replace(`{${key}}`, String(paramValue));
                url = url.replace(`:${key}`, String(paramValue));
            });
        }
    }

    return url;
}

declare global {
    function route(name: string, params?: RouteParams): string;
}

export default {
    install(app: any) {
        app.config.globalProperties.route = resolveRoute;
        (globalThis as any).route = resolveRoute;
    },
};
