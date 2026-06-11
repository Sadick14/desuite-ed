import { usePage } from '@inertiajs/vue3';

interface RouteParams {
    [key: string]: string | number | boolean | object | null | undefined;
}

function resolveRoute(name: string, params?: RouteParams | any): string {
    const page = usePage();
    const routes = (page.props as any)?.routes || {};

    // Handle routes with parameters that aren't in the routes object
    // Common patterns for Laravel routes
    const routePatterns: Record<string, string> = {
        'attendance.student': '/attendance/students/{student}',
        'students.show': '/students/{student}',
        'reports.show': '/reports/{id}',
        'reports.download': '/reports/{id}/download',
    };

    let url = routes[name];

    // If route not found in shared routes, try pattern matching
    if (!url && routePatterns[name]) {
        url = routePatterns[name];
    }

    if (!url) {
        console.warn(`Route "${name}" not found`);
        return '/';
    }

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
