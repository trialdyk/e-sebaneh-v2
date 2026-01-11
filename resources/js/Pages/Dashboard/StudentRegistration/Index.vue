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
                :rows="registrations.data" 
                :columns="columns"
            >
                <template #status-data="{ row }">
                    <UBadge :color="getStatusColor(row.status)" variant="subtle">
                        {{ getStatusLabel(row.status) }}
                    </UBadge>
                </template>
                <template #created_at-data="{ row }">
                    {{ new Date(row.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </template>
                <template #actions-data="{ row }">
                    <UButton 
                        v-if="row.status === 'pending'"
                        :to="route('dashboard.student-registrations.show', row.id)" 
                        color="primary" 
                        variant="ghost" 
                        icon="i-lucide-eye"
                        size="xs"
                    />
                    <UButton 
                        v-else
                        :to="route('dashboard.student-registrations.show', row.id)" 
                        color="neutral" 
                        variant="ghost" 
                        icon="i-lucide-eye"
                        size="xs"
                    />
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
    { key: 'registration_number', label: 'No. Daftar' },
    { key: 'name', label: 'Nama Lengkap' },
    { key: 'gender', label: 'L/P' },
    { key: 'city', label: 'Asal Kota', value: row => row.regency || '-' },
    { key: 'created_at', label: 'Tanggal Daftar' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '' },
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

watch(filters, debounce(() => {
    router.get(route('dashboard.student-registrations.index'), filters.value, { preserveState: true, replace: true });
}, 300), { deep: true });

watch(page, (newPage) => {
    router.get(route('dashboard.student-registrations.index'), { ...filters.value, page: newPage }, { preserveState: true, replace: true });
});
</script>
