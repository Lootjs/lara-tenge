<?php

namespace Loot\Tenge\Test;

use Loot\Tenge\Drivers\DriverInterface;
use Loot\Tenge\Tenge;

class MainTest extends BaseTest
{
    /** @test */
    public function check_correct_gateway(): void
    {
        $this->assertInstanceOf(DriverInterface::class, Tenge::with('epay'));
    }

    /** @test */
    public function check_wrong_gateway(): void
    {
        $this->expectException(\Exception::class);

        Tenge::with(uniqid());
    }
}
