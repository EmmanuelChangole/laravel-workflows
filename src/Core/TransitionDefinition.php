<?php

namespace Changole\Workflows\Core;

use Changole\Workflows\Contracts\Guard;

final class TransitionDefinition
{
    private string $name;

    /** @var array<string> */
    private array $from = [];

    private string $to = '';

    /** @var array<callable|Guard> */
    private array $guards = [];

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function from(string|array $states): self
    {
        $this->from = is_array($states) ? array_values($states) : [$states];

        return $this;
    }

    public function to(string $state): self
    {
        $this->to = $state;

        return $this;
    }

    public function guard(callable|Guard $guard, ?string $message = null): self
    {
        if (is_callable($guard) && ! $guard instanceof Guard) {
            $guard = new CallableGuard($guard, $message);
        }

        $this->guards[] = $guard;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string>
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return array<callable|Guard>
     */
    public function getGuards(): array
    {
        return $this->guards;
    }
}
