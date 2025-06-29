<?php

use App\Http\Controllers\API\Article\ArticleController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Contact\ContactController;
use App\Http\Controllers\API\Dons\EnfilerController;
use App\Http\Controllers\API\Member\MemberController;
use App\Http\Controllers\API\TypeDons\EnfilerTypeController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\TypeBenevole\TypeBenevoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // login route
    Route::post('login', [AuthController::class, 'login']);

    // contact route
    Route::post('contacts', [ContactController::class, 'store']);

    // membre route
    Route::post('membres', [MemberController::class, 'store']);

    // dons
    Route::post('dons', [EnfilerController::class, 'store']);

    // articles
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);

    Route::middleware('auth:api')->group(function () {
        // contact route
        Route::get('contacts', [ContactController::class, 'index']);
        Route::delete('contacts/{contact}', [ContactController::class, 'desactivate']);

        // type de dons route
        Route::get('enfiler-types', [EnfilerTypeController::class, 'index']);
        Route::post('enfiler-types', [EnfilerTypeController::class, 'store']);
        Route::get('enfiler-types/{enfilerType}', [EnfilerTypeController::class, 'show']);
        Route::put('enfiler-types/{enfilerType}', [EnfilerTypeController::class, 'update']);
        Route::delete('enfiler-types/{enfilerType}', [EnfilerTypeController::class, 'desactivate']);

        // enfiler route
        Route::get('dons', [EnfilerController::class, 'index']);
        Route::get('dons/{enfiler}', [EnfilerController::class, 'show']);
        Route::put('dons/{enfiler}', [EnfilerController::class, 'update']);
        Route::delete('dons/{enfiler}', [EnfilerController::class, 'desactivate']);

        // member route
        Route::get('membres', [MemberController::class, 'index']);
        Route::get('membres/{member}', [MemberController::class, 'show']);
        Route::put('membres/{member}', [MemberController::class, 'update']);
        Route::delete('membres/{member}', [MemberController::class, 'desactivate']);

        // type benevole route

        Route::get('type-benevoles', [TypeBenevoleController::class, 'index']);
        Route::post('type-benevoles', [TypeBenevoleController::class, 'store']);
        Route::get('type-benevoles/{typeBenevole}', [TypeBenevoleController::class, 'show']);
        Route::put('type-benevoles/{typeBenevole}', [TypeBenevoleController::class, 'update']);
        Route::delete('type-benevoles/{typeBenevole}', [TypeBenevoleController::class, 'desactivate']);

        // users route
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'desactivate']);

        // article 

        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{article}', [ArticleController::class, 'update']);
        Route::delete('/articles/{article}', [ArticleController::class, 'desactivate']);
        Route::put('/articles/images/{slug}', [ArticleController::class, 'updateImage']);
        Route::delete('/articles/images/{slug}', [ArticleController::class, 'deleteImage']);


    });
});
