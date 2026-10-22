<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/13
 * Time: 11:52
 */
?>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<h4>所属分类---<?= $applicate_category->name["zh-CN"] ?></h4>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-tab layui-tab-card">
        <ul class="layui-tab-title">
            <li class="layui-this">H5 Banner</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <button class="layui-btn layui-btn-default add_banner">添加H5 banner</button>
                <table class="layui-table" id="table1" lay-filter="table1"></table>
            </div>
        </div>
    </div>
</div>

<div class="layui-row add" style="display: none;padding: 5px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)" id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input id="applicate_category_id" name="applicate_category_id" type="hidden"
                   value="<?= $applicate_category->id ?>">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图标</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="image1">
                    </div>
                    <input type="hidden" name="image" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">banner跳转地址</label>
                <div class="layui-input-block">
                    <input type="text" class="layui-input" name="banner_url" value="">
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>


<script type="text/javascript">
    var form = layui.form;
    form.render();
    var table = layui.table;
    var applicate_category_id = $("#applicate_category_id").val();
    table.render({
        elem: '#table1',
        url: '/admin/application-category/banner-index?id=' + applicate_category_id,
        cols: [[
            {
                field: 'id',
                title: 'banner编号',
                width: 200
            },
            {
                field: 'image_url',
                title: '图片链接',
                templet: function (d) {
                    return "<a href='" + d.image_url + "' target='_blank'>" + d.image_url + "</a>";
                }
            },
            {
                field: 'banner_url',
                title: 'banner跳转链接',
                templet: function (d) {
                    return "<a href='" + d.banner_url + "' target='_blank'>" + d.banner_url + "</a>";
                }
            },
            {
                title: '操作',
                templet: '#operationTpl',
                width: 300
            },
        ]],
    });
    table.on('tool(table1)', function (obj) {
        var data = obj.data;
        var event = obj.event;
        if (event === 'del') {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/application-category/delete-banner', {
                    id: data.id,
                    applicate_category_id: applicate_category_id
                }, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table.reload('table1');
                    }
                });
            });
        }
    });

    $('.add_banner').click(function () {
        var index = layer.open({
            title: '添加图片',
            area: '800px',
            type: 1,
            content: $("#_form"),
            btn: ['保存', '取消'],
            success: function () {
                clearForm();
            },
            btn1: function () {
                $.post('/admin/application-category/add-banner', $("#form1").serialize(), function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        $("#_form").css('display', 'none');
                        table.reload('table1');
                    }
                });
            },
            btn2: function () {
                layer.close(index);
                clearForm();
                $("#_form").css('display', 'none');
            },
            cancel: function () {
                layer.close(index);
                clearForm();
                $("#_form").css('display', 'none');
            }
        });
    })
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        before: function (input) {
            var data = {};
            data._csrf = $_csrf;
            data.image_type = 'h5_banner';
            this.data = data;
        },
        done: function (res) {
            if (res.code == 0) {
                $("input[name='image']").val(res.data.image_id);
                $("#image1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });

    function clearForm() {
        form.val("form1", {
            image: '',
        });
        $("#image1").attr('src', '');
        $("input[name='banner_url']").val('');
    }

</script>