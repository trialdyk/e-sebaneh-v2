import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable untuk generate breadcrumb items secara otomatis
 * 
 * @param {Object} options - Optional custom items atau overrides
 * @returns {Array} Breadcrumb items untuk UBreadcrumb component
 */
export function useBreadcrumb(customItems = null) {
    const page = usePage();

    // Mapping path ke label & icon
    const pathMap = {
        '/dashboard': { label: 'Dashboard', icon: 'i-lucide-house' },
        '/dashboard/school-years': { label: 'Tahun Ajaran', icon: 'i-lucide-calendar' },
        '/dashboard/boarding-schools': { label: 'Pondok', icon: 'i-lucide-building-2' },
        '/dashboard/boarding-schools/create': { label: 'Tambah Pondok', icon: 'i-lucide-plus' },
        '/dashboard/posts': { label: 'Posts', icon: 'i-lucide-file-text' },
        '/dashboard/users': { label: 'Users', icon: 'i-lucide-users' },
        '/dashboard/profile': { label: 'Profil', icon: 'i-lucide-user' },
    };

    const breadcrumbItems = computed(() => {
        // Jika custom items provided, gunakan itu
        if (customItems) {
            return customItems;
        }

        const url = page.url.split('?')[0]; // Remove query string
        const segments = url.split('/').filter(Boolean);
        const items = [];

        let currentPath = '';
        segments.forEach((segment, index) => {
            currentPath += '/' + segment;

            const mapped = pathMap[currentPath];
            const isLast = index === segments.length - 1;

            if (mapped) {
                items.push({
                    label: mapped.label,
                    icon: mapped.icon,
                    to: isLast ? undefined : currentPath, // Last item tidak punya link
                });
            }
        });

        return items;
    });

    return breadcrumbItems;
}
