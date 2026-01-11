<template>
    <DashboardPage
        title="Master Sekolah"
        heading="Data Sekolah & Jenjang"
        description="Kelola data master sekolah dan tingkatan kelas"
        page-id="master-schools"
    >
        <template #header>
            <div class="flex gap-2">
                <UInput
                    v-model="search"
                    icon="i-lucide-search"
                    placeholder="Cari sekolah..."
                    class="w-64"
                    @keyup.enter="handleSearch"
                />
                <UButton
                    color="primary"
                    icon="i-lucide-plus"
                    label="Tambah Sekolah"
                    to="/dashboard/schools/create"
                />
            </div>
        </template>

        <UCard>
            <UTable
                :data="schools.data"
                :columns="columns"
                :loading="loading"
            >
                <template #name-cell="{ row }">
                    <span class="font-medium">{{ row.original.name }}</span>
                </template>

                <template #short_name-cell="{ row }">
                    <UBadge color="neutral" variant="soft">{{ row.original.short_name }}</UBadge>
                </template>

                <template #school_levels-cell="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UBadge
                            v-for="level in row.original.school_levels"
                            :key="level.id"
                            color="primary"
                            variant="subtle"
                            size="sm"
                        >
                            {{ level.name }}
                        </UBadge>
                        <span v-if="!row.original.school_levels?.length" class="text-sm text-gray-400 italic">
                            Belum ada jenjang
                        </span>
                    </div>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex gap-1">
                        <UButton
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil"
                            :to="`/dashboard/schools/${row.original.id}/edit`"
                        />
                        <UButton
                            color="error"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-trash-2"
                            @click="openDeleteModal(row.original)"
                        />
                    </div>
                </template>
            </UTable>
            
            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ schools.from }} to {{ schools.to }} of {{ schools.total }} entries
                </span>
                <UPagination
                    v-model="currentPage"
                    :total="schools.total"
                    :page-count="schools.per_page"
                />
            </div>
        </UCard>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Sekolah</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-muted mt-1">
                            Sekolah <span class="font-semibold">{{ selectedSchool?.name }}</span> akan dihapus beserta semua jenjang kelasnya.
                        </p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        color="neutral"
                        variant="soft"
                        label="Batal"
                        @click="closeDeleteModal"
                        :disabled="deleteProcessing"
                    />
                    <UButton
                        color="error"
                        label="Hapus"
                        :loading="deleteProcessing"
                        @click="confirmDelete"
                    />
                </div>
            </template>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const props = defineProps({
    schools: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const currentPage = ref(props.schools?.current_page || 1);
const loading = ref(false);

const columns = [
    { accessorKey: 'name', header: 'Nama Sekolah' },
    { accessorKey: 'short_name', header: 'Singkatan' },
    { accessorKey: 'school_levels', header: 'Jenjang Kelas' },
    { accessorKey: 'actions', header: 'Aksi' }
];

const handleSearch = () => {
    router.get('/dashboard/schools', { search: search.value }, { preserveState: true, replace: true });
};

watch(search, debounce(() => {
    handleSearch();
}, 500));

watch(currentPage, (page) => {
    router.get('/dashboard/schools', { page, search: search.value }, { preserveState: true, replace: true });
});

// Delete modal
const isDeleteModalOpen = ref(false);
const selectedSchool = ref(null);
const deleteProcessing = ref(false);

const openDeleteModal = (school) => {
    selectedSchool.value = school;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedSchool.value = null;
};

const confirmDelete = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/schools/${selectedSchool.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            closeDeleteModal();
        },
    });
};
</script>
