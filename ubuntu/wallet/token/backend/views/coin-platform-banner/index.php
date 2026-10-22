<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<h4>banner管理列表</h4>
<div class="layui-row">
    <div class="layui-col-md1">
        <button class="layui-btn layui-btn-default" id="add">添加banner图</button>
    </div>
</div>
<div class="layui-col-md9">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>
<div class="layui-row add" style="display: none;padding: 50px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)"  id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">图片</label>
                    <div class="layui-input-block">
                        <img src="" style="margin-top: 11px; max-width: 32px; max-height: 32px"
                             id="icon1">
                    </div>
                    <input type="hidden" name="image_url" value="">
                </div>
                <div class="layui-inline" style="margin-left: 50px;">
                    <button type="button" class="layui-btn" id="upload1" style="">
                        <i class="layui-icon">&#xe67c;</i>上传新图片
                    </button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">图片类型</label>
                <div class="layui-input-block">
                    <select name="banner_type">
                        <option value="1">banner</option>
                        <option value="2">海报</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
<script type="text/javascript">
    var table = layui.table;
    table.render({
        elem: '#table1',
        url: '/admin/coin-platform-banner/index',
        cols: [[
            {
                field: 'id',
                title: '编号'
            },
            {
                field: 'coin_name',
                title: '钱包',
            },
            {
                field: 'image_url',
                title: '图片地址',
                width: 600
            },
            {
                title: '操作',
                templet: '#operationTpl',
                width: 200
            }
        ]],
    });

    var form = layui.form;
    form.render();

    table.on('tool(table1)', function(obj) {
        var event = obj.event;
        var data = obj.data;
        if (event === 'del') {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/coin-platform-banner/delete', {id: data.id}, function (rev) {
                    layer.msg(rev.msg);
                    if (0==rev.code) {
                        table.reload('table1');
                    }
                });
            });
        }
    });


    $('#add').click(function () {
        var index = layer.open({
            title: '添加banner',
            area: '750px',
            type: 1,
            content: $("#_form"),
            btn: ['保存', '取消'],
            success: function() {
                clearForm();
            },
            btn1: function() {
                $.post('/admin/coin-platform-banner/add', $("#form1").serialize(), function(rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        layer.close(index);
                        $("#_form").css('display', 'none');
                        clearForm();
                        table.reload('table1');
                    }
                });
            },
            btn2: function() {
                layer.close(index);
                $("#_form").css('display', 'none');
            },
            cancel: function() {
                layer.close(index);
                $("#_form").css('display', 'none');
            }
        });
    })

    function clearForm(){
        form.val("form1", {
            image_url: '',
            banner_url: ''
        });
        $("#icon1").attr('src', '');
    }
    //图片上传
    var uploader = layui.upload;
    $_csrf = $("input[name='_csrf']").val();
    uploader.render({
        elem: "#upload1",
        url: '/admin/oss/upload',
        data: {_csrf: $_csrf,image_type: 'banner'},
        done: function (res) {
            if(res.code == 0){
                $("input[name='image_url']").val(res.data.src);
                $("#icon1").attr('src', res.data.src);
            }
        },
        error: function (res) {

        }
    });

</script>