<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post(
    'auth/login',
    [
        'uses' => 'AuthController@userAuthenticate'
    ]
);

//$router->group(['middleware' => 'cors'],
//    function() use ($router) {
//        $router->post(
//            'auth/login',
//            [
//                'uses' => 'AuthController@userAuthenticate'
//            ]
//        );
//    }
//);

$router->group(['middleware' => 'jwt.admin.auth'],
    function() use ($router) {
        $router->get('/users','UsersController@getAllUsers');
        $router->get('user-template', 'ImportExcelController@getUserTemplatePath');
    }
);




$router->group(['middleware' => 'jwt.auth'],
    function() use ($router) {
        $router->post('/get-list','PointsController@getList');
        $router->post('password-reset', 'UsersController@resetPassword');
        $router->post(
            '/get-form',
            [
                'uses' => 'PointsController@getFormAction'
            ]
        );
        $router->post(
            '/set-point',
            [
                'uses' => 'PointsController@setPointAction'
            ]
        );
    }
);

$router->get(
    '/import',
    [
        'uses' => 'ImportExcelController@importUsersAction'
    ]
);

$router->get(
    '/user-assign',
    [
        'uses' => 'ImportExcelController@setAssignedUserIds'
    ]
);

$router->get(
    '/import-parameters',
    [
        'uses' => 'ImportExcelController@importParametersAction'
    ]
);

$router->get(
    '/import-categories',
    [
        'uses' => 'ImportExcelController@importCategoriesAction'
    ]
);
$router->get(
    '/import-values',
    [
        'uses' => 'ImportExcelController@importValuesAction'
    ]
);

$router->post(
    '/set-values',
    [
        'uses' => 'CreateFormController@setValuesAction'
    ]
);


$router->post(
    '/set-categories',
    [
        'uses' => 'CreateFormController@setCategoriesAction'
    ]
);

$router->post(
    '/set-parameters',
    [
        'uses' => 'CreateFormController@setParametersAction'
    ]
);

$router->post(
    '/create-form',
    [
        'uses' => 'CreateFormController@createFormAction'
    ]
);
