<?php

namespace App\Http\Controllers;

use App\Http\Requests\BedRoomRequest;
use App\Models\BedRoom;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BedRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        $query = BedRoom::query()
            ->withCount('students'); // Assuming students relationship exists based on model

        // Scope to admin's boarding schools
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('boarding_school_id', $boardingSchoolIds);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $bedRooms = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Dashboard/BedRoom/Index', [
            'bedRooms' => $bedRooms,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Dashboard/BedRoom/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BedRoomRequest $request)
    {
        $data = $request->validated();
        
        // Assign boarding school from auth user
        $boardingSchoolId = auth()->user()->boardingSchools()->first()?->id;
        
        if (!$boardingSchoolId) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses ke Pesantren manapun.']);
        }

        $data['boarding_school_id'] = $boardingSchoolId;

        BedRoom::create($data);

        return redirect()->route('dashboard.bed-rooms.index')
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BedRoom $bedRoom): Response
    {
        return Inertia::render('Dashboard/BedRoom/Edit', [
            'bedRoom' => $bedRoom,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BedRoomRequest $request, BedRoom $bedRoom)
    {
        $bedRoom->update($request->validated());

        return redirect()->route('dashboard.bed-rooms.index')
            ->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BedRoom $bedRoom)
    {
        try {
            $bedRoom->delete();
            
            return redirect()->route('dashboard.bed-rooms.index')
                ->with('success', 'Kamar berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kamar.']);
        }
    }
}
