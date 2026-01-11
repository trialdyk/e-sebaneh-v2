<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Models\StudentViolation;
use Illuminate\Http\Request;

class StudentDetailController extends Controller
{
    // ==================== DISEASES ====================

    /**
     * Store a new disease record for a student.
     */
    public function storeDisease(Request $request, Student $student)
    {
        $validated = $request->validate([
            'disease_name' => 'required|string|max:255',
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'disease_name.required' => 'Nama penyakit wajib diisi',
        ]);

        $student->diseases()->create($validated);

        return back()->with('success', 'Riwayat penyakit berhasil ditambahkan.');
    }

    /**
     * Remove a disease record.
     */
    public function destroyDisease(Student $student, Disease $disease)
    {
        if ($disease->student_id !== $student->id) {
            abort(403);
        }

        $disease->delete();

        return back()->with('success', 'Riwayat penyakit berhasil dihapus.');
    }

    // ==================== PERMISSIONS (IZIN) ====================

    /**
     * Store a new permission record.
     */
    public function storePermission(Request $request, Student $student)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'reason' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration' => 'nullable|string',
        ], [
            'type.required' => 'Tipe izin wajib diisi',
            'reason.required' => 'Alasan wajib diisi',
        ]);

        $validated['status'] = 'approved';

        $student->permissions()->create($validated);

        return back()->with('success', 'Pengajuan izin berhasil ditambahkan.');
    }

    /**
     * Update permission status.
     */
    public function updatePermission(Request $request, Student $student, StudentPermission $permission)
    {
        if ($permission->student_id !== $student->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $permission->update($validated);

        return back()->with('success', 'Status izin berhasil diperbarui.');
    }

    /**
     * Remove a permission record.
     */
    public function destroyPermission(Student $student, StudentPermission $permission)
    {
        if ($permission->student_id !== $student->id) {
            abort(403);
        }

        $permission->delete();

        return back()->with('success', 'Data izin berhasil dihapus.');
    }

    /**
     * Mark permission as returned.
     */
    public function returnPermission(Request $request, Student $student, StudentPermission $permission)
    {
        if ($permission->student_id !== $student->id) {
            abort(403);
        }

        $validated = $request->validate([
            'returned_at' => 'required|date',
        ]);

        $permission->update(['returned_at' => $validated['returned_at']]);

        return back()->with('success', 'Status kembali berhasil dicatat.');
    }

    /**
     * Print permission letter.
     */
    public function printPermission(Student $student, StudentPermission $permission)
    {
        if ($permission->student_id !== $student->id) {
            abort(403);
        }

        try {
            $templatePath = public_path('templates/surat-izin.docx');
            if (!file_exists($templatePath)) {
                return back()->with('error', 'Template surat tidak ditemukan.');
            }

            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Load student with relations
            $student->load(['user', 'classrooms', 'boardingSchool']);
            
            // Get latest bedroom
            $bedRoom = $student->bedRooms()->latest()->first();
            
            // Get settings from boarding school
            $ketua = $student->boardingSchool->letter_head_name ?? '-';
            $sekretaris = $student->boardingSchool->letter_secretary_name ?? '-';

            $endDate = $permission->end_date ? \Carbon\Carbon::parse($permission->end_date)->translatedFormat('d F Y, \p\u\k\u\l H:i') : '-';
            
            $templateProcessor->setValue('name', $student->user->name ?? '-');
            $templateProcessor->setValue('nis', $student->student_id ?? '-');
            $templateProcessor->setValue('bedroom', $bedRoom->name ?? '-');
            $templateProcessor->setValue('address', $student->address ?? '-');
            $templateProcessor->setValue('wali', $student->father_name ?? '-');
            $templateProcessor->setValue('reason', $permission->reason);
            $templateProcessor->setValue('durasi', $permission->duration ?? '-');
            $templateProcessor->setValue('back', $endDate);
            $templateProcessor->setValue('date', now()->translatedFormat('d F Y'));
            $templateProcessor->setValue('head_name', $ketua ?? '-');
            $templateProcessor->setValue('secretary_name', $sekretaris ?? '-');

            $fileName = 'Surat Izin - ' . ($student->user->name ?? 'Student') . '.docx';
            $tempPath = storage_path('app/public/' . $fileName);
            
            // Ensure directory exists
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generating permission letter: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat surat: ' . $e->getMessage());
        }
    }

    // ==================== VIOLATIONS (PELANGGARAN) ====================

    /**
     * Store a new violation record.
     */
    public function storeViolation(Request $request, Student $student)
    {
        $validated = $request->validate([
            'violation_date' => 'required|date',
            'description' => 'required|string',
            'punishment' => 'nullable|string',
            'points' => 'nullable|integer|min:0',
        ], [
            'violation_date.required' => 'Tanggal pelanggaran wajib diisi',
            'description.required' => 'Deskripsi pelanggaran wajib diisi',
        ]);

        $student->violations()->create($validated);

        return back()->with('success', 'Data pelanggaran berhasil ditambahkan.');
    }

    /**
     * Remove a violation record.
     */
    public function destroyViolation(Student $student, StudentViolation $violation)
    {
        if ($violation->student_id !== $student->id) {
            abort(403);
        }

        $violation->delete();

        return back()->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    // ==================== MEMORIZES (HAFALAN) ====================

    /**
     * Store a new memorize record.
     */
    public function storeMemorize(Request $request, Student $student)
    {
        $validated = $request->validate([
            'juz' => 'nullable|integer|min:1|max:30',
            'surah_id' => 'nullable|exists:surahs,id',
            'verse_start' => 'nullable|integer|min:1',
            'verse_end' => 'nullable|integer|min:1|gte:verse_start',
            'notes' => 'nullable|string',
            'memorize_date' => 'nullable|date',
        ]);

        $student->memorizes()->create($validated);

        return back()->with('success', 'Catatan hafalan berhasil ditambahkan.');
    }

    /**
     * Remove a memorize record.
     */
    public function destroyMemorize(Student $student, \App\Models\StudentMemorize $memorize)
    {
        if ($memorize->student_id !== $student->id) {
            abort(403);
        }

        $memorize->delete();

        return back()->with('success', 'Catatan hafalan berhasil dihapus.');
    }
}
