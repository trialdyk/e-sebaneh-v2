import { ref } from 'vue';

const _useDashboard = () => {
    const isNotificationsSlideoverOpen = ref(false);

    return {
        isNotificationsSlideoverOpen
    };
};

export const useDashboard = _useDashboard;
