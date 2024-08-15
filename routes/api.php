<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post("/login", [AuthController::class, "authenticate"]);
Route::post("/register", [AuthController::class, "register"]);


Route::middleware("auth:sanctum")->group(function () {

    Route::prefix("/projects")->group(function () {
        Route::get("/", [ProjectController::class, "findAll"]);
        Route::get('/{id}', [ProjectController::class, 'show']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
    });

    Route::prefix("/pengguna")->group(function () {
        Route::get("/", [UserController::class, "findAll"]);
        Route::get("/detail/{id}", [UserController::class, "findById"]);
        Route::post("/", [UserController::class, "store"]);
        Route::put("/update/{id}", [UserController::class, "updateById"]);
        Route::put("/update", [UserController::class, "update"]);
        Route::delete("/{id}", [UserController::class, "destroy"]);
    });


    Route::prefix("task")->group(function(){
        Route::get("/" , [TaskController::class, "index"]);
        Route::get("/{id}" , [TaskController::class, "show"]);
        Route::get("/project/{id}" , [TaskController::class, "findByProject"]);
        Route::post("/" , [TaskController::class , "store"]);
        Route::put("/{id}" , [TaskController::class, "update"]);
        Route::delete("/{id}" , [TaskController::class, "destroy"]);
    });

});
