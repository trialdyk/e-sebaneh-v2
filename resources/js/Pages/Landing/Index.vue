<template>
    <LandingLayout 
        :site-name="site.name"
        :subtitle="site.subtitle"
        :logo="site.logo"
        :description="site.description"
        :social="social"
    >
        <!-- Hero Section with Carousel -->
        <section class="relative pt-32 pb-24 px-4 overflow-hidden">
            <!-- Background Carousel -->
            <div class="absolute inset-0">
                <div 
                    v-for="(slide, index) in heroSlides" 
                    :key="index"
                    class="absolute inset-0 transition-opacity duration-1000"
                    :class="currentSlide === index ? 'opacity-100' : 'opacity-0'"
                >
                    <img :src="slide.image" alt="Hero" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-transparent"></div>
                </div>
            </div>

            <div class="container mx-auto relative z-10">
                <div class="max-w-5xl mx-auto text-center text-white">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-600/20 backdrop-blur-sm border border-emerald-400/30 mb-8">
                        <UIcon name="i-lucide-sparkles" class="w-4 h-4 text-emerald-300" />
                        <span class="text-sm font-semibold text-emerald-100">Pendaftaran Tahun Ajaran 2025/2026 Dibuka</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight drop-shadow-lg">
                        {{ site.name }}
                    </h1>

                    <!-- Tagline -->
                    <p class="text-xl md:text-2xl mb-4 font-medium drop-shadow">
                        {{ site.tagline }}
                    </p>
                    <p class="text-base md:text-lg max-w-2xl mx-auto mb-10 text-gray-200">
                        {{ site.description }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap gap-4 justify-center mb-16">
                        <UButton to="/register-santri" size="xl" variant="subttle" color="success" icon="i-lucide-user-plus" class="shadow-lg">
                            Daftar Sekarang
                        </UButton>
                        <UButton size="xl" color="secondary" variant="outline" icon="i-lucide-download" class="border-2">
                            Download Brosur
                        </UButton>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        <div v-for="stat in site.stats" :key="stat.label" 
                             class="group p-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 hover:-translate-y-1 transition-all duration-300">
                            <div class="text-4xl font-bold text-emerald-300 mb-2">
                                {{ stat.value }}
                            </div>
                            <div class="text-xs md:text-sm text-gray-200 font-medium">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Visi Misi -->
        <section id="about" class="py-20 px-4">
            <div class="container mx-auto max-w-6xl">
                <h2 class="text-4xl font-bold text-center mb-12">Tentang Kami</h2>
                <div class="grid md:grid-cols-2 gap-12">
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-700 border border-emerald-200 dark:border-gray-600">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center">
                                <UIcon name="i-lucide-eye" class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-2xl font-bold">Visi</h3>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ site.visi }}</p>
                    </div>
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-gray-700 dark:to-gray-800 border border-teal-200 dark:border-gray-600">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-teal-600 flex items-center justify-center">
                                <UIcon name="i-lucide-target" class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-2xl font-bold">Misi</h3>
                        </div>
                        <ul class="space-y-3">
                            <li v-for="(item, index) in site.misi" :key="index" class="flex items-start gap-3">
                                <UIcon name="i-lucide-check-circle" class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" />
                                <span class="text-gray-700 dark:text-gray-300">{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs -->
        <section id="programs" class="py-20 px-4 bg-gray-50 dark:bg-gray-800">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">Program Unggulan</h2>
                    <p class="text-gray-600 dark:text-gray-400">Berbagai program untuk membentuk santri yang berkualitas</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="program in programs" :key="program.title" 
                         class="group p-6 rounded-2xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:shadow-2xl hover:border-emerald-500 transition-all duration-300 hover:-translate-y-2">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <UIcon :name="program.icon" class="w-7 h-7 text-white" />
                        </div>
                        <h3 class="font-bold text-lg mb-2">{{ program.title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ program.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Galeri -->
        <section class="py-20 px-4">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">Galeri</h2>
                    <p class="text-gray-600 dark:text-gray-400">Dokumentasi kegiatan dan fasilitas pondok</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="item in galleries" :key="item.title"
                         class="group relative rounded-2xl overflow-hidden cursor-pointer">
                        <img :src="item.image" :alt="item.title" class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-end p-6">
                            <div>
                                <h3 class="text-white font-bold text-xl mb-1">{{ item.title }}</h3>
                                <UBadge color="emerald" variant="solid" size="sm">{{ item.category }}</UBadge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- News -->
        <section id="news" class="py-20 px-4 bg-gray-50 dark:bg-gray-800">
            <div class="container mx-auto max-w-6xl">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-4xl font-bold mb-2">Berita Terbaru</h2>
                        <p class="text-gray-600 dark:text-gray-400">Informasi dan kegiatan terkini</p>
                    </div>
                    <UButton variant="outline" to="/berita" icon-trailing="i-lucide-arrow-right">
                        Lihat Semua
                    </UButton>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <a v-for="item in news" :key="item.id" :href="`/berita/${item.slug}`"
                       class="group block rounded-2xl overflow-hidden bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:shadow-2xl hover:border-emerald-500 transition-all duration-300 hover:-translate-y-2">
                        <div class="h-48 overflow-hidden">
                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div class="p-6">
                            <UBadge color="emerald" variant="subtle" size="sm" class="mb-3">{{ item.category }}</UBadge>
                            <h3 class="font-bold text-lg mb-2 group-hover:text-emerald-600 transition-colors">{{ item.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ item.excerpt }}</p>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <UIcon name="i-lucide-calendar" class="w-4 h-4" />
                                <span>{{ formatDate(item.date) }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-20 px-4">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">Testimoni</h2>
                    <p class="text-gray-600 dark:text-gray-400">Apa kata alumni dan wali santri</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="testimonial in testimonials" :key="testimonial.name"
                         class="p-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-gray-800 dark:to-gray-700 border border-emerald-200 dark:border-gray-600">
                        <div class="flex items-center gap-4 mb-4">
                            <img :src="testimonial.photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(testimonial.name)}&background=random`" :alt="testimonial.name" class="w-14 h-14 rounded-full object-cover">
                            <div>
                                <div class="font-bold">{{ testimonial.name }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ testimonial.role }}</div>
                            </div>
                        </div>
                        <p class="text-sm italic text-gray-700 dark:text-gray-300">"{{ testimonial.quote }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="py-20 px-4 bg-gray-50 dark:bg-gray-800">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">Hubungi Kami</h2>
                    <p class="text-gray-600 dark:text-gray-400">Kami siap menjawab pertanyaan Anda</p>
                </div>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center flex-shrink-0">
                                <UIcon name="i-lucide-map-pin" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold mb-1">Alamat</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ contact.address }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center flex-shrink-0">
                                <UIcon name="i-lucide-phone" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold mb-1">Telepon & WhatsApp</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ contact.phone }}</p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ contact.whatsapp }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center flex-shrink-0">
                                <UIcon name="i-lucide-mail" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold mb-1">Email</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ contact.email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-96 rounded-2xl bg-gray-200 dark:bg-gray-700 flex items-center justify-center border border-gray-300 dark:border-gray-600">
                        <div class="text-center">
                            <UIcon name="i-lucide-map" class="w-16 h-16 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-500">Google Maps Embed</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
    site: Object,
    heroSlides: Array,
    programs: Array,
    galleries: Array,
    news: Array,
    testimonials: Array,
    contact: Object,
    social: Object,
});

// Hero carousel
const currentSlide = ref(0);
let slideInterval;

onMounted(() => {
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % props.heroSlides.length;
    }, 5000); // Change slide every 5 seconds
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>
