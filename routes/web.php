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

Route::get('/admin/accreditations/{accreditation}/export/{type}', [\App\Http\Controllers\AccreditationExportController::class, 'export'])
    ->middleware('auth')
    ->name('accreditations.export');

Route::get('/admin/rtm-meetings/{meeting}/export/minutes', [\App\Http\Controllers\QualityReportExportController::class, 'exportRtmMinutes'])
    ->middleware('auth')
    ->name('rtm-meetings.export-minutes');

Route::get('/admin/ami-cycles/{cycle}/export/summary', [\App\Http\Controllers\QualityReportExportController::class, 'exportAmiSummary'])
    ->middleware('auth')
    ->name('ami-cycles.export-summary');

Route::post('/admin/impersonate/stop', [ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonation.stop');

Route::post('/admin/impersonate/{user}', [ImpersonationController::class, 'start'])
    ->middleware('auth')
    ->name('impersonation.start');
