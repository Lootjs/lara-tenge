<?php

declare(strict_types=1);

namespace Loot\Tenge;

use Illuminate\Support\Str;

class Hook
{
    /**
     * @var array|string
     */
    private $resolver;

    public static function trigger(string $hook)
    {
        $instance = new self;
        $instance->resolver = $instance->parseHook(
            config('tenge.hooks.' . $hook) ?? [$instance, 'nullResolver']
        );

        return $instance;
    }

    public function with(...$args)
    {
        return call_user_func($this->resolver, ...$args);
    }

    private function parseHook($hook)
    {
        if (is_string($hook)) {
            return $this->parseString($hook);
        }

        return $this->parseArray($hook);
    }

    private function parseString(string $hook)
    {
        if ($this->isStaticMethod($hook)) {
            return $this->parseStaticMethod($hook);
        }

        return $this->parseInstanceMethod($hook);
    }

    private function isStaticMethod(string $hook): bool
    {
        return Str::contains($hook, '::');
    }

    private function parseStaticMethod(string $hook): array
    {
        return explode('::', $hook);
    }

    private function parseInstanceMethod(string $hook): array
    {
        return explode('@', $hook);
    }

    private function parseArray(array $hook): array
    {
        [$class, $method] = $hook;
        $reflection = new \ReflectionMethod($class, $method);

        if (! $reflection->isStatic()) {
            $class = new $class;
        }

        return [$class, $method];
    }

    public function nullResolver(...$args)
    {
        return null;
    }
}
