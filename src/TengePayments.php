<?php
namespace Appstract\Options;

use Illuminate\Database\Eloquent\Model;

class TengePayments extends Model
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
}
