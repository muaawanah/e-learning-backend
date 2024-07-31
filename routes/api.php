<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExamController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('check-phone', [AuthController::class, 'checkPhone']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/latest', [CourseController::class, 'latest']);
Route::get('/courses/{course}', [CourseController::class, 'show']); // Public route

Route::get('/category-list', [CategoryController::class, 'index']);
Route::get('category/{category}', [CategoryController::class, 'show']);

Route::get('/galleries', [GalleryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    Route::post('/user/photo', [UserController::class, 'uploadPhoto']);
    
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    Route::post('/courses/{course}/thumbnail', [CourseController::class, 'uploadThumbnail']);

    Route::apiResource('category', CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::post('category', [CategoryController::class, 'store']);
    Route::put('category/{category}', [CategoryController::class, 'update']);
    Route::delete('category/{category}', [CategoryController::class, 'destroy']);

    Route::get('/courses/{course}/modules', [ModuleController::class, 'index']);
    Route::apiResource('modules', ModuleController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::get('/modules/{module}/lectures', [LectureController::class, 'index']);
    Route::post('/modules/{module}/lectures', [LectureController::class, 'store']);
    
    Route::get('/lectures/{lecture}', [LectureController::class, 'show']);

    Route::put('/modules/{module}/lectures/{lecture}', [LectureController::class, 'update']);
    Route::delete('/modules/{module}/lectures/{lecture}', [LectureController::class, 'destroy']);
    Route::post('lectures/{lecture}/complete', [LectureController::class, 'completeLecture']);
    Route::get('users/{user}/lectures/{lecture}/completion', [LectureController::class, 'getLectureCompletionStatus']);

    Route::get('/user/courses', [PurchaseController::class, 'getPurchasedCourses']);

    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::put('/coupons/{coupon}', [CouponController::class, 'update']);
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy']);
    Route::get('/coupons/{code}', [CouponController::class, 'showByCode']);

    Route::get('/transactions', [PurchaseController::class, 'getAllTransactions']);

    Route::post('courses/{course}/payment', [PaymentController::class, 'payment']);
    Route::post('courses/{course}/enroll', [PaymentController::class, 'enroll']);

    Route::post('/galleries', [GalleryController::class, 'uploadPhoto']);
    Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);
    
    Route::middleware(['auth:sanctum', 'course.purchased'])->group(function () {
        Route::get('/my-courses/{course}', [CourseController::class, 'showPurchasedCourse']);
    });

    // Exam routes
    Route::get('/modules/{module}/exams', [ExamController::class, 'index']);
    Route::post('/modules/{module}/exams', [ExamController::class, 'store']);
    Route::get('/exams/{exam}', [ExamController::class, 'show']);
    Route::put('/exams/{exam}', [ExamController::class, 'update']);
    Route::delete('/modules/{module}/exams/{exam}', [ExamController::class, 'destroy']);
});
