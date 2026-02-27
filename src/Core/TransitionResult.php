<?php

namespace Changole\Workflows\Core;

final class TransitionResult
{
    public function __construct(
        public bool $ok,
        public string $from,
        public string $to,
        public string $transition,
        public ?string $message = null,
    ) {
    }

    public static function success(string $transition, string $from, string $to, ?string $message = null): self
    {
        return new self(
            ok: true,
            from: $from,
            to: $to,
            transition: $transition,
            message: $message,
        );
    }

    public static function blocked(string $transition, string $from, string $to, ?string $message = null): self
    {
        return new self(
            ok: false,
            from: $from,
            to: $to,
            transition: $transition,
            message: $message,
        );
    }
}
