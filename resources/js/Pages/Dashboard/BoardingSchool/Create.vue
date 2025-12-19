<template>
    <DashboardPage
        title="Tambah Pondok"
        page-id="create-boarding-school"
        content-class="p-6"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Photo Upload -->
            <UFormField label="Foto Pondok" :error="form.errors.photo">
                <div class="flex items-start gap-4">
                    <div v-if="photoPreview" class="relative">
                        <img :src="photoPreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg" />
                        <UButton
                            color="error"
                            size="xs"
                            icon="i-lucide-x"
                            class="absolute top-1 right-1"
                            @click="removePhoto"
                        />
                    </div>
                    <div v-else class="w-32 h-32 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                        <UIcon name="i-lucide-image" class="w-12 h-12 text-gray-400" />
                    </div>
                    <div>
                        <input
                            ref="photoInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handlePhotoChange"
                        />
                        <UButton
                            color="neutral"
                            variant="soft"
                            label="Pilih Foto"
                            icon="i-lucide-upload"
                            @click="$refs.photoInput.click()"
                        />
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Max 2MB.</p>
                    </div>
                </div>
            </UFormField>

            <!-- Name -->
            <UFormField label="Nama Pondok" required :error="form.errors.name">
                <UInput
                    v-model="form.name"
                    placeholder="Contoh: Pondok Pesantren Al-Ikhlas"
                    size="lg"
                    class="w-full"
                    @input="form.clearErrors('name')"
                />
            </UFormField>

            <!-- Address -->
            <UFormField label="Alamat" required :error="form.errors.address">
                <UTextarea
                    v-model="form.address"
                    placeholder="Masukkan alamat lengkap pondok"
                    :rows="3"
                    size="lg"
                    class="w-full"
                    @input="form.clearErrors('address')"
                />
            </UFormField>

            <!-- Description -->
            <UFormField label="Deskripsi" :error="form.errors.description">
                <UTextarea
                    v-model="form.description"
                    placeholder="Deskripsi singkat tentang pondok"
                    :rows="4"
                    size="lg"
                    class="w-full"
                    @input="form.clearErrors('description')"
                />
            </UFormField>



            <!-- Facilities -->
            <UFormField label="Fasilitas" :error="form.errors.facilities">
                <div class="space-y-2">
                    <div v-for="(facility, index) in form.facilities" :key="index" class="flex gap-2">
                        <UInput
                            v-model="form.facilities[index]"
                            placeholder="Nama fasilitas"
                            size="lg"
                            class="flex-1"
                        />
                        <UButton
                            color="error"
                            variant="soft"
                            icon="i-lucide-trash-2"
                            @click="removeFacility(index)"
                        />
                    </div>
                    <UButton
                        color="neutral"
                        variant="soft"
                        label="Tambah Fasilitas"
                        icon="i-lucide-plus"
                        block
                        @click="addFacility"
                    />
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimal 1 fasilitas harus ditambahkan</p>
            </UFormField>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-4">
                <UButton
                    color="neutral"
                    variant="soft"
                    label="Batal"
                    to="/dashboard/boarding-schools"
                    :disabled="form.processing"
                />
                <UButton
                    type="submit"
                    color="primary"
                    label="Simpan"
                    :loading="form.processing"
                />
            </div>
        </form>
    </DashboardPage>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    adminUsers: Array,
});

const form = useForm({
    name: '',
    address: '',
    description: '',
    photo: null,
    facilities: [],
});

// Photo handling
const photoPreview = ref(null);
const photoInput = ref(null);

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
        form.clearErrors('photo');
    }
};

const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};



// Facilities
const addFacility = () => {
    form.facilities.push('');
};

const removeFacility = (index) => {
    if (form.facilities.length > 1) {
        form.facilities.splice(index, 1);
    }
};

// Submit
const submit = () => {
    form.post('/dashboard/boarding-schools', {
        onSuccess: () => {
            form.reset();
            photoPreview.value = null;
        },
    });
};
</script>
