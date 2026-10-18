<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('civicdesk:about', function (): void {
    $this->info('CivicDesk public service request portal');
})->purpose('Display CivicDesk application information');
