<?php

namespace App\Support;

class DocumentNumber
{
    public static function make(string $prefix): string
    {
        return $prefix.now()->format('YmdHis').'-'.random_int(100, 999);
    }
}
