<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

use function Illuminate\Support\php_binary;

class ServeCommand extends BaseServeCommand
{
    /**
     * Get the full server command.
     *
     * @return array<int, string>
     */
    protected function serverCommand()
    {
        return [
            php_binary(),
            '-S',
            $this->host().':'.$this->port(),
            base_path('bootstrap/dev-router.php'),
        ];
    }
}
