<template>
    <LandingLayout>
        <article class="pt-32 pb-20 px-4">
            <div class="container mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-8">
                    <UBadge color="emerald" variant="subtle" class="mb-4">{{ news.category }}</UBadge>
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ news.title }}</h1>
                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <UIcon name="i-lucide-user" class="w-4 h-4" />
                            <span>{{ news.author }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <UIcon name="i-lucide-calendar" class="w-4 h-4" />
                            <span>{{ formatDate(news.date) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <UIcon name="i-lucide-eye" class="w-4 h-4" />
                            <span>{{ news.views }} Views</span>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="rounded-2xl overflow-hidden mb-8" v-if="news.image">
                    <img :src="news.image" :alt="news.title" class="w-full h-96 object-cover">
                </div>

                <!-- Article Body -->
                <div class="prose prose-lg dark:prose-invert max-w-none mb-12">
                    <p class="text-xl text-gray-700 dark:text-gray-300 leading-relaxed font-medium">{{ news.excerpt }}</p>
                    <div v-html="news.content" class="text-gray-700 dark:text-gray-300 leading-relaxed"></div>
                </div>

                <!-- Share Buttons -->
                <div class="border-t border-b border-gray-200 dark:border-gray-700 py-6 mb-12">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <span class="font-semibold">Bagikan:</span>
                        <div class="flex gap-2">
                            <UButton size="sm" color="white" variant="solid" icon="i-lucide-facebook" @click="share('facebook')" class="ring-1 ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800" />
                            <UButton size="sm" color="white" variant="solid" icon="i-lucide-twitter" @click="share('twitter')" class="ring-1 ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800" />
                            <UButton size="sm" color="white" variant="solid" icon="i-lucide-message-circle" @click="share('whatsapp')" class="ring-1 ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800" />
                            <UButton size="sm" color="white" variant="solid" icon="i-lucide-link" @click="copyLink" class="ring-1 ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800" />
                        </div>
                    </div>
                </div>

                <!-- Related News -->
                <div v-if="relatedNews.length > 0">
                    <h2 class="text-2xl font-bold mb-6">Berita Terkait</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <a v-for="item in relatedNews" :key="item.id" :href="`/berita/${item.slug}`"
                           class="group block rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                            <div class="h-40 overflow-hidden">
                                <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <UBadge color="emerald" variant="subtle" size="xs" class="mb-2">{{ item.category }}</UBadge>
                                <h3 class="font-bold text-sm mb-1 line-clamp-2 group-hover:text-emerald-600 transition-colors">{{ item.title }}</h3>
                                <p class="text-xs text-gray-500">{{ formatDate(item.date) }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </LandingLayout>
</template>

<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
    news: Object,
    relatedNews: Array,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const share = (platform) => {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(props.news.title);
    let shareUrl = '';

    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${text}%20${url}`;
            break;
    }

    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
};

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    // You might want to show a toast here using useToast() from Nuxt UI, but keeping it simple for now
    alert('Link berhasil disalin!');
};
</script>
