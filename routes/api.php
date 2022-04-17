<?php

//use App\Http\Controllers\ToDoController;
use App\Http\Controllers\ToDoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIAuthController;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'prefix' => 'sanctum',
    'as' => 'sanctum',
], function (Router $router) {

    $router->post('register', [APIAuthController::class, 'register']);
    $router->post('token', [APIAuthController::class, 'token'])->name('login');
});

Route::middleware('auth:sanctum')->group(function (Router $router) {
    $router->get('/name', function (Request $request) {
        return response()->json(['name' => $request->user()->name]);
    });

    $router->group([
        'prefix' => 'todo-list',
        'as' => 'todo-list'
    ], function (Router $router) {
        $router->get('/', [ToDoController::class,'get'])->name('get');
        $router->get('/{issue}', [ToDoController::class,'getIssue'])->name('getIssue');
        $router->post('/{issue?}', [ToDoController::class,'createOrUpdate'])->name('createOrUpdate');
        $router->put('/{issue}/{status}', [ToDoController::class,'changeStatus'])->name('changeStatus');
        $router->delete('/{issue}', [ToDoController::class,'delete'])->name('deleteIssue');
        $router->post('/{issue}/create', [ToDoController::class,'createChild'])->name('createChild');
    });


});

