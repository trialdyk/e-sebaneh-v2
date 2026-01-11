<template>
    <DashboardPage 
        title="Laporan Arus Kas" 
        heading="Laporan Arus Kas" 
        description="Riwayat transaksi keuangan dan mutasi rekening"
    >
        <template #header>
            <div class="flex gap-2">
                 <UButton icon="i-lucide-download" color="gray" variant="ghost" label="Export Excel" @click="exportExcel" />
                 <UButton icon="i-lucide-plus" label="Catat Transaksi" @click="openCreateModal" />
            </div>
        </template>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row gap-4 mb-6 items-end">
             <div class="flex gap-2 flex-wrap items-end w-full md:w-auto">
                 <UFormGroup label="Tanggal Mulai">
                     <UInput type="date" v-model="filterForm.start_date" />
                 </UFormGroup>
                 <UFormGroup label="Tanggal Akhir">
                     <UInput type="date" v-model="filterForm.end_date" />
                 </UFormGroup>
                 <UFormGroup label="Pos Keuangan">
                      <USelectMenu
                        v-model="filterForm.account_id"
                        :items="accountOptions"
                        value-key="id"
                        label-key="name"
                        placeholder="Semua Pos"
                        class="w-48"
                      />
                 </UFormGroup>
                 <UButton icon="i-lucide-filter" @click="applyFilters" label="Filter" />
                 <UButton color="gray" variant="ghost" icon="i-lucide-x" @click="resetFilters" />
             </div>

        </div>

        <!-- Transactions Table -->
        <UCard :ui="{ body: { padding: 'p-0 sm:p-0' } }">
            <UTable :rows="transactions.data" :columns="columns">
                <template #account-cell="{ row }">
                    <div class="font-medium">{{ row.original.account.name }}</div>
                    <div class="text-xs text-gray-500 capitalize">{{ row.original.account.type }}</div>
                </template>

                <template #amount-cell="{ row }">
                    <span :class="row.original.type === 'credit' ? 'text-green-600' : 'text-red-600'" class="font-mono font-bold">
                        {{ row.original.type === 'credit' ? '+' : '-' }} {{ formatCurrency(row.original.amount) }}
                    </span>
                </template>

                <template #date-cell="{ row }">
                     {{ formatDate(row.original.date) }}
                </template>
                
                <template #user-cell="{ row }">
                    {{ row.original.user?.name || '-' }}
                </template>

                <template #description-cell="{ row }">
                    <div class="max-w-xs truncate" :title="row.original.description">{{ row.original.description }}</div>
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                 <UPagination
                    v-model="page"
                    :page-count="transactions.per_page"
                    :total="transactions.total"
                />
            </div>
        </UCard>

        <!-- Manual Transaction Modal -->
        <UModal v-model:open="isCreateOpen">
            <template #header>
                <div>
                     <h3 class="text-lg font-semibold">Catat Transaksi Manual</h3>
                     <p class="text-xs text-gray-500 mt-1">Gunakan fitur ini untuk penyesuaian saldo atau transaksi tunai manual.</p>
                </div>
            </template>
            <template #body>
                <form @submit.prevent="submitTransaction" class="space-y-4">
                    <UFormField label="Pos Keuangan" required :error="form.errors.finance_account_id">
                         <USelectMenu v-model="form.finance_account_id" :options="accounts" option-attribute="name" value-attribute="id" class="w-full" :disabled="form.processing" />
                    </UFormField>
                    
                    <UFormField label="Jenis Transaksi" required :error="form.errors.type">
                        <USelect v-model="form.type" :items="transactionTypes" label-key="label" value-key="value" placeholder="Pilih Jenis Transaksi" class="w-full" :disabled="form.processing" />
                    </UFormField>

                    <UFormField label="Jumlah (Rp)" required :error="form.errors.amount">
                        <UInput v-model="form.amount" type="number" min="0" class="w-full" :disabled="form.processing" />
                    </UFormField>

                    <UFormField label="Deskripsi/Keterangan" required :error="form.errors.description">
                        <UTextarea v-model="form.description" class="w-full" :disabled="form.processing" />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" label="Batal" @click="isCreateOpen = false" :disabled="form.processing" />
                    <UButton color="primary" label="Simpan" :loading="form.processing" @click="submitTransaction" />
                </div>
            </template>
        </UModal>

    </DashboardPage>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

const props = defineProps({
    transactions: Object,
    accounts: Array,
    filters: Object
});

const isCreateOpen = ref(false);

const filterForm = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    account_id: props.filters.account_id || null
});

const accountOptions = computed(() => {
    return [{ id: null, name: 'Semua Pos' }, ...props.accounts];
});

const page = ref(props.transactions.current_page);

watch(page, (newPage) => {
    router.get('/dashboard/finance/transactions', { ...filterForm.value, page: newPage }, { preserveState: true, preserveScroll: true });
});

const applyFilters = () => {
    page.value = 1;
    router.get('/dashboard/finance/transactions', filterForm.value, { preserveState: true });
};

const resetFilters = () => {
    filterForm.value = { start_date: '', end_date: '', account_id: null };
    applyFilters();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return format(new Date(dateString), 'dd MMM yyyy, HH:mm', { locale: id });
};

const columns = [
    { accessorKey: 'date', header: 'Tanggal' },
    { id: 'account', header: 'Pos Keuangan' },
    { accessorKey: 'description', header: 'Keterangan' },
    { id: 'amount', header: 'Nominal' },
    { id: 'user', header: 'Admin' }
];

// Manual Transaction Form
const form = useForm({
    finance_account_id: null,
    type: 'credit',
    amount: '',
    description: ''
});

const transactionTypes = [
    { label: 'Pemasukan (Credit)', value: 'credit' },
    { label: 'Pengeluaran (Debit)', value: 'debit' }
];


const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateOpen.value = true;
};

const submitTransaction = () => {
    form.post('/dashboard/finance/transactions', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        }
    });
};

const exportExcel = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.open('/dashboard/finance/transactions/export?' + params, '_blank');
};
</script>
