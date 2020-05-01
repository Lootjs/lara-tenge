<?php

Route::name('tenge.')->group(function (): void {
    Route::any('/lara-tenge/approve-link/{paymentId?}', \Loot\Tenge\Actions\ApproveAction::class)->name('approvelink');
    Route::any('/lara-tenge/check-link/{paymentId}', \Loot\Tenge\Actions\CheckAction::class)->name('checklink');
    Route::any('/lara-tenge/fail-link/{paymentId}', \Loot\Tenge\Actions\FailAction::class)->name('faillink');
});
