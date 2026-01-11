<template>
    <DashboardPage
        title="Edit Pegawai"
        page-id="teachers-edit"
        content-class="p-6"
    >
        <UCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama -->
                    <UFormField
                        label="Nama Lengkap"
                        required
                        :error="form.errors.name"
                    >
                        <UInput
                            v-model="form.name"
                            placeholder="Masukkan nama lengkap"
                            @input="form.clearErrors('name')"
                        />
                    </UFormField>

                    <!-- Email -->
                    <UFormField
                        label="Email"
                        required
                        :error="form.errors.email"
                    >
                        <UInput
                            v-model="form.email"
                            type="email"
                            placeholder="email@example.com"
                            @input="form.clearErrors('email')"
                        />
                    </UFormField>

                    <!-- No. HP -->
                    <UFormField
                        label="Nomor HP"
                        :error="form.errors.phone_number"
                    >
                        <UInput
                            v-model="form.phone_number"
                            placeholder="08xx xxxx xxxx"
                            @input="form.clearErrors('phone_number')"
                        />
                    </UFormField>

                    <!-- Password (opsional untuk edit) -->
                    <UFormField
                        label="Password Baru"
                        :error="form.errors.password"
                        hint="Kosongkan jika tidak ingin mengubah password"
                    >
                        <UInput
                            v-model="form.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            @input="form.clearErrors('password')"
                        />
                    </UFormField>

                    <!-- Jabatan -->
                    <UFormField
                        label="Jabatan"
                        required
                        :error="form.errors.position_id"
                    >
                        <USelect
                            v-model="form.position_id"
                            :items="positionOptions"
                            placeholder="Pilih jabatan"
                            @change="form.clearErrors('position_id')"
                        />
                    </UFormField>

                    <!-- NIP -->
                    <UFormField
                        label="NIP"
                        :error="form.errors.nip"
                    >
                        <UInput
                            v-model="form.nip"
                            placeholder="Nomor Induk Pegawai (opsional)"
                            @input="form.clearErrors('nip')"
                        />
                    </UFormField>

                    <!-- Gender -->
                    <UFormField
                        label="Jenis Kelamin"
                        :error="form.errors.gender"
                        class="md:col-span-2"
                    >
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.gender"
                                    value="male"
                                    class="w-4 h-4"
                                    @change="form.clearErrors('gender')"
                                />
                                <span>Laki-laki</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.gender"
                                    value="female"
                                    class="w-4 h-4"
                                    @change="form.clearErrors('gender')"
                                />
                                <span>Perempuan</span>
                            </label>
                        </div>
                    </UFormField>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4">
                    <UButton
                        type="submit"
                        color="primary"
                        :loading="form.processing"
                    >
                        Simpan Perubahan
                    </UButton>
                    <UButton
                        type="button"
                        color="neutral"
                        variant="soft"
                        :to="'/dashboard/teachers'"
                    >
                        Batal
                    </UButton>
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    teacher: Object,
    positions: Array,
    boardingSchools: Array,
});

const form = useForm({
    name: props.teacher.user.name,
    email: props.teacher.user.email,
    phone_number: props.teacher.user.phone_number || '',
    password: '',
    position_id: props.teacher.position_id,
    nip: props.teacher.nip || '',
    gender: props.teacher.user.gender || 'male',
});

const positionOptions = computed(() =>
    props.positions.map(pos => ({ label: pos.name, value: pos.id }))
);

const submit = () => {
    form.put(`/dashboard/teachers/${props.teacher.id}`, {
        preserveScroll: true,
    });
};
</script>
