<template>
    <DashboardLayout>
        <UDashboardPanel id="profile" class="!max-w-none">
            <template #header>
                <UDashboardNavbar title="Profil Saya">
                    <template #leading>
                        <UDashboardSidebarCollapse />
                    </template>
                </UDashboardNavbar>
            </template>

            <template #body>
                <div class="p-6">
                    <!-- Profile Header -->
                    <div class="flex items-center gap-6 mb-8">
                        <div class="relative">
                            <UAvatar
                                :src="user.profile_photo_url || `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.email}`"
                                size="3xl"
                                :alt="user.name"
                            />
                            <UButton
                                color="primary"
                                variant="soft"
                                size="xs"
                                icon="i-lucide-camera"
                                class="absolute -bottom-1 -right-1 rounded-full"
                                @click="triggerPhotoUpload"
                            />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">{{ user.name }}</h2>
                            <p class="text-gray-500">{{ user.email }}</p>
                            <div class="flex gap-2 mt-2">
                                <UBadge
                                    v-for="role in $page.props.auth.user?.roles"
                                    :key="role"
                                    color="primary"
                                    variant="soft"
                                >
                                    {{ role }}
                                </UBadge>
                            </div>
                        </div>

                        <input
                            ref="photoInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="uploadPhoto"
                        />
                    </div>

                    <!-- Tabs -->
                    <UTabs :items="tabs" class="w-full">
                        <!-- Profile Tab -->
                        <template #profile>
                            <UCard class="mt-4">
                                <form @submit.prevent="updateProfile" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <UFormField label="Nama Lengkap" required :error="profileForm.errors.name">
                                            <UInput
                                                v-model="profileForm.name"
                                                placeholder="Nama lengkap"
                                                class="w-full"
                                                size="lg"
                                                :disabled="profileForm.processing"
                                                @input="profileForm.clearErrors('name')"
                                            />
                                        </UFormField>

                                        <UFormField label="Email" required :error="profileForm.errors.email">
                                            <UInput
                                                v-model="profileForm.email"
                                                type="email"
                                                placeholder="email@example.com"
                                                class="w-full"
                                                size="lg"
                                                :disabled="profileForm.processing"
                                                @input="profileForm.clearErrors('email')"
                                            />
                                        </UFormField>

                                        <UFormField label="Nomor Telepon" :error="profileForm.errors.phone_number">
                                            <UInput
                                                v-model="profileForm.phone_number"
                                                placeholder="08xxxxxxxxxx"
                                                class="w-full"
                                                size="lg"
                                                :disabled="profileForm.processing"
                                            />
                                        </UFormField>

                                        <UFormField label="Jenis Kelamin">
                                            <USelect
                                                v-model="profileForm.gender"
                                                :items="genderOptions"
                                                value-key="value"
                                                label-key="label"
                                                placeholder="Pilih jenis kelamin"
                                                class="w-full"
                                                size="lg"
                                                :disabled="profileForm.processing"
                                            />
                                        </UFormField>
                                    </div>

                                    <div class="flex justify-end">
                                        <UButton
                                            type="submit"
                                            color="primary"
                                            size="lg"
                                            :loading="profileForm.processing"
                                        >
                                            Simpan Perubahan
                                        </UButton>
                                    </div>
                                </form>
                            </UCard>
                        </template>

                        <!-- Security Tab -->
                        <template #security>
                            <UCard class="mt-4">
                                <template #header>
                                    <h3 class="text-lg font-semibold">Ubah Password</h3>
                                </template>

                                <form @submit.prevent="updatePassword" class="space-y-6">
                                    <UFormField
                                        label="Password Saat Ini"
                                        required
                                        :error="passwordForm.errors.current_password"
                                    >
                                        <UInput
                                            v-model="passwordForm.current_password"
                                            type="password"
                                            placeholder="••••••••"
                                            class="w-full"
                                            size="lg"
                                            :disabled="passwordForm.processing"
                                            @input="passwordForm.clearErrors('current_password')"
                                        />
                                    </UFormField>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <UFormField
                                            label="Password Baru"
                                            required
                                            :error="passwordForm.errors.password"
                                        >
                                            <UInput
                                                v-model="passwordForm.password"
                                                type="password"
                                                placeholder="••••••••"
                                                class="w-full"
                                                size="lg"
                                                :disabled="passwordForm.processing"
                                                @input="passwordForm.clearErrors('password')"
                                            />
                                        </UFormField>

                                        <UFormField label="Konfirmasi Password" required>
                                            <UInput
                                                v-model="passwordForm.password_confirmation"
                                                type="password"
                                                placeholder="••••••••"
                                                class="w-full"
                                                size="lg"
                                                :disabled="passwordForm.processing"
                                            />
                                        </UFormField>
                                    </div>

                                    <div class="flex justify-end">
                                        <UButton
                                            type="submit"
                                            color="primary"
                                            size="lg"
                                            :loading="passwordForm.processing"
                                        >
                                            Ubah Password
                                        </UButton>
                                    </div>
                                </form>
                            </UCard>
                        </template>

                        <!-- Connections Tab -->
                        <template #connections>
                            <UCard class="mt-4">
                                <div class="space-y-4">
                                    <!-- Google -->
                                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                        <div class="flex items-center gap-4">
                                            <div class="p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                                <svg class="w-8 h-8" viewBox="0 0 24 24">
                                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-lg">Google</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ hasGoogleLinked ? 'Terhubung dengan akun Google' : 'Belum terhubung' }}
                                                </p>
                                            </div>
                                        </div>

                                        <UButton
                                            v-if="hasGoogleLinked"
                                            color="error"
                                            variant="soft"
                                            size="lg"
                                            @click="unlinkGoogle"
                                            :loading="unlinkProcessing"
                                        >
                                            Putuskan Koneksi
                                        </UButton>
                                        <UButton
                                            v-else
                                            color="neutral"
                                            variant="outline"
                                            size="lg"
                                            as="a"
                                            href="/auth/google/link"
                                        >
                                            Hubungkan
                                        </UButton>
                                    </div>
                                </div>
                            </UCard>
                        </template>
                    </UTabs>
                </div>
            </template>
        </UDashboardPanel>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    genderOptions: Array,
    hasGoogleLinked: Boolean,
});

const toast = useToast();

// Tabs
const tabs = [{
    label: 'Profil',
    icon: 'i-lucide-user',
    slot: 'profile',
}, {
    label: 'Keamanan',
    icon: 'i-lucide-shield',
    slot: 'security',
}, {
    label: 'Koneksi',
    icon: 'i-lucide-link',
    slot: 'connections',
}];

// Photo upload
const photoInput = ref(null);

const triggerPhotoUpload = () => {
    photoInput.value.click();
};

const uploadPhoto = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('photo', file);

    router.post('/dashboard/profile/photo', formData, {
        forceFormData: true,
    });
};

// Profile form
const profileForm = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone_number: props.user?.phone_number || '',
    gender: props.user?.gender || '',
});

const updateProfile = () => {
    profileForm.put('/dashboard/profile');
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/dashboard/profile/password');
};

// Google unlink
const unlinkProcessing = ref(false);

const unlinkGoogle = () => {
    unlinkProcessing.value = true;
    router.delete('/dashboard/profile/google', {
        onFinish: () => unlinkProcessing.value = false,
    });
};
</script>
