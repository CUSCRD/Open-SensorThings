<?php

use App\Constant\PathName;
use App\Constant\UserRolesFixedData;
use App\Http\Controllers\API\GetController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\API\EntityGetter\Observation;
use App\Http\Controllers\API\BatchController;
use App\Http\Controllers\API\DeleteController;
use App\Http\Controllers\API\PatchController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\IoTController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api']], function () {
    Route::post('/register', 'App\Http\Controllers\APIController@register');
    Route::post('/login', 'App\Http\Controllers\APIController@login');
    Route::get('/user', 'App\Http\Controllers\APIController@getCurrentUser');
    Route::post('/update', 'App\Http\Controllers\APIController@update');
    Route::post('/logout', 'App\Http\Controllers\APIController@logout');
    Route::post('/uploadImage', 'App\Http\Controllers\APIController@uploadImage');

    Route::middleware([EnsureTokenIsValid::class])->group(function () {
        Route::post('/updateAvatar', 'App\Http\Controllers\APIController@updateAvatar');
        Route::post('/updateInformation', 'App\Http\Controllers\APIController@updateInformation');
        Route::post('/resetPassword', 'App\Http\Controllers\APIController@resetPassword');
        Route::get('/getThings', 'App\Http\Controllers\APIController@getThings');
        //Tasking
        // Route::post(PathName::POST . '/' . PathName::CREATE_OBSERVATION, [API\PostController::class, "sensorPost"])
        // ->middleware(['auth.rest:' . UserRolesFixedData::REST_SENSOR['id']]);
        //sensor
        Route::post(PathName::POST . '/' . Observation::PATH_VARIABLE_NAME, [API\PostController::class, "sensorPost"])
            ->middleware(['auth.rest:' . UserRolesFixedData::REST_SENSOR['id']]);
        Route::post(PathName::POST . '/' . PathName::CREATE_OBSERVATION, [API\PostController::class, "sensorPost"])
            ->middleware(['auth.rest:' . UserRolesFixedData::REST_SENSOR['id']]);


        //client
        Route::post(PathName::POST . '/{params?}', [PostController::class, "action"])
            ->where('params', '^(?!.*' . Observation::PATH_VARIABLE_NAME . '(\(\d+\)(\/)?)?$).*$');

        Route::post(PathName::BATCH, [BatchController::class, 'action']);

        Route::patch(PathName::PATCH . '/{params?}', [PatchController::class, 'action'])
            ->where('params', '^(?!.*' . Observation::PATH_VARIABLE_NAME . '(\(\d+\)(\/)?)?$).*$');

        Route::delete(PathName::DELETE . '/{params?}', [DeleteController::class, 'action'])
            ->where('params', '^(?!.*' . Observation::PATH_VARIABLE_NAME . '(\(\d+\)(\/)?)?$).*$')->middleware('auth.rest:' . UserRolesFixedData::REST_DELETE['id']);

        Route::get(PathName::GET . '/{params?}', [GetController::class, 'action'])
            ->where('params', '(.*)');

        Route::get(PathName::ID . '/{params?}', [IoTController::class, 'action'])
            ->where('params', '(.*)');
    });
});
