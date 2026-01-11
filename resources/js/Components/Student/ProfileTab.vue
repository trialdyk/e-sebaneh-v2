<template>
    <div class="space-y-4 pt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Tempat, Tanggal Lahir</p>
                <p>{{ student.place_of_birth }}, {{ formatDate(student.date_of_birth) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p>{{ student.user?.email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">No HP</p>
                <p>{{ student.user?.phone_number || '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Anak Ke-</p>
                <p>{{ student.child_number || '-' }} dari {{ student.siblings_count || '-' }} bersaudara</p>
            </div>
        </div>

        <UDivider label="Alamat" />

        <div>
            <p>{{ student.address }}</p>
            <p class="text-sm text-gray-400 mt-1">
                {{ [student.village, student.district, student.regency, student.province].filter(Boolean).join(', ') || '-' }}
            </p>
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

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>
