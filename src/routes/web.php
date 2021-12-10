<?php

//php artisan route:list

Route::get('import_wordpress', 'DevStetic\WpImporter\Http\WpIController@create')->middleware(['web']);

Route::post('import_wordpress/store', 'DevStetic\WpImporter\Http\WpIController@store')->middleware(['web']);

