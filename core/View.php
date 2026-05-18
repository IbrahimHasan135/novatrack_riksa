<?php

namespace Core;

class View
{
    public static function render(string $path, array $data = []): void
    {
        Module::view($path, $data);
    }
}
