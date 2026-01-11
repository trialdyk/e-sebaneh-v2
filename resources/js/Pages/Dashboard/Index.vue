<template>
    <DashboardLayout>
        <UDashboardPanel id="dashboard">
            <template #header>
                <UDashboardNavbar title="Dashboard">
                    <template #leading>
                        <UDashboardSidebarCollapse />
                    </template>
                    <template #trailing>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted">{{ currentDate }}</span>
                        </div>
                    </template>
                </UDashboardNavbar>
            </template>

            <template #body>
                <div class="p-6 space-y-6">
                    <!-- Welcome Section -->
                    <div class="rounded-2xl bg-gradient-to-r from-primary-500 to-primary-600 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold">Selamat Datang, {{ userName }}! 👋</h2>
                                <p class="mt-1 text-white/80">{{ greeting }} - Berikut ringkasan data pondok Anda hari ini.</p>
                            </div>
                            <div class="hidden md:block">
                                <UIcon name="i-lucide-layout-dashboard" class="w-16 h-16 text-white/30" />
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards - Admin Pondok -->
                    <div v-if="isAdminPondok" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div 
                            v-for="(stat, index) in adminStats" 
                            :key="index" 
                            :class="[stat.gradient, 'relative overflow-hidden rounded-2xl p-5 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1']"
                        >
                            <div class="absolute inset-0 bg-black/5"></div>
                            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                            <div class="absolute -right-2 -bottom-2 w-16 h-16 rounded-full bg-white/5"></div>
                            <div class="relative">
                                <div class="flex items-center justify-between">
                                    <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                        <UIcon :name="stat.icon" class="w-6 h-6" />
                                    </div>
                                </div>
                                <p class="text-4xl font-bold mt-4">{{ stat.value }}</p>
                                <p class="text-sm font-medium text-white/90 mt-1">{{ stat.title }}</p>
                                <p class="text-xs text-white/70 mt-0.5">{{ stat.subtitle }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards - Super Admin -->
                    <div v-if="isSuperAdmin" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div 
                            v-for="(stat, index) in superAdminStats" 
                            :key="index" 
                            :class="[stat.gradient, 'relative overflow-hidden rounded-2xl p-5 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1']"
                        >
                            <div class="absolute inset-0 bg-black/5"></div>
                            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10"></div>
                            <div class="absolute -right-2 -bottom-2 w-16 h-16 rounded-full bg-white/5"></div>
                            <div class="relative">
                                <div class="flex items-center justify-between">
                                    <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                        <UIcon :name="stat.icon" class="w-6 h-6" />
                                    </div>
                                </div>
                                <p class="text-4xl font-bold mt-4">{{ stat.value }}</p>
                                <p class="text-sm font-medium text-white/90 mt-1">{{ stat.title }}</p>
                                <p class="text-xs text-white/70 mt-0.5">{{ stat.subtitle }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Charts & Quick Info - Admin Pondok -->
                    <div v-if="isAdminPondok" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Gender Distribution -->
                        <UCard>
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Distribusi Gender</h3>
                                    <UBadge color="primary" variant="subtle">Total: {{ genderData.male + genderData.female }}</UBadge>
                                </div>
                            </template>

                            <div class="flex items-center justify-center gap-8">
                                <div class="text-center">
                                    <div class="w-24 h-24 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-3">
                                        <UIcon name="i-lucide-user" class="w-12 h-12 text-blue-600" />
                                    </div>
                                    <p class="text-2xl font-bold text-blue-600">{{ genderData.male }}</p>
                                    <p class="text-sm text-muted">Laki-laki</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-24 h-24 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mb-3">
                                        <UIcon name="i-lucide-user" class="w-12 h-12 text-pink-600" />
                                    </div>
                                    <p class="text-2xl font-bold text-pink-600">{{ genderData.female }}</p>
                                    <p class="text-sm text-muted">Perempuan</p>
                                </div>
                            </div>
                        </UCard>

                        <!-- Age Distribution -->
                        <UCard>
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Distribusi Usia</h3>
                                    <UIcon name="i-lucide-bar-chart-3" class="w-5 h-5 text-muted" />
                                </div>
                            </template>

                            <div class="space-y-4">
                                <div v-for="(age, index) in ageData" :key="index">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium">{{ age.label }}</span>
                                        <span class="text-sm text-muted">{{ age.value }} santri</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div 
                                            :class="[age.color, 'h-3 rounded-full transition-all']"
                                            :style="{ width: `${age.percentage}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </UCard>
                    </div>

                    <!-- Quick Actions -->
                    <UCard>
                        <template #header>
                            <h3 class="font-semibold">Aksi Cepat</h3>
                        </template>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            <UButton
                                v-for="action in quickActions"
                                :key="action.label"
                                :to="action.to"
                                color="neutral"
                                variant="soft"
                                class="flex flex-col items-center gap-2 py-4 h-auto"
                            >
                                <UIcon :name="action.icon" class="w-6 h-6" />
                                <span class="text-xs text-center">{{ action.label }}</span>
                            </UButton>
                        </div>
                    </UCard>

                    <!-- Recent Activities - Admin Pondok -->
                    <div v-if="isAdminPondok" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Permissions -->
                        <UCard>
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Izin Terbaru</h3>
                                    <UButton variant="ghost" size="xs" color="primary">Lihat Semua</UButton>
                                </div>
                            </template>

                            <div v-if="recentPermissions.length === 0" class="text-center py-8">
                                <UIcon name="i-lucide-inbox" class="w-12 h-12 text-muted mx-auto mb-2" />
                                <p class="text-muted text-sm">Belum ada data izin</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="item in recentPermissions" 
                                    :key="item.id"
                                    class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50"
                                >
                                    <UAvatar :alt="item.student" size="sm" />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate">{{ item.student }}</p>
                                        <p class="text-xs text-muted">{{ item.reason }}</p>
                                    </div>
                                    <UBadge :color="item.status === 'approved' ? 'success' : 'warning'" variant="subtle" size="sm">
                                        {{ item.status === 'approved' ? 'Disetujui' : 'Pending' }}
                                    </UBadge>
                                </div>
                            </div>
                        </UCard>

                        <!-- Recent Violations -->
                        <UCard>
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Pelanggaran Terbaru</h3>
                                    <UButton variant="ghost" size="xs" color="primary">Lihat Semua</UButton>
                                </div>
                            </template>

                            <div v-if="recentViolations.length === 0" class="text-center py-8">
                                <UIcon name="i-lucide-check-circle" class="w-12 h-12 text-success mx-auto mb-2" />
                                <p class="text-muted text-sm">Tidak ada pelanggaran tercatat 🎉</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="item in recentViolations" 
                                    :key="item.id"
                                    class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50"
                                >
                                    <UAvatar :alt="item.student" size="sm" />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate">{{ item.student }}</p>
                                        <p class="text-xs text-muted">{{ item.violation }}</p>
                                    </div>
                                    <UBadge color="error" variant="subtle" size="sm">
                                        {{ item.points }} poin
                                    </UBadge>
                                </div>
                            </div>
                        </UCard>
                    </div>

                    <!-- Super Admin: Boarding Schools Overview -->
                    <div v-if="isSuperAdmin">
                        <UCard>
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold">Daftar Pondok</h3>
                                    <UButton to="/dashboard/boarding-schools" variant="ghost" size="xs" color="primary">Lihat Semua</UButton>
                                </div>
                            </template>

                            <div v-if="boardingSchools.length === 0" class="text-center py-8">
                                <UIcon name="i-lucide-building-2" class="w-12 h-12 text-muted mx-auto mb-2" />
                                <p class="text-muted text-sm">Belum ada pondok terdaftar</p>
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div 
                                    v-for="school in boardingSchools"
                                    :key="school.id"
                                    class="p-4 rounded-lg border border-default hover:border-primary transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <UAvatar :alt="school.name" size="lg" />
                                        <div>
                                            <p class="font-semibold">{{ school.name }}</p>
                                            <p class="text-xs text-muted">{{ school.students }} santri</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </UCard>
                    </div>
                </div>
            </template>
        </UDashboardPanel>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const page = usePage();

// User info
const user = computed(() => page.props.auth.user);
const userName = computed(() => user.value?.name?.split(' ')[0] || 'Admin');
const roles = computed(() => user.value?.roles || []);
const isSuperAdmin = computed(() => roles.value.includes('super-admin'));
const isAdminPondok = computed(() => roles.value.includes('admin-pondok'));

// Current date
const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

// Greeting based on time
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

// Admin Pondok Stats - Static data (empty/zero)
const adminStats = [
    {
        title: 'Total Santri',
        value: 0,
        subtitle: 'Aktif di pondok',
        icon: 'i-lucide-users',
        gradient: 'bg-gradient-to-br from-blue-500 to-blue-600'
    },
    {
        title: 'Total Kelas',
        value: 0,
        subtitle: 'Kelas aktif',
        icon: 'i-lucide-school',
        gradient: 'bg-gradient-to-br from-purple-500 to-purple-600'
    },
    {
        title: 'Total Pegawai',
        value: 0,
        subtitle: 'Ustadz & Staff',
        icon: 'i-lucide-user-cog',
        gradient: 'bg-gradient-to-br from-emerald-500 to-emerald-600'
    },
    {
        title: 'Total Kamar',
        value: 0,
        subtitle: 'Kamar asrama',
        icon: 'i-lucide-bed',
        gradient: 'bg-gradient-to-br from-orange-500 to-orange-600'
    }
];

// Super Admin Stats - Static data (empty/zero)
const superAdminStats = [
    {
        title: 'Total Pondok',
        value: 0,
        subtitle: 'Terdaftar di sistem',
        icon: 'i-lucide-building-2',
        gradient: 'bg-gradient-to-br from-primary-500 to-primary-600'
    },
    {
        title: 'Total Santri',
        value: 0,
        subtitle: 'Semua pondok',
        icon: 'i-lucide-users',
        gradient: 'bg-gradient-to-br from-blue-500 to-blue-600'
    },
    {
        title: 'Total Pegawai',
        value: 0,
        subtitle: 'Semua pondok',
        icon: 'i-lucide-user-cog',
        gradient: 'bg-gradient-to-br from-emerald-500 to-emerald-600'
    },
    {
        title: 'Total User',
        value: 0,
        subtitle: 'Pengguna aplikasi',
        icon: 'i-lucide-user-check',
        gradient: 'bg-gradient-to-br from-violet-500 to-violet-600'
    }
];

// Gender data - Static (empty)
const genderData = {
    male: 0,
    female: 0
};

// Age distribution - Static (empty)
const totalStudents = 0;
const ageData = [
    { 
        label: '7-12 tahun', 
        value: 0, 
        color: 'bg-blue-500',
        percentage: 0
    },
    { 
        label: '13-15 tahun', 
        value: 0, 
        color: 'bg-green-500',
        percentage: 0
    },
    { 
        label: '16-20 tahun', 
        value: 0, 
        color: 'bg-purple-500',
        percentage: 0
    }
];

// Quick Actions
const quickActions = computed(() => {
    if (isAdminPondok.value) {
        return [
            { label: 'Tambah Santri', icon: 'i-lucide-user-plus', to: '/dashboard/students/create' },
            { label: 'RFID Santri', icon: 'i-lucide-credit-card', to: '/dashboard/student-rfid' },
            { label: 'Manajemen Saldo', icon: 'i-lucide-wallet', to: '/dashboard/finance/student-balance' },
            { label: 'Data Kelas', icon: 'i-lucide-school', to: '/dashboard/classrooms' },
            { label: 'Data Pegawai', icon: 'i-lucide-users', to: '/dashboard/teachers' },
            { label: 'Pengaturan', icon: 'i-lucide-settings', to: '/dashboard/settings/letter' }
        ];
    }
    return [
        { label: 'Data Pondok', icon: 'i-lucide-building-2', to: '/dashboard/boarding-schools' },
        { label: 'Master Data', icon: 'i-lucide-database', to: '/dashboard/schools' },
        { label: 'Tahun Ajaran', icon: 'i-lucide-calendar', to: '/dashboard/school-years' },
        { label: 'Berita', icon: 'i-lucide-newspaper', to: '/dashboard/cms/posts' },
        { label: 'Program', icon: 'i-lucide-book-open', to: '/dashboard/cms/programs' },
        { label: 'Web Setting', icon: 'i-lucide-settings', to: '/dashboard/cms/settings' }
    ];
});

// Recent data - Empty arrays (static)
const recentPermissions = [];
const recentViolations = [];
const boardingSchools = [];
</script>
