<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInvoicePayment extends Model
{
    protected $fillable = [
        'student_invoice_id',
        'student_id',
        'user_id',
        'amount',
        'payment_type',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Get the invoice that owns this payment
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    /**
     * Get the student who made this payment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user (admin) who recorded this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
