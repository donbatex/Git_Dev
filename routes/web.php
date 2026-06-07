<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

<<<<<<< HEAD
require __DIR__.'/auth.php';
=======
// require __DIR__.'/auth.php';
>>>>>>> 07480e591029c77358bd75db8ee5f43005741d15
