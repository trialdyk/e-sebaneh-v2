<template>
    <DashboardPage
        title="Pondok"
        heading="Daftar Pondok Pesantren"
        description="Kelola data pondok pesantren"
        page-id="boarding-schools"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                label="Tambah Pondok"
                to="/dashboard/boarding-schools/create"
            />
        </template>

        <!-- Pondok Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <UCard v-for="pondok in boardingSchools" :key="pondok.id">
                <div class="mb-4">
                    <img
                        v-if="pondok.photo_url"
                        :src="pondok.photo_url"
                        :alt="pondok.name"
                        class="w-full h-40 object-cover rounded-lg mb-3"
                    />
                    <div v-else class="w-full h-40 bg-gray-200 dark:bg-gray-800 rounded-lg mb-3 flex items-center justify-center">
                        <UIcon name="i-lucide-building-2" class="text-gray-400 w-16 h-16" />
                    </div>

                    <h3 class="text-lg font-bold">{{ pondok.name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                        {{ pondok.address }}
                    </p>

                    <div class="flex gap-2 mt-3">
                        <UBadge color="primary" variant="soft">
                            {{ pondok.admins_count }} Admin
                        </UBadge>
                        <UBadge color="info" variant="soft">
                            {{ pondok.facilities_count }} Fasilitas
                        </UBadge>
                    </div>
                </div>

                <div class="flex gap-2">
                    <UButton
                        color="neutral"
                        variant="soft"
                        icon="i-lucide-eye"
                        :to="`/dashboard/boarding-schools/${pondok.id}`"
                    />
                    <UButton
                        color="neutral"
                        variant="soft"
                        icon="i-lucide-pencil"
                        :to="`/dashboard/boarding-schools/${pondok.id}/edit`"
                    />
                    <UButton
                        color="error"
                        variant="soft"
                        icon="i-lucide-trash-2"
                        @click="openDeleteModal(pondok)"
                    />
                </div>
            </UCard>

            <!-- Empty State -->
            <UCard v-if="boardingSchools.length === 0" class="col-span-full">
                <div class="text-center py-12">
                    <UIcon name="i-lucide-building-2" class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        Belum ada pondok
                    </h3>
                    <p class="text-sm text-gray-500 mt-2">
                        Klik "Tambah Pondok" untuk menambahkan pondok baru
                    </p>
                </div>
            </UCard>
        </div>

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
                            Pondok <span class="font-semibold">{{ selectedPondok?.name }}</span> akan dihapus secara permanen.
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    boardingSchools: {
        type: Array,
        default: () => [],
    },
});

// Delete modal
const isDeleteModalOpen = ref(false);
const selectedPondok = ref(null);
const deleteProcessing = ref(false);

const openDeleteModal = (pondok) => {
    selectedPondok.value = pondok;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedPondok.value = null;
};

const confirmDelete = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/boarding-schools/${selectedPondok.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            closeDeleteModal();
        },
    });
};
</script>
