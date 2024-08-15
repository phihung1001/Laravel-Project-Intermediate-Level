<?php
//
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;
//
//
//Route::post('/register',[UserController::class,'register']);
//Route::get('/login',[UserController::class,'login']);


use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('login', [UserController::class, "login"]);
    Route::post('register', [UserController::class, "register"]);

    Route::group([
        "prefix" => "/",
        'middleware' => 'auth:api',
    ], function () {

        //User basic
        Route::post('logout', [UserController::class, "logout"]);
        Route::put('update-user',[UserController::class, "update_user"]);
        Route::post('refresh', [UserController::class, "refresh"]);
        Route::get('me',  [UserController::class, "me"]);


        //Todo basic
        Route::get('get-all', [TaskController::class, 'index']); // xem tat ca task . user, leader, admin
        Route::get('show/{id}', [TaskController::class, 'show']); // show 1 cong viec bat ki
        Route::post('create', [TaskController::class, 'store']); //user tao 1 cong viec moi
        Route::post('create_task/{id}', [TaskController::class, 'create_task']); // Create task leader,admin
        Route::put('update/{id}', [TaskController::class, 'update']); // Update task , user,leader,admin
        Route::delete('destroy/{id}', [TaskController::class, 'destroy']); // Destroy task , user,leader,admin
        Route::get('get-task/{id}', [TaskController::class, 'get_task']); // Admin, Leader get task cua tung user

        //Admin Leader


        Route::get('get-user', [UserController::class, 'get_user']); // Admin, Leader get all user
        Route::get('get-user2/{id}', [UserController::class, 'get_user2']);   // Admin, Leader xem tat ca user trong 1 team nao do
        Route::post('update-user2/{id}', [UserController::class, 'update_user2']); //Admin, Leader update user bat ki
        Route::delete('delete-user2/{id}', [UserController::class, 'delete_user2']); // Admin, Leader Delete user random
        Route::post('create-user2', [UserController::class, 'create_user2']); // Create user admin, leader

    });

});
