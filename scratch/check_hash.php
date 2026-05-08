<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\Hash;

$user = new User();
$user->password = Hash::make('secret');
echo "Password with Hash::make: " . $user->password . "\n";

$user2 = new User();
$user2->password = 'secret';
echo "Password without Hash::make: " . $user2->password . "\n";

if (Hash::check('secret', $user->password)) {
    echo "Hash::check matches Hash::make (Good)\n";
} else {
    echo "Hash::check fails Hash::make (Double hashed!)\n";
}
