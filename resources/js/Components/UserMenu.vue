<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useColorMode } from '@vueuse/core';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const colorMode = useColorMode();
const toast = useToast();

// Get user from Inertia shared data
const user = computed(() => {
    const authUser = page.props.auth?.user;
    if (!authUser) {
        return {
            name: 'Guest',
            avatar: { src: null, alt: 'Guest' },
        };
    }
    return {
        name: authUser.name,
        avatar: {
            src: authUser.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${authUser.email}`,
            alt: authUser.name,
        },
    };
});

// Logout function
const handleLogout = () => {
    router.post('/logout', {}, {
        onSuccess: () => {
            toast.add({
                title: 'Logout Berhasil',
                description: 'Sampai jumpa kembali!',
                icon: 'i-lucide-log-out',
                color: 'success',
            });
        },
    });
};

const items = computed(() => [[{
    type: 'label',
    label: user.value.name,
    avatar: user.value.avatar,
}], [{
    label: 'Profil',
    icon: 'i-lucide-user',
    to: '/dashboard/profile',
}], [{
    label: 'Tampilan',
    icon: 'i-lucide-sun-moon',
    children: [{
        label: 'Light',
        icon: 'i-lucide-sun',
        type: 'checkbox',
        checked: colorMode.value === 'light',
        onSelect(e) {
            e.preventDefault();
            colorMode.value = 'light';
        },
    }, {
        label: 'Dark',
        icon: 'i-lucide-moon',
        type: 'checkbox',
        checked: colorMode.value === 'dark',
        onSelect(e) {
            e.preventDefault();
            colorMode.value = 'dark';
        },
    }],
}], [{
    label: 'Keluar',
    icon: 'i-lucide-log-out',
    onSelect: handleLogout,
}]]);
</script>

<template>
    <UDropdownMenu
        :items="items"
        :content="{ align: 'center', collisionPadding: 12 }"
        :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
    >
        <UButton
            :avatar="user.avatar"
            :label="collapsed ? undefined : user.name"
            :trailing-icon="collapsed ? undefined : 'i-lucide-chevrons-up-down'"
            color="neutral"
            variant="ghost"
            block
            :square="collapsed"
            class="data-[state=open]:bg-elevated"
            :ui="{ trailingIcon: 'text-dimmed' }"
        />
    </UDropdownMenu>
</template>
