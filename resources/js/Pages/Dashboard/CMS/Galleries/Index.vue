<template>
    <DashboardPage
        title="Galeri"
        heading="Kelola Galeri Foto"
        description="Kelola foto dan gambar pondok pesantren"
        page-id="cms-galleries"
    >
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div class="w-48">
                    <USelect
                        v-model="selectedCategory"
                        :items="categories"
                        placeholder="Semua Kategori"
                        size="sm"
                    />
                </div>
                <UButton
                    color="primary"
                    icon="i-lucide-plus"
                    label="Tambah Galeri"
                    to="/dashboard/cms/galleries/create"
                />
            </div>
        </template>

        <UCard>
            <div class="grid grid-cols-4 gap-4 p-4">
                <div v-for="gallery in galleries.data" :key="gallery.id" class="relative group">
                    <img 
                        :src="gallery.image.startsWith('http') ? gallery.image : `/storage/${gallery.image}`"
                        :alt="gallery.title"
                        class="w-full aspect-square object-cover rounded-lg"
                    />
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex flex-col items-center justify-center gap-2 p-4">
                        <p class="text-white font-medium text-center text-sm">{{ gallery.title }}</p>
                        <UBadge v-if="gallery.category" color="primary" variant="solid">{{ gallery.category }}</UBadge>
                        <div class="flex gap-2 mt-2">
                            <UButton 
                                color="neutral"
                                size="xs"
                                icon="i-lucide-pencil" 
                                :to="`/dashboard/cms/galleries/${gallery.id}/edit`"
                            />
                            <UButton 
                                color="error"
                                size="xs"
                                icon="i-lucide-trash-2" 
                                @click="confirmDelete(gallery)"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ galleries.from }} to {{ galleries.to }} of {{ galleries.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="galleries.total"
                    :page-count="galleries.per_page"
                />
            </div>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    galleries: Object,
    filters: Object,
});

const currentPage = ref(props.galleries.current_page);
const selectedCategory = ref(props.filters.category || '');

const categories = ref([
    { label: 'Semua Kategori', value: 'all' },
    { label: 'Kegiatan', value: 'Kegiatan' },
    { label: 'Fasilitas', value: 'Fasilitas' },
    { label: 'Prestasi', value: 'Prestasi' },
    { label: 'Lainnya', value: 'Lainnya' },
]);

watch(selectedCategory, (category) => {
    router.get('/dashboard/cms/galleries', { 
        page: 1, // Reset to page 1 when filter changes
        category: category === 'all' ? undefined : (category || undefined) 
    }, { preserveState: true, replace: true });
});

watch(currentPage, (page) => {
    router.get('/dashboard/cms/galleries', { 
        page, 
        category: selectedCategory.value || undefined 
    }, { preserveState: true, replace: true });
});

const confirmDelete = (gallery) => {
    if (confirm(`Yakin ingin menghapus galeri "${gallery.title}"?`)) {
        router.delete(`/dashboard/cms/galleries/${gallery.id}`);
    }
};
</script>
