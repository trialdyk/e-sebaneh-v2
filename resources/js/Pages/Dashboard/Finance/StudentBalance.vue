<template>
    <DashboardPage
        title="Manajemen Saldo Santri"
        heading="Manajemen Saldo Santri"
        description="Kelola saldo santri, penarikan via RFID, dan riwayat transaksi"
        page-id="student-balance"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-piggy-bank"
                to="/dashboard/finance/student-savings"
            >
                Kelola Tabungan
            </UButton>
        </template>

        <!-- Info Alert -->
        <UAlert
            color="info"
            variant="subtle"
            icon="i-lucide-info"
            class="mb-6"
        >
            <template #title>Tipe Transaksi</template>
            <template #description>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><strong>Topup via Mobile App:</strong> Topup saldo dari orang tua melalui aplikasi mobile (akan datang)</li>
                    <li><strong>Topup oleh Admin:</strong> Topup saldo santri melalui dashboard admin</li>
                    <li><strong>Penarikan:</strong> Tarik saldo oleh santri (via RFID atau admin)</li>
                </ul>
            </template>
        </UAlert>

        <!-- Manual Tab Buttons -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
            <UButton
                v-for="tab in tabs"
                :key="tab.key"
                :color="activeTab === tab.key ? 'primary' : 'neutral'"
                :variant="activeTab === tab.key ? 'solid' : 'ghost'"
                :icon="tab.icon"
                @click="activeTab = tab.key"
            >
                {{ tab.label }}
            </UButton>
        </div>

        <!-- Tab Content -->
        <BalanceTab 
            v-if="activeTab === 'saldo'"
            :students="students"
            :summary="summary"
            :classrooms="classrooms"
            :filters="filters"
        />
        <WithdrawTab 
            v-else-if="activeTab === 'withdraw'"
            :withdraw-limit="withdrawLimit"
        />
        <HistoryTab 
            v-else-if="activeTab === 'history'"
            :history="history"
            :summary="historySummary"
            :students="students"
            :filters="historyFilters"
        />
    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import DashboardPage from '@/Components/DashboardPage.vue';
import BalanceTab from '@/Components/Finance/BalanceTab.vue';
import WithdrawTab from '@/Components/Finance/WithdrawTab.vue';
import HistoryTab from '@/Components/Finance/HistoryTab.vue';

const props = defineProps({
    // Common data
    students: {
        type: Object,
        default: () => ({ data: [], total: 0, per_page: 15, current_page: 1 }),
    },
    classrooms: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    // Balance tab summary
    summary: {
        type: Object,
        default: () => ({ total_balance: 0, student_count: 0, average_balance: 0 }),
    },
    // Withdraw tab
    withdrawLimit: {
        type: Object,
        default: null,
    },
    // History tab
    history: {
        type: Object,
        default: () => ({ data: [], total: 0, per_page: 15, current_page: 1 }),
    },
    historySummary: {
        type: Object,
        default: () => ({ total_topup: 0, total_withdraw: 0 }),
    },
    historyFilters: {
        type: Object,
        default: () => ({}),
    },
    // Active tab
    tab: {
        type: String,
        default: 'saldo',
    },
});

const tabs = [
    { key: 'saldo', label: 'Saldo Santri', icon: 'i-lucide-wallet' },
    { key: 'withdraw', label: 'Penarikan RFID', icon: 'i-lucide-credit-card' },
    { key: 'history', label: 'Riwayat Transaksi', icon: 'i-lucide-history' },
];

const activeTab = ref(props.tab || 'saldo');
</script>
