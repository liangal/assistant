/**
 * 阿里OSS上传模块
 * date:2019-09-27   License By 253102396@qq.com
 */
layui.define(["jquery", "admin", "upload", 'element','config'], function (exports) {

    var $ = layui.jquery;
    var admin = layui.admin;
    var upload = layui.upload;
    var element = layui.element;
    var config = layui.config;

    let UPLOAD_FILES;

    var alioss = {
        url: "", // 上传OSS URL
        signatureParams: {}, // OSS 签名参数
        uploadPath: "", //上传目录
        expire: 0, // 过期时间
        val:'',
        vals:'',
        videos:'',
        // 初始化 (上传图片)
        initUpload: function(options){
            var that = this;

            var defaults = {
                previewEle: "#layui-upload-imgs ul",
                elem: "#upload_image",
                dataType: "xml",
                accept: "images",
                acceptMime: "image/*",
                auto: false,
                multiple: false,
                number: 6
            };

            options = $.extend({}, defaults ,options);
            var fileName = '';
            // 选择文件后的回调函数
            options.choose = function(obj){

                that.clearFile();
                UPLOAD_FILES = obj.pushFile();
                fileName = that.randomString();
                var count = Object.keys(UPLOAD_FILES).length + $(options.previewEle + " li").length;

                if(count > options.number ){
                    layer.msg("最多只能上传" + options.number + "张图片", {icon: 2});
                    return false;
                }

                //获取签名
                that.getSignature('image');

                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $(options.previewEle).append(that.imageHtml(index, result));
                });
                setTimeout(function(){
                    var params = that.getUploadParam();
                    uploadInst.config.url = that.url;console.log(that.url);
                    uploadInst.config.headers={
                        "Access-Control-Allow-Origin":'*',
                        'Authorization': 'Bearer ' + config.getToken().access_token
                    };
                    uploadInst.config.data = params;

                    for (index in UPLOAD_FILES) {
                        uploadInst.config.data.key = that.uploadPath + fileName + ".jpg";
                        $(options.previewEle + " #" + index + " span").eq(0).text('上传中..');
                        obj.upload(index, UPLOAD_FILES[index]);
                    }
                }, 2000);
            }

            // 执行上传请求后的回调
            options.done = function(res, index, upload){
                path = that.uploadPath + fileName + ".jpg";
                if(options.number==1){
                    that.val = path;
                    $(options.elem).parent().prev().find('input').val(that.val);
                }else{
                    that.vals+=path+',';
                    console.log(that.vals);
                    console.log(path);
                    $(options.elem).parent().prev().find('input').val(that.vals);
                }

                $(options.previewEle + " #" + index + " img").eq(0).attr('data-path', path);
                $(options.previewEle + " #" + index + " span").remove();
                $("#" + index + "_progress").remove();
            }

            options.progress = function(index, percent){
                var percent = percent + '%' //获取进度百分比
                filter_key = index + "_progress";
                element.progress(filter_key, percent);
            }

            // 执行上传错误后的回调
            options.error = function(index, upload){
                console.log(upload);
                layer.msg('上传图片失败2', {icon: 2});
            }

            let uploadInst = upload.render(options);

            that.deleteImage(options.previewEle,'image',options.number,$(options.elem).parent().prev().find('input'));
        },initSpecUpload: function(options){
            var that = this;
            var defaults = {
                previewEle: "#layui-upload-imgs ul",
                elem: "#upload_image",
                dataType: "xml",
                accept: "images",
                acceptMime: "image/*",
                auto: false,
                multiple: true,
                number: 5
            };

            options = $.extend({}, defaults ,options);
            var fileName = that.randomString();
            // 选择文件后的回调函数
            options.choose = function(obj){
                that.clearFile();
                UPLOAD_FILES = obj.pushFile();

                var count = Object.keys(UPLOAD_FILES).length + $(options.previewEle + " li").length;

                if(count > options.number ){
                    layer.msg("最多只能上传" + options.number + "张图片", {icon: 2});
                    return false;
                }

                //获取签名
                that.getSignature('image');

                //预读本地文件示例，不支持ie8
                obj.preview(function(index, file, result){
                    $(options.previewEle).append(that.imageHtml(index, result));
                });
                setTimeout(function(){
                    var params = that.getUploadParam();
                    uploadInst.config.url = that.url;
                    uploadInst.config.headers={
                        'Authorization': 'Bearer ' + config.getToken().access_token
                    };
                    uploadInst.config.data = params;

                    for (index in UPLOAD_FILES) {
                        uploadInst.config.data.key = that.uploadPath + fileName + ".jpg";
                        $(options.previewEle + " #" + index + " span").eq(0).text('上传中..');
                        obj.upload(index, UPLOAD_FILES[index]);
                    }
                }, 2000);
            }

            // 执行上传请求后的回调
            options.done = function(res, index, upload){
                path = that.uploadPath + fileName + ".jpg";
                $(options.previewEle).parent().next('.itemImg').val(path);
                $(options.previewEle + " #" + index + " img").eq(0).attr('data-path', path);
                $(options.previewEle + " #" + index + " span").remove();
                $("#" + index + "_progress").remove();
            }

            options.progress = function(index, percent){
                var percent = percent + '%' //获取进度百分比
                filter_key = index + "_progress";
                element.progress(filter_key, percent);
            }

            // 执行上传错误后的回调
            options.error = function(index, upload){
                layer.msg('上传图片失败2', {icon: 2});
            }

            let uploadInst = upload.render(options);

            that.deleteImage(options.previewEle,'image',options.number);
        },initVideoUpload: function(options){
            var that = this;

            var defaults = {
                previewEle: "#layui-upload-videos ul",
                elem: "#upload_video",
                dataType: "xml",
                accept: "video",
                acceptMime: "video/mp4",
                auto: false,
                multiple: false,
                number: 5
            };

            options = $.extend({}, defaults ,options);
            // 选择文件后的回调函数
            options.choose = function(obj){
                fileName = that.randomString();
                that.clearFile();
                UPLOAD_FILES = obj.pushFile();
                var count = Object.keys(UPLOAD_FILES).length + $(options.previewEle + " li").length;
                
                if(count > options.number ){
                    layer.msg("最多只能上传" + options.number + "个视频", {icon: 2});
                    return false;
                }

                //获取签名
                that.getSignature('video');
                
                //预读本地文件，不支持ie8               
                obj.preview(function(index, file, result){
                    $(options.previewEle).append(that.imageHtml(index, ''));
                });
                console.log(that.url);
                setTimeout(function(){
                    var params = that.getUploadParam();
                    uploadInst.config.url = that.url;
                    uploadInst.config.data = params;

                    for (index in UPLOAD_FILES) {
                        uploadInst.config.data.key = that.uploadPath + fileName + ".mp4";
                        $(options.previewEle + " #" + index + " span").eq(0).text('上传中..');
                        obj.upload(index, UPLOAD_FILES[index]);
                    }
                }, 2000);
            }
            
            // 执行上传请求后的回调
            options.done = function(res, index, upload){
                var name = fileName + ".mp4";
                var path = that.uploadPath + name;

                that.videos+=path+',';
                $(options.previewEle + " #" + index + " span").html(fileName);
                $(options.elem).parent().prev().find('input').val(that.videos)
                $(options.previewEle + " #" + index + " img").eq(0).attr('data-path', path);
                // $(options.elem).parent().prev().find('input').val(path);
                // $(options.previewEle + " #" + index + " div").html('上传成功')

                $("#" + index + "_progress").remove();
            }

            options.progress = function(index, percent){
                var percent = percent + '%' //获取进度百分比
                filter_key = index + "_progress";
                element.progress(filter_key, percent);
            }

            // 执行上传错误后的回调
            options.error = function(index, upload){
                layer.msg('上传视频失败', {icon: 2});
            }

            uploadInst = upload.render(options);
            
            that.deleteImage(options.previewEle,'video',options.number,$(options.elem).parent().prev().find('input'));
        },
        // 获取阿里OSS直传签名
        getSignature: function (type) {
            var that = this;

            now = Date.parse(new Date()) / 1000;
            if (that.expire < now + 30)
            {
                admin.req('oss/signature', {type:type}, function (res) {
                    that.signatureParams = res.msg;
                }, 'post');
            }
        },
        //获取上传文件参数
        getUploadParam: function() {
            var that = this;
            var data = that.signatureParams;

            var params = {
                'key' : '',
                'policy': data.policy,
                'OSSAccessKeyId': data.accessid,
                'success_action_status' : '200',
                'bucket' : data.bucket,
                'signature': data.signature,
            }

            that.url = data.host;
            that.expire = data.expire;
            that.uploadPath = data.dir;
            return params;
        },
        //获取上传文件参数
        getVideoUrl: function(path) {
            var that = this,url;
            admin.reqAsync('oss/getVideo', {object: path}, function (res) {
                if(res.code == 0){
                    url = res.data.url;
                }
            }, 'post');
            return url ;
        },
        // 随机字符串
        randomString: function (len) {
            len = len || 32;
        　　var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';   
        　　var maxPos = chars.length;
        　　var str = '';
        　　for (i = 0; i < len; i++) {
                str += chars.charAt(Math.floor(Math.random() * maxPos));
            }

            return str;  
        },
        // 图片HTML
        imageHtml:function (index, image) {
            return '<li id="' + index + '"><img src="'+ image +'" data-path=""><i class="layui-icon layui-icon-close-fill"></i><span>等待上传</span><div class="progress_box" id="' + index + '_progress"><div class="layui-progress" lay-showpercent="yes" lay-filter="' + index + '_progress"><div class="layui-progress-bar layui-bg-red"></div></div></li>';
        },
        // 删除图片
        deleteImage: function(previewEle,type,imgNumber,object) {
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
                            console.log(res);
                            if(res.code == 200){

                                if(type == 'image'){
                                    if(imgNumber>1){
                                        that.vals=that.vals.replace(path+',','');
                                        object.val(that.vals);
                                    }else{
                                        that.vals= '';
                                        object.val('');
                                    }
                                }

                                if(type == 'video'){
                                    if(imgNumber>1){
                                        that.videos=that.videos.replace(path+',','');
                                        object.val(that.videos);
                                    }else{
                                        that.videos= '';
                                        object.val('');
                                    }
                                }

                            }else{
                                return layer.msg('删除失败');
                            }
                        }, 'post');
                    }
                    ele.closest('li').remove();
                })              
            })
        },        
        //清空文件队列
        clearFile: function(){
            for (let i in UPLOAD_FILES) {
                delete UPLOAD_FILES[i];
            }
        }
    }

    exports("alioss", alioss);
});