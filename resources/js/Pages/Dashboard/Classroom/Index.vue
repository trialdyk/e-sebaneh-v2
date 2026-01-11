<template>
    <DashboardPage
        title="Data Kelas"
        page-id="classrooms-index"
        content-class="p-6"
    >
        <!-- Filters and Actions -->
        <UCard class="mb-6">
            <form @submit.prevent="applyFilters" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <UFormField label="Cari Nama Kelas">
                        <UInput
                            v-model="filters.search"
                            placeholder="Cari kelas..."
                            icon="i-lucide-search"
                        />
                    </UFormField>

                    <UFormField label="Tahun Ajaran">
                        <USelect
                            v-model="filters.school_year_id"
                            :items="schoolYearOptions"
                            placeholder="Pilih Tahun Ajaran"
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
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2">
                <!-- Potential Export/Import buttons if needed later -->
            </div>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                :to="'/dashboard/classrooms/create'"
            >
                Tambah Kelas
            </UButton>
        </div>

        <!-- Classrooms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <UCard v-for="classroom in classrooms.data" :key="classroom.id" class="hover:ring-2 hover:ring-primary-500/50 transition-all">
                <template #header>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ classroom.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tingkat: {{ classroom.level }}
                            </p>
                        </div>
                        <UBadge color="primary" variant="subtle">
                            {{ classroom.school_year?.name }}
                        </UBadge>
                    </div>
                </template>

                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <UIcon name="i-lucide-user" class="w-4 h-4" />
                        <span>Wali Kelas: {{ classroom.teacher?.name || '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <UIcon name="i-lucide-users" class="w-4 h-4" />
                        <span>Jumlah Santri: {{ classroom.students_count || 0 }}</span>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <UButton
                            size="xs"
                            color="primary"
                            variant="soft"
                            icon="i-lucide-pencil"
                            :to="`/dashboard/classrooms/${classroom.id}/edit`"
                        >
                            Edit
                        </UButton>
                        <UButton
                            size="xs"
                            color="error"
                            variant="soft"
                            icon="i-lucide-trash-2"
                            @click="confirmDelete(classroom)"
                        >
                            Hapus
                        </UButton>
                    </div>
                </template>
            </UCard>
        </div>

        <div v-if="classrooms.data.length === 0" class="text-center py-12 text-gray-500">
            {{ hasActiveFilters ? 'Data tidak ditemukan' : 'Belum ada data kelas' }}
        </div>

        <!-- Pagination -->
        <div v-if="classrooms.last_page > 1" class="mt-6 flex justify-center">
            <UPagination
                v-model="currentPage"
                :total="classrooms.total"
                :per-page="classrooms.per_page"
                @update:model-value="changePage"
            />
        </div>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Kelas</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Kelas <span class="font-semibold">{{ selectedClassroom?.name }}</span> akan dihapus permanen.
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
                        @click="deleteClassroom"
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
    classrooms: Object,
    schoolYears: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters.search || '',
    school_year_id: props.filters.school_year_id || 'all',
});

const currentPage = ref(props.classrooms.current_page);

const schoolYearOptions = computed(() => [
    { label: 'Semua Tahun Ajaran', value: 'all' },
    ...props.schoolYears.map(sy => ({ label: sy.name, value: sy.id }))
]);

const hasActiveFilters = computed(() => {
    return filters.value.search || (filters.value.school_year_id && filters.value.school_year_id !== 'all');
});

const applyFilters = () => {
    const params = { ...filters.value };
    if (params.school_year_id === 'all') {
        delete params.school_year_id;
    }
    
    router.get('/dashboard/classrooms', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = { search: '', school_year_id: 'all' };
    applyFilters();
};

const changePage = (page) => {
    const params = { ...filters.value };
    if (params.school_year_id === 'all') {
        delete params.school_year_id;
    }
    
    router.get(`/dashboard/classrooms?page=${page}`, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Delete functionality
const isDeleteModalOpen = ref(false);
const selectedClassroom = ref(null);
const deleteProcessing = ref(false);

const confirmDelete = (classroom) => {
    selectedClassroom.value = classroom;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedClassroom.value = null;
};

const deleteClassroom = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/classrooms/${selectedClassroom.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>
