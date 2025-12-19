import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable untuk menampilkan toast dari flash messages
 * Gunakan di layout atau App.vue agar semua halaman dapat toast
 */
export function useFlashToast() {
    const page = usePage();
    const toast = useToast();

    // Watch for flash messages from server
    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.success) {
                toast.add({
                    title: 'Berhasil',
                    description: flash.success,
                    icon: 'i-lucide-check-circle',
                    color: 'success',
                });
            }

            if (flash?.error) {
                toast.add({
                    title: 'Error',
                    description: flash.error,
                    icon: 'i-lucide-x-circle',
                    color: 'error',
                });
            }
        },
        { immediate: true, deep: true }
    );

    // Helper function untuk manual toast
    const showSuccess = (title, description) => {
        toast.add({
            title,
            description,
            icon: 'i-lucide-check-circle',
            color: 'success',
        });
    };

    const showError = (title, description) => {
        toast.add({
            title,
            description,
            icon: 'i-lucide-x-circle',
            color: 'error',
        });
    };

    const showWarning = (title, description) => {
        toast.add({
            title,
            description,
            icon: 'i-lucide-alert-triangle',
            color: 'warning',
        });
    };

    const showInfo = (title, description) => {
        toast.add({
            title,
            description,
            icon: 'i-lucide-info',
            color: 'info',
        });
    };

    return {
        toast,
        showSuccess,
        showError,
        showWarning,
        showInfo,
    };
}
