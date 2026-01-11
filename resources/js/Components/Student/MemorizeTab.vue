<template>
    <div class="space-y-4 pt-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold">Catatan Hafalan</h3>
                <UBadge v-if="totalJuz > 0" color="success" variant="subtle">
                    {{ totalJuz }} Juz
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

        <!-- Memorize Table -->
        <UTable v-if="memorizes.length" :data="memorizes" :columns="columns">
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
        
        <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-book-open">
            <template #description>
                Belum ada catatan hafalan.
            </template>
        </UAlert>

        <!-- Add Memorize Modal -->
        <UModal v-model:open="isModalOpen" title="Tambah Catatan Hafalan">
            <template #body>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <UFormField label="Juz" name="juz" :error="form.errors.juz">
                            <UInput v-model="form.juz" type="number" min="1" max="30" placeholder="1-30" class="w-full" />
                        </UFormField>

                        <UFormField label="Surah" name="surah_id" :error="form.errors.surah_id">
                            <USelectMenu 
                                v-model="form.surah_id" 
                                :items="surahs" 
                                label-key="name" 
                                value-key="id"
                                placeholder="Pilih Surah" 
                                searchable
                                class="w-full"
                            />
                            <!-- Debug output -->
                            <div v-if="false" class="text-xs">{{ surahs ? surahs.length : 0 }} surahs loaded</div>
                        </UFormField>

                        <UFormField label="Tanggal" name="memorize_date" :error="form.errors.memorize_date">
                            <UInput v-model="form.memorize_date" type="date" class="w-full" />
                        </UFormField>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <UFormField label="Ayat Mulai" name="verse_start" :error="form.errors.verse_start">
                            <UInput v-model="form.verse_start" type="number" min="1" class="w-full" />
                        </UFormField>

                        <UFormField label="Ayat Akhir" name="verse_end" :error="form.errors.verse_end">
                            <UInput v-model="form.verse_end" type="number" min="1" class="w-full" />
                        </UFormField>
                    </div>


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
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    studentId: {
        type: String,
        required: true
    },
    memorizes: {
        type: Array,
        default: () => []
    },
    surahs: {
        type: Array,
        default: () => []
    }
});


const isModalOpen = ref(false);
const deletingId = ref(null);


const totalJuz = computed(() => {
    const uniqueJuz = new Set(props.memorizes.map(m => m.juz).filter(Boolean));
    return uniqueJuz.size;
});

const columns = [
    { accessorKey: 'juz', header: 'Juz' },
    { 
        accessorKey: 'surah', 
        header: 'Surah',
        cell: ({ row }) => row.original.surah?.name || '-'
    },
    { 
        header: 'Ayat',
        cell: ({ row }) => {
            const start = row.original.verse_start;
            const end = row.original.verse_end;
            if (start && end) return `${start} - ${end}`;
            if (start) return `${start}`;
            return '-';
        }
    },
    { 
        accessorKey: 'memorize_date', 
        header: 'Tanggal',
        cell: ({ row }) => formatDate(row.original.memorize_date)
    },
    { id: 'actions', header: '' },
];

const form = useForm({
    juz: '',
    surah_id: '',
    verse_start: '',
    verse_end: '',
    notes: '',
    memorize_date: new Date().toISOString().split('T')[0],
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID');
};

const submit = () => {
    form.post(`/dashboard/students/${props.studentId}/memorizes`, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const confirmDelete = (memorize) => {
    if (confirm(`Hapus catatan hafalan ini?`)) {
        deletingId.value = memorize.id;
        router.delete(`/dashboard/students/${props.studentId}/memorizes/${memorize.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        });
    }
};
</script>
