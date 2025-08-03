<?php

use App\Http\Controllers\API\Article\ArticleController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Contact\ContactController;
use App\Http\Controllers\API\Dons\EnfilerController;
use App\Http\Controllers\API\Member\MemberController;
use App\Http\Controllers\API\NewLetter\NewLetterController;
use App\Http\Controllers\API\TypeDons\EnfilerTypeController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\TypeBenevole\TypeBenevoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route pour gérer les requêtes OPTIONS (preflight CORS)
Route::options('{any}', function (Request $request) {
    $corsConfig = config('cors');
    $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
    $origin = $request->header('Origin');

    $response = response('', 200);

    if (in_array($origin, $allowedOrigins)) {
        $response->header('Access-Control-Allow-Origin', $origin);
    }

    return $response
        ->header('Access-Control-Allow-Methods', implode(', ', $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']))
        ->header('Access-Control-Allow-Headers', implode(', ', $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin', 'X-CSRF-TOKEN']))
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');

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
        Route::patch('dons/{enfiler}/activate', [EnfilerController::class, 'activate']);
        Route::patch('dons/{enfiler}/toggle-active', [EnfilerController::class, 'toggleActive']);

        // member route
        Route::get('membres', [MemberController::class, 'index']);
        Route::get('membres/{member}', [MemberController::class, 'show']);
        Route::put('membres/{member}', [MemberController::class, 'update']);
        Route::delete('membres/{member}', [MemberController::class, 'desactivate']);
        Route::patch('membres/{member}/activate', [MemberController::class, 'activate']);
        Route::patch('membres/{member}/toggle-volunteer', [MemberController::class, 'toggleVolunteer']);
        Route::patch('membres/{member}/approve', [MemberController::class, 'approve']);
        Route::patch('membres/{member}/reject', [MemberController::class, 'reject']);

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

        // new letter route
        Route::get('new-letters', [NewLetterController::class, 'index']);
        Route::post('new-letters', [NewLetterController::class, 'store']);
        Route::delete('new-letters/{newLetter}', [NewLetterController::class, 'desactivate']);
    });
});
