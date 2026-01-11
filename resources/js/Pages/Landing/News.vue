<template>
    <LandingLayout>
        <section class="pt-32 pb-20 px-4">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h1 class="text-5xl font-bold mb-4">Berita & Kegiatan</h1>
                    <p class="text-gray-600 dark:text-gray-400">Informasi terkini dari Pondok Pesantren Al-Hikmah</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    <a v-for="item in news.data" :key="item.id" :href="`/berita/${item.slug}`"
                       class="group block rounded-2xl overflow-hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-2xl hover:border-emerald-500 transition-all duration-300 hover:-translate-y-2">
                        <div class="h-56 overflow-hidden">
                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-3">
                                <UBadge color="emerald" variant="subtle" size="sm">{{ item.category }}</UBadge>
                                <span class="text-xs text-gray-500">{{ formatDate(item.date) }}</span>
                            </div>
                            <h3 class="font-bold text-xl mb-2 group-hover:text-emerald-600 transition-colors">{{ item.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ item.excerpt }}</p>
                        </div>
                    </a>
                </div>

                <!-- Pagination -->
                <div class="flex justifies-center items-center gap-4">
                     <UButton
                        v-if="news.prev_page_url"
                        :to="news.prev_page_url"
                        icon="i-lucide-arrow-left"
                        color="gray"
                        variant="ghost"
                    >
                        Sebelumnya
                    </UButton>
                     <UButton
                        v-if="news.next_page_url"
                        :to="news.next_page_url"
                        icon-trailing="i-lucide-arrow-right"
                        color="gray"
                        variant="ghost"
                    >
                        Selanjutnya
                    </UButton>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
    news: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>
