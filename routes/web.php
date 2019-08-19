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
        $router->post('new-user', 'UsersController@newUserAction');
        $router->post('edit-user', 'UsersController@editUserAction');
        $router->post('add-value', 'CreateFormController@insertNewValue');
        $router->post('add-category', 'CreateFormController@insertNewCategory');
        $router->post('add-parameter', 'CreateFormController@insertNewParameter');
    }
);

$router->get('get-values', function () {
    return \App\Values::all();
});

$router->get('get-parameters', function () {
    return \App\Parameters::all();
});



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

