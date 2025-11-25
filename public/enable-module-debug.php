<?php

// Temporary debug script to enable NsCustomFields module
// Access this via: https://yourdomain.com/enable-module-debug.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $modulesService = app()->make(App\Services\ModulesService::class);
    
    echo "Attempting to enable NsCustomFields module...\n\n";
    
    $result = $modulesService->enable('NsCustomFields');
    
    echo "Result:\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
}
