<template>
    <DashboardPage
        title="Tambah FAQ"
        heading="Tambah Pertanyaan Baru"
        description="Tambahkan pertanyaan yang sering diajukan"
        page-id="cms-faqs-create"
    >
        <template #header>
            <UButton
                color="neutral"
                icon="i-lucide-arrow-left"
                label="Kembali"
                variant="ghost"
                to="/dashboard/cms/faqs"
            />
        </template>

        <UCard>
            <UForm :state="form" @submit="submit" class="space-y-6 p-4 max-w-3xl mx-auto">
                <UFormField label="Pertanyaan" name="question" required>
                    <UInput 
                        v-model="form.question" 
                        placeholder="Masukkan pertanyaan..."
                        size="lg"
                        class="w-full"
                    />
                </UFormField>

                <UFormField label="Jawaban" name="answer" required>
                    <UTextarea 
                        v-model="form.answer" 
                        placeholder="Masukkan jawaban..."
                        :rows="6"
                        size="lg"
                        class="w-full"
                    />
                </UFormField>

                <div class="grid grid-cols-2 gap-4">
                    <UFormField label="Urutan" name="order" hint="Urutan tampil (0 = paling atas)">
                        <UInput 
                            v-model="form.order" 
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField label="Status" name="is_active">
                        <div class="flex items-center gap-3 pt-2">
                            <UCheckbox v-model="form.is_active" />
                            <span class="text-sm">{{ form.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </UFormField>
                </div>

                <div class="flex gap-2 pt-4 border-t">
                    <UButton 
                        type="submit" 
                        label="Simpan" 
                        icon="i-lucide-save"
                        :loading="form.processing"
                    />
                    <UButton 
                        label="Batal" 
                        color="neutral" 
                        variant="outline"
                        to="/dashboard/cms/faqs"
                    />
                </div>
            </UForm>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const form = useForm({
    question: '',
    answer: '',
    order: 0,
    is_active: true,
});

const submit = () => {
    form.post('/dashboard/cms/faqs');
};
</script>
