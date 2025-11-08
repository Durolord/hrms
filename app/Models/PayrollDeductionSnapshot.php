<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PayrollDeductionSnapshot extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_id',
        'deduction_id',
        'name',
        'amount',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
    public function deduction()
    {
        return $this->belongsTo(Deduction::class);
    }
}