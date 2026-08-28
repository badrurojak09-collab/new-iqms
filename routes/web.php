<?php

use App\Http\Controllers\DocumentOutputController;
use App\Http\Controllers\DownloadEvidenceDocumentController;
use App\Http\Controllers\ImpersonationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/evidence-versions/{evidenceVersion}/download', DownloadEvidenceDocumentController::class)
    ->middleware(['auth', 'signed'])
    ->name('evidence-versions.download');

Route::get('/admin/document-output/{request}/preview', [DocumentOutputController::class, 'preview'])
    ->middleware('auth')
    ->name('document-output.preview');

Route::get('/admin/document-output/{request}/download', [DocumentOutputController::class, 'download'])
    ->middleware('auth')
    ->name('document-output.download');

Route::post('/admin/impersonate/stop', [ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonation.stop');

Route::post('/admin/impersonate/{user}', [ImpersonationController::class, 'start'])
    ->middleware('auth')
    ->name('impersonation.start');
