<template>
    <DashboardPage
        title="Edit Kelas"
        page-id="classrooms-edit"
        content-class="p-6"
    >
        <div class="max-w-2xl mx-auto">
            <UCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <UFormField label="Nama Kelas" :error="form.errors.name">
                        <UInput
                            v-model="form.name"
                            placeholder="Contoh: VII A"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Tingkat" :error="form.errors.level">
                        <UInput
                            v-model="form.level"
                            placeholder="Contoh: 1, 2, VII, X, Ula, Wustho"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Tahun Ajaran" :error="form.errors.school_year_id">
                        <USelect
                            v-model="form.school_year_id"
                            :items="schoolYearOptions"
                            placeholder="Pilih Tahun Ajaran"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Wali Kelas" :error="form.errors.teacher_id">
                        <USelectMenu
                            v-model="form.teacher_id"
                            :items="teacherOptions"
                            value-key="value"
                            label-key="label"
                            searchable
                            searchable-placeholder="Cari wali kelas..."
                            placeholder="Pilih Wali Kelas"
                            class="w-full"
                        />
                    </UFormField>

                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-800">
                        <UButton
                            color="neutral"
                            variant="soft"
                            to="/dashboard/classrooms"
                            :disabled="form.processing"
                        >
                            Batal
                        </UButton>
                        <UButton
                            type="submit"
                            color="primary"
                            :loading="form.processing"
                        >
                            Simpan Perubahan
                        </UButton>
                    </div>
                </form>
            </UCard>
        </div>
    </DashboardPage>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    classroom: Object,
    schoolYears: Array,
    teachers: Array,
});

const form = useForm({
    name: props.classroom.name,
    level: props.classroom.level,
    school_year_id: props.classroom.school_year_id,
    teacher_id: props.classroom.teacher_id,
});

const schoolYearOptions = computed(() => 
    props.schoolYears.map(sy => ({ label: sy.name + (sy.is_active ? ' (Aktif)' : ''), value: sy.id }))
);

const teacherOptions = computed(() => 
    props.teachers.map(t => ({ label: t.name, value: t.id, description: t.email }))
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        teacher_id: data.teacher_id?.value || data.teacher_id
    })).put(`/dashboard/classrooms/${props.classroom.id}`);
};
</script>
