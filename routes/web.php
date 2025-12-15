<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/show-stock', [StockController::class, 'showStock'])->name('show-stock');
Route::get('/show-stock/{id}/edit', [StockController::class, 'edit'])->name('stock.edit');
Route::put('/show-stock/{id}/update-movement', [StockController::class, 'updateMovement'])->name('stock.updateMovement');
Route::post('/show-stock/{id}/add', [StockController::class, 'addStock'])->name('stock.addStock');
Route::post('/show-stock/store', [StockController::class, 'store'])->name('stock.store');

Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
Route::post('/cashier/add', [CashierController::class, 'add'])->name('cashier.add');

route::get('/report', [ReportController::class, 'categorySummary'])->name('report');
Route::get('/summary/export', [ReportController::class, 'export'])->name('summary.export');
Route::get('/report/summary/detail/{category}', [ReportController::class, 'categoryDetail'])->name('summary.detail');
Route::get('/report/summary/detail/{category}/export', [ReportController::class, 'exportDetail'])->name('summary.exportDetail');

Route::get('/receipts', [ReceiptController::class,'index'])->name('receipts.index');
Route::get('/receipts/export', [ReceiptController::class,'export'])->name('receipts.export');
Route::get('/receipts/{bill_id}/detail', [ReceiptController::class,'detail'])->name('receipts.detail');
Route::get('/receipts/{bill_id}/detail/export', [ReceiptController::class,'exportDetail'])->name('receipts.detail.export');
Route::get('receipts/detail/pdf/{bill_id}', [ReceiptController::class, 'exportDetailPdf'])->name('receipts.detail.pdf');
Route::get(
    '/receipts/{bill_id}/pdf',
    [ReceiptController::class, 'exportPdf']
)->name('receipts.detail.pdf');
Route::get('/receipts/{bill_id}/tax', [ReceiptController::class, 'exportTax'])
    ->name('receipts.detail.tax');


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/reset-password', [ResetPasswordController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');