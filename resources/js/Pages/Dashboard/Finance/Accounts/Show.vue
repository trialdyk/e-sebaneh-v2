<template>
    <DashboardPage 
        :title="`Detail Pos: ${account.name}`" 
        :heading="account.name" 
        :description="account.description || 'Detail transaksi dan mutasi saldo'"
    >
        <template #header>
            <div class="flex gap-2">
                <UButton color="gray" variant="ghost" icon="i-lucide-arrow-left" label="Kembali" @click="router.visit('/dashboard/finance/accounts')" />
                <UButton color="primary" variant="outline" icon="i-lucide-download" label="Export Excel" @click="exportToExcel" />
                <UButton icon="i-lucide-plus" label="Catat Mutasi" @click="isCreateOpen = true" />
            </div>
        </template>

        <!-- Summary cards - Breakdown untuk Saldo Santri dan Tagihan Santri -->
        <div v-if="account.slug === 'student-balance' || account.slug === 'student-invoices'" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <UCard class="bg-primary-50 dark:bg-primary-950">
                <div class="p-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium uppercase tracking-wider">Total Saldo</p>
                    <p class="text-3xl font-bold font-mono text-primary-600 dark:text-primary-400 mt-1">{{ formatCurrency(Number(account.balance) + Number(account.pending_balance)) }}</p>
                </div>
            </UCard>

            <UCard class="bg-green-50 dark:bg-green-950">
                <div class="p-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium uppercase tracking-wider">Saldo Tersedia</p>
                    <p class="text-3xl font-bold font-mono text-green-600 dark:text-green-400 mt-1">{{ formatCurrency(account.balance) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dapat digunakan</p>
                </div>
            </UCard>

            <UCard class="bg-orange-50 dark:bg-orange-950">
                <div class="p-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium uppercase tracking-wider">Saldo Pending</p>
                    <p class="text-3xl font-bold font-mono text-orange-600 dark:text-orange-400 mt-1">{{ formatCurrency(account.pending_balance) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tertahan di Super Admin</p>
                </div>
            </UCard>
        </div>

        <!-- Tampilan sederhana untuk pos lainnya -->
        <div v-else class="mb-6">
            <UCard class="bg-primary-50 dark:bg-primary-950">
                <div class="p-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium uppercase tracking-wider">Saldo Saat Ini</p>
                    <p class="text-3xl font-bold font-mono text-primary-600 dark:text-primary-400 mt-1">{{ formatCurrency(account.balance) }}</p>
                </div>
            </UCard>
        </div>

        <UAlert 
            v-if="account.pending_balance > 0 && (account.slug === 'student-balance' || account.slug === 'student-invoices')" 
            color="orange" 
            variant="subtle" 
            icon="i-lucide-info"
            title="Informasi Saldo Pending"
            :description="`Terdapat dana sebesar ${formatCurrency(account.pending_balance)} yang masih tertahan di Super Admin dan belum dapat digunakan. Saldo ini berasal dari pembayaran online melalui aplikasi yang menunggu proses penarikan.`"
            class="mb-6"
        />

        <UCard :ui="{ body: { padding: 'p-0 sm:p-0' } }">
            <UTable :data="transactions.data" :columns="columns">
                 <template #type-cell="{ row }">
                    <UBadge :color="row.original.type === 'credit' ? 'green' : 'red'" variant="subtle">
                        {{ row.original.type === 'credit' ? 'Pemasukan' : 'Pengeluaran' }}
                    </UBadge>
                </template>

                <template #amount-cell="{ row }">
                    <span :class="row.original.type === 'credit' ? 'text-green-600' : 'text-red-600'" class="font-mono font-medium">
                        {{ row.original.type === 'credit' ? '+' : '-' }} {{ formatCurrency(row.original.amount) }}
                    </span>
                </template>

                <template #date-cell="{ row }">
                    {{ formatDate(row.original.date) }}
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-gray-200" v-if="transactions.total > 0">
                 <p class="text-sm text-gray-500">
                    Menampilkan {{ transactions.from }} sampai {{ transactions.to }} dari {{ transactions.total }} data
                 </p>
                 <UPagination v-model="page" :page-count="transactions.per_page" :total="transactions.total" />
            </div>
        </UCard>

        <!-- Create Transaction Modal -->
        <UModal v-model:open="isCreateOpen" title="Catat Mutasi Keuangan">
            <template #body>
                <div class="space-y-4">
                    <UFormField label="Jenis Mutasi" required :error="form.errors.type">
                        <USelect v-model="form.type" :items="transactionTypes" label-key="label" value-key="value" placeholder="Pilih Jenis Mutasi" class="w-full" />
                    </UFormField>

                    <UFormField label="Jumlah (Rp)" required :error="form.errors.amount">
                        <UInput v-model="form.amount" type="number" min="0" class="w-full" />
                    </UFormField>
                    
                    <UFormField label="Tanggal" required :error="form.errors.date">
                         <UInput type="date" v-model="form.date" class="w-full" />
                    </UFormField>

                    <UFormField label="Keterangan" required :error="form.errors.description">
                        <UTextarea v-model="form.description" class="w-full" />
                    </UFormField>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" label="Batal" @click="isCreateOpen = false" />
                    <UButton color="primary" label="Simpan" :loading="form.processing" @click="submitCreate" />
                </div>
            </template>
        </UModal>

    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

const props = defineProps({
    account: Object,
    transactions: Object
});

const isCreateOpen = ref(false);
const page = ref(props.transactions.current_page);

const columns = [
    { accessorKey: 'date', header: 'Tanggal' },
    { accessorKey: 'description', header: 'Keterangan' },
    { accessorKey: 'type', header: 'Jenis' },
    { accessorKey: 'amount', header: 'Nominal' },
];

const form = useForm({
    finance_account_id: props.account.id,
    type: 'credit',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0]
});

const transactionTypes = [
    { label: 'Pemasukan (Credit)', value: 'credit' },
    { label: 'Pengeluaran (Debit)', value: 'debit' }
];


const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return format(new Date(dateString), 'dd MMM yyyy', { locale: id });
};

watch(page, (newPage) => {
    router.visit(`/dashboard/finance/accounts/${props.account.id}?page=${newPage}`, { preserveState: true, preserveScroll: true });
});

const submitCreate = () => {
    form.post('/dashboard/finance/transactions', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
            // Ensure account_id is kept
            form.finance_account_id = props.account.id;
            form.date = new Date().toISOString().split('T')[0];
        }
    });
};

const exportToExcel = () => {
    window.open(`/dashboard/finance/accounts/${props.account.id}/export`, '_blank');
};

</script>
