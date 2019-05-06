<?php

use Illuminate\Http\Request;

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

Route::get('/', function () {
    echo '这是API';
});

Route::any('selectOrderList', 'Order\OrderController@selectOrderList');//查询订单列表
Route::any('selectLottery', 'Order\OrderController@select_lottery');//查询在售彩票列表
Route::any('selectOrderFirst', 'Order\OrderController@OrderFirst');//查询订单详情
Route::any('testingGoods', 'Order\OrderController@TestingGoods');//提交购物车
Route::any('settlementOrder', 'Order\OrderController@settlementOrder');//提交购物车
Route::any('commitOrder', 'Order\OrderController@commitOrder');//提交订单
Route::any('add_address', 'User\UserController@add');//添加或修改收货地址
Route::any('selectArea', 'User\UserController@selectArea');//查询当前店铺位置所在省下的所有市和区
Route::any('isConfirmation', 'Order\OrderController@confirmation');//确认收货
Route::any('getOpenCity', 'Order\OrderController@getOpenCity');//test alipay
Route::any('test', 'Payback\DatestController@test');//test alipay

Route::any('sendupdateremain', 'Order\OrderController@sendupdateremain');//提交订单

Route::group( [ 'namespace' => 'Payback' ], function ()  {
   
    Route::any('alipay', 'AlipayAppController@setParamsForAliPay');//test alipay
    Route::any('wechat', 'WeChatController@weChatpPay');//test alipay
    Route::any( 'alipayAppcallBack', 'AlipayAppController@alipayNotify' );//支付宝异步通知地址
    //alipayAppcallBack
    
} );  