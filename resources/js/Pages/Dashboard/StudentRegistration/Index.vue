<template>
    <DashboardPage
        title="Pendaftaran Santri"
        heading="Data Pendaftar Santri Baru"
        description="Daftar calon santri yang mendaftar melalui website."
        page-id="student-registrations"
    >
        <template #actions>
             <!-- No create button as registrations come from public form -->
        </template>

        <UCard class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <UInput v-model="filters.search" icon="i-lucide-search" placeholder="Cari nama atau nomor pendaftaran..." class="flex-1" />
                <USelect v-model="filters.status" :items="statusOptions" label-key="label" value-key="value" placeholder="Status" class="w-full md:w-48" />
            </div>
        </UCard>

        <UCard :ui="{ body: { padding: 'p-0' } }">
            <UTable 
                :data="registrations.data" 
                :columns="columns"
            >
                <template #created_at-data="{ row }">
                    {{ new Date(row.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </template>
                
                <template #status-data="{ row }">
                    <UBadge :color="getStatusColor(row.status)" variant="subtle">
                        {{ getStatusLabel(row.status) }}
                    </UBadge>
                </template>
                
                <template #actions-data="{ row }">
                    <UDropdownMenu :items="getActionItems(row)">
                        <UButton color="neutral" variant="ghost" icon="i-lucide-more-horizontal" />
                    </UDropdownMenu>
                </template>
            </UTable>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-800" v-if="registrations.links">
                <UPagination v-model="page" :total="registrations.total" :page-count="registrations.per_page" />
            </div>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const props = defineProps({
    registrations: Object,
    filters: Object,
});

const page = ref(props.registrations.current_page);
const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
});

const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'pending', label: 'Pending (Menunggu)' },
    { value: 'accepted', label: 'Diterima' },
    { value: 'rejected', label: 'Ditolak' },
];

const columns = [
    { accessorKey: 'registration_number', header: 'No. Daftar' },
    { accessorKey: 'name', header: 'Nama Lengkap' },
    { accessorKey: 'gender', header: 'L/P' },
    { accessorKey: 'regency', header: 'Asal Kota' },
    { id: 'created_at', header: 'Tanggal Daftar' }, // Virtual - uses template
    { id: 'status', header: 'Status' }, // Virtual - uses template
    { id: 'actions', header: '' }, // Virtual - uses template
];

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'warning';
        case 'accepted': return 'success';
        case 'rejected': return 'error';
        default: return 'neutral';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Menunggu';
        case 'accepted': return 'Diterima';
        case 'rejected': return 'Ditolak';
        default: return status;
    }
};

const getActionItems = (registration) => {
    const items = [
        [
            {
                label: 'Detail',
                icon: 'i-lucide-eye',
                iconClass: 'text-primary',
                onSelect: () => router.visit(`/dashboard/student-registrations/${registration.id}`)
            }
        ]
    ];

    // Download actions - always available
    items.push([
        {
            label: 'Download Pernyataan',
            icon: 'i-lucide-file-text',
            iconClass: 'text-blue-600',
            onSelect: () => window.open(`/register-santri/download/statement?token=${registration.id}`, '_blank')
        },
        {
            label: 'Download Formulir',
            icon: 'i-lucide-download',
            iconClass: 'text-green-600',
            onSelect: () => window.open(`/dashboard/student-registrations/${registration.id}/pdf`, '_blank')
        }
    ]);

    // Accept/Reject only for pending status
    if (registration.status === 'pending') {
        items.push([
            {
                label: 'Terima Pendaftar',
                icon: 'i-lucide-check-circle',
                iconClass: 'text-success',
                onSelect: () => acceptRegistration(registration.id)
            },
            {
                label: 'Tolak Pendaftar',
                icon: 'i-lucide-x-circle',
                iconClass: 'text-error',
                onSelect: () => rejectRegistration(registration.id)
            }
        ]);
    }

    return items;
};

const acceptRegistration = (id) => {
    if (confirm('Yakin ingin menerima pendaftar ini sebagai santri?')) {
        router.post(`/dashboard/student-registrations/${id}/accept`, {}, {
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
};

const rejectRegistration = (id) => {
    if (confirm('Yakin ingin menolak pendaftar ini?')) {
        router.post(`/dashboard/student-registrations/${id}/reject`, {}, {
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
};

watch(filters, debounce(() => {
    router.get('/dashboard/student-registrations', filters.value, { preserveState: true, replace: true });
}, 300), { deep: true });

watch(page, (newPage) => {
    router.get('/dashboard/student-registrations', { ...filters.value, page: newPage }, { preserveState: true, replace: true });
});
</script>
