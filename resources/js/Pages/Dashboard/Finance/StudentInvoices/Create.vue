<template>
    <DashboardPage
        title="Buat Tagihan Baru"
        heading="Buat Tagihan Baru"
        description="Buat tagihan untuk santri dengan berbagai opsi targeting"
        page-id="create-student-invoice"
    >
        <template #header>
            <UButton
                color="neutral"
                variant="ghost"
                icon="i-lucide-arrow-left"
                to="/dashboard/finance/student-invoices"
            >
                Kembali
            </UButton>
        </template>

        <form @submit.prevent="submitForm">
            <UCard class="mb-6">
                <template #header>
                    <h3 class="text-lg font-semibold">Informasi Tagihan</h3>
                </template>

                <div class="space-y-4">
                    <UFormField label="Nama Tagihan" required>
                        <UInput
                            v-model="form.name"
                            placeholder="Contoh: SPP Bulan Januari 2026"
                            :error="!!errors.name"
                            class="w-full"
                        />
                        <template v-if="errors.name" #error>{{ errors.name }}</template>
                    </UFormField>

                    <UFormField label="Jumlah Tagihan" required>
                        <UInput
                            v-model="form.amount"
                            type="number"
                            placeholder="100000"
                            :error="!!errors.amount"
                            class="w-full"
                        />
                        <template v-if="errors.amount" #error>{{ errors.amount }}</template>
                    </UFormField>

                    <UFormField label="Deskripsi">
                        <UTextarea
                            v-model="form.description"
                            placeholder="Deskripsi tagihan (opsional)"
                            rows="3"
                            class="w-full"
                        />
                    </UFormField>
                </div>
            </UCard>

            <UCard class="mb-6">
                <template #header>
                    <h3 class="text-lg font-semibold">Target Tagihan</h3>
                </template>

                <div class="space-y-4">
                    <UFormField label="Tipe Tagihan" required>
                        <USelectMenu
                            v-model="form.type"
                            :items="typeOptions"
                            value-key="value"
                            label-key="label"
                            :error="!!errors.type"
                            class="w-full"
                        />
                        <template v-if="errors.type" #error>{{ errors.type }}</template>
                    </UFormField>

                    <!-- For by_gender -->
                    <UFormField v-if="form.type === 'by_gender'" label="Gender" required>
                        <URadioGroup
                            v-model="form.for_gender"
                            :options="genderOptions"
                            :error="!!errors.for_gender"
                        />
                        <template v-if="errors.for_gender" #error>{{ errors.for_gender }}</template>
                    </UFormField>

                    <!-- For by_classroom -->
                    <UFormField v-if="form.type === 'by_classroom'" label="Pilih Kelas" required>
                        <USelectMenu
                            v-model="form.classrooms"
                            :items="classrooms"
                            multiple
                            placeholder="Pilih satu atau lebih kelas"
                            value-key="id"
                            label-key="name"
                            class="w-full"
                        />
                        <template v-if="errors.classrooms" #error>{{ errors.classrooms }}</template>
                    </UFormField>

                    <!-- For specific_students -->
                    <UFormField v-if="form.type === 'specific_students'" label="Pilih Santri" required>
                        <div class="space-y-2">
                            <UInput
                                v-model="studentSearch"
                                placeholder="Cari santri..."
                                icon="i-lucide-search"
                                @input="searchStudents"
                                class="w-full"
                            />
                            <div v-if="searchResults.length > 0" class="border rounded-lg p-2 max-h-48 overflow-y-auto">
                                <div
                                    v-for="student in searchResults"
                                    :key="student.id"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded cursor-pointer"
                                    @click="addStudent(student)"
                                >
                                    {{ student.text }}
                                </div>
                            </div>
                            <div v-if="form.students.length > 0" class="flex flex-wrap gap-2 mt-2">
                                <UBadge
                                    v-for="studentId in form.students"
                                    :key="studentId"
                                    color="primary"
                                    variant="subtle"
                                >
                                    {{ getStudentName(studentId) }}
                                    <UButton
                                        color="neutral"
                                        variant="ghost"
                                        icon="i-lucide-x"
                                        size="xs"
                                        @click="removeStudent(studentId)"
                                    />
                                </UBadge>
                            </div>
                        </div>
                        <template v-if="errors.students" #error>{{ errors.students }}</template>
                    </UFormField>
                </div>
            </UCard>

            <div class="flex justify-end gap-3">
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/dashboard/finance/student-invoices"
                >
                    Batal
                </UButton>
                <UButton
                    type="submit"
                    color="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Simpan Tagihan
                </UButton>
            </div>
        </form>
    </DashboardPage>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import axios from 'axios';

const props = defineProps({
    classrooms: Array,
    errors: Object
});

const form = useForm({
    name: '',
    amount: '',
    description: '',
    type: 'all_students',
    for_gender: 'male',
    classrooms: [],
    students: []
});

const studentSearch = ref('');
const searchResults = ref([]);
const selectedStudents = ref([]);

const typeOptions = [
    { value: 'all_students', label: 'Semua Santri' },
    { value: 'by_classroom', label: 'Berdasarkan Kelas' },
    { value: 'by_gender', label: 'Berdasarkan Gender' },
    { value: 'specific_students', label: 'Santri Tertentu' }
];

const genderOptions = [
    { value: 'male', label: 'Laki-laki' },
    { value: 'female', label: 'Perempuan' }
];

let searchTimeout = null;

function searchStudents() {
    clearTimeout(searchTimeout);
    
    if (studentSearch.value.length < 2) {
        searchResults.value = [];
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get('/dashboard/finance/student-invoices/search-students', {
                params: { q: studentSearch.value }
            });
            searchResults.value = response.data;
        } catch (error) {
            console.error('Error searching students:', error);
        }
    }, 300);
}

function addStudent(student) {
    if (!form.students.includes(student.id)) {
        form.students.push(student.id);
        selectedStudents.value.push(student);
    }
    studentSearch.value = '';
    searchResults.value = [];
}

function removeStudent(studentId) {
    form.students = form.students.filter(id => id !== studentId);
    selectedStudents.value = selectedStudents.value.filter(s => s.id !== studentId);
}

function getStudentName(studentId) {
    const student = selectedStudents.value.find(s => s.id === studentId);
    return student ? student.text : studentId;
}

function submitForm() {
    form.transform((data) => {
        const payload = {
            name: data.name,
            amount: data.amount,
            description: data.description,
            type: data.type
        };

        // Only include type-specific fields
        if (data.type === 'by_gender') {
            payload.for_gender = data.for_gender;
        } else if (data.type === 'by_classroom') {
            payload.classrooms = data.classrooms;
        } else if (data.type === 'specific_students') {
            payload.students = data.students;
        }

        return payload;
    }).post('/dashboard/finance/student-invoices', {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
}

</script>
