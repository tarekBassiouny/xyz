<?php

use App\Http\Controllers\Admin\SettingsPreviewController;
use App\Http\Controllers\Admin\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['require.permission:settings.manage', 'scope.system'])->group(function (): void {
    Route::get('/settings/preview', SettingsPreviewController::class);
    Route::get('/settings', [SystemSettingController::class, 'index']);
    Route::post('/settings', [SystemSettingController::class, 'store']);
    Route::get('/settings/{systemSetting}', [SystemSettingController::class, 'show'])->whereNumber('systemSetting');
    Route::put('/settings/{systemSetting}', [SystemSettingController::class, 'update'])->whereNumber('systemSetting');
    Route::delete('/settings/{systemSetting}', [SystemSettingController::class, 'destroy'])->whereNumber('systemSetting');
});
