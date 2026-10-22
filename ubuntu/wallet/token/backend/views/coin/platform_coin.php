<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/16
 * Time: 10:53
 */
?>

<h4>平台币种管理</h4>

<div class="layui-row layui-col-md6" >
    <table id="table1" class="layui-table" lay-filter="table1"></table>
</div>


<div class="layui-row add" style="display: none;padding: 5px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)"  id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input type="hidden" name="platform" value="">
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
        </form>
    </div>
</div>

<script>
    var table = layui.table;
    table.render({
        elem: "#table1",
        cols: [[
            {field: 'platform', title: '平台'},
            {field: 'icon', title: '图标', templet: "#iconTpl"},
            {field: 'brower_url', title:'区块链浏览器地址',edit:'text',width:500},
            {title: '操作', templet: "#operatorTpl"}
        ]],
        url: '/admin/coin/platform-coin'
    });

    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        data:{_csrf:$_csrf,image_type:'platform_icon'},
        done: function (res) {
            if(res.code == 0){
                $("input[name='image']").val(res.data.src);
                $("#image1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });
    var form = layui.form;
    form.render();
    table.on('tool(table1)', function(obj) {
        var event = obj.event;
        var data = obj.data;
        if (event === 'edit') {
            var index = layer.open({
                title: '编辑应用分类',
                area: '500px',
                type: 1,
                content: $("#_form"),
                btn: ['保存', '取消'],
                success: function() {
                    form.val("form1", {
                        platform: data.platform,
                        image: data.icon
                    });
                    $("#image1").attr('src', data.icon);
                },
                btn1: function() {
                    $.post('/admin/coin/add-platform-coin', $("#form1").serialize(), function(rev) {
                        layer.msg(rev.msg);
                        if (0 == rev.code) {
                            layer.close(index);
                            $("#_form").css('display', 'none');
                            table.reload('table1');
                        }
                        clearForm();
                    });
                },
                btn2: function() {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                    clearForm();
                },
                cancel: function() {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                    clearForm();
                }
            });
        }
    });
    function clearForm(){
        form.val("form1", {
            image: '',
            platform: ''
        });
        $("#image1").attr('src', '');
    }

    //监听单元格编辑
    table.on('edit(table1)', function(obj){
        var value = obj.value; //得到修改后的值
        var data = obj.data;    //得到所在行所有键值
        $.get('/admin/coin/set-brower', {platform:data.platform,brower_url:value}, function(rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                table.reload('table1',{});
            }else{

            }
        });
    });

</script>
<script type="text/html" id="operatorTpl">
    <button class="layui-btn layui-btn-xs" lay-event="edit">编辑币种图片</button>
</script>

<script type="text/html" id="iconTpl">
    <a href="{{d.icon}}" target="_blank"><img src="{{d.icon}}" style="max-width: 32px; max-height: 32px;"/></a>
</script>