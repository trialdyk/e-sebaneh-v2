<template>
    <DashboardPage
        title="Detail Santri"
        :heading="student.user?.name"
        :description="student.student_id"
        page-id="students-show"
        back-url="/dashboard/students"
    >
        <template #header>
            <UButton 
                label="Edit Data" 
                icon="i-lucide-pencil" 
                :to="`/dashboard/students/${student.id}/edit`" 
                color="primary"
                variant="soft"
            />
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Column: Profile Card -->
            <div class="lg:col-span-1 space-y-6">
                <UCard>
                    <div class="flex flex-col items-center py-4">
                        <UAvatar 
                            :src="student.user?.photo ? (student.user.photo.startsWith('http') ? student.user.photo : `/storage/${student.user.photo}`) : null"
                            :alt="student.user?.name"
                            size="3xl"
                            class="mb-4"
                        />
                        <h2 class="text-xl font-bold text-center">{{ student.user?.name }}</h2>
                        <UBadge :color="getStatusColor(student.status)" variant="subtle" class="mt-2">
                            {{ getStatusLabel(student.status) }}
                        </UBadge>
                        
                        <div class="w-full mt-6 space-y-3">
                            <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                                <span class="text-gray-500 text-sm">NIS</span>
                                <span class="font-medium">{{ student.student_id }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                                <span class="text-gray-500 text-sm">Jenis Kelamin</span>
                                <span class="font-medium">{{ student.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
                                <span class="text-gray-500 text-sm">Kelas</span>
                                <span class="font-medium">{{ student.current_classroom?.classroom?.name || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </UCard>

                <!-- Quick Navigation -->
                <UCard>
                    <template #header>
                        <h3 class="font-semibold text-sm">Menu Cepat</h3>
                    </template>
                    <nav class="space-y-1">
                        <button 
                            v-for="(tab, index) in tabs" 
                            :key="tab.slot"
                            @click="activeTab = index"
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors"
                            :class="activeTab === index 
                                ? 'bg-primary text-white' 
                                : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400'"
                        >
                            <UIcon :name="tab.icon" class="w-4 h-4" />
                            <span>{{ tab.label }}</span>
                        </button>
                    </nav>
                </UCard>
            </div>

            <!-- Right Column: Content -->
            <div class="lg:col-span-3">
                <UCard>
                    <template #header>
                        <div class="flex items-center gap-3">
                            <UIcon :name="tabs[activeTab].icon" class="w-5 h-5 text-primary" />
                            <h3 class="font-semibold text-lg">{{ tabs[activeTab].label }}</h3>
                        </div>
                    </template>

                    <!-- Biodata Tab -->
                    <ProfileTab v-if="activeTab === 0" :student="student" />

                    <!-- Parents Tab -->
                    <ParentsTab v-else-if="activeTab === 1" :student="student" />

                    <!-- Academic Tab -->
                    <AcademicTab v-else-if="activeTab === 2" :student="student" />

                    <!-- Bedroom Tab -->
                    <BedRoomTab v-else-if="activeTab === 3" :bed-rooms="student.bed_rooms || []" />

                    <!-- Medical Records Tab -->
                    <DiseaseTab 
                        v-else-if="activeTab === 4"
                        :student-id="student.id" 
                        :diseases="student.diseases || []" 
                    />

                    <!-- Memorize Tab -->
                    <MemorizeTab 
                        v-else-if="activeTab === 5"
                        :student-id="student.id" 
                        :memorizes="student.memorizes || []" 
                        :surahs="surahs || []"
                    />

                    <!-- Permission Tab -->
                    <PermissionTab 
                        v-else-if="activeTab === 6"
                        :student-id="student.id" 
                        :student-name="student.user?.name"
                        :permissions="student.permissions || []" 
                    />

                    <!-- Violation Tab -->
                    <ViolationTab 
                        v-else-if="activeTab === 7"
                        :student-id="student.id" 
                        :violations="student.violations || []" 
                    />
                </UCard>
            </div>
        </div>
    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import DashboardPage from '@/Components/DashboardPage.vue';
import ProfileTab from '@/Components/Student/ProfileTab.vue';
import ParentsTab from '@/Components/Student/ParentsTab.vue';
import AcademicTab from '@/Components/Student/AcademicTab.vue';
import BedRoomTab from '@/Components/Student/BedRoomTab.vue';
import DiseaseTab from '@/Components/Student/DiseaseTab.vue';
import MemorizeTab from '@/Components/Student/MemorizeTab.vue';
import PermissionTab from '@/Components/Student/PermissionTab.vue';
import ViolationTab from '@/Components/Student/ViolationTab.vue';

const props = defineProps({
    student: Object,
    surahs: Array
});

const activeTab = ref(0);

const tabs = [
    { label: 'Biodata', slot: 'profile', icon: 'i-lucide-user' },
    { label: 'Orang Tua', slot: 'parents', icon: 'i-lucide-users' },
    { label: 'Akademik', slot: 'academic', icon: 'i-lucide-graduation-cap' },
    { label: 'Kamar', slot: 'bedroom', icon: 'i-lucide-bed' },
    { label: 'Rekam Medis', slot: 'medical', icon: 'i-lucide-heart-pulse' },
    { label: 'Hafalan', slot: 'hafalan', icon: 'i-lucide-book-open' },
    { label: 'Izin', slot: 'izin', icon: 'i-lucide-calendar-check' },
    { label: 'Pelanggaran', slot: 'pelanggaran', icon: 'i-lucide-alert-triangle' },
];

const getStatusLabel = (status) => {
    const labels = {
        active: 'Aktif',
        inactive: 'Tidak Aktif',
        graduated: 'Lulus',
        dropped_out: 'Keluar/DO'
    };
    return labels[status] || status;
};

const getStatusColor = (status) => {
    const colors = {
        active: 'success',
        inactive: 'neutral',
        graduated: 'info',
        dropped_out: 'error'
    };
    return colors[status] || 'neutral';
};
</script>
