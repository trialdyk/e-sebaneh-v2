<template>
    <DashboardPage
        title="Data Pegawai"
        page-id="teachers-index"
        content-class="p-6"
    >
        <!-- Filters and Actions -->
        <UCard class="mb-6">
            <form @submit.prevent="applyFilters" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <UFormField label="Cari Nama Pegawai">
                        <UInput
                            v-model="filters.search"
                            placeholder="Masukkan nama..."
                            icon="i-lucide-search"
                        />
                    </UFormField>

                    <UFormField label="Filter Jabatan">
                        <USelect
                            v-model="filters.position_id"
                            :items="positionOptions"
                            placeholder="Pilih Jabatan"
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
                <UButton
                    color="primary"
                    variant="outline"
                    icon="i-lucide-download"
                    :href="exportUrl"
                >
                    Export Excel
                </UButton>
                <UButton
                    color="primary"
                    variant="soft"
                    icon="i-lucide-upload"
                    @click="openImportModal"
                >
                    Import Excel
                </UButton>
            </div>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                :to="'/dashboard/teachers/create'"
            >
                Tambah Pegawai
            </UButton>
        </div>

        <!-- Teachers Table -->
        <UCard>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">NIP</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No. HP</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(teacher, index) in teachers.data" :key="teacher.id">
                            <td class="px-4 py-3 text-sm">{{ teachers.from + index }}</td>
                            <td class="px-4 py-3 text-sm">{{ teacher.nip || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ teacher.user?.name}}</td>
                            <td class="px-4 py-3 text-sm">{{ teacher.position?.name || '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ teacher.user?.email }}</td>
                            <td class="px-4 py-3 text-sm">{{ teacher.user?.phone_number || '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex gap-2">
                                    <UButton
                                        size="xs"
                                        color="primary"
                                        variant="soft"
                                        icon="i-lucide-pencil"
                                        :to="`/dashboard/teachers/${teacher.id}/edit`"
                                    >
                                        Edit
                                    </UButton>
                                    <UButton
                                        size="xs"
                                        color="error"
                                        variant="soft"
                                        icon="i-lucide-trash-2"
                                        @click="confirmDelete(teacher)"
                                    >
                                        Hapus
                                    </UButton>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="teachers.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                {{ hasActiveFilters ? 'Data tidak ditemukan' : 'Belum ada data pegawai' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="teachers.last_page > 1" class="mt-4 flex justify-center">
                <UPagination
                    v-model="currentPage"
                    :total="teachers.total"
                    :per-page="teachers.per_page"
                    @update:model-value="changePage"
                />
            </div>
        </UCard>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Pegawai</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Pegawai <span class="font-semibold">{{ selectedTeacher?.user?.name }}</span> akan dihapus permanen.
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
                        @click="deleteTeacher"
                    >
                        Hapus
                    </UButton>
                </div>
            </template>
        </UModal>

        <!-- Import Modal -->
        <UModal v-model:open="isImportModalOpen" :ui="{ width: 'sm:max-w-2xl' }">
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <UIcon name="i-lucide-file-spreadsheet" class="w-6 h-6 text-primary" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Import Data Pegawai</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Unggah file Excel untuk mengimpor data pegawai secara massal</p>
                    </div>
                </div>
            </template>

            <template #body>
                <div class="space-y-4">
                    <!-- Instructions -->
                    <UAlert
                        color="info"
                        variant="subtle"
                        icon="i-lucide-info"
                        title="Petunjuk Import"
                    >
                        <template #description>
                            <ol class="list-decimal list-inside space-y-1 text-sm mt-2">
                                <li>Download template Excel terlebih dahulu</li>
                                <li>Isi data pegawai sesuai format yang tersedia</li>
                                <li>Pastikan <strong>NIP</strong> dan <strong>Email</strong> unik (tidak duplikat)</li>
                                <li>Kolom <strong>Jabatan</strong> harus sesuai dengan daftar jabatan yang ada</li>
                                <li>Kolom <strong>Nama Lengkap</strong>, <strong>Email</strong>, dan <strong>Jabatan</strong> wajib diisi</li>
                                <li>Simpan file dalam format .xlsx atau .xls</li>
                                <li>Password default: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">password123</code></li>
                            </ol>
                        </template>
                    </UAlert>

                    <!-- Download Template -->
                    <UButton
                            color="primary"
                            variant="outline"
                            icon="i-lucide-download"
                            block
                            :href="'/dashboard/teachers/template/download'"
                            target="_blank"
                        >
                        Download Template Excel
                    </UButton>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Pilih File Excel <span class="text-error">*</span>
                        </label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".xlsx,.xls"
                            @change="handleFileSelect"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary/10 file:text-primary
                                hover:file:bg-primary/20
                                cursor-pointer"
                        />
                        <p v-if="selectedFile" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            File terpilih: <span class="font-medium">{{ selectedFile.name }}</span>
                            (<span>{{ formatFileSize(selectedFile.size) }}</span>)
                        </p>
                    </div>

                    <!-- Import Errors -->
                    <UAlert
                        v-if="page.props.flash.import_errors && page.props.flash.import_errors.length > 0"
                        color="error"
                        variant="subtle"
                        icon="i-lucide-alert-circle"
                        title="Terdapat Kesalahan Import"
                    >
                        <template #description>
                            <ul class="space-y-1 text-xs mt-2 max-h-40 overflow-y-auto">
                                <li v-for="(error, index) in page.props.flash.import_errors" :key="index" class="flex items-start gap-2">
                                    <span class="text-error mt-0.5">•</span>
                                    <span>{{ error }}</span>
                                </li>
                            </ul>
                        </template>
                    </UAlert>

                    <!-- Format Info -->
                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <p>• Format file: .xlsx atau .xls</p>
                        <p>• Ukuran maksimal: 5MB</p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        color="neutral"
                        variant="soft"
                        @click="closeImportModal"
                        :disabled="importProcessing"
                    >
                        Batal
                    </UButton>
                    <UButton
                        color="primary"
                        :loading="importProcessing"
                        :disabled="!selectedFile"
                        @click="submitImport"
                    >
                        Upload & Import
                    </UButton>
                </div>
            </template>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const page = usePage();

const props = defineProps({
    teachers: Object,
    positions: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters.search || '',
    position_id: props.filters.position_id || 'all',
});

const currentPage = ref(props.teachers.current_page);

const positionOptions = computed(() => [
    { label: 'Semua Jabatan', value: 'all' },
    ...props.positions.map(pos => ({ label: pos.name, value: pos.id }))
]);

const hasActiveFilters = computed(() => {
    return filters.value.search || (filters.value.position_id && filters.value.position_id !== 'all');
});

const exportUrl = computed(() => {
    const params = getExportParams();
    const queryString = new URLSearchParams(params).toString();
    return `/dashboard/teachers/export/excel${queryString ? '?' + queryString : ''}`;
});

const applyFilters = () => {
    const params = { ...filters.value };
    // Don't send 'all' to backend, just omit position_id
    if (params.position_id === 'all') {
        delete params.position_id;
    }
    
    router.get('/dashboard/teachers', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = { search: '', position_id: 'all' };
    applyFilters();
};

const changePage = (page) => {
    const params = { ...filters.value };
    // Don't send 'all' to backend
    if (params.position_id === 'all') {
        delete params.position_id;
    }
    
    router.get(`/dashboard/teachers?page=${page}`, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Export functionality
const getExportParams = () => {
    const params = { ...filters.value };
    if (params.position_id === 'all') {
        delete params.position_id;
    }
    return params;
};

// Import functionality
const isImportModalOpen = ref(false);
const selectedFile = ref(null);
const fileInput = ref(null);
const importProcessing = ref(false);

// Check if there are import errors on mount, and reopen modal
onMounted(() => {
    if (page.props.flash.import_errors && page.props.flash.import_errors.length > 0) {
        isImportModalOpen.value = true;
    }
});

const openImportModal = () => {
    isImportModalOpen.value = true;
};

const closeImportModal = () => {
    isImportModalOpen.value = false;
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const submitImport = () => {
    if (!selectedFile.value) return;

    importProcessing.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    router.post('/dashboard/teachers/import/excel', formData, {
        onSuccess: (response) => {
            // Only close modal if no errors
            if (!page.props.flash.import_errors || page.props.flash.import_errors.length === 0) {
                closeImportModal();
            }
        },
        onError: (errors) => {
            // Keep modal open to show errors
            // Errors will be displayed in the modal body
        },
        onFinish: () => {
            importProcessing.value = false;
        },
        preserveScroll: true,
    });
};

// Delete functionality
const isDeleteModalOpen = ref(false);
const selectedTeacher = ref(null);
const deleteProcessing = ref(false);

const confirmDelete = (teacher) => {
    selectedTeacher.value = teacher;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedTeacher.value = null;
};

const deleteTeacher = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/teachers/${selectedTeacher.value.id}`, {
        onSuccess: () => {
            closeDeleteModal();
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>
