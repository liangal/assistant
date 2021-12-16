/** EasyWeb spa v3.1.4 date:2019-07-12 License By http://easyweb.vip */

layui.config({
    version: true,   // 更新组件缓存，设为true不缓存，也可以设一个固定值
    base: 'assets/module/'
}).extend({
    formSelects: 'formSelects/formSelects-v4',
    treetable: 'treetable-lay/treetable',
    dropdown: 'dropdown/dropdown',
    notice: 'notice/notice',
    step: 'step-lay/step',
    dtree: 'dtree/dtree',
    citypicker: 'city-picker/city-picker',
    tableSelect: 'tableSelect/tableSelect',
    Cropper: 'Cropper/Cropper',
    zTree: 'zTree/zTree',
    introJs: 'introJs/introJs',
    fileChoose: 'fileChoose/fileChoose',
    tagsInput: 'tagsInput/tagsInput',
    CKEDITOR: 'ckeditor/ckeditor',
    Split: 'Split/Split',
    cascader: 'cascader/cascader'
}).use(['config', 'layer', 'element', 'index', 'admin', 'laytpl'], function () {
    var $ = layui.jquery;
    var layer = layui.layer;
    var element = layui.element;
    var config = layui.config;
    var index = layui.index;
    var admin = layui.admin;
    var laytpl = layui.laytpl;

    // 检查是否登录
    if (!config.getToken()) {
        return location.replace('/manage/login.html');
    }

    // 获取用户信息
    admin.req('system/admin/info', {}, function (res) {
        if (200 == res.code) {
            config.putUser(res.data);
            admin.renderPerm();  // 移除没有权限的元素
            $('#huName').text(res.data.nickName);

            // 加载侧边栏
            admin.req('menu/list', {}, function (res) {
                laytpl(sideNav.innerHTML).render(res, function (html) {
                    $('.layui-layout-admin .layui-side .layui-nav').html(html);
                    element.render('nav');
                });

                index.regRouter(res);  // 注册路由

                var url = "";
                if(admin.hasPerm('main:index:show')){
                    url = "#/main/index";
                } else {
                    url = "#/main/empty";
                }

                index.loadHome({  // 加载主页
                    url: url,
                    name: '<i class="layui-icon layui-icon-home"></i>'
                });
            }, 'post');

        } else {
            layer.msg('获取用户失败', {icon: 2});
        }
    }, 'post');



    // 移除loading动画
    setTimeout(function () {
        admin.removeLoading();
    }, 300);

    // 提示
    if (!config.pageTabs) {
        // layer.confirm('SPA版本默认关闭多标签功能，你可以在设置界面开启', {
        //     skin: 'layui-layer-admin',
        //     area: '280px',
        //     title: '温馨提示',
        //     shade: 0,
        //     btn: ['打开设置', '知道了']
        // }, function (i) {
        //     layer.close(i);
        //     $('a[ew-event="theme"]').trigger('click');
        // });
    }

});
