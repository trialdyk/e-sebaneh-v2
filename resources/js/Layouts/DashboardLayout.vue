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

const links = computed(() => {
    const roles = page.props.auth.user?.roles || [];
    const isSuperAdmin = roles.includes('super-admin');
    const isAdminPondok = roles.includes('admin-pondok');
    const isAdminPPOB = roles.includes('admin-ppob');
    const canAccessPPOB = isSuperAdmin || isAdminPPOB;

    const mainLinks = [
        {
            label: 'Dashboard',
            icon: 'i-lucide-house',
            to: '/dashboard',
            exact: true,
            onSelect: () => { open.value = false; }
        },

        // ========================================
        // SUPER ADMIN ONLY - Master Data
        // ========================================
        ...(isSuperAdmin ? [{
            label: 'Master Data',
            icon: 'i-lucide-database',
            type: 'trigger',
            defaultOpen: page.url.startsWith('/dashboard/schools') || page.url.startsWith('/dashboard/positions') || page.url.startsWith('/dashboard/school-years'),
            children: [
                {
                    label: 'Jenjang Sekolah',
                    to: '/dashboard/schools',
                    icon: 'i-lucide-graduation-cap',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Jabatan',
                    to: '/dashboard/positions',
                    icon: 'i-lucide-briefcase',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Tahun Ajaran',
                    to: '/dashboard/school-years',
                    icon: 'i-lucide-calendar',
                    onSelect: () => { open.value = false; }
                }
            ]
        }] : []),

        // Pondok - visible to all
        {
            label: 'Pondok',
            icon: 'i-lucide-building-2',
            to: '/dashboard/boarding-schools',
            onSelect: () => { open.value = false; }
        },

        // ========================================
        // ADMIN PONDOK - Kesiswaan
        // ========================================
        ...(isAdminPondok ? [{
            label: 'Kesiswaan',
            icon: 'i-lucide-users',
            type: 'trigger',
            defaultOpen: page.url.startsWith('/dashboard/students') || page.url.startsWith('/dashboard/classrooms') || page.url.startsWith('/dashboard/bed-rooms'),
            children: [
                {
                    label: 'Pendaftaran (PSB)',
                    to: '/dashboard/student-registrations',
                    icon: 'i-lucide-clipboard-list',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Data Santri',
                    to: '/dashboard/students',
                    icon: 'i-lucide-user-check',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'RFID Santri',
                    to: '/dashboard/student-rfid',
                    icon: 'i-lucide-credit-card',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Data Kelas',
                    to: '/dashboard/classrooms',
                    icon: 'i-lucide-school',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Data Kamar',
                    to: '/dashboard/bed-rooms',
                    icon: 'i-lucide-bed',
                    onSelect: () => { open.value = false; }
                }
            ]
        }] : []),

        // ========================================
        // ADMIN PONDOK - Keuangan
        // ========================================
        ...(isAdminPondok ? [{
            label: 'Keuangan',
            icon: 'i-lucide-wallet',
            type: 'trigger',
            defaultOpen: page.url.startsWith('/dashboard/finance'),
            children: [
                {
                    label: 'Pos Keuangan',
                    to: '/dashboard/finance/accounts',
                    icon: 'i-lucide-landmark',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Laporan Arus Kas',
                    to: '/dashboard/finance/transactions',
                    icon: 'i-lucide-scroll-text',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Manajemen Saldo',
                    to: '/dashboard/finance/student-balance',
                    icon: 'i-lucide-banknote',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Tabungan Santri',
                    to: '/dashboard/finance/student-savings',
                    icon: 'i-lucide-piggy-bank',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Batas Penarikan',
                    to: '/dashboard/finance/student-withdraw-limit',
                    icon: 'i-lucide-hand-coins',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Tagihan Santri',
                    to: '/dashboard/finance/student-invoices',
                    icon: 'i-lucide-receipt',
                    onSelect: () => { open.value = false; }
                }
            ]
        }] : []),

        // ========================================
        // ADMIN PONDOK - Kepegawaian & Pengaturan
        // ========================================
        ...(isAdminPondok ? [{
            label: 'Data Pegawai',
            icon: 'i-lucide-user-cog',
            to: '/dashboard/teachers',
            onSelect: () => { open.value = false; }
        }, {
            label: 'Pengaturan Surat',
            icon: 'i-lucide-file-signature',
            to: '/dashboard/settings/letter',
            onSelect: () => { open.value = false; }
        }] : []),

        // ========================================
        // PPOB - Super Admin & Admin Pondok
        // ========================================
        ...(canAccessPPOB ? [{
            label: 'PPOB',
            icon: 'i-lucide-smartphone',
            type: 'trigger',
            defaultOpen: page.url.startsWith('/dashboard/ppob'),
            children: [
                {
                    label: 'Admin PPOB',
                    to: '/dashboard/ppob/admin',
                    icon: 'i-lucide-user-cog',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Metode Pembayaran',
                    to: '/dashboard/ppob/payment-methods',
                    icon: 'i-lucide-landmark',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Produk',
                    to: '/dashboard/ppob/products',
                    icon: 'i-lucide-package',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Biaya Pembayaran',
                    to: '/dashboard/ppob/payment-fees',
                    icon: 'i-lucide-receipt',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Verifikasi Topup',
                    to: '/dashboard/ppob/topup-verification',
                    icon: 'i-lucide-check-circle',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'History Transaksi',
                    to: '/dashboard/ppob/transactions',
                    icon: 'i-lucide-history',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Penarikan Saldo',
                    to: '/dashboard/ppob/withdrawals',
                    icon: 'i-lucide-arrow-down-circle',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Daftar Pengguna',
                    to: '/dashboard/ppob/users',
                    icon: 'i-lucide-users',
                    onSelect: () => { open.value = false; }
                }
            ]
        }] : []),

        // ========================================
        // CMS - Super Admin Only
        // ========================================
        ...(isSuperAdmin ? [{
            label: 'CMS',
            icon: 'i-lucide-layout-dashboard',
            type: 'trigger',
            defaultOpen: page.url.startsWith('/dashboard/cms'),
            children: [
                {
                    label: 'Berita',
                    to: '/dashboard/cms/posts',
                    icon: 'i-lucide-newspaper',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Program',
                    to: '/dashboard/cms/programs',
                    icon: 'i-lucide-book-open',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Galeri',
                    to: '/dashboard/cms/galleries',
                    icon: 'i-lucide-images',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Testimoni',
                    to: '/dashboard/cms/testimonials',
                    icon: 'i-lucide-quote',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'FAQ',
                    to: '/dashboard/cms/faqs',
                    icon: 'i-lucide-help-circle',
                    onSelect: () => { open.value = false; }
                },
                {
                    label: 'Web Setting',
                    to: '/dashboard/cms/settings',
                    icon: 'i-lucide-settings',
                    onSelect: () => { open.value = false; }
                }
            ]
        }] : []),
    ];

    const supportLinks = [{
        label: 'Help & Support',
        icon: 'i-lucide-info',
        to: 'https://ui.nuxt.com',
        target: '_blank'
    }];

    return [mainLinks, supportLinks];
});

const groups = computed(() => [{
    id: 'links',
    label: 'Go to',
    items: links.value.flat()
}]);
</script>
