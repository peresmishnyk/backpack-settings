<?php

/*
|--------------------------------------------------------------------------
| Peresmishnyk\BackpackSetting Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Peresmishnyk\BackpackSetting package.
|
*/

/**
 * User Routes
 */

// Route::group([
//     'middleware'=> array_merge(
//     	(array) config('backpack.base.web_middleware', 'web'),
//     ),
// ], function() {
//     Route::get('something/action', \Peresmishnyk\BackpackSetting\Http\Controllers\SomethingController::actionName());
// });


/**
 * Admin Routes
 */

// Route::group([
//     'prefix' => config('backpack.base.route_prefix', 'admin'),
//     'middleware' => array_merge(
//         (array) config('backpack.base.web_middleware', 'web'),
//         (array) config('backpack.base.middleware_key', 'admin')
//     ),
// ], function () {
//     Route::crud('some-entity-name', \Peresmishnyk\BackpackSetting\Http\Controllers\Admin\EntityNameCrudController::class);
// });