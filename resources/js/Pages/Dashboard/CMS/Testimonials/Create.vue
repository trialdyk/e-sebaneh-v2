<template>
    <DashboardPage
        title="Tambah Testimoni"
        heading="Buat Testimoni Baru"
        description="Tambahkan testimoni santri atau wali santri"
        page-id="cms-testimonials-create"
    >
        <template #header>
            <UButton
                color="neutral"
                icon="i-lucide-arrow-left"
                label="Kembali"
                variant="ghost"
                to="/dashboard/cms/testimonials"
            />
        </template>

        <UCard>
            <UForm :state="form" @submit="submit" class="space-y-6 p-4 max-w-3xl mx-auto w-full">
                <!-- Photo Upload -->
                <UFormField label="Foto Profil" name="photo" hint="Format: JPG, PNG, WEBP (Max 2MB)">
                     <div class="space-y-3">
                        <input 
                            type="file" 
                            @change="handleFileChange"
                            accept="image/*"
                            class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600"
                        />
                        <div v-if="photoPreview" class="relative inline-block">
                            <img :src="photoPreview" alt="Preview" class="w-32 h-32 rounded-full object-cover border border-gray-200 shadow-sm" />
                             <UButton 
                                v-if="photoPreview"
                                icon="i-lucide-x"
                                color="error"
                                size="xs"
                                class="absolute top-0 right-0 rounded-full"
                                @click="clearPhoto"
                            />
                        </div>
                    </div>
                </UFormField>

                <div class="grid grid-cols-2 gap-6">
                    <UFormField label="Nama Lengkap" name="name" required>
                        <UInput 
                            v-model="form.name" 
                            placeholder="Contoh: Ahmad Fulan"
                            size="lg"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Peran / Jabatan" name="role" required>
                        <UInput 
                            v-model="form.role" 
                            placeholder="Contoh: Alumni 2020"
                            size="lg"
                            class="w-full"
                        />
                    </UFormField>
                </div>

                <UFormField label="Isi Testimoni" name="quote" required>
                    <UTextarea 
                        v-model="form.quote" 
                        placeholder="Tuliskan pengalaman atau kesan..."
                        :rows="5"
                        size="lg"
                        class="w-full"
                    />
                </UFormField>

                <div class="grid grid-cols-3 gap-6">
                    <UFormField label="Rating (1-5)" name="rating">
                        <UInput 
                            v-model="form.rating" 
                            type="number"
                            min="1"
                            max="5"
                            placeholder="5"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Urutan" name="order">
                        <UInput 
                            v-model="form.order" 
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Status" name="is_active">
                        <div class="flex items-center gap-3 pt-2">
                            <UCheckbox v-model="form.is_active" />
                            <span class="text-sm">{{ form.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </UFormField>
                </div>

                <div class="flex gap-2 pt-4 border-t">
                    <UButton 
                        type="submit" 
                        label="Simpan" 
                        icon="i-lucide-save"
                        :loading="form.processing"
                    />
                    <UButton 
                        label="Batal" 
                        color="neutral" 
                        variant="outline"
                        to="/dashboard/cms/testimonials"
                    />
                </div>
            </UForm>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const form = useForm({
    name: '',
    role: '',
    photo: null,
    quote: '',
    rating: 5,
    order: 0,
    is_active: true,
});

const photoPreview = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const clearPhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    // Note: To clear the file input visually, we might need a ref to the input element, 
    // but clearing the preview is sufficient for now as form.photo is null.
};

const submit = () => {
    form.post('/dashboard/cms/testimonials', {
        forceFormData: true,
    });
};
</script>
