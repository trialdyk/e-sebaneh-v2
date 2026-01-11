<template>
    <DashboardPage
        title="Tabungan Santri"
        heading="Tabungan Santri"
        description="Kelola tabungan santri - setor dan tarik dengan PIN"
        page-id="student-savings"
    >
        <template #header>
            <UButton
                color="primary"
                variant="soft"
                icon="i-lucide-arrow-left"
                to="/dashboard/finance/student-balance"
            >
                Kembali ke Saldo
            </UButton>
        </template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Tabungan</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                            Rp {{ formatCurrency(summary?.total_savings) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-piggy-bank" class="w-10 h-10 text-blue-600 opacity-20" />
                </div>
            </UCard>

            <UCard>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Tabungan</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                            Rp {{ formatCurrency(summary?.average_savings) }}
                        </p>
                    </div>
                    <UIcon name="i-lucide-trending-up" class="w-10 h-10 text-purple-600 opacity-20" />
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

        <!-- Info Alert -->
        <UAlert
            color="info"
            variant="subtle"
            icon="i-lucide-info"
            class="mb-6"
        >
            <template #title>Perbedaan Saldo vs Tabungan</template>
            <template #description>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><strong>Saldo</strong>: Untuk pengeluaran sehari-hari, dapat ditarik santri via RFID</li>
                    <li><strong>Tabungan</strong>: Untuk disimpan, hanya dapat ditarik via admin dengan PIN</li>
                </ul>
            </template>
        </UAlert>

        <!-- Filters -->
        <UCard class="mb-6">
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
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ row.original.user?.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ row.original.student_id }}</p>
                        </div>
                    </div>
                </template>

                <template #classroom-cell="{ row }">
                    <span class="text-sm">{{ row.original.current_classroom?.classroom?.name || '-' }}</span>
                </template>

                <template #savings-cell="{ row }">
                    <span class="font-semibold text-blue-600 dark:text-blue-400">
                        Rp {{ formatCurrency(row.original.savings || 0) }}
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
            <div class="flex justify-between items-center px-4 py-3 border-t border-default" v-if="students?.total > students?.per_page">
                <span class="text-sm text-muted">
                    Menampilkan {{ students.from }} - {{ students.to }} dari {{ students.total }}
                </span>
                <UPagination
                    v-model="currentPage"
                    :total="students?.total || 0"
                    :page-count="students?.per_page || 15"
                />
            </div>
        </UCard>

        <!-- Deposit Modal -->
        <UModal v-model:open="depositModal.open">
            <template #header>
                <h3 class="text-lg font-semibold">Setor Tabungan</h3>
            </template>

            <template #body>
                <form @submit.prevent="submitDeposit" class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Santri: <strong>{{ depositModal.student?.user?.name }}</strong>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tabungan Saat Ini: <strong class="text-blue-600">Rp {{ formatCurrency(depositModal.student?.savings || 0) }}</strong>
                        </p>
                    </div>

                    <UFormField label="Jumlah Setoran (Rp)" required :error="depositForm.errors.amount">
                        <UInput
                            v-model="depositForm.amount"
                            type="number"
                            min="1"
                            step="1000"
                            placeholder="Masukkan jumlah..."
                        />
                    </UFormField>

                    <UFormField label="Keterangan" :error="depositForm.errors.notes">
                        <UTextarea
                            v-model="depositForm.notes"
                            placeholder="Keterangan (opsional)..."
                            rows="3"
                        />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        @click="depositModal.open = false"
                        color="neutral"
                        variant="soft"
                    >
                        Batal
                    </UButton>
                    <UButton
                        @click="submitDeposit"
                        color="success"
                        :loading="depositForm.processing"
                        icon="i-lucide-plus-circle"
                    >
                        Setor Tabungan
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- Withdraw Modal -->
        <UModal v-model:open="withdrawModal.open">
            <template #header>
                <h3 class="text-lg font-semibold">Tarik Tabungan</h3>
            </template>

            <template #body>
                <form @submit.prevent="submitWithdraw" class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Santri: <strong>{{ withdrawModal.student?.user?.name }}</strong>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tabungan Saat Ini: <strong class="text-blue-600">Rp {{ formatCurrency(withdrawModal.student?.savings || 0) }}</strong>
                        </p>
                    </div>

                    <UFormField label="Jumlah Penarikan (Rp)" required :error="withdrawForm.errors.amount">
                        <UInput
                            v-model="withdrawForm.amount"
                            type="number"
                            min="1"
                            step="1000"
                            placeholder="Masukkan jumlah..."
                        />
                    </UFormField>

                    <UFormField label="PIN Santri" required :error="withdrawForm.errors.pin">
                        <UInput
                            v-model="withdrawForm.pin"
                            type="password"
                            maxlength="6"
                            placeholder="Masukkan PIN 6 digit..."
                        />
                    </UFormField>

                    <UFormField label="Keterangan" :error="withdrawForm.errors.notes">
                        <UTextarea
                            v-model="withdrawForm.notes"
                            placeholder="Keterangan (opsional)..."
                            rows="3"
                        />
                    </UFormField>
                </form>
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
                        Tarik Tabungan
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- History Modal -->
        <UModal v-model:open="historyModal.open" :ui="{ width: 'sm:max-w-4xl' }">
            <template #header>
                <h3 class="text-lg font-semibold">Riwayat Tabungan: {{ historyModal.student?.user?.name }}</h3>
            </template>

            <template #body>
                <div class="space-y-4">
                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <UFormField label="Tanggal Mulai">
                            <UInput
                                v-model="historyFilters.start_date"
                                type="date"
                            />
                        </UFormField>

                        <UFormField label="Tanggal Akhir">
                            <UInput
                                v-model="historyFilters.end_date"
                                type="date"
                            />
                        </UFormField>

                        <UFormField label="Tipe">
                            <USelect
                                v-model="historyFilters.type"
                                :items="typeOptions"
                                label-key="label"
                                value-key="value"
                            />
                        </UFormField>
                    </div>

                    <!-- Summary -->
                    <div class="grid grid-cols-2 gap-4" v-if="historySummary">
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-sm text-green-600 dark:text-green-400">Total Setor</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                Rp {{ formatCurrency(historySummary.total_deposit) }}
                            </p>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">Total Tarik</p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-400">
                                Rp {{ formatCurrency(historySummary.total_withdrawal) }}
                            </p>
                        </div>
                    </div>

                    <!-- Table -->
                    <UTable
                        :data="historyTransactions.data || []"
                        :columns="historyColumns"
                        :loading="loadingHistory"
                    >
                        <template #created_at-cell="{ row }">
                            <span class="text-sm">{{ formatDate(row.original.created_at) }}</span>
                        </template>

                        <template #type-cell="{ row }">
                            <UBadge
                                :color="row.original.type === 'deposit' ? 'success' : 'error'"
                                variant="subtle"
                            >
                                {{ row.original.type === 'deposit' ? 'Setor' : 'Tarik' }}
                            </UBadge>
                        </template>

                        <template #amount-cell="{ row }">
                            <span
                                :class="[
                                    'font-semibold',
                                    row.original.type === 'deposit' ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ row.original.type === 'deposit' ? '+' : '-' }} Rp {{ formatCurrency(row.original.amount) }}
                            </span>
                        </template>

                        <template #balance_after-cell="{ row }">
                            <span class="text-sm font-medium">Rp {{ formatCurrency(row.original.balance_after) }}</span>
                        </template>

                        <template #notes-cell="{ row }">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ row.original.notes || '-' }}</span>
                        </template>
                    </UTable>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <UButton
                        @click="exportHistory"
                        color="success"
                        variant="soft"
                        icon="i-lucide-download"
                        :loading="exporting"
                    >
                        Export Excel
                    </UButton>

                    <UPagination
                        v-model="historyPage"
                        :total="historyTransactions.total || 0"
                        :page-count="historyTransactions.per_page || 15"
                    />
                </div>
            </template>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { format, parseISO } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    students: Object,
    summary: Object,
    classrooms: Array,
    filters: Object,
});

const loading = ref(false);
const loadingHistory = ref(false);
const exporting = ref(false);
const currentPage = ref(props.students?.current_page || 1);

const columns = [
    { id: 'student', header: 'Nama / NIS' },
    { id: 'classroom', header: 'Kelas' },
    { id: 'savings', header: 'Tabungan' },
    { id: 'actions', header: 'Aksi' },
];

const historyColumns = [
    { id: 'created_at', header: 'Tanggal' },
    { id: 'type', header: 'Tipe' },
    { id: 'amount', header: 'Jumlah' },
    { id: 'balance_after', header: 'Saldo Setelah' },
    { id: 'notes', header: 'Keterangan' },
];

const localFilters = ref({
    search: props.filters?.search || '',
    classroom_id: props.filters?.classroom_id || 'all',
    rfid: props.filters?.rfid || '',
});

const historyFilters = ref({
    start_date: '',
    end_date: '',
    type: 'all',
});

const historyTransactions = ref({ data: [] });
const historySummary = ref(null);
const historyPage = ref(1);

const typeOptions = [
    { label: 'Semua Tipe', value: 'all' },
    { label: 'Setor', value: 'deposit' },
    { label: 'Tarik', value: 'withdrawal' },
];

const classroomOptions = computed(() => [
    { label: 'Semua Kelas', value: 'all' },
    ...(props.classrooms || []).map(c => ({
        label: c.name,
        value: c.id,
    })),
]);

// Modals
const depositModal = ref({ open: false, student: null });
const withdrawModal = ref({ open: false, student: null });
const historyModal = ref({ open: false, student: null });

// Forms
const depositForm = useForm({
    amount: '',
    notes: '',
});

const withdrawForm = useForm({
    amount: '',
    pin: '',
    notes: '',
});

// Watch filters with debounce
const debouncedFilter = useDebounceFn(() => {
    router.get('/dashboard/finance/student-savings', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(localFilters, debouncedFilter, { deep: true });

watch(currentPage, (page) => {
    router.get('/dashboard/finance/student-savings', { ...localFilters.value, page }, {
        preserveState: true,
        preserveScroll: true,
    });
});

// Watch history filters
watch(historyFilters, async () => {
    if (historyModal.value.student) {
        await fetchHistory();
    }
}, { deep: true });

watch(historyPage, async () => {
    await fetchHistory();
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

const resetFilters = () => {
    localFilters.value = {
        search: '',
        classroom_id: 'all',
        rfid: '',
    };
};

const getActionItems = (student) => [[{
    label: 'Setor Tabungan',
    icon: 'i-lucide-plus-circle',
    iconClass: 'text-green-600',
    onSelect: () => {
        depositModal.value = { open: true, student };
        depositForm.reset();
    },
}, {
    label: 'Tarik Tabungan',
    icon: 'i-lucide-minus-circle',
    iconClass: 'text-red-600',
    onSelect: () => {
        withdrawModal.value = { open: true, student };
        withdrawForm.reset();
    },
}, {
    label: 'Riwayat Tabungan',
    icon: 'i-lucide-history',
    iconClass: 'text-blue-600',
    onSelect: () => openHistoryModal(student),
}]];

const submitDeposit = () => {
    if (!depositModal.value.student) return;
    
    depositForm.post(`/dashboard/finance/student-savings/${depositModal.value.student.id}/deposit`, {
        preserveScroll: true,
        onSuccess: () => {
            depositModal.value.open = false;
            depositForm.reset();
        },
    });
};

const submitWithdraw = () => {
    if (!withdrawModal.value.student) return;
    
    withdrawForm.post(`/dashboard/finance/student-savings/${withdrawModal.value.student.id}/withdraw`, {
        preserveScroll: true,
        onSuccess: () => {
            withdrawModal.value.open = false;
            withdrawForm.reset();
        },
    });
};

const openHistoryModal = async (student) => {
    historyModal.value = { open: true, student };
    historyFilters.value = {
        start_date: '',
        end_date: '',
        type: 'all',
    };
    historyPage.value = 1;
    await fetchHistory();
};

const fetchHistory = async () => {
    if (!historyModal.value.student) return;

    loadingHistory.value = true;
    try {
        const params = new URLSearchParams({
            ...historyFilters.value,
            page: historyPage.value,
        });

        const response = await axios.get(
            `/dashboard/finance/student-savings/${historyModal.value.student.id}/history` + '?' + params,
        );

        historyTransactions.value = response.data.transactions;
        historySummary.value = response.data.summary;
    } catch (error) {
        console.error('Error fetching history:', error);
    } finally {
        loadingHistory.value = false;
    }
};

const exportHistory = () => {
    if (!historyModal.value.student) return;

    exporting.value = true;

    const params = new URLSearchParams();
    Object.entries(historyFilters.value).forEach(([key, value]) => {
        if (value && value !== 'all') {
            params.append(key, value);
        }
    });

    window.location.href = `/dashboard/finance/student-savings/${historyModal.value.student.id}/export` + '?' + params.toString();

    setTimeout(() => {
        exporting.value = false;
    }, 1000);
};
</script>
