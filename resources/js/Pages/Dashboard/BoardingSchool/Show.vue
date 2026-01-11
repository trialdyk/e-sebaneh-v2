<template>
    <DashboardPage
        title="Detail Pondok"
        page-id="show-boarding-school"
        content-class="p-6"
    >
        <div class="space-y-6">
            <!-- Photo & Basic Info -->
            <UCard>
                <div class="flex gap-6">
                    <div v-if="boardingSchool.photo_url" class="shrink-0">
                        <img
                            :src="boardingSchool.photo_url"
                            :alt="boardingSchool.name"
                            class="w-48 h-48 object-cover rounded-lg"
                        />
                    </div>
                    <div v-else class="shrink-0 w-48 h-48 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                        <UIcon name="i-lucide-building-2" class="w-24 h-24 text-gray-400" />
                    </div>

                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-2">{{ boardingSchool.name }}</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-start gap-2">
                                <UIcon name="i-lucide-map-pin" class="w-5 h-5 text-gray-400 mt-0.5" />
                                <p class="text-gray-600 dark:text-gray-300">{{ boardingSchool.address }}</p>
                            </div>
                            <div v-if="boardingSchool.description" class="flex items-start gap-2">
                                <UIcon name="i-lucide-file-text" class="w-5 h-5 text-gray-400 mt-0.5" />
                                <p class="text-gray-600 dark:text-gray-300">{{ boardingSchool.description }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 mt-4">
                            <UButton
                                color="primary"
                                icon="i-lucide-pencil"
                                label="Edit"
                                :to="`/dashboard/boarding-schools/${boardingSchool.id}/edit`"
                            />
                            <UButton
                                v-if="isSuperAdmin"
                                color="error"
                                variant="soft"
                                icon="i-lucide-trash-2"
                                label="Hapus"
                                @click="openDeleteModal"
                            />
                        </div>
                    </div>
                </div>
            </UCard>

            <!-- Admin List (Super Admin Only) -->
            <UCard v-if="isSuperAdmin">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Admin Pondok</h3>
                        <UButton
                            color="primary"
                            variant="soft"
                            icon="material-symbols:settings-account-box-outline-sharp"
                            label="Atur Admin"
                            @click="openAdminModal"
                        />
                    </div>
                </template>

                <div v-if="boardingSchool.admins.length > 0" class="space-y-3">
                    <div v-for="admin in boardingSchool.admins" :key="admin.id" class="flex items-center gap-3">
                        <UAvatar :src="admin.avatar" :alt="admin.name" size="md" />
                        <div>
                            <p class="font-medium">{{ admin.name }}</p>
                            <p class="text-sm text-gray-500">{{ admin.email }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-500 text-sm">Belum ada admin yang ditugaskan</p>
            </UCard>

            <!-- Facilities -->
            <UCard>
                <template #header>
                    <h3 class="text-lg font-semibold">Fasilitas</h3>
                </template>

                <div v-if="boardingSchool.facilities.length > 0" class="flex flex-wrap gap-2">
                    <UBadge
                        v-for="facility in boardingSchool.facilities"
                        :key="facility.id"
                        color="primary"
                        variant="soft"
                    >
                        {{ facility.name }}
                    </UBadge>
                </div>
                <p v-else class="text-gray-500 text-sm">Belum ada fasilitas yang ditambahkan</p>
            </UCard>
        </div>

        <!-- Admin Management Modal -->
        <UModal v-model:open="isAdminModalOpen" :ui="{ width: 'max-w-4xl' }">
            <template #header>
                <h3 class="text-lg font-semibold">Kelola Admin Pondok</h3>
            </template>

            <template #body>
                <div class="space-y-6">
                    <!-- Create Admin Form -->
                    <div class="pb-4">
                        <h4 class="font-semibold mb-3">Tambah Admin Baru</h4>
                        <form @submit.prevent="submitCreateAdmin" class="grid grid-cols-2 gap-4">
                            <UFormField label="Nama" required :error="adminForm.errors.name">
                                <UInput
                                    v-model="adminForm.name"
                                    placeholder="Nama lengkap"
                                    @input="adminForm.clearErrors('name')"
                                />
                            </UFormField>

                            <UFormField label="Email" required :error="adminForm.errors.email">
                                <UInput
                                    v-model="adminForm.email"
                                    type="email"
                                    placeholder="email@example.com"
                                    @input="adminForm.clearErrors('email')"
                                />
                            </UFormField>

                            <UFormField label="Password" required :error="adminForm.errors.password">
                                <UInput
                                    v-model="adminForm.password"
                                    type="password"
                                    placeholder="Minimal 8 karakter"
                                    @input="adminForm.clearErrors('password')"
                                />
                            </UFormField>

                            <UFormField label="No. Telepon" :error="adminForm.errors.phone_number">
                                <UInput
                                    v-model="adminForm.phone_number"
                                    placeholder="08xx xxxx xxxx"
                                    @input="adminForm.clearErrors('phone_number')"
                                />
                            </UFormField>

                            <UFormField label="Jenis Kelamin" required :error="adminForm.errors.gender" class="col-span-2">
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="adminForm.gender"
                                            value="male"
                                            class="w-4 h-4"
                                            @change="adminForm.clearErrors('gender')"
                                        />
                                        <span>Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="adminForm.gender"
                                            value="female"
                                            class="w-4 h-4"
                                            @change="adminForm.clearErrors('gender')"
                                        />
                                        <span>Perempuan</span>
                                    </label>
                                </div>
                            </UFormField>

                            <div class="col-span-2">
                                <UButton
                                    type="submit"
                                    color="primary"
                                    label="Tambah Admin"
                                    :loading="adminForm.processing"
                                    block
                                />
                            </div>
                        </form>
                    </div>

                    <!-- Separator -->
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>

                    <!-- Admin List -->
                    <div>
                        <h4 class="font-semibold mb-3">Daftar Admin ({{ admins.length }})</h4>
                        <div v-if="loadingAdmins" class="text-center py-4">
                            <UIcon name="i-lucide-loader-2" class="animate-spin w-6 h-6 mx-auto" />
                        </div>
                        <div v-else-if="admins.length > 0" class="space-y-2">
                            <div
                                v-for="admin in admins"
                                :key="admin.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <UAvatar :src="admin.avatar" :alt="admin.name" size="sm" />
                                    <div>
                                        <p class="font-medium">{{ admin.name }}</p>
                                        <p class="text-xs text-gray-500">{{ admin.email }}</p>
                                    </div>
                                </div>
                                <UButton
                                    color="error"
                                    variant="soft"
                                    icon="i-lucide-trash-2"
                                    size="sm"
                                    @click="confirmDeleteAdmin(admin)"
                                />
                            </div>
                        </div>
                        <p v-else class="text-gray-500 text-sm text-center py-4">
                            Belum ada admin
                        </p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end">
                    <UButton
                        color="neutral"
                        variant="soft"
                        label="Tutup"
                        @click="closeAdminModal"
                    />
                </div>
            </template>
        </UModal>

        <!-- Delete Admin Confirmation Modal -->
        <UModal v-model:open="isDeleteAdminModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Admin</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Admin <span class="font-semibold">{{ selectedAdmin?.name }}</span> akan dihapus dan 
                            akun user-nya juga akan terhapus permanen.
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
                        @click="closeDeleteAdminModal"
                        :disabled="deleteAdminProcessing"
                    />
                    <UButton
                        color="error"
                        label="Hapus"
                        :loading="deleteAdminProcessing"
                        @click="deleteAdmin"
                    />
                </div>
            </template>
        </UModal>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Pondok</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Pondok <span class="font-semibold">{{ boardingSchool.name }}</span> akan dihapus secara permanen.
                            Semua admin assignment dan fasilitas juga akan ikut terhapus.
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
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    boardingSchool: Object,
});

const page = usePage();
const isSuperAdmin = computed(() => {
    const roles = page.props.auth.user?.roles || [];
    return roles.includes('super-admin');
});

// Admin Modal
const isAdminModalOpen = ref(false);
const admins = ref([]);
const loadingAdmins = ref(false);

const adminForm = useForm({
    name: '',
    email: '',
    password: '',
    phone_number: '',
    gender: 'male',
});

const openAdminModal = async () => {
    isAdminModalOpen.value = true;
    await loadAdmins();
};

const closeAdminModal = () => {
    isAdminModalOpen.value = false;
    adminForm.reset();
};

const loadAdmins = async () => {
    loadingAdmins.value = true;
    try {
        const response = await axios.get(`/dashboard/boarding-schools/${props.boardingSchool.id}/admins`);
        admins.value = response.data.admins;
    } catch (error) {
        console.error('Failed to load admins:', error);
    } finally {
        loadingAdmins.value = false;
    }
};

const submitCreateAdmin = () => {
    adminForm.post(`/dashboard/boarding-schools/${props.boardingSchool.id}/admins`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Admin created successfully');
            adminForm.reset();
            loadAdmins();
            // Reload page to update admin count on card
            router.reload({ only: ['boardingSchool'] });
        },
        onError: (errors) => {
            console.error('Failed to create admin:', errors);
        },
    });
};

// Delete Admin
const isDeleteAdminModalOpen = ref(false);
const selectedAdmin = ref(null);
const deleteAdminProcessing = ref(false);

const confirmDeleteAdmin = (admin) => {
    selectedAdmin.value = admin;
    isDeleteAdminModalOpen.value = true;
};

const closeDeleteAdminModal = () => {
    isDeleteAdminModalOpen.value = false;
    selectedAdmin.value = null;
};

const deleteAdmin = () => {
    deleteAdminProcessing.value = true;
    router.delete(`/dashboard/boarding-schools/${props.boardingSchool.id}/admins/${selectedAdmin.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            loadAdmins();
            closeDeleteAdminModal();
            // Reload page to update admin count on card
            router.reload({ only: ['boardingSchool'] });
        },
        onFinish: () => {
            deleteAdminProcessing.value = false;
        },
    });
};

// Delete Pondok
const isDeleteModalOpen = ref(false);
const deleteProcessing = ref(false);

const openDeleteModal = () => {
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
};

const confirmDelete = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/boarding-schools/${props.boardingSchool.id}`, {
        onSuccess: () => {
            // Will redirect to index via controller
        },
        onFinish: () => {
            deleteProcessing.value = false;
            closeDeleteModal();
        },
    });
};
</script>
