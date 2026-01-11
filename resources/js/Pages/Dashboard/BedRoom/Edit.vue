<template>
    <DashboardPage
        title="Edit Kamar"
        page-id="bedrooms-edit"
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
                            Simpan Perubahan
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

const props = defineProps({
    bedRoom: Object,
});

const form = useForm({
    name: props.bedRoom.name,
    capacity: props.bedRoom.capacity,
});

const submit = () => {
    form.put(`/dashboard/bed-rooms/${props.bedRoom.id}`);
};
</script>
