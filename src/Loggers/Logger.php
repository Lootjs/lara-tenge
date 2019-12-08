<?php
namespace Loot\Tenge\Loggers;

class Logger {
    /**
     * @var $manager LoggerInterface::class
     */
    public $manager;

    public function __construct($logger) {
        if (! array_key_exists(LoggerInterface::class, class_implements($logger))) {
            throw new \Exception(
                sprintf('Logger [%s] should implement [%s]', $logger, LoggerInterface::class)
            );
        }

        $this->manager = new $logger;
    }

    public function getManager() {
        return $this->manager;
    }
}
