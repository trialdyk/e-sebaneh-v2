<template>
    <UApp>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-violet-800 p-8">
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- Hero Card -->
                <UCard>
                    <template #header>
                        <h1 class="text-3xl font-bold">Laravel + Inertia.js + Nuxt UI ✨</h1>
                    </template>

                    <div class="space-y-4">
                        <p class="text-lg">
                            Successfully integrated Nuxt UI components with Vue 3 and Inertia.js!
                        </p>

                        <!-- Alert Demo -->
                        <UAlert
                            color="primary"
                            variant="soft"
                            title="Success!"
                            description="Nuxt UI components are working perfectly in your Laravel project."
                        />

                        <!-- Buttons Demo -->
                        <div class="flex flex-wrap gap-3">
                            <UButton color="primary">Primary</UButton>
                            <UButton color="secondary" variant="soft">Secondary</UButton>
                            <UButton color="success" variant="outline">Success</UButton>
                            <UButton color="danger">Danger</UButton>
                        </div>

                        <!-- Badges Demo -->
                        <div class="flex flex-wrap gap-2">
                            <UBadge color="error">Laravel 12</UBadge>
                            <UBadge color="success">Vue 3</UBadge>
                            <UBadge color="secondary">Inertia.js</UBadge>
                            <UBadge color="warning">Vite</UBadge>
                            <UBadge color="info">TailwindCSS</UBadge>
                            <UBadge color="primary">Nuxt UI</UBadge>
                        </div>

                        <!-- Input Demo -->
                        <div class="space-y-3 max-w-md">
                            <UInput placeholder="Try typing here..." />
                            <UTextarea placeholder="Enter your message..." />
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Powered by Nuxt UI</span>
                            <UButton
                                variant="ghost"
                                to="https://ui.nuxt.com"
                                target="_blank"
                            >
                                View Docs →
                            </UButton>
                        </div>
                    </template>
                </UCard>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <UCard>
                        <div class="text-center">
                            <div class="text-4xl mb-2">⚡</div>
                            <h3 class="font-semibold mb-1">Lightning Fast</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Vite-powered development
                            </p>
                        </div>
                    </UCard>

                    <UCard>
                        <div class="text-center">
                            <div class="text-4xl mb-2">🎨</div>
                            <h3 class="font-semibold mb-1">Beautiful UI</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                100+ Nuxt UI components
                            </p>
                        </div>
                    </UCard>

                    <UCard>
                        <div class="text-center">
                            <div class="text-4xl mb-2">🚀</div>
                            <h3 class="font-semibold mb-1">SPA Experience</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Seamless navigation
                            </p>
                        </div>
                    </UCard>
                </div>
            </div>
        </div>
    </UApp>
</template>

<script setup>
// Nuxt UI components are auto-imported by the Vite plugin
</script>
