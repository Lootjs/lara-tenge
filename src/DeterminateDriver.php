<?php
namespace Loot\Tenge;

use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class DeterminateDriver {

    /**
     * @var Request $request
     */
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function process(): Fluent {
        $result = [];

        if ($this->request->has('WMI_PAYMENT_NO')) {
            $result = [
                'payment_id' => $this->request->input('WMI_PAYMENT_NO'),
                'driver' => 'walletone',
            ];
        }

        return new Fluent($result);
    }
}
