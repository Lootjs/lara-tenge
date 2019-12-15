<?php
namespace Loot\Tenge;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TengePayment
 * @package Loot\Tenge
 *
 * @property $id int
 * @property $payment_id int
 * @property $status int
 * @property $driver string
 * @property $data array
 */

class TengePayment extends Model
{
    const STATUS_RECEIVED = 0;
    const STATUS_SETTLED = 1;
    const STATUS_CANCELLED = 2;

    public $timestamps = false;
    public $casts = [
        'data' => 'array',
    ];

    protected $fillable = [
        'payment_id',
        'amount',
        'status',
        'driver',
        'data',
        'created_at',
        'updated_at',
        'approved_at',
        'canceled_at',
        'failed_at',
    ];

    public function setApproveStatus() {
        $this->update([
            'status' => self::STATUS_SETTLED,
            'approved_at' => now(),
        ]);
    }

    public function setCanceledStatus() {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'canceled_at' => now(),
        ]);
    }
}
