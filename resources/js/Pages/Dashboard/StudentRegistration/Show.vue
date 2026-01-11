<template>
    <DashboardPage
        title="Detail Pendaftaran"
        :heading="registration.name"
        :description="'No. Pendaftaran: ' + registration.registration_number"
        page-id="student-registrations-show"
        back-url="/dashboard/student-registrations"
    >
        <template #actions>
            <UButton 
                :href="route('dashboard.student-registrations.pdf', registration.id)"
                target="_blank"
                color="neutral" 
                variant="outline" 
                icon="i-lucide-file-text" 
                label="Cetak Formulir (Word)" 
            />
            
            <UButton 
                v-if="registration.status === 'pending'"
                @click="openAcceptModal" 
                color="primary" 
                icon="i-lucide-check-circle" 
                label="Terima Santri" 
            />
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Bio -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Alert -->
                <UAlert 
                    v-if="registration.status === 'pending'"
                    color="warning" 
                    variant="subtle" 
                    icon="i-lucide-clock"
                    title="Status: Menunggu Konfirmasi"
                    description="Pendaftaran ini belum diproses. Silahkan cek kelengkapan data sebelum menerima."
                />
                <UAlert 
                    v-else-if="registration.status === 'accepted'"
                    color="success" 
                    variant="subtle" 
                    icon="i-lucide-check-circle"
                    title="Status: Diterima"
                    description="Pendaftaran ini telah diterima. Data santri sudah masuk ke database utama."
                />

                <UCard>
                    <template #header><h3 class="font-semibold">Data Pribadi</h3></template>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div><dt class="text-sm text-gray-500">Nama Lengkap</dt><dd class="font-medium">{{ registration.name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Jenis Kelamin</dt><dd class="font-medium">{{ registration.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Tempat, Tanggal Lahir</dt><dd class="font-medium">{{ registration.place_of_birth }}, {{ new Date(registration.date_of_birth).toLocaleDateString('id-ID') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Anak Ke / Dari</dt><dd class="font-medium">{{ registration.child_number }} dari {{ registration.siblings_count }} bersaudara</dd></div>
                        <div class="sm:col-span-2"><dt class="text-sm text-gray-500">Alamat Lengkap</dt><dd class="font-medium">{{ registration.address }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Desa / Kelurahan</dt><dd class="font-medium">{{ registration.village }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Kecamatan</dt><dd class="font-medium">{{ registration.district }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Kabupaten / Kota</dt><dd class="font-medium">{{ registration.regency }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Provinsi</dt><dd class="font-medium">{{ registration.province }}</dd></div>
                    </dl>
                </UCard>

                <UCard>
                    <template #header><h3 class="font-semibold">Data Orang Tua</h3></template>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700 border-b pb-2">Ayah</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-sm text-gray-500">Nama</dt><dd class="font-medium">{{ registration.father_name }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Pekerjaan</dt><dd class="font-medium">{{ registration.father_job || '-' }}</dd></div>
                                <div><dt class="text-sm text-gray-500">No HP</dt><dd class="font-medium">{{ registration.father_phone || '-' }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Penghasilan</dt><dd class="font-medium">{{ registration.father_income || '-' }}</dd></div>
                            </dl>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700 border-b pb-2">Ibu</h4>
                            <dl class="space-y-2">
                                <div><dt class="text-sm text-gray-500">Nama</dt><dd class="font-medium">{{ registration.mother_name }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Pekerjaan</dt><dd class="font-medium">{{ registration.mother_job || '-' }}</dd></div>
                                <div><dt class="text-sm text-gray-500">No HP</dt><dd class="font-medium">{{ registration.mother_phone || '-' }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Penghasilan</dt><dd class="font-medium">{{ registration.mother_income || '-' }}</dd></div>
                            </dl>
                        </div>
                        </div>


                    <div v-if="registration.guardian_name" class="mt-6 pt-6 border-t">
                        <h4 class="font-medium text-gray-700 border-b pb-2 mb-4">Wali Santri</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                            <div><dt class="text-sm text-gray-500">Nama Wali</dt><dd class="font-medium">{{ registration.guardian_name }}</dd></div>
                            <div><dt class="text-sm text-gray-500">Pekerjaan</dt><dd class="font-medium">{{ registration.guardian_job || '-' }}</dd></div>
                            <div><dt class="text-sm text-gray-500">No HP</dt><dd class="font-medium">{{ registration.guardian_phone || '-' }}</dd></div>
                            <div class="md:col-span-2"><dt class="text-sm text-gray-500">Alamat</dt><dd class="font-medium">{{ registration.guardian_address || 'Sama dengan santri' }}</dd></div>
                        </dl>
                    </div>
                </UCard>

                 <UCard>
                    <template #header><h3 class="font-semibold">Riwayat Pendidikan</h3></template>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div><dt class="text-sm text-gray-500">Asal Sekolah</dt><dd class="font-medium">{{ registration.previous_school_name }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Alamat Sekolah</dt><dd class="font-medium">{{ registration.previous_school_address || '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Tahun Lulus</dt><dd class="font-medium">{{ registration.graduation_year || '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">No Ijazah</dt><dd class="font-medium">{{ registration.certificate_number || '-' }}</dd></div>
                    </dl>
                </UCard>
            </div>

            <!-- Right Column: Meta & Actions -->
            <div class="space-y-6">
                <UCard>
                    <template #header><h3 class="font-semibold">Info Pendaftaran</h3></template>
                    <dl class="space-y-4">
                        <div><dt class="text-sm text-gray-500">Nomor Pendaftaran</dt><dd class="font-mono text-lg font-bold">{{ registration.registration_number }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Tanggal Daftar</dt><dd class="font-medium">{{ new Date(registration.created_at).toLocaleString('id-ID') }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Sekolah Tujuan</dt><dd class="font-medium">{{ registration.school?.name || '-' }}</dd></div>
                        <div><dt class="text-sm text-gray-500">Jenjang / Kelas</dt><dd class="font-medium">{{ registration.school_level?.name || '-' }}</dd></div>
                    </dl>
                </UCard>
                
                <div v-if="registration.photo">
                    <img :src="'/storage/' + registration.photo" alt="Foto Santri" class="w-full rounded-lg shadow-sm border border-gray-200" />
                </div>
                <div v-else class="bg-gray-100 rounded-lg h-48 flex items-center justify-center text-gray-400">
                    <span class="text-sm">Tidak ada foto</span>
                </div>
            </div>
        </div>

        <!-- Accept Modal -->
        <UModal v-model="showAcceptModal" title="Konfirmasi Terima Santri">
            <div class="p-4 space-y-4">
                <p>Apakah Anda yakin ingin menerima calon santri ini? Data akan dipindahkan ke Data Santri Utama.</p>
                <div class="flex justify-end gap-2">
                    <UButton label="Batal" color="neutral" variant="ghost" @click="showAcceptModal = false" />
                    <UButton label="Ya, Terima Santri" color="success" :loading="processing" @click="confirmAccept" />
                </div>
            </div>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    registration: Object,
});

const showAcceptModal = ref(false);
const processing = ref(false);

const openAcceptModal = () => {
    showAcceptModal.value = true;
};

const confirmAccept = () => {
    processing.value = true;
    router.post(route('dashboard.student-registrations.accept', props.registration.id), {}, {
        onFinish: () => {
            processing.value = false;
            showAcceptModal.value = false;
        }
    });
};
</script>
