<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/15
 * Time: 14:29
 */
?>
<style>
    .layui-form-label {
        width: 100px;
    }
</style>
<h4>所属分类---<?= $parent_category->name["zh-CN"] ?></h4>
<div class="layui-row" style="padding: 5px;">
    <div class="layui-col-md1">
        <a href="/admin/application/add?category_id=<?= $parent_category->id ?>">
            <button class="layui-btn layui-btn-default" id="add">添加应用</button>
        </a>
    </div>
</div>
<input id="category_id" type="hidden" value="<?= $parent_category->id ?>">
<div class="layui-col-md10">
    <table class="layui-table" id="table1" lay-filter="table1"></table>
</div>

<!-- 添加页面 -->
<div class="layui-row" style="display: none;padding: 5px;" id="_form">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form" action="javascript:void(0)" id="form1" method="post" lay-filter="form1">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
            <input id="app_id" name="app_id" type="hidden" value="">
            <div class="layui-form-item">
                <label class="layui-form-label">所属分类</label>
                <div class="layui-input-block">
                    <select name="cate_id" id="cate_id">
                        <?php foreach ($cate_items as $cate_item): ?>
                            <option value="<?= $cate_item['id'] ?>"><?= $cate_item['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">应用排序</label>
                <div class="layui-input-block">
                    <input class="layui-input" name="sort" value="">
                </div>
            </div>
        </form>
    </div>
</div>


<script type="text/html" id="iconTpl">
    <img src="{{d.icon_url}}" style="max-width: 32px; max-height: 32px;"/>
</script>
<script type="text/html" id="operationTpl">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="add_category">添加所属分类</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs" href="/admin/application/image-index?id={{d.id}}">详情图管理</a>
</script>
<script type="text/html" id="recommendTpl">
    <input type="checkbox" name="isrecommend" value="{{d.id}}" lay-skin="switch" lay-text="是|否"
           lay-filter="recommendDemo" {{ d.isrecommend== 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="enableTpl">
    <input type="checkbox" name="isenable" value="{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="enableDemo"
           {{ d.enable== 1 ? 'checked' : '' }}>
</script>
<script type="text/javascript">
    var form = layui.form;
    form.render();
    var table = layui.table;
    var category_id = $("#category_id").val();
    table.render({
        elem: '#table1',
        page: true,
        limit: 10,
        url: '/admin/application/list?id=' + category_id,
        cols: [[
            {
                field: 'id',
                title: 'ID'
            },
            {
                field: 'name',
                title: '名称',
                templet: function (data) {
                    var name = JSON.parse(data.name);
                    console.log(typeof (data.name), name.zh);
                    return name.zh
                }
            },

            {
                field: 'icon_url',
                title: '图标',
                templet: '#iconTpl'
            },
            {
                field: 'sort',
                title: '排序',
                edit: 'text'
            },
            {
                field: 'has_h5',
                title: 'h5',
                templet: function (d) {
                    if (d.has_h5) {
                        return "有";
                    } else {
                        return "无";
                    }
                }
            },
            {
                field: 'has_android',
                title: '安卓',
                templet: function (d) {
                    if (d.has_android) {
                        return "有";
                    } else {
                        return "无";
                    }
                }
            },
            {
                field: 'has_ios',
                title: 'ios',
                templet: function (d) {
                    if (d.has_ios === 3) {
                        return "企业版/商店版";
                    } else if (d.has_ios === 2) {
                        return "商店版";
                    } else if (d.has_ios === 1) {
                        return "企业版";
                    } else {
                        return "无";
                    }
                }
            },
            {
                title: '操作',
                templet: '#operationTpl',
                width: 300
            },
            {
                title: '首页推荐',
                field: 'isrecommend',
                templet: '#recommendTpl',
                width: 100
            },
            {
                title: '状态',
                field: 'enable',
                templet: '#enableTpl',
                width: 100
            },
        ]],
    });

    table.on('tool(table1)', function (obj) {
        var data = obj.data;
        var event = obj.event;
        if (event === 'edit') {
            $.get('/admin/application/edit', {id: data.id, category_id: category_id}, function (str) {
                var editIndex = layer.open({
                    type: 1,
                    title: '编辑: ' + data.name,
                    area: ['625px', '800px'],
                    content: str,
                    btn: ['保存', '取消'],
                    btn1: function () {
                        // console.log();
                        $.post('/admin/application/edit', $("#applicateEdit").serialize(), function (rev) {
                            layer.msg(rev.msg);
                            if (rev.code == 0) {
                                table.reload("table1", {
                                    page: {
                                        curr: 1
                                    }
                                });
                                layer.close(editIndex);
                            }
                        });
                    }
                });
            });
        } else if ('del' === event) {
            var index = layer.confirm("确认删除?", {icon: 3, title: '删除'}, function () {
                $.get('/admin/application/delete', {id: data.id, category_id: category_id}, function (rev) {
                    layer.msg(rev.msg);
                    if (0 == rev.code) {
                        table.reload('table1', {
                            page: {curr: 1}
                        });
                    }
                });
            });
        } else if ('add_category' === event) {
            $("#app_id").val(data.id);
            var index = layer.open({
                title: '添加应用分类',
                area: ['500px', '400px'],
                type: 1,
                content: $("#_form"),
                btn: ['保存', '取消'],
                success: function () {
                    form.val("form1", {
                        cate_id: 0,
                        sort: '',
                    });
                },
                btn1: function () {
                    $.post('/admin/application/add-category', $("#form1").serialize(), function (rev) {
                        layer.msg(rev.msg);
                        if (0 == rev.code) {
                            layer.close(index);
                            $("#_form").css('display', 'none');
                        }
                    });
                },
                btn2: function () {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                },
                cancel: function () {
                    layer.close(index);
                    $("#_form").css('display', 'none');
                }
            });
        }
    });
    form.on('switch(recommendDemo)', function (obj) {
        //layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);return;
        if (obj.elem.checked) {
            $.get('/admin/applicate-recommend/add', {id: this.value, type: 2}, function (rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                } else {

                }
            });
        } else {
            $.get('/admin/applicate-recommend/delete', {id: this.value, type: 2}, function (rev) {
                layer.msg(rev.msg);
                if (0 == rev.code) {
                } else {

                }
            });
        }
    });

    form.on('switch(enableDemo)', function (obj) {
        var enable = 1;
        if (obj.elem.checked) {
            enable = 1
        } else {
            enable = 0;
        }
        $.get('/admin/application/set-enable', {id: this.value, enable: enable}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
            } else {

            }
        });
    });

    //监听单元格编辑
    table.on('edit(table1)', function (obj) {
        var value = obj.value; //得到修改后的值
        var data = obj.data;    //得到所在行所有键值
        $.get('/admin/application/set-sort', {id: data.id, category_id: category_id, sort: value}, function (rev) {
            layer.msg(rev.msg);
            if (0 == rev.code) {
                table.reload('table1', {});
            } else {

            }
        });
    });
</script>