<template>
    <DashboardPage
        title="Edit Jabatan"
        heading="Edit Data Jabatan"
        description="Perbarui data jabatan guru"
        page-id="master-positions-edit"
    >
        <UCard>
            <form @submit.prevent="submit" class="space-y-6">
                <UFormField label="Nama Jabatan" name="name" :error="form.errors.name">
                    <UInput v-model="form.name" />
                </UFormField>

                <div class="flex justify-end gap-2 pt-6 border-t mt-6">
                    <UButton color="neutral" variant="soft" label="Batal" to="/dashboard/positions" />
                    <UButton type="submit" color="primary" label="Simpan Perubahan" :loading="form.processing" />
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    position: Object,
});

const form = useForm({
    name: props.position.name,
});

const submit = () => {
    form.put(`/dashboard/positions/${props.position.id}`);
};
</script>
