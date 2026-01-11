<template>
    <div class="space-y-4 pt-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold">Riwayat Pelanggaran</h3>
                <UBadge v-if="totalPoints > 0" color="error" variant="subtle">
                    Total: {{ totalPoints }} poin
                </UBadge>
            </div>
            <UButton 
                color="primary" 
                size="sm" 
                icon="i-lucide-plus"
                @click="isModalOpen = true"
            >
                Tambah
            </UButton>
        </div>

        <!-- Violation Table -->
        <UTable v-if="violations.length" :data="violations" :columns="columns">
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
        
        <UAlert v-else color="success" variant="subtle" icon="i-lucide-check-circle">
            <template #description>
                Belum ada catatan pelanggaran. Pertahankan!
            </template>
        </UAlert>

        <!-- Add Violation Modal -->
        <UModal v-model:open="isModalOpen" title="Tambah Catatan Pelanggaran">
            <template #body>
                <form @submit.prevent="submit" class="space-y-4">
                    <UFormField label="Tanggal Pelanggaran" name="violation_date" :error="form.errors.violation_date" required>
                        <UInput v-model="form.violation_date" type="date" class="w-full" />
                    </UFormField>

                    <UFormField label="Deskripsi Pelanggaran" name="description" :error="form.errors.description" required>
                        <UTextarea v-model="form.description" placeholder="Jelaskan pelanggaran yang dilakukan..." class="w-full" />
                    </UFormField>

                    <UFormField label="Hukuman/Sanksi" name="punishment" :error="form.errors.punishment">
                        <UTextarea v-model="form.punishment" placeholder="Hukuman yang diberikan..." class="w-full" />
                    </UFormField>

                    <UFormField label="Poin Pelanggaran" name="points" :error="form.errors.points">
                        <UInput v-model="form.points" type="number" min="0" placeholder="0" class="w-full" />
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
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    studentId: {
        type: String,
        required: true
    },
    violations: {
        type: Array,
        default: () => []
    }
});

const isModalOpen = ref(false);
const deletingId = ref(null);

const totalPoints = computed(() => {
    return props.violations.reduce((sum, v) => sum + (v.points || 0), 0);
});

const columns = [
    { 
        accessorKey: 'violation_date', 
        header: 'Tanggal',
        cell: ({ row }) => formatDate(row.original.violation_date)
    },
    { accessorKey: 'description', header: 'Deskripsi' },
    { accessorKey: 'punishment', header: 'Hukuman', cell: ({ row }) => row.original.punishment || '-' },
    { accessorKey: 'points', header: 'Poin', cell: ({ row }) => row.original.points || 0 },
    { id: 'actions', header: '' },
];

const form = useForm({
    violation_date: new Date().toISOString().split('T')[0],
    description: '',
    punishment: '',
    points: 0,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID');
};

const submit = () => {
    form.post(`/dashboard/students/${props.studentId}/violations`, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const confirmDelete = (violation) => {
    if (confirm(`Hapus catatan pelanggaran ini?`)) {
        deletingId.value = violation.id;
        router.delete(`/dashboard/students/${props.studentId}/violations/${violation.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        });
    }
};
</script>
