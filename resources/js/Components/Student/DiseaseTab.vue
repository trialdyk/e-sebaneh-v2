<template>
    <div class="space-y-4 pt-4">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold">Rekam Medis</h3>
            <UButton 
                color="primary" 
                size="sm" 
                icon="i-lucide-plus"
                @click="isModalOpen = true"
            >
                Tambah
            </UButton>
        </div>

        <!-- Disease Table -->
        <UTable v-if="diseases.length" :data="diseases" :columns="columns">
            <template #actions-cell="{ row }">
                <UButton 
                    color="error" 
                    variant="ghost" 
                    size="xs"
                    icon="i-lucide-trash-2"
                    :loading="deletingId === row.original.id"
                    @click="confirmDelete(row.original)"
                />
            </template>
        </UTable>
        
        <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-info">
            <template #description>
                Belum ada riwayat penyakit tercatat.
            </template>
        </UAlert>

        <!-- Add Disease Modal -->
        <UModal v-model:open="isModalOpen" title="Tambah Riwayat Penyakit">
            <template #body>
                <form @submit.prevent="submit" class="space-y-4">
                    <UFormField label="Nama Penyakit" name="disease_name" :error="form.errors.disease_name" required>
                        <UInput v-model="form.disease_name" placeholder="Contoh: Demam, Flu, dsb." class="w-full" />
                    </UFormField>

                    <UFormField label="Tanggal Diagnosa" name="diagnosed_date" :error="form.errors.diagnosed_date">
                        <UInput v-model="form.diagnosed_date" type="date" class="w-full" />
                    </UFormField>

                    <UFormField label="Catatan" name="notes" :error="form.errors.notes">
                        <UTextarea v-model="form.notes" placeholder="Catatan tambahan..." class="w-full" />
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
    diseases: {
        type: Array,
        default: () => []
    }
});

const isModalOpen = ref(false);
const deletingId = ref(null);

const columns = [
    { accessorKey: 'disease_name', header: 'Nama Penyakit' },
    { 
        accessorKey: 'diagnosed_date', 
        header: 'Tanggal Diagnosa',
        cell: ({ row }) => row.original.diagnosed_date ? new Date(row.original.diagnosed_date).toLocaleDateString('id-ID') : '-'
    },
    { accessorKey: 'notes', header: 'Catatan', cell: ({ row }) => row.original.notes || '-' },
    { id: 'actions', header: '' },
];

const form = useForm({
    disease_name: '',
    diagnosed_date: '',
    notes: '',
});

const submit = () => {
    form.post(`/dashboard/students/${props.studentId}/diseases`, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const confirmDelete = (disease) => {
    if (confirm(`Hapus riwayat penyakit "${disease.disease_name}"?`)) {
        deletingId.value = disease.id;
        router.delete(`/dashboard/students/${props.studentId}/diseases/${disease.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        });
    }
};
</script>
