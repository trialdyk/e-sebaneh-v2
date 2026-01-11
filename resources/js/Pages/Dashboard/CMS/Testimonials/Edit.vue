<template>
    <DashboardPage
        title="Edit Testimoni"
        heading="Edit Testimoni"
        description="Ubah data testimoni"
        page-id="cms-testimonials-edit"
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
                        <!-- Current Photo -->
                        <div v-if="!photoPreview && testimonial.photo" class="mb-2">
                             <img 
                                :src="testimonial.photo.startsWith('http') ? testimonial.photo : `/storage/${testimonial.photo}`" 
                                alt="Current Photo" 
                                class="w-32 h-32 rounded-full object-cover border border-gray-200"
                            />
                            <p class="text-xs text-gray-500 mt-1">Foto saat ini</p>
                        </div>

                        <!-- Upload Input -->
                        <input 
                            type="file" 
                            @change="handleFileChange"
                            accept="image/*"
                            class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600"
                        />
                        
                        <!-- New Photo Preview -->
                        <div v-if="photoPreview" class="relative inline-block mt-2">
                            <img :src="photoPreview" alt="Preview Baru" class="w-32 h-32 rounded-full object-cover border border-gray-200 shadow-sm" />
                             <UButton 
                                icon="i-lucide-x"
                                color="error"
                                size="xs"
                                class="absolute top-0 right-0 rounded-full"
                                @click="clearPhoto"
                            />
                            <p class="text-xs text-gray-500 mt-1">Preview foto baru</p>
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
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Urutan" name="order">
                        <UInput 
                            v-model="form.order" 
                            type="number"
                            min="0"
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
                        label="Simpan Perubahan" 
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

const props = defineProps({
    testimonial: Object,
});

const form = useForm({
    name: props.testimonial.name,
    role: props.testimonial.role,
    photo: null,
    quote: props.testimonial.quote,
    rating: props.testimonial.rating,
    order: props.testimonial.order,
    is_active: !!props.testimonial.is_active,
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
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/dashboard/cms/testimonials/${props.testimonial.id}`, {
        forceFormData: true,
    });
};
</script>
