<template>
    <div class="space-y-6">
        <!-- Filters -->
        <UCard>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <UFormField label="Santri">
                    <USelect
                        v-model="localFilters.student_id"
                        :items="studentOptions"
                        label-key="label"
                        value-key="value"
                        placeholder="Semua Santri"
                    />
                </UFormField>

                <UFormField label="Tanggal Mulai">
                    <UInput
                        v-model="localFilters.start_date"
                        type="date"
                    />
                </UFormField>

                <UFormField label="Tanggal Akhir">
                    <UInput
                        v-model="localFilters.end_date"
                        type="date"
                    />
                </UFormField>

                <UFormField label="Tipe Transaksi">
                    <USelect
                        v-model="localFilters.type"
                        :items="typeOptions"
                        label-key="label"
                        value-key="value"
                    />
                </UFormField>
            </div>

            <div class="flex justify-between items-center mt-4">
                <UButton
                    @click="resetFilters"
                    color="neutral"
                    variant="soft"
                    icon="i-lucide-x"
                >
                    Reset Filter
                </UButton>

                <UButton
                    @click="exportExcel"
                    color="success"
                    icon="i-lucide-download"
                    :loading="exporting"
                >
                    Export Excel
                </UButton>
            </div>
        </UCard>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Topup</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                            Rp {{ formatCurrency(summary?.total_topup) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-arrow-up" class="w-10 h-10 text-green-600 opacity-20" />
                </div>
            </UCard>

            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Penarikan</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                            Rp {{ formatCurrency(summary?.total_withdraw) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-arrow-down" class="w-10 h-10 text-red-600 opacity-20" />
                </div>
            </UCard>
        </div>

        <!-- History Table -->
        <UCard>
            <template #header>
                <h3 class="text-lg font-semibold">Riwayat Transaksi</h3>
            </template>

            <UTable
                :data="history?.data || []"
                :columns="columns"
                :loading="loading"
            >
                <template #date-cell="{ row }">
                    <span class="text-sm">{{ formatDate(row.original.date) }}</span>
                </template>

                <template #student-cell="{ row }">
                    <div>
                        <p class="font-medium">{{ row.original.student?.user?.name || '-' }}</p>
                        <p class="text-sm text-gray-500">{{ row.original.student?.student_id || '-' }}</p>
                    </div>
                </template>

                <template #type-cell="{ row }">
                    <UBadge
                        :color="getTypeColor(row.original.type)"
                        variant="subtle"
                    >
                        {{ getTypeLabel(row.original.type) }}
                    </UBadge>
                </template>

                <template #amount-cell="{ row }">
                    <span
                        :class="[
                            'font-semibold',
                            row.original.type === 'withdraw' ? 'text-red-600' : 'text-green-600'
                        ]"
                    >
                        {{ row.original.type === 'withdraw' ? '-' : '+' }} Rp {{ formatCurrency(row.original.amount) }}
                    </span>
                </template>

                <template #description-cell="{ row }">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ row.original.description || '-' }}
                    </span>
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="flex justify-center mt-4" v-if="history?.total > history?.per_page">
                <UPagination
                    v-model="currentPage"
                    :total="history?.total || 0"
                    :page-count="history?.per_page || 15"
                />
            </div>
        </UCard>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { format, parseISO } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';

const props = defineProps({
    history: Object,
    summary: Object,
    students: Object,
    filters: Object,
});

const loading = ref(false);
const exporting = ref(false);
const currentPage = ref(props.history?.current_page || 1);

const columns = [
    { id: 'date', header: 'Tanggal' },
    { id: 'student', header: 'Santri' },
    { id: 'type', header: 'Tipe' },
    { id: 'amount', header: 'Jumlah' },
    { id: 'description', header: 'Keterangan' },
];

const localFilters = ref({
    student_id: props.filters?.student_id || 'all',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    type: props.filters?.type || 'all',
});

const studentOptions = computed(() => {
    const studentsList = Array.isArray(props.students) 
        ? props.students 
        : (props.students?.data || []);
    return [
        { label: 'Semua Santri', value: 'all' },
        ...studentsList.map(s => ({
            label: `${s.user?.name || s.name} (${s.student_id})`,
            value: s.id,
        })),
    ];
});

const typeOptions = [
    { label: 'Semua Tipe', value: 'all' },
    { label: 'Topup via Mobile App', value: 'topup' },
    { label: 'Topup oleh Admin', value: 'topup_by_admin' },
    { label: 'Penarikan', value: 'withdraw' },
];

// Watch filters with debounce
const debouncedFilter = useDebounceFn(() => {
    router.get('/dashboard/finance/student-balance/history', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(localFilters, debouncedFilter, { deep: true });

watch(currentPage, (page) => {
    router.get('/dashboard/finance/student-balance/history', { ...localFilters.value, page }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatDate = (date) => {
    if (!date) return '-';
    try {
        return format(parseISO(date), 'dd MMM yyyy HH:mm', { locale: idLocale });
    } catch {
        return date;
    }
};

const getTypeLabel = (type) => {
    const labels = {
        topup: 'Topup via Mobile App',
        topup_by_admin: 'Topup oleh Admin',
        withdraw: 'Penarikan',
    };
    return labels[type] || type;
};

const getTypeColor = (type) => {
    const colors = {
        topup: 'success',
        topup_by_admin: 'info',
        withdraw: 'error',
    };
    return colors[type] || 'neutral';
};

const resetFilters = () => {
    localFilters.value = {
        student_id: 'all',
        start_date: '',
        end_date: '',
        type: 'all',
    };
};

const exportExcel = () => {
    exporting.value = true;
    
    const params = new URLSearchParams();
    Object.entries(localFilters.value).forEach(([key, value]) => {
        if (value && value !== 'all') {
            params.append(key, value);
        }
    });

    window.location.href = '/dashboard/finance/student-balance/history/export' + '?' + params.toString();

    setTimeout(() => {
        exporting.value = false;
    }, 1000);
};
</script>
