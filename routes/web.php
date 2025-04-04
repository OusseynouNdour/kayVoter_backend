<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

Route::get('/storage/programmes/{path}', function ($path) {
    $path = storage_path('app/public/programmes/'.$path);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf'
    ]);
})->where('path', '.*');
