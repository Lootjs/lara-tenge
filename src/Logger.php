<?php
namespace Loot\Tenge;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ {
    NullHandler, StreamHandler
};

class Logger {
    /**
     * @var \Monolog\Logger
     */
    private $manager;

    public function __construct() {
        $this->manager = new \Monolog\Logger('lara-tenge');
        $handler = new NullHandler;

        if (config('tenge.logging')) {
            try {
                $file = 'logs/tenge/lara-tenge__'.date('m.Y').'.log';
                $handler = new StreamHandler(storage_path($file));
                $output = "[%datetime%] %channel%.%level_name%: %message% %context.user%\n";
                $handler->setFormatter(new LineFormatter($output));
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }

        $this->manager->pushHandler($handler);
    }

    public function getManager() {
        return $this->manager;
    }
}
