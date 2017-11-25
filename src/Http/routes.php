<?php

use Illuminate\Support\Facades\Route;

Route::get('~scrutiny/check-probes', [
    'as'   => 'scrutinise::show-checks',
    'uses' => 'Scrutiny\Http\CheckProbesController@get',
]);