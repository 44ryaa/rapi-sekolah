<?php

declare(strict_types=1);

abstract class Controller
{
    protected function user(): array
    {
        return auth();
    }

    protected function id(string $key = 'id'): int
    {
        return (int) ($_POST[$key] ?? $_GET[$key] ?? 0);
    }

    protected function str(string $key): string
    {
        return trim((string) ($_POST[$key] ?? ''));
    }

    protected function back(string $route, string $type, string $msg, array $q = []): never
    {
        flash($type, $msg);
        redirect($route, $q);
    }
}
