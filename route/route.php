<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
Route::get('/download', '\app\http\controllers\home\Download@show');

Route::group('/api/v1', function () {
    Route::post('user/login', 'User@login');
    Route::post('user/mobile', 'User@mobile');

	Route::post('main/carousel/list', 'Carousel@list');
	Route::post('main/indexData', 'Main@initData');
	Route::post('main/search', 'Main@search');
	Route::post('main/searchList', 'Main@searchList');
    Route::post('main/payBack','Main@payBack');
    Route::post('main/sandCallback','Main@sandCallback');
    Route::post('main/refund','Main@refund');
    Route::post('main/sandRefund','Main@sandRefund');
    Route::post('auth/verifyToken', 'Main@verifyToken');
    Route::post('oss/signature', 'Main@signature');

	Route::post('goods/list','Goods/list');
	Route::post('goods/getCategory','Goods/getCategory');
	Route::post('goods/nav', 'Goods@getNav');
    Route::post('goods/detail', 'Goods@detail');

	Route::post('course/nav', 'Course@getNav');
    Route::post('course/getCategory','Course/getCategory');
	Route::post('course/list', 'Course@list');
	Route::post('course/detail', 'Course@detail');
	Route::post('course/teacherList', 'Course@teacherList');

    Route::post('live/nav', 'Live@getNavList');
    Route::post('live/list', 'Live@list');
	Route::post('appservice/list', 'AppService@list');

    Route::post('ip/location', 'Ip@location');
	Route::post('site/visiter', 'SiteVisiter@index');



	//银商接口

})->prefix('\app\http\controllers\api\v1\\');

Route::group('/api/v1', function () {
	Route::post('uploade/image64', 'UploadeFile@imageBase64');

	Route::post('user/info', 'User@info');

	//课程
    Route::post('course/my', 'Course@mycourse');
    Route::post('course/addPlayTime', 'Course@addPlayTime');

    //订单
	Route::post('order/confirm','Order/confirm');
	Route::post('order/save','Order/save');
	Route::post('order/list','Order/list');
	Route::post('order/detail','Order/detail');
	Route::post('order/cancel','Order/cancel');
	Route::post('order/pay','Order/pay');
	Route::post('order/unified','Order@unifiedShande');
	Route::post('order/mycount','Order/myCount');
	Route::post('order/refundAsd','Order/refundAsd');
	Route::post('order/refundReason','Order/refundReason');
	Route::post('order/del','Order/del');
    Route::post('order/deliverConfirm', 'Order@deliverConfirm');
    Route::post('order/unifiedShande', 'Order@unifiedShande');

    //购物车
	Route::post('cart/list','Carts/list');
	Route::post('cart/count','Carts/count');
	Route::post('cart/save','Carts/save');
	Route::post('cart/delete','Carts/delete');
    Route::post('cart/check','Carts/checkGoods');

    //收货地址
	Route::post('address/list','Address/list');
	Route::post('address/save','Address/save');
	Route::post('address/update','Address/update');
	Route::post('address/delete','Address/delete');
	Route::post('address/getAddress','Address/getAddress');
	Route::post('address/getDefault','Address/getDefault');
	Route::post('address/changeDefault','Address/changeDefault');

	Route::post('content/system/category', 'SystemInformation@category');
	Route::post('content/system/list', 'SystemInformation@list');
	Route::post('content/system/detail', 'SystemInformation@detail');
	Route::post('content/system/unread', 'SystemInformation@unread');

	//获取订单key
    Route::get('order/getkey','Order/getOrderKey');
	// 搜索
    Route::get('search/list', 'Search@list');
})->prefix('\app\http\controllers\api\v1\\')->middleware(['UserAuthAuthenticate']);
