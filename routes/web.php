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
    return 'Digikala HR application';
});

$router->post(
    'auth/login',
    [
        'uses' => 'AuthController@userAuthenticate'
    ]
);

$router->post(
    'auth/admin-login',
    [
        'uses' => 'AuthController@adminAuthenticate'
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
        $router->get('/user','UsersController@getUser');

        $router->get('/user-management','UsersController@managementAction');
        $router->get('user-template', 'ImportExcelController@getUserTemplatePath');
        $router->post('new-user', 'UsersController@newUserAction');
        $router->post('edit-user', 'UsersController@editUserAction');
        $router->post('add-value', 'CreateFormController@insertNewValue');
        $router->post('add-category', 'CreateFormController@insertNewCategory');
        $router->post('add-parameter', 'CreateFormController@insertNewParameter');

        $router->get('get-cards', 'CardsController@getAllCards');
        $router->post('set-card', 'CardsController@submitCard');

        $router->get('get-all','CreateFormController@getAll');

        $router->get('get-forms', function () {
            $allForms = \App\Forms::all();
            $forms = [];

            foreach ($allForms as $allForm) {
                $forms[] = ['id' => $allForm['id'], 'name'=> $allForm['name']];
            }
            return $forms;
        });

        $router->post('delete-form', 'CreateFormController@deleteAction');
        $router->post('edit-form', 'CreateFormController@editAction');

        $router->get('get-values', function () {
            return \App\Values::all();
        });

        $router->get('get-all-parameters', function () {
            return \App\Parameters::all();
        });


        $router->get('get-parameters', 'CreateFormController@getParameters');

        $router->get('get-all-categories', function () {
            return \App\Categories::all();
        });

        $router->get('get-categories', 'CreateFormController@getCategories');
        $router->get('new-form', 'CreateFormController@newFormData');

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

        $router->post(
            '/set-form',
            [
                'uses' => 'CreateFormController@setFormAction'
            ]
        );

    }
);

$router->group(['middleware' => 'jwt.auth'],
    function() use ($router) {
        $router->get('/get-appraiser-list','PointsController@getAppraiserList');
        $router->get('/get-appraisal-list','PointsController@getAppraisalList');
        $router->post('password-reset', 'UsersController@resetPassword');
        $router->get(
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

        $router->get('/home','IndexController@getHome');
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
