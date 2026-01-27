<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'');
foreach ($tables as $table) {
    echo "Table: " . $table->table_name . "\n";
    $columns = Schema::getColumnListing($table->table_name);
    echo "  Columns: " . implode(', ', $columns) . "\n\n";
}
