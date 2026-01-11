<template>
    <DashboardPage
        title="Edit Sekolah"
        heading="Edit Data Sekolah"
        description="Perbarui data sekolah dan jenjang kelas"
        page-id="master-schools-edit"
    >
        <UCard>
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <UFormField label="Nama Sekolah" name="name" :error="form.errors.name">
                        <UInput v-model="form.name" />
                    </UFormField>
                    <UFormField label="Singkatan" name="short_name" :error="form.errors.short_name">
                        <UInput v-model="form.short_name" />
                    </UFormField>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Jenjang Kelas</h3>
                        <UButton
                            size="sm"
                            color="primary"
                            variant="soft"
                            icon="i-lucide-plus"
                            label="Tambah Jenjang"
                            @click="addLevel"
                        />
                    </div>
                    
                    <div v-if="form.levels.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg dashed border-2 border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500">Belum ada jenjang kelas ditambahkan</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(level, index) in form.levels" :key="index" class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="py-2 text-sm font-bold text-gray-400 w-6">{{ index + 1 }}.</span>
                            
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <UFormField
                                    :name="`levels.${index}.name`"
                                    :error="form.errors[`levels.${index}.name`]"
                                >
                                    <UInput v-model="level.name" placeholder="Nama Jenjang" />
                                </UFormField>

                                <UFormField
                                    :name="`levels.${index}.order_level`"
                                    :error="form.errors[`levels.${index}.order_level`]"
                                >
                                    <UInput v-model="level.order_level" type="number" placeholder="Urutan" />
                                </UFormField>
                            </div>

                            <UButton
                                color="error"
                                variant="ghost"
                                icon="i-lucide-trash"
                                @click="removeLevel(index)"
                                tabindex="-1"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-6 border-t mt-6">
                    <UButton color="neutral" variant="soft" label="Batal" to="/dashboard/schools" />
                    <UButton type="submit" color="primary" label="Simpan Perubahan" :loading="form.processing" />
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    school: Object,
});

const form = useForm({
    name: props.school.name,
    short_name: props.school.short_name,
    levels: props.school.school_levels?.map(l => ({
        id: l.id,
        name: l.name,
        order_level: l.order_level
    })) || [],
});

const addLevel = () => {
    const nextOrder = form.levels.length > 0 
        ? Math.max(...form.levels.map(l => Number(l.order_level))) + 1 
        : 1;
    
    form.levels.push({
        name: '',
        order_level: nextOrder
    });
};

const removeLevel = (index) => {
    form.levels.splice(index, 1);
};

const submit = () => {
    form.put(`/dashboard/schools/${props.school.id}`);
};
</script>
