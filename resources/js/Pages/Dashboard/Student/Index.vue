<template>
    <DashboardPage
        title="Data Santri"
        heading="Kelola Data Santri"
        description="Manajemen data santri/santriwati pondok pesantren"
        page-id="students-index"
    >
        <template #header>
            <div class="flex gap-2">
                <UButton
                    color="primary"
                    icon="i-lucide-plus"
                    label="Tambah Santri"
                    to="/dashboard/students/create"
                />
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
            <UInput
                v-model="filters.search"
                icon="i-lucide-search"
                placeholder="Cari nama/NIS..."
            />
            <UInput
                v-model="filters.rfid"
                icon="i-lucide-credit-card"
                placeholder="RFID..."
            />
            <USelect
                v-model="filters.status"
                :items="statusOptions"
                label-key="label"
                value-key="value"
                placeholder="Status"
            />
            <USelect
                v-model="filters.gender"
                :items="genderOptions"
                label-key="label"
                value-key="value"
                placeholder="Jenis Kelamin"
            />
            <USelect
                v-model="filters.classroom_id"
                :items="classroomOptions"
                label-key="name"
                value-key="id"
                placeholder="Kelas"
            />
        </div>

        <!-- Info Alert -->
        <UAlert
            color="info"
            variant="subtle"
            icon="i-lucide-info"
            class="mb-4"
        >
            <template #title>Fitur Lengkap di Halaman Detail</template>
            <template #description>
                Klik <strong>Detail</strong> pada aksi santri untuk menambah Rekam Medis, Catatan Hafalan, Izin, Pelanggaran, dan lainnya.
            </template>
        </UAlert>

        <!-- Actions Bar -->
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
        </div>

        <UCard>
            <UTable 
                :data="students.data" 
                :columns="columns"
                :loading="false"
            >
                <template #title-cell="{ row }">
                    <div class="flex items-center gap-3">
                        <UAvatar
                            :src="row.original.user?.photo ? (row.original.user.photo.startsWith('http') ? row.original.user.photo : `/storage/${row.original.user.photo}`) : null"
                            :alt="row.original.user?.name"
                        />
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ row.original.user?.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ row.original.student_id }}</p>
                        </div>
                    </div>
                </template>

                <template #classroom-cell="{ row }">
                    <UBadge v-if="row.original.current_classroom?.classroom" color="primary" variant="subtle">
                        {{ row.original.current_classroom.classroom.name }}
                    </UBadge>
                    <span v-else class="text-gray-400 text-sm">-</span>
                </template>

                <template #status-cell="{ row }">
                    <UBadge :color="getStatusColor(row.original.status)" variant="subtle">
                        {{ getStatusLabel(row.original.status) }}
                    </UBadge>
                </template>

                <template #actions-cell="{ row }">
                    <UDropdownMenu :items="getActionItems(row.original)">
                        <UButton color="neutral" variant="ghost" icon="i-lucide-more-horizontal" />
                    </UDropdownMenu>
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ students.from }} to {{ students.to }} of {{ students.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="students.total"
                    :page-count="students.per_page"
                />
            </div>
        </UCard>

        <!-- Import Modal -->
        <UModal v-model:open="isImportModalOpen" :ui="{ width: 'sm:max-w-2xl' }">
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <UIcon name="i-lucide-file-spreadsheet" class="w-6 h-6 text-primary" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Import Data Santri</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Unggah file Excel untuk mengimpor data santri secara massal</p>
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
                                <li>Isi data santri sesuai format yang tersedia</li>
                                <li><strong>Wajib:</strong> NIS, Nama, Tempat Lahir, Tanggal Lahir, L/P, Status, Alamat</li>
                                <li><strong>Opsional:</strong> Email, No HP, Anak Ke-, Jumlah Saudara, Data Orang Tua</li>
                                <li><strong>L/P</strong>: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">L</code> / <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">Laki-laki</code> atau <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">P</code> / <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">Perempuan</code></li>
                                <li><strong>Status</strong>: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">Aktif</code> / <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">Lulus</code> / <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">Keluar/DO</code></li>
                                <li>Email akan dibuat otomatis dari NIS jika kosong</li>
                                <li>Password default: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">password</code></li>
                            </ol>
                        </template>
                    </UAlert>

                    <!-- Download Template -->
                    <UButton
                        color="primary"
                        variant="outline"
                        icon="i-lucide-download"
                        block
                        :href="'/dashboard/students/template/download'"
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
                            class="block w-full text-sm text-gray-500 dark:text-gray-400
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
import { ref, watch, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const page = usePage();

const props = defineProps({
    students: Object,
    classrooms: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    rfid: props.filters?.rfid || '',
    status: props.filters?.status || 'active',
    gender: props.filters?.gender || 'all',
    classroom_id: props.filters?.classroom_id || 'all',
});

const currentPage = ref(props.students.current_page);

const statusOptions = [
    { value: 'all', label: 'Semua Status' },
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
    { value: 'graduated', label: 'Lulus' },
    { value: 'dropped_out', label: 'Keluar/DO' },
];

const genderOptions = [
    { value: 'all', label: 'Semua' },
    { value: 'male', label: 'Laki-laki' },
    { value: 'female', label: 'Perempuan' },
];

const classroomOptions = computed(() => [
    { id: 'all', name: 'Semua Kelas' },
    ...(props.classrooms || [])
]);

// Export URL with current filters
const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.rfid) params.set('rfid', filters.value.rfid);
    if (filters.value.status) params.set('status', filters.value.status);
    if (filters.value.gender) params.set('gender', filters.value.gender);
    if (filters.value.classroom_id) params.set('classroom_id', filters.value.classroom_id);
    return `/dashboard/students/export/excel${params.toString() ? '?' + params.toString() : ''}`;
});

const columns = [
    { id: 'title', header: 'Nama & NIS' }, // Virtual
    { id: 'classroom', header: 'Kelas' }, // Virtual
    { accessorKey: 'gender', header: 'L/P', cell: ({ row }) => getGenderLabel(row.original.gender) },
    { accessorKey: 'status', header: 'Status', cell: ({ row }) => getStatusLabel(row.original.status) },
    { id: 'actions', header: 'Aksi' }, // Virtual
];

const getGenderLabel = (gender) => {
    switch (gender) {
        case 'male': return 'Laki-laki';
        case 'female': return 'Perempuan';
        default: return gender;
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'active': return 'Aktif';
        case 'inactive': return 'Tidak Aktif';
        case 'graduated': return 'Lulus';
        case 'dropped_out': return 'Keluar/DO';
        default: return status;
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'registered': return 'info';
        case 'alumni': return 'neutral';
        case 'dropped_out': return 'error';
        default: return 'neutral';
    }
};

const getActionItems = (student) => [
    [
        {
            label: 'Detail',
            icon: 'i-lucide-eye',
            iconClass: 'text-primary',
            onSelect: () => router.visit(`/dashboard/students/${student.id}`)
        },
        {
            label: 'Edit Data',
            icon: 'i-lucide-pencil',
            iconClass: 'text-warning',
            onSelect: () => router.visit(`/dashboard/students/${student.id}/edit`)
        }
    ],
    [
        {
            label: 'Hapus',
            icon: 'i-lucide-trash-2',
            iconClass: 'text-error',
            onSelect: () => confirmDelete(student)
        }
    ]
];

const handleFilters = debounce(() => {
    router.get('/dashboard/students', filters.value, { preserveState: true, replace: true });
}, 500);

watch(filters, handleFilters, { deep: true });

watch(currentPage, (page) => {
    router.get('/dashboard/students', { ...filters.value, page }, { preserveState: true, replace: true });
});

const confirmDelete = (student) => {
    if (confirm(`Yakin ingin menghapus data santri "${student.user?.name}"?`)) {
        router.delete(`/dashboard/students/${student.id}`, {
            onSuccess: () => {
                // Toast handled by layout/flash
            }
        });
    }
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

    router.post('/dashboard/students/import/excel', formData, {
        onSuccess: () => {
            // Only close modal if no errors
            if (!page.props.flash.import_errors || page.props.flash.import_errors.length === 0) {
                closeImportModal();
            }
        },
        onError: () => {
            // Keep modal open to show errors
        },
        onFinish: () => {
            importProcessing.value = false;
        },
        preserveScroll: true,
    });
};
</script>
