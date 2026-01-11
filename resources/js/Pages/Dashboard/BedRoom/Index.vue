<template>
    <DashboardPage
        title="Data Kamar"
        page-id="bedrooms-index"
        content-class="p-6"
    >
        <!-- Filters and Actions -->
        <UCard class="mb-6">
            <form @submit.prevent="applyFilters" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <UFormField label="Cari Nama Kamar">
                        <UInput
                            v-model="filters.search"
                            placeholder="Cari kamar..."
                            icon="i-lucide-search"
                            class="w-full"
                        />
                    </UFormField>

                    <div class="flex items-end gap-2">
                        <UButton type="submit" color="primary">
                            Cari
                        </UButton>
                        <UButton
                            v-if="hasActiveFilters"
                            type="button"
                            color="neutral"
                            variant="soft"
                            @click="resetFilters"
                        >
                            Reset
                        </UButton>
                    </div>
                </div>
            </form>
        </UCard>

        <!-- Actions -->
        <div class="flex justify-end mb-4">
            <UButton
                color="primary"
                icon="i-lucide-plus"
                :to="'/dashboard/bed-rooms/create'"
            >
                Tambah Kamar
            </UButton>
        </div>

        <!-- BedRooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <UCard v-for="bedroom in bedRooms.data" :key="bedroom.id" class="hover:ring-2 hover:ring-primary-500/50 transition-all">
                <template #header>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ bedroom.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Kapasitas: {{ bedroom.capacity }}
                            </p>
                        </div>
                        <UBadge color="primary" variant="subtle">
                            {{ bedroom.students_count || 0 }} Santri
                        </UBadge>
                    </div>
                </template>

                <div class="space-y-3">
                    <UProgress :model-value="bedroom.students_count || 0" :max="bedroom.capacity" color="primary" size="sm" />
                    <p class="text-xs text-right text-gray-500">
                        Terisi {{ bedroom.students_count }} dari {{ bedroom.capacity }}
                    </p>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <UButton
                            size="xs"
                            color="primary"
                            variant="soft"
                            icon="i-lucide-pencil"
                            :to="`/dashboard/bed-rooms/${bedroom.id}/edit`"
                        >
                            Edit
                        </UButton>
                        <UButton
                            size="xs"
                            color="error"
                            variant="soft"
                            icon="i-lucide-trash-2"
                            @click="confirmDelete(bedroom)"
                        >
                            Hapus
                        </UButton>
                    </div>
                </template>
            </UCard>
        </div>

        <div v-if="bedRooms.data.length === 0" class="text-center py-12 text-gray-500">
            {{ hasActiveFilters ? 'Data tidak ditemukan' : 'Belum ada data kamar' }}
        </div>

        <!-- Pagination -->
        <div v-if="bedRooms.last_page > 1" class="mt-6 flex justify-center">
            <UPagination
                v-model="currentPage"
                :total="bedRooms.total"
                :per-page="bedRooms.per_page"
                @update:model-value="changePage"
            />
        </div>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Kamar</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Kamar <span class="font-semibold">{{ selectedBedRoom?.name }}</span> akan dihapus permanen.
                        </p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        color="neutral"
                        variant="soft"
                        @click="closeDeleteModal"
                        :disabled="deleteProcessing"
                    >
                        Batal
                    </UButton>
                    <UButton
                        color="error"
                        :loading="deleteProcessing"
                        @click="deleteBedRoom"
                    >
                        Hapus
                    </UButton>
                </div>
            </template>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    bedRooms: Object,
    filters: Object,
});

const filters = ref({
    search: props.filters.search || '',
});

const currentPage = ref(props.bedRooms.current_page);

const hasActiveFilters = computed(() => {
    return filters.value.search;
});

const applyFilters = () => {
    router.get('/dashboard/bed-rooms', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = { search: '' };
    applyFilters();
};

const changePage = (page) => {
    router.get(`/dashboard/bed-rooms?page=${page}`, filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Delete functionality
const isDeleteModalOpen = ref(false);
const selectedBedRoom = ref(null);
const deleteProcessing = ref(false);

const confirmDelete = (bedroom) => {
    selectedBedRoom.value = bedroom;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedBedRoom.value = null;
};

const deleteBedRoom = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/bed-rooms/${selectedBedRoom.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>
