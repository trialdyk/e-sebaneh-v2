<template>
    <div class="space-y-6 pt-4">
        <!-- Father Section -->
        <div>
            <h4 class="font-semibold mb-3 border-b border-gray-200 dark:border-gray-700 pb-1">Data Ayah</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nama</p>
                    <p>{{ student.father_name || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pekerjaan</p>
                    <p>{{ student.father_job || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">No HP</p>
                    <p>{{ student.father_phone || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Penghasilan</p>
                    <p>{{ formatIncome(student.father_income) }}</p>
                </div>
            </div>
        </div>

        <!-- Mother Section -->
        <div>
            <h4 class="font-semibold mb-3 border-b border-gray-200 dark:border-gray-700 pb-1">Data Ibu</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nama</p>
                    <p>{{ student.mother_name || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pekerjaan</p>
                    <p>{{ student.mother_job || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">No HP</p>
                    <p>{{ student.mother_phone || '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Penghasilan</p>
                    <p>{{ formatIncome(student.mother_income) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    student: {
        type: Object,
        required: true
    }
});

const formatIncome = (income) => {
    if (!income) return '-';
    return `Rp ${parseInt(income).toLocaleString('id-ID')}`;
};
</script>
