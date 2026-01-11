<?php

namespace App\Http\Controllers;

use App\Models\BoardingSchool;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LetterSettingController extends Controller
{
    /**
     * Show the letter settings form
     */
    public function edit(): Response
    {
        $user = auth()->user();
        
        // Get admin's boarding school
        $boardingSchool = $user->hasRole('super-admin')
            ? BoardingSchool::first()
            : $user->boardingSchools()->first();
            
        if (!$boardingSchool) {
            abort(404, 'Pondok tidak ditemukan');
        }

        return Inertia::render('Dashboard/Settings/Letter', [
            'boardingSchool' => $boardingSchool,
        ]);
    }
    
    /**
     * Update letter settings
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Get admin's boarding school
        $boardingSchool = $user->hasRole('super-admin')
            ? BoardingSchool::first()
            : $user->boardingSchools()->first();
            
        if (!$boardingSchool) {
            abort(404, 'Pondok tidak ditemukan');
        }
        
        // Validate
        $validated = $request->validate([
            'letter_head_name' => 'required|string|max:255',
            'letter_secretary_name' => 'required|string|max:255',
        ]);
        
        // Update
        $boardingSchool->update($validated);
        
        return back()->with('success', 'Pengaturan surat berhasil diperbarui.');
    }
}
