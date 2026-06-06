<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'amount',
        'payment_type',
        'payment_method',
        'receipt_number',
        'payment_date',
        'user_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReceiptNumber(): string
    {
        return DB::transaction(function () {
            $lastPayment = self::whereYear('created_at', now()->year)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $nextNumber = 1;
            if ($lastPayment && preg_match('/RCP-\d+-(\d+)$/', $lastPayment->receipt_number, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            return 'RCP-'.now()->year.'-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
