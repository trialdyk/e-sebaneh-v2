<template>
    <div class="space-y-4 pt-4">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold">Riwayat Izin</h3>
            <UButton 
                color="primary" 
                size="sm" 
                icon="i-lucide-plus"
                @click="isModalOpen = true"
            >
                Tambah Izin
            </UButton>
        </div>

        <!-- Permission Table -->
        <UTable v-if="permissions.length" :data="permissions" :columns="columns">
            <template #type-cell="{ row }">
                <UBadge :color="getTypeColor(row.original.type)" variant="subtle">
                    {{ getTypeLabel(row.original.type) }}
                </UBadge>
            </template>
            <template #status-cell="{ row }">
                <UBadge :color="row.original.returned_at ? 'success' : 'warning'" variant="subtle">
                    {{ row.original.returned_at ? 'Sudah Kembali' : 'Belum Kembali' }}
                </UBadge>
            </template>
            <template #actions-cell="{ row }">
                <div class="flex gap-1">
                    <UTooltip text="Cetak Surat Izin">
                        <UButton 
                            color="neutral" 
                            variant="ghost" 
                            size="xs"
                            icon="i-lucide-printer"
                            :to="`/dashboard/students/${studentId}/permissions/${row.original.id}/print`"
                            target="_blank"
                            external
                        />
                    </UTooltip>
                    
                    <UTooltip text="Catat Kembali" v-if="!row.original.returned_at">
                        <UButton 
                            color="primary" 
                            variant="ghost" 
                            size="xs"
                            icon="i-lucide-check-square"
                            @click="openReturnModal(row.original)"
                        />
                    </UTooltip>

                    <UButton 
                        color="error" 
                        variant="ghost" 
                        size="xs"
                        icon="i-lucide-trash-2"
                        @click="confirmDelete(row.original)"
                    />
                </div>
            </template>
        </UTable>
        
        <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-info">
            <template #description>
                Belum ada riwayat izin tercatat.
            </template>
        </UAlert>

        <!-- Add Permission Modal -->
        <UModal v-model:open="isModalOpen" title="Tambah Pengajuan Izin">
            <template #body>
                <form @submit.prevent="submit" class="space-y-4">
                    <UFormField label="Tipe Izin" name="type" :error="form.errors.type" required>
                        <USelect 
                            v-model="form.type" 
                            :items="typeOptions"
                            label-key="label"
                            value-key="value"
                            placeholder="Pilih tipe izin"
                            class="w-full" 
                        />
                    </UFormField>

                    <UFormField label="Alasan" name="reason" :error="form.errors.reason" required>
                        <UTextarea v-model="form.reason" placeholder="Alasan pengajuan izin..." class="w-full" />
                    </UFormField>

                    <div class="grid grid-cols-2 gap-4">
                        <UFormField label="Tanggal Mulai" name="start_date" :error="form.errors.start_date">
                            <UInput v-model="form.start_date" type="date" class="w-full" />
                        </UFormField>

                        <UFormField label="Tanggal Selesai" name="end_date" :error="form.errors.end_date">
                            <UInput v-model="form.end_date" type="date" class="w-full" />
                        </UFormField>
                    </div>

                    <UFormField label="Durasi" name="duration" :error="form.errors.duration">
                        <UInput v-model="form.duration" placeholder="Contoh: 3 hari" class="w-full" />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" @click="closeModal">
                        Batal
                    </UButton>
                    <UButton 
                        color="primary" 
                        :loading="form.processing"
                        @click="submit"
                    >
                        Simpan
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- Return Modal -->
        <UModal v-model:open="isReturnModalOpen" title="Catat Tanggal Kembali">
            <template #body>
                <form @submit.prevent="submitReturn" class="space-y-4">
                    <p class="text-sm text-gray-500">
                        Pastikan santri <strong>{{ studentName }}</strong> benar-benar sudah kembali ke pondok.
                    </p>
                    <UFormField label="Tanggal Kembali" name="returned_at" :error="returnForm.errors.returned_at" required>
                        <UInput v-model="returnForm.returned_at" type="datetime-local" class="w-full" />
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton color="neutral" variant="soft" @click="isReturnModalOpen = false">
                        Batal
                    </UButton>
                    <UButton 
                        color="primary" 
                        :loading="returnForm.processing"
                        @click="submitReturn"
                    >
                        Simpan
                    </UButton>
                </div>
            </template>
        </UModal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    studentId: {
        type: String,
        required: true
    },
    studentName: {
        type: String,
        default: ''
    },
    permissions: {
        type: Array,
        default: () => []
    }
});

const isModalOpen = ref(false);

const typeOptions = [
    { value: 'sick', label: 'Sakit' },
    { value: 'permit', label: 'Izin Pulang' },
    { value: 'family', label: 'Urusan Keluarga' },
    { value: 'other', label: 'Lainnya' },
];

const columns = [
    { 
        id: 'type',
        header: 'Tipe',
    },
    { accessorKey: 'reason', header: 'Alasan' },
    { 
        accessorKey: 'start_date', 
        header: 'Mulai',
        cell: ({ row }) => formatDate(row.original.start_date)
    },
    { 
        accessorKey: 'duration', 
        header: 'Durasi Awal Izin',
        cell: ({ row }) => row.original.duration || '-'
    },
    { 
        accessorKey: 'returned_at', 
        header: 'Tanggal Kembali', 
        cell: ({ row }) => row.original.returned_at ? formatDate(row.original.returned_at) : '-'
    },
    { id: 'status', header: 'Status' },
    { id: 'actions', header: '' },
];

const form = useForm({
    type: '',
    reason: '',
    start_date: '',
    end_date: '',
    duration: '',
});

const returnForm = useForm({
    returned_at: new Date().toISOString().slice(0, 16), // current datetime-local format
});

const isReturnModalOpen = ref(false);
const selectedPermission = ref(null);

const getTypeLabel = (type) => {
    const labels = { sick: 'Sakit', permit: 'Izin Pulang', family: 'Urusan Keluarga', other: 'Lainnya' };
    return labels[type] || type;
};

const getTypeColor = (type) => {
    const colors = { sick: 'error', permit: 'info', family: 'warning', other: 'neutral' };
    return colors[type] || 'neutral';
};



const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID');
};

const submit = () => {
    form.post(`/dashboard/students/${props.studentId}/permissions`, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};



const openReturnModal = (permission) => {
    selectedPermission.value = permission;
    returnForm.returned_at = new Date().toISOString().slice(0, 16);
    isReturnModalOpen.value = true;
};

const submitReturn = () => {
    returnForm.put(`/dashboard/students/${props.studentId}/permissions/${selectedPermission.value.id}/return`, {
        preserveScroll: true,
        onSuccess: () => {
            isReturnModalOpen.value = false;
            returnForm.reset();
        },
    });
};

const confirmDelete = (permission) => {
    if (confirm(`Hapus data izin ini?`)) {
        router.delete(`/dashboard/students/${props.studentId}/permissions/${permission.id}`, {
            preserveScroll: true,
        });
    }
};
</script>
