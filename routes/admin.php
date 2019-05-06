<?php
/**
 * Created by PhpStorm.
 * User: 栾军
 * Date: 2018/9/14
 * Time: 14:26
 */

/*
 * 彩票后台 路由文件
 */

Route::any('login', ['as' => 'login', 'uses' => 'LoginController@index']);

Route::group(['middleware' => ['auth:admin']], function () {
    Route::get('/', 'IndexController@index');
    Route::any('profile', 'LoginController@profile');
    Route::any('verify', 'LoginController@verify');
    Route::any('logout', 'LoginController@logout');

    Route::group(['prefix' => 'common'], function () {
        Route::any('area', ['as' => 'common_area', 'uses' => 'CommonController@area']);
        Route::any('verify', ['as' => 'common_verify', 'uses' => 'VerifyController@index']);
    });

    Route::group(['middleware' => ['check-authority-admin']], function () {

        //系统管理（管理员管理）
        Route::group(['namespace' => 'System', 'prefix' => 'system'], function () {
            //账号管理
            Route::group(['prefix' => 'account'], function () {
                Route::any('/', ['as' => 'system_account', 'uses' => 'AccountController@index']);
                Route::any('add', ['as' => 'system_account_add', 'uses' => 'AccountController@add']);
                Route::any('reset', ['as' => 'system_account_reset', 'uses' => 'AccountController@reset']);
                Route::any('delete', ['as' => 'system_account_delete', 'uses' => 'AccountController@delete']);
            });
        });

        Route::group(['namespace' => 'LotteryTicket', 'prefix' => 'lotteryTicket'], function () {
            Route::group(['prefix' => 'type'], function () {
                Route::any('/', ['as' => 'lotteryTicket_type', 'uses' => 'TypeController@index']);
                Route::any('add', ['as' => 'lotteryTicket_type_add', 'uses' => 'TypeController@add']);
                Route::any('edit', ['as' => 'lotteryTicket_type_edit', 'uses' => 'TypeController@edit']);
                Route::any('delete', ['as' => 'lotteryTicket_type_delete', 'uses' => 'TypeController@delete']);
            });
        });

        Route::group(['namespace' => 'Manage', 'prefix' => 'manage'], function () {
            Route::group(['prefix' => 'order'], function () {
                Route::any('/', ['as' => 'manage_order', 'uses' => 'OrderController@index']);
                Route::any('detail/{order_id}', ['as' => 'manage_order_detail', 'uses' => 'OrderController@detail']);
                Route::any('handleOrder', ['as' => 'manage_order_handleOrder', 'uses' => 'OrderController@handleOrder']);
                Route::any('remark', ['as' => 'manage_order_remark', 'uses' => 'OrderController@remark']);
            });
        });
        Route::group(['namespace' => 'Income', 'prefix' => 'income'], function () {
            Route::group(['prefix' => 'order'], function () {
                Route::any('/', ['as' => 'income_order', 'uses' => 'IncomeController@index']);
            });
        });
        Route::group(['namespace' => 'Canteen', 'prefix' => 'canteen'], function () {
            Route::group(['prefix' => 'order'], function () {
                Route::any('/', ['as' => 'canteen_order', 'uses' => 'OrderController@index']);
            });
        });


    });
});