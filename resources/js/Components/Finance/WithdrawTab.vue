<template>
    <div class="space-y-6">

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Left: RFID Scanner (2 cols) -->
            <div class="lg:col-span-2">
                <UCard class="h-full">
                    <div class="text-center mb-6">
                        <div class="inline-flex p-4 bg-primary-100 dark:bg-primary-900/30 rounded-full mb-4">
                            <UIcon name="i-lucide-scan" class="w-12 h-12 text-primary-600 dark:text-primary-400" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Scan Kartu RFID</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tempelkan kartu atau masukkan nomor RFID</p>
                    </div>

                    <form @submit.prevent="scanRfid" class="space-y-4">
                        <UFormField :error="scanError">
                            <UInput
                                ref="rfidInput"
                                v-model="scanForm.rfid"
                                placeholder="Scan atau ketik nomor RFID..."
                                icon="i-lucide-credit-card"
                                size="lg"
                                autofocus
                                class="w-full"
                            />
                        </UFormField>
                    </form>

                    <!-- Quick Tips -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Tips</p>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <UIcon name="i-lucide-check-circle" class="w-4 h-4 mt-0.5 text-green-500 flex-shrink-0" />
                                <span>Pastikan kartu RFID valid dan terdaftar</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <UIcon name="i-lucide-check-circle" class="w-4 h-4 mt-0.5 text-green-500 flex-shrink-0" />
                                <span>Santri harus memiliki PIN untuk menarik saldo</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <UIcon name="i-lucide-check-circle" class="w-4 h-4 mt-0.5 text-green-500 flex-shrink-0" />
                                <span>Penarikan dibatasi sesuai limit harian</span>
                            </li>
                        </ul>
                    </div>
                </UCard>
            </div>

            <!-- Right: Student Info & Withdraw Form (3 cols) -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Empty State -->
                <UCard v-if="!studentData" class="h-full">
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                            <UIcon name="i-lucide-user-search" class="w-12 h-12 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum Ada Data Santri</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-sm">
                            Scan kartu RFID santri atau masukkan nomor RFID untuk memulai proses penarikan saldo.
                        </p>
                    </div>
                </UCard>

                <!-- Student Info -->
                <UCard v-if="studentData">
                    <div class="flex items-start gap-4">
                        <UAvatar
                            :src="studentData.student.photo"
                            :alt="studentData.student.name"
                            size="xl"
                            class="ring-4 ring-primary-100 dark:ring-primary-900/50"
                        />
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ studentData.student.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        NIS: {{ studentData.student.student_id }}
                                    </p>
                                </div>
                                <UButton
                                    @click="resetScan"
                                    color="neutral"
                                    variant="ghost"
                                    icon="i-lucide-x"
                                    size="sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Balance Cards -->
                    <div class="grid grid-cols-3 gap-4 mt-6">
                        <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl">
                            <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Saldo</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">
                                Rp {{ formatCurrency(studentData.student.balance) }}
                            </p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl">
                            <p class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">Tarik Hari Ini</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">
                                Rp {{ formatCurrency(studentData.today_withdraw) }}
                            </p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl">
                            <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Sisa Limit</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">
                                Rp {{ formatCurrency(studentData.remaining_limit) }}
                            </p>
                        </div>
                    </div>
                </UCard>

                <!-- Withdraw Form -->
                <UCard v-if="studentData">
                    <template #header>
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <UIcon name="i-lucide-banknote" class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Proses Penarikan</h3>
                                <p class="text-sm text-gray-500">Masukkan jumlah dan PIN santri</p>
                            </div>
                        </div>
                    </template>

                    <form @submit.prevent="processWithdraw" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <UFormField label="Jumlah Penarikan" required :error="withdrawError">
                                <UInput
                                    v-model="withdrawForm.amount"
                                    type="number"
                                    min="1"
                                    step="1000"
                                    placeholder="Rp 0"
                                    size="xl"
                                    icon="i-lucide-banknote"
                                />
                                <template #hint>
                                    <div class="flex gap-2 mt-2">
                                        <UButton
                                            v-for="amount in quickAmounts"
                                            :key="amount"
                                            size="xs"
                                            color="neutral"
                                            variant="soft"
                                            @click="withdrawForm.amount = amount"
                                        >
                                            {{ formatCurrency(amount) }}
                                        </UButton>
                                    </div>
                                </template>
                            </UFormField>

                            <UFormField label="PIN Santri (6 Digit)" required :error="pinError">
                                <UInput
                                    v-model="withdrawForm.pin"
                                    type="password"
                                    maxlength="6"
                                    placeholder="••••••"
                                    size="xl"
                                    icon="i-lucide-lock"
                                />
                            </UFormField>
                        </div>

                        <div class="flex gap-3">
                            <UButton
                                @click="resetScan"
                                color="neutral"
                                variant="soft"
                                icon="i-lucide-arrow-left"
                                size="lg"
                            >
                                Batal
                            </UButton>
                            <UButton
                                type="submit"
                                color="error"
                                size="lg"
                                class="flex-1"
                                :loading="processing"
                                icon="i-lucide-check"
                            >
                                Proses Penarikan
                            </UButton>
                        </div>
                    </form>
                </UCard>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const props = defineProps({
    withdrawLimit: Object,
});

const rfidInput = ref(null);
const scanning = ref(false);
const processing = ref(false);
const studentData = ref(null);
const scanError = ref('');
const withdrawError = ref('');
const pinError = ref('');

const quickAmounts = [5000, 10000, 20000, 50000];

const scanForm = ref({
    rfid: '',
});

const withdrawForm = ref({
    rfid: '',
    amount: '',
    pin: '',
});

const formatCurrency = (value) => {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
};

const scanRfid = async () => {
    if (!scanForm.value.rfid) return;

    scanning.value = true;
    scanError.value = '';

    try {
        const response = await axios.post('/dashboard/finance/student-withdraw/show-by-rfid', {
            rfid: scanForm.value.rfid,
        });

        studentData.value = response.data;
        withdrawForm.value.rfid = scanForm.value.rfid;
        withdrawForm.value.amount = '';
        withdrawForm.value.pin = '';
    } catch (error) {
        scanError.value = error.response?.data?.error || 'Santri tidak ditemukan';
        studentData.value = null;
    } finally {
        scanning.value = false;
    }
};

const processWithdraw = async () => {
    if (!studentData.value) return;

    processing.value = true;
    withdrawError.value = '';
    pinError.value = '';

    try {
        const response = await axios.post('/dashboard/finance/student-withdraw/process', {
            rfid: withdrawForm.value.rfid,
            amount: withdrawForm.value.amount,
            pin: withdrawForm.value.pin,
        });

        // Update student balance
        if (response.data.new_balance !== undefined) {
            studentData.value.student.balance = response.data.new_balance;
            studentData.value.today_withdraw += parseFloat(withdrawForm.value.amount);
            studentData.value.remaining_limit -= parseFloat(withdrawForm.value.amount);
        }

        // Reset form
        withdrawForm.value.amount = '';
        withdrawForm.value.pin = '';

        // Refocus on amount input after successful withdraw
        nextTick(() => {
            const amountInput = document.querySelector('input[type="number"]');
            amountInput?.focus();
        });
    } catch (error) {
        const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat memproses penarikan';
        if (errorMsg.toLowerCase().includes('pin')) {
            pinError.value = errorMsg;
        } else {
            withdrawError.value = errorMsg;
        }
    } finally {
        processing.value = false;
    }
};

const resetScan = () => {
    studentData.value = null;
    scanForm.value.rfid = '';
    withdrawForm.value = { rfid: '', amount: '', pin: '' };
    scanError.value = '';
    withdrawError.value = '';
    pinError.value = '';
    
    nextTick(() => {
        rfidInput.value?.$el?.querySelector('input')?.focus();
    });
};
</script>
