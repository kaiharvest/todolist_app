<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('tasks', TaskController::class);

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])->name('tasks.toggle');
    Route::get('dashboard', [TaskController::class, 'dashboard'])->name('tasks.dashboard');
});
