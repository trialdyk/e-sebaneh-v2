<template>
    <DashboardPage
        title="Tambah Jabatan"
        heading="Tambah Jabatan Baru"
        description="Buat data master jabatan guru"
        page-id="master-positions-create"
    >
        <UCard>
            <form @submit.prevent="submit" class="space-y-6">
                <UFormField label="Nama Jabatan" name="name" :error="form.errors.name">
                    <UInput v-model="form.name" placeholder="Contoh: Kepala Sekolah" />
                </UFormField>

                <div class="flex justify-end gap-2 pt-6 border-t mt-6">
                    <UButton color="neutral" variant="soft" label="Batal" to="/dashboard/positions" />
                    <UButton type="submit" color="primary" label="Simpan" :loading="form.processing" />
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/dashboard/positions');
};
</script>
