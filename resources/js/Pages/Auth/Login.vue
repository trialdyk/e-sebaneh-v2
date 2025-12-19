<template>
    <UApp :toaster="{position: 'top-right'}">
        <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 p-4">
            <div class="w-full max-w-md">
                <UCard>
                    <template #header>
                        <div class="text-center">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Masuk ke E-Sebaneh
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Masukkan email dan password Anda
                            </p>
                        </div>
                    </template>

                    <form @submit.prevent="submitLogin" class="space-y-4">
                        <!-- Email -->
                        <UFormField label="Email" required :error="form.errors.email">
                            <UInput
                                v-model="form.email"
                                type="email"
                                placeholder="email@example.com"
                                icon="i-lucide-mail"
                                size="lg"
                                class="w-full"
                                :disabled="form.processing"
                                @input="form.clearErrors('email')"
                            />
                        </UFormField>

                        <!-- Password -->
                        <UFormField label="Password" required :error="form.errors.password">
                            <UInput
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                icon="i-lucide-lock"
                                size="lg"
                                class="w-full"
                                :disabled="form.processing"
                                @input="form.clearErrors('password')"
                            >
                                <template #trailing>
                                    <UButton
                                        color="neutral"
                                        variant="ghost"
                                        size="xs"
                                        :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                        @click="showPassword = !showPassword"
                                    />
                                </template>
                            </UInput>
                        </UFormField>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <UCheckbox v-model="form.remember" label="Ingat saya" />
                        </div>

                        <!-- Submit Button -->
                        <UButton
                            type="submit"
                            color="primary"
                            size="lg"
                            block
                            :loading="form.processing"
                        >
                            Masuk
                        </UButton>
                    </form>

                    <!-- Divider -->
                    <USeparator label="Atau" class="my-6" />

                    <!-- Google Login -->
                    <UButton
                        color="neutral"
                        variant="outline"
                        size="lg"
                        block
                        :href="route('google.redirect')"
                        as="a"
                    >
                        <template #leading>
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </template>
                        Masuk dengan Google
                    </UButton>

                    <template #footer>
                        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum punya akun? Hubungi administrator.
                        </p>
                    </template>
                </UCard>
            </div>
        </div>
    </UApp>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const toast = useToast();
const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitLogin = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            // Show toast for login error
            const errorMessage = errors.email || errors.password || 'Login gagal. Silakan coba lagi.';
            toast.add({
                title: 'Login Gagal',
                description: errorMessage,
                icon: 'i-lucide-x-circle',
                color: 'error',
            });
        },
        onSuccess: () => {
            toast.add({
                title: 'Login Berhasil',
                description: 'Selamat datang kembali!',
                icon: 'i-lucide-check-circle',
                color: 'success',
            });
        },
    });
};

// Helper for named routes
const route = (name) => {
    const routes = {
        'google.redirect': '/auth/google',
    };
    return routes[name] || '#';
};
</script>
