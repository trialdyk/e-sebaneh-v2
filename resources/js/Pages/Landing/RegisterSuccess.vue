<template>
    <LandingLayout>
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <UCard>
                    <div class="text-center py-8">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900 mb-4">
                            <UIcon name="i-lucide-check" class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Pendaftaran Berhasil!</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-8">
                            Terima kasih telah mendaftar. Data Anda telah kami terima.<br>
                            Silahkan download dan lengkapi dokumen berikut sebagai syarat pendaftaran ulang.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-lg mx-auto">
                            <UButton
                                block
                                size="lg"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-file-text"
                                label="Download Surat Pernyataan"
                                @click="downloadStatement"
                            />
                            
                            <UButton
                                block
                                size="lg"
                                color="primary"
                                variant="soft"
                                icon="i-lucide-file-spreadsheet"
                                label="Download Formulir"
                                @click="downloadForm"
                            />
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                            <UButton
                                to="/"
                                color="neutral"
                                variant="ghost"
                                label="Kembali ke Beranda"
                            />
                        </div>
                    </div>
                </UCard>
            </div>
        </div>
    </LandingLayout>
</template>

<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';

const downloadStatement = () => {
    // In actual implementation, this will hit an endpoint that returns the file
    // For now we just show an alert or redirect
    window.location.href = '/register-santri/download/statement';
};

const downloadForm = () => {
    window.location.href = '/register-santri/download/form';
};
</script>
