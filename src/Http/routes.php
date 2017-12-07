<?php

use Illuminate\Support\Facades\Route;

// Link straight to the check page
Route::get('~scrutiny/check-probes', [
    'as'   => 'scrutinise::show-checks',
    'uses' => 'Scrutiny\Http\CheckProbesController@get',
]);