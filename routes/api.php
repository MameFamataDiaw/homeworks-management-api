<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\EleveController;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware'=>['auth:sanctum']],function(){
    Route::get('/check/user/loggedin',[AuthController::class,'userIdLoggedIn']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/firstLogin', [AuthController::class, 'changePassword']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
// Gestion des classes
     // Route::apiResource('classes', ClasseController::class);
    Route::get('/classes', [ClasseController::class, 'index']);
    Route::get('/classes/list', [ClasseController::class, 'getClasses']);
    Route::post('/classes', [ClasseController::class, 'store']);
    Route::get('/classes/{id}', [ClasseController::class, 'show']);
    Route::put('/classes/{id}', [ClasseController::class, 'update']);
    Route::delete('/classes/{id}', [ClasseController::class, 'destroy']);
    Route::post('/classes/{id}/matieres', [ClasseController::class, 'assignSubjectsToClass']);

    // Gestion des matieres
    Route::get('/matieres', [MatiereController::class, 'index']);
    Route::get('/matieres/list', [MatiereController::class, 'getMatieres']);
    Route::post('/matieres', [MatiereController::class, 'store']);
    Route::get('/matieres/{id}', [MatiereController::class, 'show']);
    Route::put('/matieres/{id}', [MatiereController::class, 'update']);
    Route::delete('/matieres/{id}', [MatiereController::class, 'destroy']);
    // Route::apiResource('matieres', MatiereController::class);

    // Gestion des enseignants
    Route::post('/enseignants', [EnseignantController::class, 'store']);

    // Gestion des parents
    Route::post('/parents', [ParentController::class, 'store']);
    Route::post('/eleves', [EleveController::class, 'store']);
});
