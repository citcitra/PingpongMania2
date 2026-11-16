
<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Auth::routes();

Route::get('/home', function () {
    $role = Auth::user()->role;

    if (in_array($role, ['super_admin', 'admin'])) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('user.dashboard');
})->middleware('auth')->name('home');


/*
|--------------------------------------------------------------------------
| Route khusus USER
|--------------------------------------------------------------------------
| Hanya pengguna biasa yang dapat mengakses dashboard dan kontrol mesin.
*/

Route::middleware(['auth', 'user'])->group(function () {

    Route::get('/user-dashboard', 'PingPongController@dashboard')
        ->name('user.dashboard');

    Route::post('/machine/control', 'PingPongController@control')
        ->middleware('throttle:20,1')
        ->name('machine.control');

    Route::post('/machine/stop', 'PingPongController@stop')
        ->name('machine.stop');

    Route::post('/waiting-list/join/{slot_id}', 'WaitingListController@join')
        ->name('waiting-list.join');

    Route::post('/waiting-list/cancel/{id}', 'WaitingListController@cancel')
        ->name('waiting-list.cancel');
});


/*
|--------------------------------------------------------------------------
| Route yang dapat digunakan semua role yang sudah login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profil', 'ProfileController@edit')
        ->name('profile.edit');

    Route::put('/profil', 'ProfileController@update')
        ->name('profile.update');

    Route::post('/profil/password', 'ProfileController@password')
        ->name('profile.password');
});


/*
|--------------------------------------------------------------------------
| Route khusus ADMIN dan SUPER ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', 'AdminController@dashboard')
        ->name('admin.dashboard');

    Route::get('/users', 'AdminController@users')
        ->name('admin.users');

    Route::get('/riwayat-latihan', 'PingPongController@history')
        ->name('training.history');

    Route::post('/users/{id}/role', 'AdminController@updateRole')
        ->middleware('super_admin')
        ->name('admin.users.role');

    Route::delete('/users/{id}', 'AdminController@destroy')
        ->name('admin.users.destroy');
});


/*
|--------------------------------------------------------------------------
| Route khusus SUPER ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('super-admin')->middleware(['auth', 'super_admin'])->group(function () {

    Route::get('/admins', 'SuperAdminController@index')
        ->name('super-admin.admins.index');

    Route::get('/admins/create', 'SuperAdminController@create')
        ->name('super-admin.admins.create');

    Route::post('/admins', 'SuperAdminController@store')
        ->name('super-admin.admins.store');

    Route::get('/admins/{id}/edit', 'SuperAdminController@edit')
        ->name('super-admin.admins.edit');

    Route::put('/admins/{id}', 'SuperAdminController@update')
        ->name('super-admin.admins.update');

    Route::delete('/admins/{id}', 'SuperAdminController@destroy')
        ->name('super-admin.admins.destroy');
});
