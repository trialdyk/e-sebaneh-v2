<template>
    <DashboardPage
        title="Tambah Kamar"
        page-id="bedrooms-create"
        content-class="p-6"
    >
        <div class="max-w-2xl mx-auto">
            <UCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <UFormField label="Nama Kamar" :error="form.errors.name">
                        <UInput
                            v-model="form.name"
                            placeholder="Contoh: Asrama A"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Kapasitas" :error="form.errors.capacity">
                        <UInput
                            v-model="form.capacity"
                            type="number"
                            placeholder="Contoh: 20"
                            class="w-full"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Jumlah maksimal santri dalam kamar ini.
                        </p>
                    </UFormField>

                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-800">
                        <UButton
                            color="neutral"
                            variant="soft"
                            to="/dashboard/bed-rooms"
                            :disabled="form.processing"
                        >
                            Batal
                        </UButton>
                        <UButton
                            type="submit"
                            color="primary"
                            :loading="form.processing"
                        >
                            Simpan
                        </UButton>
                    </div>
                </form>
            </UCard>
        </div>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const form = useForm({
    name: '',
    capacity: '',
});

const submit = () => {
    form.post('/dashboard/bed-rooms');
};
</script>
