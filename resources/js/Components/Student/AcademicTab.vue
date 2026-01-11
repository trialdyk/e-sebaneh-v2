<template>
    <div class="space-y-6 pt-4">
        <!-- Classroom History -->
        <div>
            <h4 class="font-semibold mb-3">Riwayat Kelas (Pondok)</h4>
            <ul v-if="student.classrooms?.length" class="space-y-2">
                <li 
                    v-for="classroom in student.classrooms" 
                    :key="classroom.id" 
                    class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                >
                    <span class="font-medium">{{ classroom.name }}</span>
                    <UBadge color="neutral" variant="subtle">
                        {{ classroom.school_year?.name || `TA ${classroom.pivot?.school_year_id}` }}
                    </UBadge>
                </li>
            </ul>
            <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-info">
                <template #description>Belum ada riwayat kelas.</template>
            </UAlert>
        </div>

        <UDivider />

        <!-- Formal School -->
        <div>
            <h4 class="font-semibold mb-3">Sekolah Formal</h4>
            <ul v-if="student.schools?.length" class="space-y-2">
                <li 
                    v-for="school in student.schools" 
                    :key="school.id" 
                    class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                >
                    <p class="font-medium">{{ school.name }}</p>
                    <p class="text-sm text-gray-500">{{ getSchoolLevelName(school) }}</p>
                </li>
            </ul>
            <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-info">
                <template #description>Belum ada data sekolah formal.</template>
            </UAlert>
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

const getSchoolLevelName = (school) => {
    // Try to get level name from relation or pivot
    const levelId = school.pivot?.school_level_id;
    const levels = school.school_levels || [];
    const level = levels.find(l => l.id === levelId);
    return level?.name || `Level ID: ${levelId}`;
};
</script>
