<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <img 
                            v-if="logo" 
                            :src="logo" 
                            alt="Logo" 
                            class="w-12 h-12 rounded-xl object-cover shadow-lg"
                        >
                        <div v-else class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                            <UIcon name="i-lucide-mosque" class="w-7 h-7 text-white" />
                        </div>
                        <div>
                            <h1 class="font-bold text-lg">{{ siteName }}</h1>
                            <p class="text-xs text-gray-500">{{ subtitle }}</p>
                        </div>
                    </a>
                    <div class="hidden md:flex items-center gap-6">
                        <a href="/#about" class="text-sm font-medium hover:text-emerald-600 transition">Tentang</a>
                        <a href="/#programs" class="text-sm font-medium hover:text-emerald-600 transition">Program</a>
                        <a href="/berita" class="text-sm font-medium hover:text-emerald-600 transition">Berita</a>
                        <a href="/#contact" class="text-sm font-medium hover:text-emerald-600 transition">Kontak</a>
                        <UButton 
                            :icon="isDark ? 'i-lucide-sun' : 'i-lucide-moon'" 
                            color="gray" 
                            variant="ghost" 
                            size="md"
                            @click="toggleTheme"
                        />
                        <UButton color="emerald" size="md" to="/login">Masuk</UButton>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div>
            <slot />
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12 px-4">
            <div class="container mx-auto max-w-6xl">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <img 
                                v-if="logo" 
                                :src="logo" 
                                alt="Logo" 
                                class="w-12 h-12 rounded-xl object-cover"
                            >
                            <div v-else class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-xl flex items-center justify-center">
                                <UIcon name="i-lucide-mosque" class="w-7 h-7 text-white" />
                            </div>
                            <h3 class="font-bold text-lg">{{ siteName }}</h3>
                        </div>
                        <p class="text-gray-400 text-sm">{{ description }}</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                        <div class="space-y-2 text-sm">
                            <a href="/#about" class="block text-gray-400 hover:text-white transition">Tentang</a>
                            <a href="/#programs" class="block text-gray-400 hover:text-white transition">Program</a>
                            <a href="/berita" class="block text-gray-400 hover:text-white transition">Berita</a>
                            <a href="/#contact" class="block text-gray-400 hover:text-white transition">Kontak</a>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-4">Social Media</h3>
                        <div class="flex gap-3">
                            <a :href="social.facebook" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-emerald-600 flex items-center justify-center transition">
                                <UIcon name="i-lucide-facebook" class="w-5 h-5" />
                            </a>
                            <a :href="social.instagram" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-emerald-600 flex items-center justify-center transition">
                                <UIcon name="i-lucide-instagram" class="w-5 h-5" />
                            </a>
                            <a :href="social.youtube" class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-emerald-600 flex items-center justify-center transition">
                                <UIcon name="i-lucide-youtube" class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                    <p>&copy; 2025 {{ siteName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useColorMode } from '@vueuse/core';

defineProps({
    siteName: {
        type: String,
        default: 'Pondok Pesantren Al-Hikmah'
    },
    subtitle: {
        type: String,
        default: 'Sejak 1999'
    },
    logo: {
        type: String,
        default: null
    },
    description: {
        type: String,
        default: 'Lembaga pendidikan Islam terpercaya'
    },
    social: {
        type: Object,
        default: () => ({
            facebook: '#',
            instagram: '#',
            youtube: '#'
        })
    }
});

const colorMode = useColorMode();
const isDark = computed(() => colorMode.value === 'dark');

const toggleTheme = () => {
    colorMode.value = colorMode.value === 'dark' ? 'light' : 'dark';
};
</script>
