<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/15
 * Time: 21:01
 */
?>
<style type="text/css">
    .uploader-list {
        margin-left: -15px;
    }

    .uploader-list .info {
        position: relative;
        margin-top: -25px;
        background-color: black;
        color: white;
        filter: alpha(Opacity=80);
        -moz-opacity: 0.5;
        opacity: 0.5;
        width: 100px;
        height: 25px;
        text-align: center;
        display: none;
    }

    .uploader-list .handle {
        position: relative;
        background-color: black;
        color: white;
        filter: alpha(Opacity=80);
        -moz-opacity: 0.5;
        opacity: 0.5;
        width: 100px;
        text-align: right;
        height: 18px;
        margin-bottom: -18px;
        display: none;
    }

    .uploader-list .handle span {
        margin-right: 5px;
    }

    .uploader-list .handle span:hover {
        cursor: pointer;
    }

    .uploader-list .file-iteme {
        margin: 12px 0 0 15px;
        padding: 1px;
        float: left;
    }
</style>

<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md6">
        <form class="layui-form" method="post" action="">
            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-upload">
                <button type="button" class="layui-btn" id="test2">多图片上传</button>
                <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 5px;width: 88%">
                    <div class="layui-upload-list uploader-list" style="overflow: auto;" id="uploader-list">
                    </div>
                </blockquote>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn">提交</button>
            </div>
        </form>
    </div>
</div>
<script>
    //form render
    var form = layui.form;
    form.render();

    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#test2",
        url: '/admin/image/upload',
        data: {_csrf: $_csrf},
        done: function (res) {
            if(res.code == 0){
                //上传完毕
                $('#uploader-list').append(
                    '<div id="" class="file-iteme">' +
                    '<div class="handle"><span class="glyphicon glyphicon-trash"></span></div>' +
                    '<img style="width: 100px;height: 100px;" src='+ res.data.image_src +'>' +
                    '<div class="info">' + res.data.image_id + '</div>' +
                    '<input type="hidden" name ="h5_icons[]" value=' +"'"+res.data.image_id +"'>"+
                    '</div>'
                );
            }
        },
        error: function (res) {

        }
    });

    $(document).on("mouseenter mouseleave", ".file-iteme", function(event){
        if(event.type === "mouseenter"){
            //鼠标悬浮
            $(this).children(".info").fadeIn("fast");
            $(this).children(".handle").fadeIn("fast");
        }else if(event.type === "mouseleave") {
            //鼠标离开
            $(this).children(".info").hide();
            $(this).children(".handle").hide();
        }
    });
    // 删除图片
    $(document).on("click", ".file-iteme .handle", function(event){
        $(this).parent().remove();
    });

</script>