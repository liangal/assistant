layui.define(["jquery", "admin", "upload", 'element','config','layer','alioss'], function (exports) {
    var $ = layui.jquery;
    var upload = layui.upload;
    var config = layui.config;
    var layer = layui.layer;
    var admin = layui.admin;
    var element = layui.element;
    var alioss = layui.alioss;
    var uploadst ={
        previewEle: "#layui-upload-imgs",
        elem: "#upload_image",
        url:config.base_server+'uploade/image',
        img:'',
        wechatImg :[],
        initupImg:function (option) {
            var that = this,url = this.url,img = this.img;
            if(option != undefined){
                that = option;
            }
            setTimeout(()=>{

                upload.render({
                    elem: that.elem
                    ,url: url, //上传接口
                    headers: {
                        'Authorization': 'Bearer ' + config.getToken().access_token
                    },
                    accept: "images",
                    acceptMime: "image/*",
                    multiple: true,
                    number: 1
                    ,before: function(obj){
                        //预读本地文件示例，不支持ie8
                        obj.preview(function(index, file, result){
                            $(that.previewEle).attr('src', result); //图片链接（base64）
                        });
                    }
                    ,done: function(res){
                        //如果上传失败
                        if(res.code > 0){
                            return layer.msg('上传失败');
                        }
                        if(res.code == 0){
                            $(that.previewEle).attr('src', res.data.src); //图片链接（base64）
                            $(that.previewEle).next('input').val(res.data.src); //图片链接（base64）
                            $(that.previewEle).css('width','92px');
                            $(that.previewEle).css('height','92px');
                        }
                        //上传成功
                    }
                    ,error: function(){
                        //演示失败状态，并实现重传
                        var demoText = $('#demoText');
                        demoText.html('<span style="color: #FF5722;">上传失败</span> ');
                        // demoText.find('.demo-reload').on('click', function(){
                        //     uploadInst.upload();
                        // });
                    }

                });
            },2000)
        },
        uploadWechatImg(option){
            var that = this;
            if(option != undefined){
                that = option;
            }
            var url = that.url,img = that.img;
            var load;
            upload.render({
                elem: that.elem
                ,url:url, //上传接口
                headers: {
                    'Authorization': 'Bearer ' + config.getToken().access_token
                },
                accept: "images",
                acceptMime: "image/*",
                multiple: true,
                number: 1
                ,before: function(obj){
                    load = layer.load();
                }
                ,done: function(res){
                    layer.close(load);

                    if(res.code){
                        return layer.msg('上传图片失败');
                    }
                    $(that.previewEle).attr('src', res.path);

                    $(that.previewEle).nextAll().eq(1).val(res.media_id);
                    $(that.previewEle).nextAll().eq(2).val(res.path);
                    $(that.previewEle).css('width','92px');
                    $(that.previewEle).css('height','92px');


                    $(that.previewEle).next('i').css('display','block')
                    $(that.previewEle).next('i').css({
                        position: 'absolute',
                        left: '78px',
                        top: '-15px',
                        'font-size': '26px',
                        'background-color': '#fff',
                        'border-radius': '13px',
                    })
                    $(that.previewEle).next('i').click(function(){
                        ele = $(this);
                        layer.confirm('确定要删除吗？删除后不可恢复。', {
                            skin: 'layui-layer-admin'
                        }, function (i) {
                            layer.close(i);

                            path = ele.siblings('img').eq(0).attr('data-path');
                            if(path != ""){
                                admin.req('oss/delete', {object: res.path,type:'image'}, function (res) {

                                }, 'post');
                            }

                            ele.remove();
                            ele.css('display','none')
                            $(that.previewEle).attr('src', '');

                        })
                    })
                }
                ,error: function(){
                    layer.close(load);
                    layer.msg('系统错误');
                }
            });
        }, uploadGoodsWechatImg(option){
            var that = this
            if(option != undefined){
                that = option;
            }
           var url = that.url,img = that.img;
            var load;
            upload.render({
                elem: that.elem
                ,url:url, //上传接口
                headers: {
                    'Authorization': 'Bearer ' + config.getToken().access_token
                },
                accept: "images",
                acceptMime: "image/*",
                multiple: true,
                number: 1
                ,before: function(obj){
                    load = layer.load();
                }
                ,done: function(res){
                    layer.close(load);
                    console.log(res);
                    if(res.code){
                        return layer.msg(res.msg);
                    }
                    $(that.previewEle).attr('src', res.url);

                    $(that.previewEle).nextAll().eq(1).val(res.media_id);
                    $(that.previewEle).nextAll().eq(2).val(res.path);
                    $(that.previewEle).css('width','92px');
                    $(that.previewEle).css('height','92px');


                    $(that.previewEle).next('i').css('display','block')
                    $(that.previewEle).next('i').css({
                        position: 'absolute',
                        left: '78px',
                        top: '-15px',
                        'font-size': '26px',
                        'background-color': '#fff',
                        'border-radius': '13px',
                    })
                    $(that.previewEle).next('i').click(function(){
                        ele = $(this);
                        layer.confirm('确定要删除吗？删除后不可恢复。', {
                            skin: 'layui-layer-admin'
                        }, function (i) {
                            layer.close(i);

                            path = ele.siblings('img').eq(0).attr('data-path');
                            if(path != ""){
                                admin.req('oss/delete', {object: res.path,type:'image'}, function (res) {

                                }, 'post');
                            }
                            ele.css('display','none')
                            $(that.previewEle).attr('src', '');

                        })
                    })
                }
                ,error: function(){
                    layer.close(load);
                    layer.msg('系统错误');
                }
            });
        },
        deleteImaged: function(previewEle,type) {   // 删除图片
            var that = this;
            $(previewEle).on('click', 'i', function(){
                ele = $(this);
                layer.confirm('确定要删除吗？删除后不可恢复。', {
                    skin: 'layui-layer-admin'
                }, function (i) {
                    layer.close(i);

                    path = ele.siblings('img').eq(0).attr('data-path');
                    if(path != ""){
                        admin.req('oss/delete', {object: path,type:type}, function (res) {

                        }, 'post');
                    }

                    ele.closest('li').remove();
                })
            })
        },
    }

    exports("uploadst", uploadst);
})