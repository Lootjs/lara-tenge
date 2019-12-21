<?php

namespace Loot\Tenge;

use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class DeterminateDriver
{
    /**
     * @var Request
     */
    protected $request;

    protected $checkers = [
        'isWalletone',
    ];

    /**
     * DeterminateDriver constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Find a driver, that handle request.
     *
     * @return Fluent
     */
    public function process(): Fluent
    {
        $result = [];

        foreach ($this->checkers as $checker) {
            if ($checkerResult = call_user_func([$this, $checker])) {
                $result = $checkerResult;

                break;
            }
        }

        return new Fluent($result);
    }

    /**
     * Walletone checker.
     *
     * @return array
     */
    protected function isWalletone()
    {
        if ($this->request->has('WMI_PAYMENT_NO')) {
            return [
                'payment_id' => $this->request->input('WMI_PAYMENT_NO'),
                'driver' => 'walletone',
            ];
        }
    }
}
