<template>
    <DashboardPage
        title="Edit Santri"
        :heading="`Edit Santri: ${student.user?.name}`"
        description="Perbarui data santri lengkap"
        page-id="students-edit"
        back-url="/dashboard/students"
    >
        <UCard>
            <form @submit.prevent="submit">
                <UStepper ref="stepper" :items="steps" class="w-full mb-8">
                    <template #profile="{ item }">
                         <div class="space-y-4 mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <UFormField label="Nama Lengkap" name="name" :error="form.errors.name" required>
                                    <UInput v-model="form.name" placeholder="Nama sesuai ijazah" class="w-full" />
                                </UFormField>
                                
                                <UFormField label="NIS (Nomor Induk Santri)" name="student_id" :error="form.errors.student_id" required>
                                    <UInput v-model="form.student_id" placeholder="Nomor Induk Santri" disabled class="bg-gray-100 w-full" />
                                </UFormField>

                                <UFormField label="Email" name="email" :error="form.errors.email" hint="Opsional, akan dibuat otomatis dari NIS jika kosong">
                                    <UInput v-model="form.email" type="email" placeholder="example@santri.com" class="w-full" />
                                </UFormField>

                                <UFormField label="Foto" name="photo" :error="form.errors.photo" hint="Opsional, max 2MB">
                                    <UInput type="file" accept="image/*" @change="handlePhotoUpload" class="w-full" />
                                    <div v-if="photoPreview || student.user?.photo" class="mt-2">
                                        <img :src="photoPreview || (student.user?.photo?.startsWith('http') ? student.user.photo : `/storage/${student.user.photo}`)" alt="Preview" class="w-24 h-24 object-cover rounded" />
                                    </div>
                                </UFormField>

                                <UFormField label="Nomor HP" name="phone_number" :error="form.errors.phone_number">
                                    <UInput v-model="form.phone_number" placeholder="08..." class="w-full" />
                                </UFormField>

                                <UFormField label="Tempat Lahir" name="place_of_birth" :error="form.errors.place_of_birth" required>
                                    <UInput v-model="form.place_of_birth" class="w-full" />
                                </UFormField>

                                <UFormField label="Tanggal Lahir" name="date_of_birth" :error="form.errors.date_of_birth" required>
                                    <UInput v-model="form.date_of_birth" type="date" class="w-full" />
                                </UFormField>

                                <UFormField label="Jenis Kelamin" name="gender" :error="form.errors.gender" required>
                                    <USelect v-model="form.gender" :items="genders" label-key="label" value-key="value" placeholder="Pilih Jenis Kelamin" class="w-full" />
                                </UFormField>

                                <UFormField label="Status Santri" name="status" :error="form.errors.status" required>
                                    <USelect v-model="form.status" :items="statuses" label-key="label" value-key="value" placeholder="Pilih Status" class="w-full" />
                                </UFormField>

                                <UFormField label="Anak Ke-" name="child_number" :error="form.errors.child_number">
                                    <UInput v-model="form.child_number" type="number" class="w-full" />
                                </UFormField>
                                
                                <UFormField label="Jumlah Saudara" name="siblings_count" :error="form.errors.siblings_count">
                                    <UInput v-model="form.siblings_count" type="number" class="w-full" />
                                </UFormField>
                            </div>

                            <UDivider label="Alamat Lengkap" />
                            
                            <div class="space-y-4">
                                <UFormField label="Alamat Jalan/Dusun" name="address" :error="form.errors.address" required>
                                    <UTextarea v-model="form.address" placeholder="Nama jalan, RT/RW, Dusun" class="w-full" />
                                </UFormField>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <UFormField label="Provinsi" name="province_id" :error="form.errors.province_id">
                                        <USelectMenu 
                                            v-model="form.province_id" 
                                            :items="provinces" 
                                            label-key="name" 
                                            value-key="id"
                                            placeholder="Pilih Provinsi" 
                                            searchable
                                            @change="fetchRegencies(true)"
                                            class="w-full"
                                        />
                                    </UFormField>

                                    <UFormField label="Kabupaten/Kota" name="regency_id" :error="form.errors.regency_id">
                                        <USelectMenu 
                                            v-model="form.regency_id" 
                                            :items="regencies" 
                                            label-key="name" 
                                            value-key="id"
                                            placeholder="Pilih Kota/Kabupaten" 
                                            :disabled="!form.province_id"
                                            searchable
                                            @change="fetchDistricts(true)"
                                            class="w-full"
                                        />
                                    </UFormField>

                                    <UFormField label="Kecamatan" name="district_id" :error="form.errors.district_id">
                                        <USelectMenu 
                                            v-model="form.district_id" 
                                            :items="districts" 
                                            label-key="name" 
                                            value-key="id"
                                            placeholder="Pilih Kecamatan" 
                                            :disabled="!form.regency_id"
                                            searchable
                                            @change="fetchVillages(true)"
                                            class="w-full"
                                        />
                                    </UFormField>

                                    <UFormField label="Desa/Kelurahan" name="village_id" :error="form.errors.village_id">
                                        <USelectMenu 
                                            v-model="form.village_id" 
                                            :items="villages" 
                                            label-key="name" 
                                            value-key="id"
                                            placeholder="Pilih Desa" 
                                            :disabled="!form.district_id"
                                            searchable
                                            class="w-full"
                                        />
                                    </UFormField>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template #parents="{ item }">
                         <div class="space-y-6 mt-4">
                            <UCard>
                                <template #header><h3 class="font-semibold">Data Ayah</h3></template>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <UFormField label="Nama Ayah" name="father_name" :error="form.errors.father_name">
                                        <UInput v-model="form.father_name" class="w-full" />
                                    </UFormField>
                                    <UFormField label="Pekerjaan Ayah" name="father_job">
                                        <UInput v-model="form.father_job" class="w-full" />
                                    </UFormField>
                                    <UFormField label="No HP Ayah" name="father_phone">
                                        <UInput v-model="form.father_phone" class="w-full" />
                                    </UFormField>
                                    <UFormField label="Penghasilan Ayah" name="father_income">
                                        <UInput v-model="form.father_income" class="w-full" />
                                    </UFormField>
                                </div>
                            </UCard>

                            <UCard>
                                <template #header><h3 class="font-semibold">Data Ibu</h3></template>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <UFormField label="Nama Ibu" name="mother_name" :error="form.errors.mother_name">
                                        <UInput v-model="form.mother_name" class="w-full" />
                                    </UFormField>
                                    <UFormField label="Pekerjaan Ibu" name="mother_job">
                                        <UInput v-model="form.mother_job" class="w-full" />
                                    </UFormField>
                                    <UFormField label="No HP Ibu" name="mother_phone">
                                        <UInput v-model="form.mother_phone" class="w-full" />
                                    </UFormField>
                                    <UFormField label="Penghasilan Ibu" name="mother_income">
                                        <UInput v-model="form.mother_income" class="w-full" />
                                    </UFormField>
                                </div>
                            </UCard>
                        </div>
                    </template>

                    <template #academic="{ item }">
                        <div class="space-y-4 mt-4">
                            <UAlert 
                                v-if="!activeSchoolYear" 
                                color="warning" 
                                variant="subtle" 
                                title="Peringatan"
                                description="Belum ada Tahun Ajaran Aktif. Data kelas dan kamar tidak akan tersimpan untuk tahun ajaran ini."
                            />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">




                                <UFormField label="Kelas Pondok" name="classroom_id">
                                    <USelectMenu 
                                        v-model="form.classroom_id" 
                                        :items="classrooms" 
                                        label-key="name" 
                                        value-key="id"
                                        placeholder="Pilih Kelas"
                                        class="w-full"
                                    />
                                </UFormField>

                                <UFormField label="Kamar / Asrama" name="bed_room_id">
                                    <USelectMenu 
                                        v-model="form.bed_room_id" 
                                        :items="bedRooms" 
                                        label-key="name" 
                                        value-key="id"
                                        placeholder="Pilih Kamar"
                                        class="w-full"
                                    />
                                </UFormField>
                            </div>

                            <UDivider label="Sekolah Formal (Opsional)" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <UFormField label="Sekolah Formal" name="school_id">
                                    <USelectMenu 
                                        v-model="form.school_id" 
                                        :items="schools" 
                                        label-key="name" 
                                        value-key="id"
                                        placeholder="Pilih Sekolah Formal"
                                        @change="filterSchoolLevels"
                                        class="w-full"
                                    />
                                </UFormField>

                                <UFormField label="Tingkat/Kelas Formal" name="school_level_id">
                                    <USelectMenu 
                                        v-model="form.school_level_id" 
                                        :items="filteredSchoolLevels" 
                                        label-key="name" 
                                        value-key="id"
                                        placeholder="Pilih Tingkat"
                                        :disabled="!form.school_id"
                                        class="w-full"
                                    />
                                </UFormField>
                            </div>
                        </div>
                    </template>
                </UStepper>

                <div class="flex justify-between pt-6 border-t border-gray-200">
                    <UButton 
                        :disabled="!stepperRef?.hasPrev"
                        @click="stepperRef?.prev()" 
                        color="neutral" 
                        variant="soft"
                        label="Kembali"
                        leading-icon="i-lucide-arrow-left"
                    />

                    <UButton 
                        v-if="stepperRef?.hasNext"
                        @click="stepperRef?.next()" 
                        color="primary" 
                        label="Lanjut"
                        trailing-icon="i-lucide-arrow-right"
                    />
                    <UButton 
                        v-else
                        type="submit" 
                        color="primary" 
                        label="Simpan Data Santri"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, watch, computed, onMounted, useTemplateRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import axios from 'axios';

const props = defineProps({
    student: Object,
    boardingSchools: Array,
    genders: Array,
    statuses: Array,
    provinces: Array,
    initialRegencies: Array,
    initialDistricts: Array,
    initialVillages: Array,
    regionIds: Object,
    academicIds: Object,
    classrooms: Array,
    bedRooms: Array,
    schools: Array,
    activeSchoolYear: Object,
});

const stepperRef = useTemplateRef('stepper');
const steps = [
    { slot: 'profile', title: 'Data Pribadi', icon: 'i-lucide-user' },
    { slot: 'parents', title: 'Orang Tua', icon: 'i-lucide-users' },
    { slot: 'academic', title: 'Akademik', icon: 'i-lucide-graduation-cap' }
];

const form = useForm({
    // Pribadi
    name: props.student.user.name || '',
    student_id: props.student.student_id,
    email: props.student.user.email,
    phone_number: props.student.user.phone_number,
    place_of_birth: props.student.place_of_birth,
    date_of_birth: props.student.date_of_birth,
    gender: props.student.gender,
    status: props.student.status,
    child_number: props.student.child_number,
    siblings_count: props.student.siblings_count,
    address: props.student.address,
    photo: null,
    
    // Region
    province_id: props.regionIds.province_id,
    regency_id: props.regionIds.regency_id,
    district_id: props.regionIds.district_id,
    village_id: props.regionIds.village_id,

    // Orang Tua
    father_name: props.student.father_name,
    father_job: props.student.father_job,
    father_phone: props.student.father_phone,
    father_income: props.student.father_income,
    mother_name: props.student.mother_name,
    mother_job: props.student.mother_job,
    mother_phone: props.student.mother_phone,
    mother_income: props.student.mother_income,

    // Akademik
    boarding_school_id: props.student.boarding_school_id,
    classroom_id: props.academicIds.classroom_id,
    bed_room_id: props.academicIds.bed_room_id,
    school_id: props.academicIds.school_id,
    school_level_id: props.academicIds.school_level_id,
});

// Photo handling
const photoPreview = ref(null);

const handlePhotoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

// Region Logic with Initialization
const regencies = ref(props.initialRegencies || []);
const districts = ref(props.initialDistricts || []);
const villages = ref(props.initialVillages || []);

const fetchRegencies = async (reset = false) => {
    if (reset) { form.regency_id = null; form.district_id = null; form.village_id = null; }
    regencies.value = []; districts.value = []; villages.value = [];
    if (!form.province_id) return;
    try { const { data } = await axios.get(`/dashboard/region/${form.province_id}/regencies`); regencies.value = data; } catch (e) { console.error(e); }
};

const fetchDistricts = async (reset = false) => {
    if (reset) { form.district_id = null; form.village_id = null; }
    districts.value = []; villages.value = [];
    if (!form.regency_id) return;
    try { const { data } = await axios.get(`/dashboard/region/${form.regency_id}/districts`); districts.value = data; } catch (e) { console.error(e); }
};

const fetchVillages = async (reset = false) => {
    if (reset) { form.village_id = null; }
    villages.value = [];
    if (!form.district_id) return;
    try { const { data } = await axios.get(`/dashboard/region/${form.district_id}/villages`); villages.value = data; } catch (e) { console.error(e); }
};

// Formal School Logic
const filteredSchoolLevels = computed(() => {
    if (!form.school_id) return [];
    const selectedSchool = props.schools.find(s => s.id === form.school_id);
    return selectedSchool ? selectedSchool.school_levels : [];
});

const filterSchoolLevels = () => {
    form.school_level_id = null;
};

const submit = () => {
    form.put(`/dashboard/students/${props.student.id}`, {
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            if (firstError) {
                console.error('Validation Error:', firstError);
                // The toast should be handled by the app layout automatically
            }
        }
    });
};
</script>
