<?php

declare(strict_types=1);

use Bites\Identity\Http\Controllers\IdentityController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/pic/{filename}', function (string $filename) {
    $url = sprintf('http://10.40.3.41:8080/%s.jpg', $filename);
    $response = Http::get($url);
    if (! $response->ok()) {
        abort(404);
    }
    return response(
        $response->body()
    )->header(
        'Content-Type',
        'image/jpeg'
    );
})->name('staff.pic');