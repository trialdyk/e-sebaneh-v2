<template>
    <DashboardPage
        title="Edit Berita"
        heading="Edit Berita"
        description="Ubah informasi berita"
        page-id="cms-posts-edit"
    >
        <template #header>
            <UButton
                color="neutral"
                icon="i-lucide-arrow-left"
                label="Kembali"
                variant="ghost"
                to="/dashboard/cms/posts"
            />
        </template>

        <UCard>
            <UForm :state="form" @submit="submit" class="space-y-6 p-4 max-w-6xl mx-auto">
                <div class="grid grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="col-span-2 space-y-6">
                        <UFormField label="Judul Berita" name="title" required>
                            <UInput 
                                v-model="form.title" 
                                placeholder="Masukkan judul berita..."
                                size="lg"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Ringkasan" name="excerpt" required hint="Ringkasan singkat untuk preview (max 500 karakter)">
                            <UTextarea 
                                v-model="form.excerpt" 
                                placeholder="Masukkan ringkasan berita..."
                                :rows="3"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Konten" name="content" required>
                            <TiptapEditor 
                                v-model="form.content" 
                                placeholder="Masukkan konten berita lengkap..."
                            />
                        </UFormField>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <UFormField label="Gambar Utama" name="image">
                            <div class="space-y-3">
                                <!-- Existing Image -->
                                <div v-if="!imagePreview && post.image" class="relative">
                                    <img 
                                        :src="post.image.startsWith('http') ? post.image : `/storage/${post.image}`"
                                        alt="Current"
                                        class="w-full rounded-lg"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Gambar saat ini</p>
                                </div>

                                <!-- New Image Upload -->
                                <input 
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600"
                                />
                                
                                <!-- New Image Preview -->
                                <div v-if="imagePreview" class="relative">
                                    <img :src="imagePreview" alt="Preview" class="w-full rounded-lg" />
                                    <UButton 
                                        icon="i-lucide-x"
                                        color="error"
                                        size="xs"
                                        class="absolute top-2 right-2"
                                        @click="clearImage"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Gambar baru (belum tersimpan)</p>
                                </div>
                                
                                <p class="text-xs text-gray-500">Format: JPG, PNG, WebP. Max: 2MB. Kosongkan jika tidak ingin mengganti.</p>
                            </div>
                        </UFormField>

                        <UFormField label="Kategori" name="category" required>
                            <USelect
                                v-model="form.category"
                                :options="categories"
                                placeholder="Pilih kategori"
                            />
                        </UFormField>

                        <UFormField label="Tanggal Publikasi" name="published_at">
                            <UInput 
                                v-model="form.published_at" 
                                type="datetime-local"
                            />
                        </UFormField>

                        <UFormField label="Status Publikasi" name="is_published">
                            <div class="flex items-center gap-3">
                                <USwitch v-model="form.is_published" />
                                <span class="text-sm">{{ form.is_published ? 'Publish' : 'Draft' }}</span>
                            </div>
                        </UFormField>

                        <UFormField label="Total Views" name="views">
                            <UInput 
                                :model-value="post.views" 
                                disabled
                            />
                        </UFormField>
                    </div>
                </div>

                <div class="flex gap-2 pt-4 border-t">
                    <UButton 
                        type="submit"
                        label="Update Berita"
                        icon="i-lucide-save"
                        :loading="form.processing"
                    />
                    <UButton 
                        label="Batal"
                        color="neutral"
                        variant="outline"
                        to="/dashboard/cms/posts"
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
import TiptapEditor from '@/Components/TiptapEditor.vue';

const props = defineProps({
    post: Object,
});

const form = useForm({
    title: props.post.title,
    excerpt: props.post.excerpt,
    content: props.post.content,
    image: null,
    category: props.post.category,
    published_at: props.post.published_at ? new Date(props.post.published_at).toISOString().slice(0, 16) : '',
    is_published: props.post.is_published,
});

const categories = ['Kegiatan', 'Prestasi', 'Acara', 'Pengumuman', 'Artikel'];
const imagePreview = ref(null);

const handleImageUpload = (event) => {
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
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/dashboard/cms/posts/${props.post.id}`, {
        forceFormData: true,
    });
};
</script>
