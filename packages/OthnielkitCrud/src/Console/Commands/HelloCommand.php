<?php

namespace Othnielkit\Crud\Console\Commands;

use Illuminate\Console\Command;

class HelloCommand extends Command
{
    protected $signature = 'hello';
    protected $description = 'Test du package Othnielkit CRUD';

    public function handle()
    {
        $this->info('🎉 Le package Othnielkit CRUD est installé avec succès !');
    }
}