<template>
    <div class="space-y-4 pt-4">
        <h3 class="font-semibold">Riwayat Kamar / Asrama</h3>
        
        <ul v-if="bedRooms?.length" class="space-y-2">
            <li 
                v-for="room in bedRooms" 
                :key="room.id" 
                class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <div class="flex items-center gap-3">
                    <UIcon name="i-lucide-bed" class="w-5 h-5 text-primary" />
                    <span class="font-medium">{{ room.name }}</span>
                </div>
                <UBadge color="neutral" variant="subtle">
                    {{ room.school_year?.name || `TA ${room.pivot?.school_year_id}` }}
                </UBadge>
            </li>
        </ul>
        
        <UAlert v-else color="neutral" variant="subtle" icon="i-lucide-info">
            <template #description>
                Belum ada riwayat kamar tercatat.
            </template>
        </UAlert>
    </div>
</template>

<script setup>
const props = defineProps({
    bedRooms: {
        type: Array,
        default: () => []
    }
});
</script>
