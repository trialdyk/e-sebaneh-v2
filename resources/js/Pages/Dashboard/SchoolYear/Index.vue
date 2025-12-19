<template>
    <DashboardLayout>
        <UDashboardPanel id="school-years">
            <template #header>
                <UDashboardNavbar title="Tahun Ajaran">
                    <template #leading>
                        <UDashboardSidebarCollapse />
                    </template>
                </UDashboardNavbar>
            </template>

            <template #body>
                <!-- Breadcrumb -->
                <div class="px-6 pt-6">
                    <UBreadcrumb :items="breadcrumbItems" />
                </div>

                <!-- Header with Add Button -->
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h2 class="text-2xl font-bold">Daftar Tahun Ajaran</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Kelola tahun ajaran akademik sekolah
                        </p>
                    </div>
                    <UButton
                        color="primary"
                        icon="i-lucide-plus"
                        label="Tambah Tahun Ajaran"
                        @click="openCreateModal"
                    />
                </div>

                <!-- School Years Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 px-6 pb-6">
                    <UCard v-for="schoolYear in schoolYears" :key="schoolYear.id">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold">{{ schoolYear.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ schoolYear.is_active ? 'Tahun Ajaran Aktif' : 'Tidak Aktif' }}
                                </p>
                            </div>
                            <UBadge
                                :color="schoolYear.is_active ? 'info' : 'error'"
                                variant="soft"
                            >
                                {{ schoolYear.is_active ? 'Aktif' : 'Non-Aktif' }}
                            </UBadge>
                        </div>

                        <div class="flex gap-2">
                            <UButton
                                v-if="!schoolYear.is_active"
                                color="success"
                                variant="soft"
                                label="Aktifkan"
                                block
                                @click="activateSchoolYear(schoolYear)"
                            />
                            <UButton
                                color="neutral"
                                variant="soft"
                                icon="i-lucide-pencil"
                                @click="openEditModal(schoolYear)"
                            />
                            <UButton
                                color="error"
                                variant="soft"
                                icon="i-lucide-trash-2"
                                :disabled="schoolYear.is_active"
                                @click="openDeleteModal(schoolYear)"
                            />
                        </div>
                    </UCard>

                    <!-- Empty State -->
                    <UCard v-if="schoolYears.length === 0">
                        <div class="text-center py-12">
                            <UIcon name="i-lucide-calendar" class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                Belum ada tahun ajaran
                            </h3>
                            <p class="text-sm text-gray-500 mt-2">
                                Klik tombol "Tambah Tahun Ajaran" untuk membuat tahun ajaran baru
                            </p>
                        </div>
                    </UCard>
                </div>
            </template>
        </UDashboardPanel>

        <!-- Create/Edit Modal -->
        <UModal v-model:open="isFormModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">
                    {{ isEditing ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
                </h3>
            </template>

            <template #body>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <UFormField label="Nama Tahun Ajaran" required :error="form.errors.name">
                        <UInput
                            v-model="form.name"
                            placeholder="Contoh: 2024/2025"
                            size="lg"
                            class="w-full"
                            :disabled="form.processing"
                            @input="form.clearErrors('name')"
                        />
                    </UFormField>

                    <UFormField label="Status">
                        <UCheckbox
                            v-model="form.is_active"
                            label="Aktifkan tahun ajaran ini"
                            :disabled="form.processing"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Jika diaktifkan, tahun ajaran lain akan otomatis dinonaktifkan
                        </p>
                    </UFormField>
                </form>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        color="neutral"
                        variant="soft"
                        label="Batal"
                        @click="closeFormModal"
                        :disabled="form.processing"
                    />
                    <UButton
                        color="primary"
                        :label="isEditing ? 'Simpan' : 'Tambah'"
                        :loading="form.processing"
                        @click="submitForm"
                    />
                </div>
            </template>
        </UModal>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Tahun Ajaran</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Tahun ajaran <span class="font-semibold">{{ selectedSchoolYear?.name }}</span> akan dihapus secara permanen.
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2">
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
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useBreadcrumb } from '@/composables/useBreadcrumb';

const props = defineProps({
    schoolYears: {
        type: Array,
        default: () => [],
    },
});

// Breadcrumb - auto generate dari URL
const breadcrumbItems = useBreadcrumb();

// Modals state
const isFormModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isEditing = ref(false);
const selectedSchoolYear = ref(null);
const deleteProcessing = ref(false);

// Form
const form = useForm({
    name: '',
    is_active: false,
});

// Create Modal
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isFormModalOpen.value = true;
};

// Edit Modal
const openEditModal = (schoolYear) => {
    isEditing.value = true;
    selectedSchoolYear.value = schoolYear;
    form.name = schoolYear.name;
    form.is_active = schoolYear.is_active;
    form.clearErrors();
    isFormModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    form.reset();
    selectedSchoolYear.value = null;
};

// Submit Form
const submitForm = () => {
    if (isEditing.value) {
        form.put(`/dashboard/school-years/${selectedSchoolYear.value.id}`, {
            onSuccess: () => {
                closeFormModal();
            },
        });
    } else {
        form.post('/dashboard/school-years', {
            onSuccess: () => {
                closeFormModal();
            },
        });
    }
};

// Activate School Year
const activateSchoolYear = (schoolYear) => {
    router.post(`/dashboard/school-years/${schoolYear.id}/activate`);
};

// Delete Modal
const openDeleteModal = (schoolYear) => {
    selectedSchoolYear.value = schoolYear;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedSchoolYear.value = null;
};

const confirmDelete = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/school-years/${selectedSchoolYear.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            closeDeleteModal();
        },
    });
};
</script>
