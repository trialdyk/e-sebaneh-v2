<template>
    <DashboardPage 
        title="Pos Keuangan" 
        heading="Manajemen Pos Keuangan" 
        description="Kelola pos saldo dan akun keuangan pondok"
    >
        <!-- Actions -->
        <template #header>
            <UButton label="Buat Pos Baru" icon="i-lucide-plus" @click="openCreateModal" />
        </template>

        <!-- Accounts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <UCard v-for="account in accounts" :key="account.id" :class="{'border-primary-500/20': account.is_system}">
                <template #header>
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-lg">{{ account.name }}</h3>
                                <UBadge v-if="account.is_system" color="primary" variant="subtle" size="xs">System</UBadge>
                            </div>
                            <p class="text-sm text-gray-500">{{ account.slug }}</p>
                        </div>
                        <UDropdown v-if="!account.is_system" :items="getActionItems(account)">
                            <UButton color="gray" variant="ghost" icon="i-lucide-ellipsis-vertical" />
                        </UDropdown>
                    </div>
                </template>

                <div class="py-2 space-y-3">
                    <!-- Breakdown untuk Saldo Santri dan Tagihan Santri -->
                    <template v-if="account.slug === 'student-balance' || account.slug === 'student-invoices'">
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wider mb-1">Total Saldo</p>
                            <p class="text-3xl font-bold font-mono text-primary-600">{{ formatCurrency(Number(account.balance) + Number(account.pending_balance)) }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Tersedia</p>
                                <p class="text-lg font-semibold font-mono text-green-600">{{ formatCurrency(account.balance) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Pending</p>
                                <p class="text-lg font-semibold font-mono text-orange-600">{{ formatCurrency(account.pending_balance) }}</p>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Tampilan sederhana untuk pos lainnya -->
                    <template v-else>
                        <p class="text-3xl font-bold font-mono text-primary-600">{{ formatCurrency(account.balance) }}</p>
                    </template>
                    
                    <UAlert 
                        v-if="account.pending_balance > 0 && (account.slug === 'student-balance' || account.slug === 'student-invoices')" 
                        color="orange" 
                        variant="subtle" 
                        icon="i-lucide-info"
                        title="Saldo Pending"
                        :description="`Dana sebesar ${formatCurrency(account.pending_balance)} masih tertahan di Super Admin dan belum dapat digunakan. Saldo ini berasal dari pembayaran online yang menunggu proses penarikan.`"
                        class="text-xs"
                    />
                    
                    <p class="text-xs text-gray-500 mt-2 uppercase tracking-wider">{{ account.type }}</p>
                </div>

                <template #footer>
                     <div class="flex justify-between items-center gap-2">
                        <p class="text-sm text-gray-600 line-clamp-2 flex-1">{{ account.description || 'Tidak ada deskripsi' }}</p>
                        <UButton size="xs" color="gray" variant="soft" icon="i-lucide-eye" label="Detail" @click="router.visit(`/dashboard/finance/accounts/${account.id}`)" />
                     </div>
                </template>
            </UCard>
        </div>

        <!-- Create Modal -->
        <UModal v-model:open="isCreateOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Buat Pos Keuangan Baru</h3>
            </template>
            
            <template #body>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <UFormField label="Nama Pos" required :error="form.errors.name">
                        <UInput v-model="form.name" placeholder="Contoh: Kas Pembangunan" class="w-full" :disabled="form.processing" @input="form.clearErrors('name')" />
                    </UFormField>
                    <UFormField label="Deskripsi" :error="form.errors.description">
                        <UTextarea v-model="form.description" class="w-full" :disabled="form.processing" />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" label="Batal" @click="isCreateOpen = false" :disabled="form.processing" />
                    <UButton color="primary" label="Simpan" :loading="form.processing" @click="submitCreate" />
                </div>
            </template>
        </UModal>

        <!-- Edit Modal -->
        <UModal v-model:open="isEditOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Edit Pos Keuangan</h3>
            </template>
            
            <template #body>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <UFormField label="Nama Pos" required :error="editForm.errors.name">
                        <UInput v-model="editForm.name" class="w-full" :disabled="editForm.processing" @input="editForm.clearErrors('name')" />
                    </UFormField>
                    <UFormField label="Deskripsi" :error="editForm.errors.description">
                        <UTextarea v-model="editForm.description" class="w-full" :disabled="editForm.processing" />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" label="Batal" @click="isEditOpen = false" :disabled="editForm.processing" />
                    <UButton color="primary" label="Update" :loading="editForm.processing" @click="submitEdit" />
                </div>
            </template>
        </UModal>
        
    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    accounts: Array,
    types: Array
});

const isCreateOpen = ref(false);
const isEditOpen = ref(false);

const typeOptions = [
    { label: 'Income (Pemasukan/Dana Masuk)', value: 'income' },
    { label: 'Expense (Pengeluaran)', value: 'expense' }
];

const form = useForm({
    name: '',
    description: ''
});

const editForm = useForm({
    id: null,
    name: '',
    description: ''
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateOpen.value = true;
};

const submitCreate = () => {
    form.post('/dashboard/finance/accounts', {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        }
    });
};

const openEdit = (account) => {
    editForm.reset();
    editForm.clearErrors();
    editForm.id = account.id;
    editForm.name = account.name;
    editForm.description = account.description;
    isEditOpen.value = true;
};

const submitEdit = () => {
    editForm.put(`/dashboard/finance/accounts/${editForm.id}`, {
        onSuccess: () => {
             isEditOpen.value = false;
             editForm.reset();
        }
    });
};

const deleteAccount = (account) => {
    if (confirm(`Apakah Anda yakin ingin menghapus pos ${account.name}?`)) {
        router.delete(`/dashboard/finance/accounts/${account.id}`);
    }
};

const getActionItems = (account) => [
    [{
        label: 'Edit',
        icon: 'i-lucide-pencil',
        click: () => openEdit(account)
    }],
    [{
        label: 'Hapus',
        icon: 'i-lucide-trash-2',
        color: 'red',
        click: () => deleteAccount(account)
    }]
];
</script>
