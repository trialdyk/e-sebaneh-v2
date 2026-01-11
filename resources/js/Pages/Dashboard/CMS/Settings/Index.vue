<template>
    <DashboardPage
        title="Web Setting"
        heading="Pengaturan Website"
        description="Kelola pengaturan umum website"
        page-id="cms-settings"
    >
        <UCard>
            <form @submit.prevent="submit" class="space-y-8 p-4">
                <!-- General Settings -->
                <div v-if="settings.general" class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Pengaturan Umum</h3>
                    
                    <div v-for="setting in settings.general" :key="setting.key" class="grid grid-cols-3 gap-4 items-start">
                        <label class="text-sm font-medium pt-2">{{ formatLabel(setting.key) }}</label>
                        
                        <!-- Text Input -->
                        <UInput 
                            v-if="setting.type === 'text'"
                            v-model="form.settings[setting.key]"
                            class="col-span-2"
                        />
                        
                        <!-- Textarea -->
                        <UTextarea 
                            v-else-if="setting.type === 'textarea'"
                            v-model="form.settings[setting.key]"
                            :rows="3"
                            class="col-span-2"
                        />
                        
                        <!-- JSON (for misi - array of strings) -->
                        <div v-else-if="setting.type === 'json'" class="col-span-2 space-y-2">
                            <div v-for="(item, index) in parsedMisi" :key="index" class="flex gap-2">
                                <UInput 
                                    v-model="parsedMisi[index]"
                                    class="flex-1"
                                />
                                <UButton 
                                    icon="i-lucide-trash-2"
                                    color="error"
                                    variant="ghost"
                                    size="xs"
                                    @click="removeMisiItem(index)"
                                />
                            </div>
                            <UButton 
                                icon="i-lucide-plus"
                                label="Tambah Misi"
                                size="sm"
                                variant="outline"
                                @click="addMisiItem"
                            />
                        </div>

                        <!-- Image Upload -->
                        <div v-else-if="setting.type === 'image'" class="col-span-2 space-y-3">
                            <!-- Logo Preview -->
                            <div v-if="imagePreviews[setting.key] || setting.value" class="mb-3">
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ imagePreviews[setting.key] ? 'Preview Logo Baru:' : 'Logo saat ini:' }}
                                </p>
                                <div class="relative max-w-xs">
                                    <img 
                                        :src="imagePreviews[setting.key] || (setting.value.startsWith('http') ? setting.value : `/storage/${setting.value}`)"
                                        alt="Logo Preview"
                                        class="w-full h-auto rounded border border-gray-200"
                                    />
                                    <!-- Clear Preview Button -->
                                    <UButton
                                        v-if="imagePreviews[setting.key]"
                                        icon="i-lucide-x"
                                        color="error"
                                        size="xs"
                                        class="absolute top-2 right-2"
                                        @click="clearImagePreview(setting.key)"
                                    />
                                </div>
                            </div>
                            
                            <!-- Upload New Logo -->
                            <div>
                                <input 
                                    type="file"
                                    ref="fileInputs"
                                    accept="image/*"
                                    @change="handleImageUpload($event, setting.key)"
                                    class="block w-full text-sm"
                                />
                                <p class="text-xs text-gray-500 mt-1">Upload logo website (optional)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Settings -->
                <div v-if="settings.contact" class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Kontak</h3>
                    
                    <div v-for="setting in settings.contact" :key="setting.key" class="grid grid-cols-3 gap-4 items-start">
                        <label class="text-sm font-medium pt-2">{{ formatLabel(setting.key) }}</label>
                        
                        <UInput 
                            v-if="setting.type === 'text'"
                            v-model="form.settings[setting.key]"
                            class="col-span-2"
                        />
                        
                        <UTextarea 
                            v-else-if="setting.type === 'textarea'"
                            v-model="form.settings[setting.key]"
                            :rows="2"
                            class="col-span-2"
                        />
                    </div>
                </div>

                <!-- Social Media -->
                <div v-if="settings.social" class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Social Media</h3>
                    
                    <div v-for="setting in settings.social" :key="setting.key" class="grid grid-cols-3 gap-4 items-center">
                        <label class="text-sm font-medium">{{ formatLabel(setting.key) }}</label>
                        <UInput 
                            v-model="form.settings[setting.key]"
                            placeholder="https://..."
                            class="col-span-2"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-2 pt-4 border-t">
                    <UButton 
                        type="submit"
                        label="Simpan Perubahan"
                        icon="i-lucide-save"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    settings: Object,
});

// Initialize form with current settings
const formSettings = {};
Object.values(props.settings).flat().forEach(setting => {
    formSettings[setting.key] = setting.value;
});

const form = useForm({
    settings: formSettings,
});

// Parse misi JSON for editing
const parsedMisi = ref([]);
if (form.settings.misi) {
    try {
        parsedMisi.value = JSON.parse(form.settings.misi);
    } catch (e) {
        parsedMisi.value = [];
    }
}

const addMisiItem = () => {
    parsedMisi.value.push('');
};

const removeMisiItem = (index) => {
    parsedMisi.value.splice(index, 1);
};

const formatLabel = (key) => {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
        .replace('Site', '')
        .replace('Contact', '')
        .replace('Social', '')
        .replace('Stats', '')
        .trim();
};

const imagePreviews = ref({});
const fileInputs = ref([]);

const handleImageUpload = (event, key) => {
    const file = event.target.files[0];
    if (file) {
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value[key] = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // Update form
        form.settings[key] = file;
    }
};

const clearImagePreview = (key) => {
    imagePreviews.value[key] = null;
    form.settings[key] = null;
    
    // Clear file input if possible (optional but good for UX)
    // Note: This is tricky with v-for refs but not critical
};


const submit = () => {
    // Update misi JSON before submit
    if (parsedMisi.value.length > 0) {
        form.settings.misi = JSON.stringify(parsedMisi.value);
    }

    // We need to transform the data manually because inertia's forceFormData 
    // doesn't handle array of objects transformation exactly how we need it for validation
    // or rather, we were not creating the structure expected by the backend validation
    
    // Create a new form data object to mimic the structure
    // actually, let's use router.post directly with FormData to be safe and precise
    // BUT maintaining Inertia's form helper is better for loading states/errors.
    
    // Strategy: Transform the form data to match what the controller expects:
    // settings[0][key] = 'site_name', settings[0][value] = '...'
    
    form.transform((data) => {
        const formData = new FormData();
        
        let index = 0;
        Object.entries(data.settings).forEach(([key, value]) => {
            formData.append(`settings[${index}][key]`, key);
            
            // Handle file upload or text value
            if (value instanceof File) {
                 formData.append(`settings[${index}][value]`, value); // Sending file as value, or maybe specialized key?
                 // Wait, controller checks: if ($request->hasFile("settings.{$settingModel->key}"))
                 // This logic in controller is checking `settings.site_logo` directly in request root...
                 // No, update method validates 'settings.*.value'. 
                 
                 // Let's look at controller logic again.
                 // The controller iterates validated['settings'].
                 // AND checks $request->hasFile("settings.{$settingModel->key}") ?? That seems wrong if structure is settings[index][key/value]
                 
                 // Let's FIX the Frontend to send a clean array structure, 
                 // AND send files separately or within the structure correctly.
                 
                 // If we send `settings[0][value]` as a File object, Laravel handles it.
                 formData.append(`settings[${index}][value]`, value);
            } else {
                 formData.append(`settings[${index}][value]`, value !== null ? value : '');
            }
            index++;
        });
        
        return formData; // Inertia will use this FormData
    }).post('/dashboard/cms/settings', {
        forceFormData: true,
    });
};
</script>
