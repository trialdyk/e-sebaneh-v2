<?php

namespace App\Http\Controllers;

use App\Http\Requests\BoardingSchool\StoreBoardingSchoolRequest;
use App\Http\Requests\BoardingSchool\UpdateBoardingSchoolRequest;
use App\Models\BoardingSchool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BoardingSchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $boardingSchools = BoardingSchool::withCounts()
            ->latest()
            ->get();

        return Inertia::render('Dashboard/BoardingSchool/Index', [
            'boardingSchools' => $boardingSchools,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Dashboard/BoardingSchool/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardingSchoolRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = Storage::disk('public')->putFile('boarding-schools', $request->file('photo'));
            }

            // Create boarding school
            $boardingSchool = BoardingSchool::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]);

            if (! empty($data['facilities'])) {
                foreach ($data['facilities'] as $facilityName) {
                    $boardingSchool->facilities()->create([
                        'name' => $facilityName,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('boarding-schools.index')
                ->with('success', 'Pondok berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Gagal menyimpan pondok: '.$e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BoardingSchool $boardingSchool): Response
    {
        $boardingSchool->load(['admins:id,name,email,profile_photo', 'facilities']);

        // Format admins
        $boardingSchool->admins = $boardingSchool->admins->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->profile_photo_url ?? "https://api.dicebear.com/7.x/avataaars/svg?seed={$user->email}",
        ]);

        return Inertia::render('Dashboard/BoardingSchool/Show', [
            'boardingSchool' => $boardingSchool,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoardingSchool $boardingSchool): Response
    {
        // Get facilities as array of strings directly
        $facilities = $boardingSchool->facilities()->pluck('name')->values()->all();

        return Inertia::render('Dashboard/BoardingSchool/Edit', [
            'boardingSchool' => array_merge($boardingSchool->toArray(), [
                'facilities' => $facilities,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardingSchoolRequest $request, BoardingSchool $boardingSchool)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($boardingSchool->photo) {
                    Storage::disk('public')->delete($boardingSchool->photo);
                }

                $data['photo'] = Storage::disk('public')->putFile('boarding-schools', $request->file('photo'));
            } else {
                // Keep existing photo
                unset($data['photo']);
            }

            // Update boarding school
            $boardingSchool->update([
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'] ?? null,
                'photo' => $data['photo'] ?? $boardingSchool->photo,
            ]);

            $boardingSchool->facilities()->delete();
            if (! empty($data['facilities'])) {
                foreach ($data['facilities'] as $facilityName) {
                    $boardingSchool->facilities()->create([
                        'name' => $facilityName,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('boarding-schools.show', $boardingSchool)
                ->with('success', 'Pondok berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Gagal memperbarui pondok: '.$e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardingSchool $boardingSchool)
    {
        try {
            // Delete photo if exists
            if ($boardingSchool->photo) {
                Storage::disk('public')->delete($boardingSchool->photo);
            }

            // Delete boarding school (cascade will delete admins & facilities)
            $boardingSchool->delete();

            return redirect()->route('boarding-schools.index')
                ->with('success', 'Pondok berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Gagal menghapus pondok: '.$e->getMessage(),
            ]);
        }
    }
}
