<?php

namespace Loot\Tenge\Actions;

use Illuminate\Http\Request;

abstract class Action
{
    abstract public function handler($paymentId, Request $request);

    public function __invoke($paymentId, Request $request)
    {
        try {
            return $this->handler(...func_get_args());
        } catch (\Exception $exception) {
            resolve('tenge_logger')->info($exception->getMessage());

            return $exception->getMessage();
        }
    }
}
