<template>
    <DashboardPage
        title="Batas Penarikan Santri"
        heading="Pengaturan Batas Penarikan"
        description="Atur batas maksimal penarikan saldo santri per hari"
        page-id="student-withdraw-limit"
    >
        <UCard>
            <!-- Info Alert -->
            <UAlert
                color="info"
                variant="subtle"
                icon="i-lucide-info"
                class="mb-6"
            >
                <template #title>Atur Batas Penarikan Uang</template>
                <template #description>
                    <div class="space-y-2">
                        <p>Batasi penarikan saldo santri harian untuk mengontrol pengeluaran.</p>
                        <p v-if="withdrawLimit?.limit" class="font-semibold text-primary">
                            Batas Saat Ini: Rp {{ formatCurrency(withdrawLimit.limit) }} / Hari
                        </p>
                    </div>
                </template>
            </UAlert>

            <!-- Current Limit Display -->
            <div v-if="withdrawLimit?.limit" class="mb-6">
                <UCard class="bg-primary/5 dark:bg-primary/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Batas Penarikan Harian</p>
                            <p class="text-3xl font-bold text-primary mt-1">
                                Rp {{ formatCurrency(withdrawLimit.limit) }}
                            </p>
                        </div>
                        <UIcon name="i-lucide-wallet" class="w-12 h-12 text-primary opacity-20" />
                    </div>
                </UCard>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <UFormField 
                    label="Batas Penarikan Harian (Rp)" 
                    name="limit" 
                    :error="form.errors.limit"
                    required
                >
                    <UInput 
                        v-model="form.limit" 
                        type="number" 
                        min="0"
                        step="1000"
                        placeholder="Masukkan batas penarikan..."
                        class="w-full"
                    />
                    <template #hint>
                        <p class="text-sm text-gray-500">
                            Santri hanya dapat menarik saldo maksimal sebesar jumlah ini per hari
                        </p>
                    </template>
                </UFormField>

                <div class="flex justify-end gap-2">
                    <UButton 
                        color="primary" 
                        type="submit"
                        :loading="form.processing"
                        icon="i-lucide-save"
                    >
                        Simpan Pengaturan
                    </UButton>
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    withdrawLimit: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    limit: props.withdrawLimit?.limit ?? '',
});

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

const submit = () => {
    form.put('/dashboard/finance/student-withdraw-limit', {
        preserveScroll: true,
    });
};
</script>

