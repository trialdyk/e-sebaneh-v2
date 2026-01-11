<template>
    <DashboardPage
        title="Tambah Galeri"
        heading="Buat Galeri Baru"
        description="Tambahkan foto atau dokumen baru ke galeri"
        page-id="cms-galleries-create"
    >
        <template #header>
            <UButton
                color="neutral"
                icon="i-lucide-arrow-left"
                label="Kembali"
                variant="ghost"
                to="/dashboard/cms/galleries"
            />
        </template>

        <UCard>
            <UForm :state="form" @submit="submit" class="space-y-6 p-4 max-w-3xl mx-auto w-full">
                <!-- Image Upload -->
                <UFormField label="Upload Gambar" name="image" hint="Format: JPG, PNG, WEBP (Max 2MB)" required>
                     <div class="space-y-3">
                        <input 
                            type="file" 
                            @change="handleFileChange"
                            accept="image/*"
                            class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600"
                        />
                        <div v-if="imagePreview" class="relative inline-block">
                            <img :src="imagePreview" alt="Preview" class="w-full max-w-xs rounded-lg object-cover border border-gray-200 shadow-sm" />
                             <UButton 
                                v-if="imagePreview"
                                icon="i-lucide-x"
                                color="error"
                                size="xs"
                                class="absolute top-2 right-2 rounded-full"
                                @click="clearImage"
                            />
                        </div>
                    </div>
                </UFormField>

                <div class="grid grid-cols-2 gap-6">
                    <UFormField label="Judul Galeri" name="title" required>
                        <UInput 
                            v-model="form.title" 
                            placeholder="Contoh: Kegiatan Maulid Nabi"
                            size="lg"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Kategori" name="category" required>
                         <USelect 
                            v-model="form.category" 
                            :items="categories"
                            placeholder="Pilih Kategori"
                            size="lg"
                            class="w-full"
                        />
                    </UFormField>
                </div>

                <UFormField label="Deskripsi" name="description">
                    <UTextarea 
                        v-model="form.description" 
                        placeholder="Tuliskan deskripsi singkat..."
                        :rows="4"
                        size="lg"
                        class="w-full"
                    />
                </UFormField>

                <div class="grid grid-cols-2 gap-6">
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
                        to="/dashboard/cms/galleries"
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

const categories = [
    'Kegiatan',
    'Fasilitas',
    'Prestasi',
    'Lainnya',
];

const form = useForm({
    title: '',
    category: '',
    description: '',
    image: null,
    order: 0,
    is_active: true,
});

const imagePreview = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const clearImage = () => {
    form.image = null;
    imagePreview.value = null;
};

const submit = () => {
    form.post('/dashboard/cms/galleries', {
        forceFormData: true,
    });
};
</script>
