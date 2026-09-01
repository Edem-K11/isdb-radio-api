<?php

use Illuminate\Support\Facades\Route;

// The only human-facing surface is the Filament admin panel.
Route::redirect('/', '/admin');
