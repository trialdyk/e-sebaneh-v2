<template>
    <UApp :toaster="{position: 'top-right'}">
        <UDashboardGroup unit="rem" storage="local">
            <UDashboardSidebar
                id="default"
                v-model:open="open"
                collapsible
                resizable
                class="bg-elevated/25"
                :ui="{ footer: 'lg:border-t lg:border-default' }"
            >
                <template #header="{ collapsed }">
                    <TeamsMenu :collapsed="collapsed" />
                </template>

                <template #default="{ collapsed }">
                    <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

                    <UNavigationMenu
                        :collapsed="collapsed"
                        :items="links[0]"
                        orientation="vertical"
                        tooltip
                        popover
                    />

                    <UNavigationMenu
                        :collapsed="collapsed"
                        :items="links[1]"
                        orientation="vertical"
                        tooltip
                        class="mt-auto"
                    />
                </template>

                <template #footer="{ collapsed }">
                    <UserMenu :collapsed="collapsed" />
                </template>
            </UDashboardSidebar>

            <UDashboardSearch :groups="groups" />

            <slot />
        </UDashboardGroup>
    </UApp>
</template>

<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TeamsMenu from '@/Components/TeamsMenu.vue';
import UserMenu from '@/Components/UserMenu.vue';
import { useFlashToast } from '@/composables/useFlashToast';

const page = usePage();
const open = ref(false);

// Initialize flash toast - akan auto show toast dari server flash messages
useFlashToast();

const links = [[{
    label: 'Dashboard',
    icon: 'i-lucide-house',
    to: '/dashboard',
    exact: true
}, {
    label: 'Tahun Ajaran',
    icon: 'i-lucide-calendar',
    to: '/dashboard/school-years'
}, {
    label: 'Pondok',
    icon: 'i-lucide-building-2',
    to: '/dashboard/boarding-schools'
}, {
    label: 'Posts',
    icon: 'i-lucide-file-text',
    to: '/dashboard/posts'
}, {
    label: 'Users',
    icon: 'i-lucide-users',
    to: '/dashboard/users'
}], [{
    label: 'Help & Support',
    icon: 'i-lucide-info',
    to: 'https://ui.nuxt.com',
    target: '_blank'
}]];

const groups = computed(() => [{
    id: 'links',
    label: 'Go to',
    items: links.flat()
}]);
</script>
