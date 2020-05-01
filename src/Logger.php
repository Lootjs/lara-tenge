<?php

namespace Loot\Tenge;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;

class Logger
{
    /**
     * @var \Monolog\Logger
     */
    private $manager;

    public function __construct()
    {
        $this->manager = new \Monolog\Logger('lara-tenge');

        $this->manager->pushHandler(
            $this->getHandler()
        );
    }

    public function getManager()
    {
        return $this->manager;
    }

    private function getHandler()
    {
        if (config('tenge.logging')) {
            $file = 'logs/tenge/lara-tenge__'.date('m.Y').'.log';
            $handler = new StreamHandler(storage_path($file));
            $output = "[%datetime%] %channel%.%level_name%: %message% %context.user%\n";
            $handler->setFormatter(new LineFormatter($output));

            return $handler;
        }

        return new NullHandler;
    }
}
