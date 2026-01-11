<?php

namespace App\Http\Controllers\CMS;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController
{
    public function index()
    {
        $settings = SiteSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return Inertia::render('Dashboard/CMS/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($validated['settings'] as $setting) {
            $settingModel = SiteSetting::where('key', $setting['key'])->first();

            if (! $settingModel) {
                continue;
            }

            $value = $setting['value'];

            // Handle image upload
            if ($settingModel->type === 'image' && isset($setting['value']) && $setting['value'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($settingModel->value && Storage::disk('public')->exists($settingModel->value)) {
                    Storage::disk('public')->delete($settingModel->value);
                }

                // Store new image
                $value = $setting['value']->store('settings', 'public');
            } elseif ($settingModel->type === 'image' && is_string($value)) {
                // If string (existing path), keep it?
                // Or if it's confusing, maybe just don't update if it's not a file?
                // But the form sends string if no new file.
                // It sends "" (empty string) or the "path/to/image.jpg" ??

                // Frontend sends string if not a file.
                // We should keep the OLD value if the new value is not a file?
                // Wait, frontend sends: formData.append(`settings[${index}][value]`, value !== null ? value : '');
                // If value is existing path, it sends existing path.
                // So updating with $value is fine.
            }

            $settingModel->update(['value' => $value]);
        }

        return redirect()
            ->route('dashboard.cms.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui');
    }
}
