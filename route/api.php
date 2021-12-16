<?php
Route::rule('/','/manage/index.html');
Route::post('/manage/api/oauth/token', '\app\http\controllers\manage\api\Oauth@token');
Route::post('/manage/api/system/user/syscityreg', '\app\http\controllers\manage\api\SysCityUser@reg');

Route::group('/manage/api/', function () {
	Route::post('main/index/show', 'Main@index');

	//轮播图
    Route::post('main/carousel/list', 'Carousel@getList');
    Route::post('main/carousel/save', 'Carousel@save');
    Route::post('main/carousel/update', 'Carousel@update');
    Route::post('main/carousel/delete', 'Carousel@delete');

    //商品导航
    Route::post('main/goodsnav/list', 'GoodsNav@getList');
    Route::post('main/goodsnav/save', 'GoodsNav@save');
    Route::post('main/goodsnav/update', 'GoodsNav@update');
    Route::post('main/goodsnav/delete', 'GoodsNav@delete');

    //课程视频导航
    Route::post('main/coursenav/list', 'CourseNav@getList');
    Route::post('main/coursenav/save', 'CourseNav@save');
    Route::post('main/coursenav/update', 'CourseNav@update');
    Route::post('main/coursenav/delete', 'CourseNav@delete');

    //导师
    Route::post('main/teacher/list', 'Teacher@getList');
    Route::post('main/teacher/save', 'Teacher@save');
    Route::post('main/teacher/update', 'Teacher@update');
    Route::post('main/teacher/delete', 'Teacher@delete');

	Route::post('system/role/list', 'Role@list');
	Route::post('system/role/store', 'Role@store');
	Route::post('system/role/update', 'Role@update');
	Route::post('system/role/delete', 'Role@delete');
	Route::post('system/role/assignpermissions', 'Role@assignPermissions');
	Route::post('system/role/updatepermissions', 'Role@updateRolePermissions');
	
	Route::post('system/permission/list', 'Permission@list');
	Route::post('system/permission/store', 'Permission@store');
	Route::post('system/permission/update', 'Permission@update');
	Route::post('system/permission/delete', 'Permission@delete');

	Route::post('system/admin/list', 'AdminUser@list');
	Route::post('system/admin/store', 'AdminUser@store');
	Route::post('system/admin/update', 'AdminUser@update');
	Route::post('system/admin/delete', 'AdminUser@delete');
	Route::post('system/admin/reset', 'AdminUser@reset');
	Route::post('system/admin/switch', 'AdminUser@switch');
	
	Route::post('system/user/list', 'Users@list');
	Route::post('system/user/reset', 'Users@reset');
	Route::post('system/user/switch', 'Users@switch');
	Route::post('system/user/delete', 'Users@delete');
	Route::post('system/user/cancelDel', 'Users@cancelDel');

	//商品分类
    Route::post('content/goodscategory/list', 'GoodsCategories@getlist');
    Route::post('content/goodscategory/save', 'GoodsCategories@save');
    Route::post('content/goodscategory/update', 'GoodsCategories@update');
    Route::post('content/goodscategory/del', 'GoodsCategories@delete');

    //商品
    Route::post('content/goods/list', 'Goods@getlist');
    Route::post('content/goods/save', 'Goods@save');
    Route::post('content/goods/update', 'Goods@update');
    Route::post('content/goods/delete', 'Goods@delete');
    Route::post('content/goods/upStatus', 'Goods@upStatus');
    //商品规格
    Route::post('content/goods/saveOption', 'Goods@saveOption');
    Route::post('content/goods/delSpec', 'Goods@delSpec');
    Route::post('content/goods/delSpecItem', 'Goods@delSpecItem');

    //课程管理
    Route::post('course/list', 'Course@getlist');
    Route::post('course/save', 'Course@save');
    Route::post('course/update', 'Course@update');
    Route::post('course/delete', 'Course@delete');

    //课程分类
    Route::post('coursecategory/list', 'CourseCategory@getlist');
    Route::post('coursecategory/save', 'CourseCategory@save');
    Route::post('coursecategory/update', 'CourseCategory@update');
    Route::post('coursecategory/delete', 'CourseCategory@delete');

	Route::post('content/system/list', 'SystemInformation@list');
	Route::post('content/system/store', 'SystemInformation@store');
	Route::post('content/system/update', 'SystemInformation@update');
	Route::post('content/system/delete', 'SystemInformation@delete');

	//订单管理
    Route::post('order/list', 'Order@getList');
    Route::post('order/courseList', 'Order@courseList');
    Route::post('order/update', 'Order@update');
    Route::post('order/updateMark', 'Order@updateMark');
    Route::post('order/orderRefund', 'Order@orderRefund');
    Route::post('order/deliver', 'Order@deliver');
    Route::post('order/cancel', 'Order@cancel');

    //发货
    Route::post('expresses/list', 'Expresses@list');

    //直播管理
    Route::post('live/list', 'Live@getList');
    Route::post('live/save', 'Live@save');
    Route::post('live/delete', 'Live@delete');
    Route::post('live/addgoods', 'Live@addgoods');
//    Route::post('live/list', 'Live@getList');

    //直播导航
    Route::post('main/livenav/list', 'LiveNav@getList');
    Route::post('main/livenav/save', 'LiveNav@save');
    Route::post('main/livenav/update', 'LiveNav@update');
    Route::post('main/livenav/delete', 'LiveNav@delete');

    //商品库
    Route::post('goodsHouse/list','GoodsHouse/getList');
    Route::post('goodsHouse/save','GoodsHouse/save');
    Route::post('goodsHouse/restaudit','GoodsHouse/restaudit');
    Route::post('goodsHouse/audit','GoodsHouse/audit');
    Route::post('goodsHouse/delete','GoodsHouse/delete');
    Route::post('goodsHouse/update','GoodsHouse/update');

	Route::post('content/comment/list', 'Comment@list');
	Route::post('content/comment/update', 'Comment@update');
	Route::post('content/comment/delete', 'Comment@delete');

	Route::post('system/adminlog/list', 'AdminLog@list');

	//商户类型
    Route::post('storetype/list', 'Storetype@list');
    Route::post('storetype/save', 'Storetype@save');
    Route::post('storetype/update', 'Storetype@update');
    Route::post('storetype/del', 'Storetype@del');

    //商户
    Route::post('store/index', 'Store/index');
    Route::post('store/create', 'Store@create');
    Route::post('store/update', 'Store@update');
    Route::post('store/del', 'Store@del');

    //任务
    Route::post('task/index', 'task/index');
    Route::post('task/create', 'task@create');
    Route::post('task/update', 'task@update');
    Route::post('task/del', 'task@del');

})->prefix('\app\http\controllers\manage\api\\')->middleware(['ManageAuthAuthenticate','AuthAuthenticateCheck']);

Route::group('/manage/api/', function () {
	Route::post('uploade/image', 'UploadeFile@image');
	Route::post('uploade/wechatImage', 'UploadeFile@wechatImage');
	Route::post('uploade/wechatGoodsImage', 'UploadeFile@wechatGoodsImage');
	Route::post('uploade/image64', 'UploadeFile@imageBase64');
	Route::post('uploade/video', 'UploadeFile@video');
	Route::post('uploade/excel', 'UploadeFile@excel');

    Route::post('uploade/ckeditor/image', 'UploadeFile@ckEditorImage');
    Route::post('uploade/ckeditor/file', 'UploadeFile@ckEditorFile');

    Route::post('nav/liveList', 'Live@getNavList');

    Route::post('menu/list', 'Menu@list');

	Route::post('system/admin/info', 'AdminUser@info');
	Route::post('system/admin/role', 'AdminUser@role');

	Route::post('category/list', 'Classification@category');

	Route::post('userinfo/info', 'UserInfo@info');
	Route::post('userinfo/update', 'UserInfo@update');
	Route::post('userinfo/password', 'UserInfo@updatePassword');

	Route::post('/test/jpush', 'Jpush@push');

	Route::post('oss/signature', 'AliOSS@signature');
	Route::post('oss/delete', 'AliOSS@delete');
	Route::post('oss/put', 'AliOSS@put');
	Route::post('oss/getVideo', 'AliOSS@getVideo');

})->prefix('\app\http\controllers\manage\api\\')->middleware(['ManageAuthAuthenticate']);

return [

];