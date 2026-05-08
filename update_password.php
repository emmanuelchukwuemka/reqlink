<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Domains\Users\Models\User::where('email', 'nwekee125@gmail.com')->first();
if ($user) {
    $user->password = '12345678';
    $user->save();
    echo "Password updated successfully.";
} else {
    echo "User not found.";
}
