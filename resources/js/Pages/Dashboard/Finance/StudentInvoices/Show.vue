<template>
    <DashboardPage
        title="Detail Tagihan"
        :heading="invoice.name"
        description="Detail tagihan dan riwayat pembayaran"
        page-id="show-student-invoice"
    >
        <template #header>
            <div class="flex gap-2">
                <UButton
                    color="neutral"
                    variant="ghost"
                    icon="i-lucide-arrow-left"
                    to="/dashboard/finance/student-invoices"
                >
                    Kembali
                </UButton>
                <UButton
                    color="primary"
                    variant="soft"
                    icon="i-lucide-pencil"
                    :to="`/dashboard/finance/student-invoices/${invoice.id}/edit`"
                >
                    Edit
                </UButton>
            </div>
        </template>

        <!-- Invoice Info -->
        <UCard class="mb-6">
            <template #header>
                <h3 class="text-lg font-semibold">Informasi Tagihan</h3>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nama Tagihan</p>
                    <p class="font-medium">{{ invoice.name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Jumlah Tagihan</p>
                    <p class="text-2xl font-bold text-blue-600">Rp {{ formatCurrency(invoice.amount) }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Tipe Tagihan</p>
                    <UBadge :color="getTypeBadgeColor(invoice.type)" variant="subtle">
                        {{ getTypeLabel(invoice.type) }}
                    </UBadge>
                </div>

                <div class="md:col-span-2" v-if="invoice.description">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Deskripsi</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ invoice.description }}</p>
                </div>
            </div>
        </UCard>

        <!-- Targeted Students List -->
        <UCard class="mb-6">
            <template #header>
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Daftar Tagihan Santri</h3>
                    <UBadge color="neutral" variant="subtle">
                        Total {{ students?.total || 0 }} Santri
                    </UBadge>
                </div>
            </template>

            <UTable
                :data="students?.data || []"
                :columns="columns"
            >
                <template #name-cell="{ row }">
                    <div class="flex items-center gap-3">
                        <UAvatar
                            :src="row.original.user?.photo ? (row.original.user.photo.startsWith('http') ? row.original.user.photo : `/storage/${row.original.user.photo}`) : null"
                            :alt="row.original.user?.name"
                            size="sm"
                        />
                        <div>
                            <p class="font-medium">{{ row.original.user?.name }}</p>
                            <p class="text-xs text-gray-500">{{ row.original.student_id }}</p>
                        </div>
                    </div>
                </template>

                <template #info-cell="{ row }">
                    <div class="flex gap-2">
                        <UBadge v-if="row.original.classrooms?.[0]" color="neutral" variant="subtle" size="md">
                            {{ row.original.classrooms[0].name }}
                        </UBadge>
                        <UBadge v-if="row.original.gender" :color="row.original.gender === 'male' ? 'secondary' : 'warning'" variant="subtle" size="md">
                            {{ row.original.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                        </UBadge>
                    </div>
                </template>

                <template #status-cell="{ row }">
                    <UBadge :color="row.original.is_paid ? 'success' : 'warning'" variant="subtle">
                        {{ row.original.is_paid ? 'Sudah Bayar' : 'Belum Bayar' }}
                    </UBadge>
                </template>

                <template #action-cell="{ row }">
                    <UButton
                        v-if="!row.original.is_paid"
                        color="primary"
                        variant="soft"
                        size="xs"
                        icon="i-lucide-wallet"
                        @click="processPayment(row.original)"
                    >
                        Bayar Offline
                    </UButton>
                    <span v-else class="text-xs text-green-600 font-medium flex items-center gap-1">
                        <UIcon name="i-lucide-check-circle" class="w-4 h-4" />
                        Lunas
                    </span>
                </template>
            </UTable>

            <!-- Pagination -->
            <div v-if="students?.last_page > 1" class="flex justify-between items-center mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500">
                    {{ students.from }} - {{ students.to }} dari {{ students.total }}
                </p>
                <UPagination
                    v-model="page"
                    :total="students.total"
                    :per-page="students.per_page"
                    @update:model-value="changePage"
                />
            </div>
        </UCard>


    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    invoice: Object,
    students: Object
});

const columns = [
    { id: 'name', header: 'Nama Santri' },
    { id: 'info', header: 'Kelas / Gender' },
    { id: 'status', header: 'Status' },
    { id: 'action', header: 'Aksi' }
];

const page = ref(props.students?.current_page || 1);

function changePage(newPage) {
    router.get(`/dashboard/finance/student-invoices/${props.invoice.id}`, {
        page: newPage
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['students']
    });
}

function processPayment(student) {
    if (confirm(`Yakin ingin mengubah status santri ${student.user.name} menjadi SUDAH BAYAR?`)) {
        router.post(`/dashboard/finance/student-invoices/${props.invoice.id}/pay/${student.id}`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
}

const paymentColumns = [
    { id: 'student', header: 'Santri' },
    { id: 'amount', header: 'Jumlah' },
    { id: 'payment_type', header: 'Tipe' },
    { id: 'created_at', header: 'Tanggal' },
    { id: 'user', header: 'Dicatat Oleh' }
];

function getTypeLabel(type) {
    const typeMap = {
        all_students: 'Semua Santri',
        by_classroom: 'Berdasarkan Kelas',
        by_gender: 'Berdasarkan Gender',
        specific_students: 'Santri Tertentu'
    };
    return typeMap[type] || type;
}

function getTypeBadgeColor(type) {
    const colorMap = {
        all_students: 'primary',
        by_classroom: 'info',
        by_gender: 'warning',
        specific_students: 'success'
    };
    return colorMap[type] || 'neutral';
}

function formatCurrency(value) {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
}

function formatDateTime(datetime) {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

</script>
