<template>
    <DashboardPage
        title="Berita"
        heading="Kelola Berita & Artikel"
        description="Kelola berita dan artikel pondok pesantren"
        page-id="cms-posts"
    >
        <template #header>
            <div class="flex gap-2">
                <UInput
                    v-model="search"
                    icon="i-lucide-search"
                    placeholder="Cari berita..."
                    class="w-64"
                />
                <UButton
                    color="primary"
                    icon="i-lucide-plus"
                    label="Tambah Berita"
                    to="/dashboard/cms/posts/create"
                />
            </div>
        </template>

        <UCard>
            <UTable 
                :data="posts.data" 
                :columns="columns"
                :loading="false"
            >
                <template #title-cell="{ row }">
                    <div class="max-w-md">
                        <p class="font-medium">{{ row.original.title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.original.excerpt }}</p>
                    </div>
                </template>

                <template #image-cell="{ row }">
                    <img 
                        v-if="row.original.image"
                        :src="row.original.image.startsWith('http') ? row.original.image : `/storage/${row.original.image}`"
                        :alt="row.original.title"
                        class="w-16 h-16 object-cover rounded"
                    />
                    <div v-else class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                        <UIcon name="i-lucide-image-off" class="text-gray-400" />
                    </div>
                </template>

                <template #category-cell="{ row }">
                    <UBadge color="primary" variant="subtle">{{ row.original.category }}</UBadge>
                </template>

                <template #is_published-cell="{ row }">
                    <UBadge :color="row.original.is_published ? 'success' : 'neutral'" variant="subtle">
                        {{ row.original.is_published ? 'Published' : 'Draft' }}
                    </UBadge>
                </template>

                <template #views-cell="{ row }">
                    <span class="text-sm text-gray-600">{{ row.original.views }}</span>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex gap-1">
                        <UButton 
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil" 
                            :to="`/dashboard/cms/posts/${row.original.id}/edit`"
                        />
                        <UButton 
                            color="error"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-trash-2" 
                            @click="confirmDelete(row.original)"
                        />
                    </div>
                </template>
            </UTable>

            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="posts.total"
                    :page-count="posts.per_page"
                />
            </div>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const props = defineProps({
    posts: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const currentPage = ref(props.posts.current_page);

const columns = [
    { accessorKey: 'image', header: 'Gambar' },
    { accessorKey: 'title', header: 'Judul & Ringkasan' },
    { accessorKey: 'category', header: 'Kategori' },
    { accessorKey: 'is_published', header: 'Status' },
    { accessorKey: 'views', header: 'Views' },
    { accessorKey: 'actions', header: 'Aksi' },
];

const handleSearch = debounce(() => {
    router.get('/dashboard/cms/posts', { search: search.value }, { preserveState: true, replace: true });
}, 500);

watch(search, handleSearch);

watch(currentPage, (page) => {
    router.get('/dashboard/cms/posts', { page, search: search.value }, { preserveState: true, replace: true });
});

const confirmDelete = (post) => {
    if (confirm(`Yakin ingin menghapus berita "${post.title}"?`)) {
        router.delete(`/dashboard/cms/posts/${post.id}`);
    }
};
</script>
