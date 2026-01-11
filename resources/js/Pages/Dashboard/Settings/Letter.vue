<template>
    <DashboardPage
        title="Pengaturan Surat"
        page-id="letter-settings"
        content-class="p-6"
    >
        <UCard>
            <template #header>
                <h3 class="text-lg font-semibold">Pengaturan Penandatangan Surat Izin</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Nama-nama berikut akan digunakan untuk penandatanganan surat izin santri
                </p>
            </template>

            <form @submit.prevent="submit" class="space-y-6">
                <UFormField
                    label="Nama Ketua"
                    required
                    :error="form.errors.letter_head_name"
                >
                    <UInput
                        v-model="form.letter_head_name"
                        placeholder="Masukkan nama lengkap ketua"
                        @input="form.clearErrors('letter_head_name')"
                        class="w-64"
                    />
                </UFormField>

                <UFormField
                    label="Nama Sekretaris"
                    required
                    :error="form.errors.letter_secretary_name"
                >
                    <UInput
                        v-model="form.letter_secretary_name"
                        placeholder="Masukkan nama lengkap sekretaris"
                        @input="form.clearErrors('letter_secretary_name')"
                        class="w-64"
                    />
                </UFormField>

                <div class="flex gap-2">
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
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    boardingSchool: Object,
});

const form = useForm({
    letter_head_name: props.boardingSchool.letter_head_name || '',
    letter_secretary_name: props.boardingSchool.letter_secretary_name || '',
});

const submit = () => {
    form.patch('/dashboard/settings/letter', {
        preserveScroll: true,
    });
};
</script>
