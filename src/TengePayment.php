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
 */

class TengePayment extends Model
{
    const STATUS_RECEIVED = 0;
    const STATUS_SETTLED = 1;
    const STATUS_CANCELLED = 2;

    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'amount',
        'status',
    ];

    public function setApproveStatus() {
        $this->update([
            'status' => self::STATUS_SETTLED,
        ]);
    }
}
