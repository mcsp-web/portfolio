<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/work/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [Admin\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [Admin\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project:id}/edit', [Admin\ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project:id}', [Admin\ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project:id}', [Admin\ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::patch('/projects/{project:id}/toggle', [Admin\ProjectController::class, 'toggle'])->name('projects.toggle');
    Route::patch('/projects/{project:id}/move', [Admin\ProjectController::class, 'move'])->name('projects.move');

    Route::post('/projects/{project:id}/screenshots', [Admin\ScreenshotController::class, 'store'])->name('screenshots.store');
    Route::patch('/screenshots/{screenshot}', [Admin\ScreenshotController::class, 'update'])->name('screenshots.update');
    Route::patch('/screenshots/{screenshot}/move', [Admin\ScreenshotController::class, 'move'])->name('screenshots.move');
    Route::delete('/screenshots/{screenshot}', [Admin\ScreenshotController::class, 'destroy'])->name('screenshots.destroy');

    Route::post('/projects/{project:id}/features', [Admin\FeatureController::class, 'store'])->name('features.store');
    Route::patch('/projects/{project:id}/feature-groups', [Admin\FeatureController::class, 'group'])->name('features.group');
    Route::patch('/projects/{project:id}/feature-groups/move', [Admin\FeatureController::class, 'moveGroup'])->name('features.moveGroup');
    Route::patch('/features/{feature}', [Admin\FeatureController::class, 'update'])->name('features.update');
    Route::delete('/features/{feature}', [Admin\FeatureController::class, 'destroy'])->name('features.destroy');

    Route::get('/stack', [Admin\TechnologyController::class, 'index'])->name('technologies.index');
    Route::post('/stack', [Admin\TechnologyController::class, 'store'])->name('technologies.store');
    Route::put('/stack/{technology}', [Admin\TechnologyController::class, 'update'])->name('technologies.update');
    Route::delete('/stack/{technology}', [Admin\TechnologyController::class, 'destroy'])->name('technologies.destroy');

    Route::get('/profile', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/profile', [Admin\SettingsController::class, 'update'])->name('settings.update');
});
