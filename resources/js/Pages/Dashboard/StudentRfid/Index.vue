<template>
    <DashboardPage
        title="Pendaftaran RFID"
        heading="Kelola RFID Santri"
        description="Daftarkan dan kelola kartu RFID untuk santri"
        page-id="student-rfid-index"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-credit-card"
                label="Scan RFID"
                @click="openModal(null)"
            />
        </template>

        <!-- Filters -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <UInput
                v-model="filters.search"
                icon="i-lucide-search"
                placeholder="Cari nama/NIS..."
            />
            <USelect
                v-model="filters.classroom_id"
                :items="classroomOptions"
                label-key="name"
                value-key="id"
                placeholder="Kelas"
            />
            <USelect
                v-model="filters.rfid_status"
                :items="rfidStatusOptions"
                label-key="label"
                value-key="value"
                placeholder="Status RFID"
            />
        </div>

        <UCard>
            <UTable 
                :data="students.data" 
                :columns="columns"
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

                <template #rfid-cell="{ row }">
                    <UBadge v-if="row.original.rfid" color="success" variant="subtle">
                        {{ row.original.rfid }}
                    </UBadge>
                    <UBadge v-else color="neutral" variant="subtle">
                        Belum Terdaftar
                    </UBadge>
                </template>

                <template #actions-cell="{ row }">
                    <UButton
                        :color="row.original.rfid ? 'warning' : 'primary'"
                        variant="soft"
                        size="sm"
                        :icon="row.original.rfid ? 'i-lucide-refresh-cw' : 'i-lucide-plus'"
                        @click="openModal(row.original)"
                    >
                        {{ row.original.rfid ? 'Update' : 'Daftar' }}
                    </UButton>
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

        <!-- RFID Modal -->
        <UModal v-model:open="isModalOpen" :title="selectedStudent?.rfid ? 'Update RFID' : 'Daftar RFID'">
            <template #body>
                <div v-if="selectedStudent" class="space-y-4">
                    <!-- Student Info -->
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <UAvatar
                            :src="selectedStudent.user?.photo ? (selectedStudent.user.photo.startsWith('http') ? selectedStudent.user.photo : `/storage/${selectedStudent.user.photo}`) : null"
                            :alt="selectedStudent.user?.name"
                            size="lg"
                        />
                        <div>
                            <p class="font-semibold">{{ selectedStudent.user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ selectedStudent.student_id }}</p>
                        </div>
                    </div>

                    <!-- RFID Input -->
                    <form @submit.prevent="submit">
                        <UFormField label="RFID" name="rfid" :error="form.errors.rfid" required>
                            <UInput 
                                ref="rfidInput"
                                v-model="form.rfid" 
                                placeholder="Scan atau ketik RFID..." 
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                    </form>
                </div>
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
    </DashboardPage>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const props = defineProps({
    students: Object,
    classrooms: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    classroom_id: props.filters?.classroom_id || 'all',
    rfid_status: props.filters?.rfid_status || 'unregistered',
});

const currentPage = ref(props.students.current_page);
const isModalOpen = ref(false);
const selectedStudent = ref(null);
const rfidInput = ref(null);

const rfidStatusOptions = [
    { value: 'all', label: 'Semua' },
    { value: 'unregistered', label: 'Belum Terdaftar' },
    { value: 'registered', label: 'Sudah Terdaftar' },
];

const classroomOptions = computed(() => [
    { id: 'all', name: 'Semua Kelas' },
    ...(props.classrooms || [])
]);

const columns = [
    { id: 'title', header: 'Nama & NIS' },
    { id: 'classroom', header: 'Kelas' },
    { id: 'rfid', header: 'RFID' },
    { id: 'actions', header: 'Aksi' },
];

const form = useForm({
    rfid: '',
});

const openModal = (student) => {
    selectedStudent.value = student;
    form.rfid = student?.rfid || '';
    isModalOpen.value = true;
    
    // Auto-focus input after modal opens
    nextTick(() => {
        rfidInput.value?.$el?.querySelector('input')?.focus();
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedStudent.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (!selectedStudent.value) return;
    
    form.put(`/dashboard/student-rfid/${selectedStudent.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const handleFilters = debounce(() => {
    router.get('/dashboard/student-rfid', filters.value, { preserveState: true, replace: true });
}, 500);

watch(filters, handleFilters, { deep: true });

watch(currentPage, (page) => {
    router.get('/dashboard/student-rfid', { ...filters.value, page }, { preserveState: true, replace: true });
});
</script>
