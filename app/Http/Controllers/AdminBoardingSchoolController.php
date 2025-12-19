<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminBoardingSchool\StoreAdminRequest;
use App\Models\BoardingSchool;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminBoardingSchoolController extends Controller
{
    /**
     * Display a listing of admins for a boarding school
     */
    public function index(BoardingSchool $boardingSchool)
    {
        $admins = $boardingSchool->admins()
            ->select('users.id', 'users.name', 'users.email', 'users.phone_number', 'users.gender', 'users.profile_photo')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'gender' => $user->gender_label,
                'avatar' => $user->profile_photo_url ?? "https://api.dicebear.com/7.x/avataaars/svg?seed={$user->email}",
            ]);

        return response()->json([
            'admins' => $admins,
        ]);
    }

    /**
     * Store a newly created admin for boarding school
     */
    public function store(StoreAdminRequest $request, BoardingSchool $boardingSchool)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $data['gender'],
            ]);

            // Assign role admin-pondok
            $user->assignRole('admin-pondok');

            // Attach to boarding school with UUID
            DB::table('admin_boarding_schools')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'boarding_school_id' => $boardingSchool->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Admin berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Gagal menambahkan admin: '.$e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Remove admin from boarding school and delete user
     */
    public function destroy(BoardingSchool $boardingSchool, User $admin)
    {
        try {
            // Detach from boarding school
            $boardingSchool->admins()->detach($admin->id);

            // Delete user account
            $admin->delete();

            return redirect()->back()
                ->with('success', 'Admin berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Gagal menghapus admin: '.$e->getMessage(),
            ]);
        }
    }
}
