<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('cache-test-save-key', function (Request $request) {
    if ($request->cache) {
        Cache::put('cache-test-save-key', $request->cache, 60);

        return 'key saved';
    }
});

Route::get('cache-test-retrieve-key', function () {
    return Cache::get('cache-test-save-key', 'not-set-yet');
});
