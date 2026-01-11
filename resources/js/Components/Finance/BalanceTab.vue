<template>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Saldo</p>
                        <p class="text-2xl font-bold text-primary mt-1">
                            Rp {{ formatCurrency(summary?.total_balance) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-wallet" class="w-10 h-10 text-primary opacity-20" />
                </div>
            </UCard>

            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Saldo</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                            Rp {{ formatCurrency(summary?.average_balance) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-trending-up" class="w-10 h-10 text-blue-600 opacity-20" />
                </div>
            </UCard>

            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Santri</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                            {{ summary?.student_count || 0 }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-users" class="w-10 h-10 text-green-600 opacity-20" />
                </div>
            </UCard>
        </div>

        <!-- Filters -->
        <UCard>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <UInput
                    v-model="localFilters.search"
                    placeholder="Cari nama/NIS..."
                    icon="i-lucide-search"
                />

                <USelect
                    v-model="localFilters.classroom_id"
                    :items="classroomOptions"
                    label-key="label"
                    value-key="value"
                    placeholder="Semua Kelas"
                />

                <UInput
                    v-model="localFilters.rfid"
                    placeholder="Cari RFID..."
                    icon="i-lucide-credit-card"
                />

                <UButton
                    @click="resetFilters"
                    color="neutral"
                    variant="soft"
                    icon="i-lucide-x"
                    block
                >
                    Reset Filter
                </UButton>
            </div>
        </UCard>

        <!-- Table -->
        <UCard>
            <UTable
                :data="students?.data || []"
                :columns="columns"
                :loading="loading"
            >
                <template #student-cell="{ row }">
                    <div class="flex items-center gap-3">
                        <UAvatar
                            :src="row.original.user?.photo"
                            :alt="row.original.user?.name"
                            size="sm"
                        />
                        <div>
                            <div class="flex items-center gap-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ row.original.user?.name }}</p>
                                <UTooltip text="Santri ini menggunakan PIN Default (123456)" v-if="row.original.user?.is_default_pin">
                                    <UIcon name="i-lucide-alert-triangle" class="w-4 h-4 text-amber-500 cursor-help" />
                                </UTooltip>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ row.original.student_id }}</p>
                        </div>
                    </div>
                </template>

                <template #classroom-cell="{ row }">
                    <span class="text-sm">{{ row.original.current_classroom?.classroom?.name || '-' }}</span>
                </template>

                <template #saldo-cell="{ row }">
                    <span class="font-semibold text-green-600 dark:text-green-400">
                        Rp {{ formatCurrency(row.original.user?.balance || 0) }}
                    </span>
                </template>

                <template #actions-cell="{ row }">
                    <UDropdownMenu :items="getActionItems(row.original)">
                        <UButton
                            color="neutral"
                            variant="ghost"
                            icon="i-lucide-more-vertical"
                            size="sm"
                        />
                    </UDropdownMenu>
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="flex justify-center mt-4" v-if="students?.total > students?.per_page">
                <UPagination
                    v-model="currentPage"
                    :total="students?.total || 0"
                    :page-count="students?.per_page || 15"
                />
            </div>
        </UCard>

        <!-- Topup Modal -->
        <UModal v-model:open="topupModal.open">
            <template #header>
                <h3 class="text-lg font-semibold">Topup Saldo</h3>
            </template>

            <template #body>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Santri: <strong>{{ topupModal.student?.user?.name }}</strong>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Saldo Saat Ini: <strong class="text-green-600">Rp {{ formatCurrency(topupModal.student?.user?.balance || 0) }}</strong>
                        </p>
                    </div>

                    <UFormField label="Jumlah Topup (Rp)" required :error="topupForm.errors.amount">
                        <UInput
                            v-model="topupForm.amount"
                            type="number"
                            min="1"
                            class="w-full"
                            placeholder="Masukkan jumlah..."
                        />
                    </UFormField>

                    <UFormField label="Keterangan" :error="topupForm.errors.description">
                        <UTextarea
                            v-model="topupForm.description"
                            placeholder="Keterangan (opsional)..."
                            rows="3"
                            class="w-full"
                        />
                    </UFormField>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        @click="topupModal.open = false"
                        color="neutral"
                        variant="soft"
                    >
                        Batal
                    </UButton>
                    <UButton
                        @click="submitTopup"
                        color="primary"
                        :loading="topupForm.processing"
                        icon="i-lucide-plus-circle"
                    >
                        Topup Saldo
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- Withdraw Modal -->
        <UModal v-model:open="withdrawModal.open">
            <template #header>
                <h3 class="text-lg font-semibold">Tarik Saldo</h3>
            </template>

            <template #body>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Santri: <strong>{{ withdrawModal.student?.user?.name }}</strong>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Saldo Saat Ini: <strong class="text-green-600">Rp {{ formatCurrency(withdrawModal.student?.user?.balance || 0) }}</strong>
                        </p>
                    </div>

                    <UFormField label="Jumlah Penarikan (Rp)" required :error="withdrawForm.errors.amount">
                        <UInput
                            v-model="withdrawForm.amount"
                            type="number"
                            min="1"
                            class="w-full"
                            placeholder="Masukkan jumlah..."
                        />
                    </UFormField>

                    <UFormField label="PIN Santri" required :error="withdrawForm.errors.pin">
                        <UInput
                            v-model="withdrawForm.pin"
                            type="password"
                            maxlength="6"
                            class="w-full"
                            placeholder="Masukkan PIN 6 digit..."
                        />
                    </UFormField>

                    <UFormField label="Keterangan" :error="withdrawForm.errors.description">
                        <UTextarea
                            v-model="withdrawForm.description"
                            placeholder="Keterangan (opsional)..."
                            class="w-full"
                            rows="3"
                        />
                    </UFormField>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        @click="withdrawModal.open = false"
                        color="neutral"
                        variant="soft"
                    >
                        Batal
                    </UButton>
                    <UButton
                        @click="submitWithdraw"
                        color="error"
                        :loading="withdrawForm.processing"
                        icon="i-lucide-minus-circle"
                    >
                        Tarik Saldo
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- Update PIN Modal -->
        <UModal v-model:open="pinModal.open">
            <template #header>
                <h3 class="text-lg font-semibold">Ubah PIN</h3>
            </template>

            <template #body>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Santri: <strong>{{ pinModal.student?.user?.name }}</strong>
                        </p>
                    </div>

                    <UAlert
                        v-if="pinModal.student?.user?.is_default_pin"
                        color="warning"
                        variant="subtle"
                        icon="i-lucide-alert-triangle"
                        title="PIN Default"
                        description="Santri ini menggunakan PIN default (123456)."
                        class="mb-4"
                    />

                    <UFormField label="PIN Baru" required :error="pinForm.errors.pin">
                        <UInput
                            v-model="pinForm.pin"
                            type="text"
                            maxlength="6"
                            placeholder="Masukkan PIN 6 digit..."
                            class="w-full"
                        />
                        <template #hint>
                            <p class="text-sm text-gray-500">PIN harus 6 digit angka</p>
                        </template>
                    </UFormField>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        @click="pinModal.open = false"
                        color="neutral"
                        variant="soft"
                    >
                        Batal
                    </UButton>
                    <UButton
                        @click="submitUpdatePin"
                        color="primary"
                        :loading="pinForm.processing"
                        icon="i-lucide-key"
                    >
                        Update PIN
                    </UButton>
                </div>
            </template>
        </UModal>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps({
    students: Object,
    summary: Object,
    classrooms: Array,
    filters: Object,
});

const loading = ref(false);
const currentPage = ref(props.students?.current_page || 1);

const columns = [
    { id: 'student', header: 'Nama / NIS' },
    { id: 'classroom', header: 'Kelas' },
    { id: 'saldo', header: 'Saldo' },
    { id: 'actions', header: 'Aksi' },
];

const localFilters = ref({
    search: props.filters?.search || '',
    classroom_id: props.filters?.classroom_id || 'all',
    rfid: props.filters?.rfid || '',
});

const classroomOptions = computed(() => [
    { label: 'Semua Kelas', value: 'all' },
    ...(props.classrooms || []).map(c => ({
        label: c.name,
        value: c.id,
    })),
]);

// Modals
const topupModal = ref({ open: false, student: null });
const withdrawModal = ref({ open: false, student: null });
const pinModal = ref({ open: false, student: null });

// Forms
const topupForm = useForm({
    amount: null,
    description: '',
});

const withdrawForm = useForm({
    amount: null,
    pin: '',
    description: '',
});

const pinForm = useForm({
    pin: '',
});

// Watch filters with debounce
const debouncedFilter = useDebounceFn(() => {
    router.get('/dashboard/finance/student-balance', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(localFilters, debouncedFilter, { deep: true });

watch(currentPage, (page) => {
    router.get('/dashboard/finance/student-balance', { ...localFilters.value, page }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

const resetFilters = () => {
    localFilters.value = {
        search: '',
        classroom_id: 'all',
        rfid: '',
    };
};

const getActionItems = (student) => [[{
    label: 'Topup Saldo',
    icon: 'i-lucide-plus-circle',
    iconClass: 'text-green-600',
    onSelect: () => {
        topupModal.value = { open: true, student };
        topupForm.reset();
        nextTick(() => topupForm.clearErrors());
    },
}, {
    label: 'Tarik Saldo',
    icon: 'i-lucide-minus-circle',
    iconClass: 'text-red-600',
    onSelect: () => {
        withdrawModal.value = { open: true, student };
        withdrawForm.reset();
        nextTick(() => withdrawForm.clearErrors());
    },
}, {
    label: 'Ubah PIN',
    icon: 'i-lucide-key',
    iconClass: 'text-blue-600',
    onSelect: () => {
        pinModal.value = { open: true, student };
        pinForm.reset();
        nextTick(() => pinForm.clearErrors());
    },
}]];

const submitTopup = () => {
    if (!topupModal.value.student) return;
    
    topupForm.post(`/dashboard/finance/student-balance/${topupModal.value.student.id}/topup`, {
        preserveScroll: true,
        onSuccess: () => {
            topupModal.value.open = false;
            topupForm.reset();
        },
    });
};

const submitWithdraw = () => {
    if (!withdrawModal.value.student) return;
    
    withdrawForm.post(`/dashboard/finance/student-balance/${withdrawModal.value.student.id}/withdraw`, {
        preserveScroll: true,
        onSuccess: () => {
            withdrawModal.value.open = false;
            withdrawForm.reset();
        },
    });
};

const submitUpdatePin = () => {
    if (!pinModal.value.student) return;
    
    pinForm.put(`/dashboard/finance/student-balance/${pinModal.value.student.id}/pin`, {
        preserveScroll: true,
        onSuccess: () => {
            pinModal.value.open = false;
            pinForm.reset();
        },
    });
};
</script>
