<?php
$home = function () {
    $stats = json_decode(file_get_contents(public_path() . '/stats.json'));
    return View::make('index', array(
        'baseUrl' => env('BASE_URL'),
        'stats' => $stats
    ));
};
Route::get('/', $home);
Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'Auth\AuthenticateController@login');
        Route::post('signup', 'Auth\AuthenticateController@signup');
    });

    Route::resource('workflow', 'WorkflowController', ['only' => ['store', 'index', 'update', 'destroy']]);
    Route::resource('report', 'ReportController', ['only' => ['store', 'create', 'index', 'destroy']]);
    Route::get('report/user/{id}', 'ReportController@user');
    Route::get('workflow/info', 'WorkflowController@info');
    Route::get('workflow/group/{id}', 'WorkflowController@group');

    Route::group(['prefix' => 'user'], function () {
        Route::resource('group', 'User\GroupsController', ['only' => ['store', 'index', 'edit', 'update', 'create', 'show']]);
        Route::group(['prefix' => 'profile'], function () {
            Route::get('index', 'User\ProfileController@index');
            Route::post('update', 'User\ProfileController@update');
            Route::get('my', 'User\ProfileController@my');
            Route::post('reset', 'User\ProfileController@reset');
        });
    });
});
Route::any('{path}', $home)->where("path", "^(?!api).+");
