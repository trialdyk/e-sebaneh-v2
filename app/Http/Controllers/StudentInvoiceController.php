<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentInvoiceRequest;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentInvoiceController extends Controller
{
    /**
     * Get the boarding school ID for the authenticated admin
     */
    private function getBoardingSchoolId()
    {
        $user = auth()->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;
        
        if (!$boardingSchoolId) {
            abort(403, 'Anda tidak memiliki akses ke pondok pesantren.');
        }
        
        return $boardingSchoolId;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $boardingSchoolId = $this->getBoardingSchoolId();

        $invoices = StudentInvoice::query()
            ->forBoardingSchool($boardingSchoolId)
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->name.'%');
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->withCount('payments')
            ->latest()
            ->paginate(15);

        return Inertia::render('Dashboard/Finance/StudentInvoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['name', 'type']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $boardingSchoolId = $this->getBoardingSchoolId();
        $classrooms = Classroom::where('boarding_school_id', $boardingSchoolId)->get();

        return Inertia::render('Dashboard/Finance/StudentInvoices/Create', [
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentInvoiceRequest $request)
    {
        $validated = $request->validated();
        $boardingSchoolId = $this->getBoardingSchoolId();

        DB::transaction(function () use ($validated, $boardingSchoolId) {
            $invoice = StudentInvoice::create([
                'boarding_school_id' => $boardingSchoolId,
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'for_gender' => $validated['for_gender'] ?? null,
            ]);

            // Attach classrooms if type is by_classroom
            if ($validated['type'] === 'by_classroom' && isset($validated['classrooms'])) {
                $invoice->classrooms()->attach($validated['classrooms']);
            }

            // Attach students if type is specific_students
            if ($validated['type'] === 'specific_students' && isset($validated['students'])) {
                $invoice->students()->attach($validated['students']);
            }
        });

        return redirect()->route('dashboard.finance.student-invoices.index')
            ->with('success', 'Tagihan berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentInvoice $studentInvoice)
    {
        $boardingSchoolId = $this->getBoardingSchoolId();

        if ($studentInvoice->boarding_school_id !== $boardingSchoolId) {
            abort(403);
        }

        $studentInvoice->load(['classrooms', 'students']);

        // Fetch targeted active students
        $query = \App\Models\Student::query()
            ->with(['user', 'classrooms'])
            ->where('status', 'active')
            ->where('boarding_school_id', $boardingSchoolId);

        // check if paid
        $query->withExists(['invoicePayments as is_paid' => function ($q) use ($studentInvoice) {
            $q->where('student_invoice_id', $studentInvoice->id);
        }]);

        // Apply filters based on invoice type
        if ($studentInvoice->type === 'by_gender') {
            $query->where('gender', $studentInvoice->for_gender);
        } elseif ($studentInvoice->type === 'by_classroom') {
            $query->whereHas('classrooms', function ($q) use ($studentInvoice) {
                $q->whereIn('classrooms.id', $studentInvoice->classrooms->pluck('id'));
            });
        } elseif ($studentInvoice->type === 'specific_students') {
            $query->whereIn('id', $studentInvoice->students->pluck('id'));
        }

        $students = $query->paginate(10);

        return Inertia::render('Dashboard/Finance/StudentInvoices/Show', [
            'invoice' => $studentInvoice,
            'students' => $students
        ]);
    }

    public function payOffline(Request $request, StudentInvoice $studentInvoice, $studentId, \App\Services\FinanceService $financeService)
    {
        $boardingSchoolId = $this->getBoardingSchoolId();
        
        if ($studentInvoice->boarding_school_id !== $boardingSchoolId) {
            abort(403);
        }

        // Validate student exists and is active
        $student = \App\Models\Student::with('user')->where('id', $studentId)
            ->where('boarding_school_id', $boardingSchoolId)
            ->firstOrFail();

        // Check if already paid
        $alreadyPaid = \App\Models\StudentInvoicePayment::where('student_invoice_id', $studentInvoice->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyPaid) {
            return back()->with('error', 'Santri ini sudah membayar tagihan ini.');
        }

        DB::transaction(function () use ($studentInvoice, $student, $financeService) {
            $payment = \App\Models\StudentInvoicePayment::create([
                'student_invoice_id' => $studentInvoice->id,
                'student_id' => $student->id,
                'user_id' => auth()->id(),
                'amount' => $studentInvoice->amount,
                'payment_type' => 'offline',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Finance System: Record Revenue
            $financeService->recordTransaction(
                accountSlug: 'student-invoices',
                amount: $studentInvoice->amount,
                type: \App\Enums\FinanceTransactionTypeEnum::CREDIT,
                description: "Pembayaran Tagihan (Offline): {$studentInvoice->name} - {$student->user->name}",
                user: auth()->user(),
                reference: $payment,
                boardingSchoolId: $studentInvoice->boarding_school_id
            );
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentInvoice $studentInvoice)
    {
        $boardingSchoolId = $this->getBoardingSchoolId();
        $classrooms = Classroom::where('boarding_school_id', $boardingSchoolId)->get();

        $studentInvoice->load(['classrooms:id', 'students.user:id,name']);

        return Inertia::render('Dashboard/Finance/StudentInvoices/Edit', [
            'invoice' => $studentInvoice,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentInvoiceRequest $request, StudentInvoice $studentInvoice)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $studentInvoice) {
            $studentInvoice->update([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'for_gender' => $validated['for_gender'] ?? null,
            ]);

            // Detach all and reattach
            $studentInvoice->classrooms()->detach();
            $studentInvoice->students()->detach();

            if ($validated['type'] === 'by_classroom' && isset($validated['classrooms'])) {
                $studentInvoice->classrooms()->attach($validated['classrooms']);
            }

            if ($validated['type'] === 'specific_students' && isset($validated['students'])) {
                $studentInvoice->students()->attach($validated['students']);
            }
        });

        return redirect()->route('dashboard.finance.student-invoices.index')
            ->with('success', 'Tagihan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentInvoice $studentInvoice)
    {
        $studentInvoice->delete();

        return redirect()->route('dashboard.finance.student-invoices.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    /**
     * Search students for selection (AJAX)
     */
    public function searchStudents(Request $request)
    {
        $boardingSchoolId = $this->getBoardingSchoolId();
        $search = $request->q;
        $classroomIds = $request->classrooms;

        $students = Student::query()
            ->with('user')
            ->where('boarding_school_id', $boardingSchoolId)
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('student_id', 'like', "%{$search}%");
            })
            ->when($classroomIds, function ($query, $classroomIds) {
                return $query->whereHas('classrooms', function ($q) use ($classroomIds) {
                    $q->whereIn('classrooms.id', $classroomIds);
                });
            })
            ->limit(20)
            ->get();

        $results = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'text' => $student->user->name.' ('.$student->student_id.')',
            ];
        });

        return response()->json($results);
    }
}
