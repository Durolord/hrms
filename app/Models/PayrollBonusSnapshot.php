<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PayrollBonusSnapshot extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_id',
        'bonus_id',
        'name',
        'amount',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}