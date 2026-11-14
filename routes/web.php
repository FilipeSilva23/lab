<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliverLocationController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

Route::post('/idioma/{locale}', LocaleController::class)->name('locale.switch');


Route::get('/', function () {
    //if is authenticated, cant go to landing page
    if(auth()->check())
        return redirect()->route('dashboard');

    return view('index');
});

//route for who isnt logged in
//guest is from Laravel, not a custom middleware
Route::middleware('guest')->group(function () {
    // Show the login form view to the user
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    // Process the submitted login credentials
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

//route for who is logged in
//auth is from Laravel, not a custom middleware
//its a protection so guests cant access
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //creates the routes of each method in ItemController
    Route::resource('items', ItemController::class);

    //creates the routes of each method in CategoryController
    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('deliver-locations', DeliverLocationController::class)
        ->only(['store', 'update', 'destroy']);

    // PATCH -> partial update of an existing resource
    // Never GET for actions that change data and PATCH requires the CSRF token
    Route::patch('/dashboard/deliver-location', [DashboardController::class, 'updateDeliverLocation'])
        ->name('dashboard.deliver-location.update');

    // the admin changes the delivery location of another user (student or service)
    Route::patch('/users/{user}/deliver-location', [DashboardController::class, 'updateUserDeliverLocation'])
        ->name('users.deliver-location.update');
});

