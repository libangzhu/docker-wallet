<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\assets\AppAsset;

AppAsset::addCss($this, '@web/css/miner/add.css');
?>
<h1><?= Html::encode($this->title) ?></h1>
<p>
</p>
<!DOCTYPE html>
<html>
<script>
	$(document).ready(function(){
		var laydate = layui.laydate;
		laydate.render({
			elem: '.create_time' //指定元素
			,type: 'datetime'
			,range: '~'
		});
		var laypage = layui.laypage;
		//执行一个laypage实例
		laypage.render({
			elem: 'page'
			,count: <?= isset($data['count']) ? $data['count'] : 0 ?> //数据总数，从服务端得到
            ,limit: <?= isset($limit) ? $limit : 20?>
			,curr: <?= isset($_GET['page'])?$_GET['page']:1?>
			,theme: "#337ab7"
			,jump: function(obj, first){
				//首次不执行
				if(!first){
					window.location.href = "/admin/app/list?page="+obj.curr;
				}
			}
		});

        //提交搜索
		$(".btn_popularize_search").click(function(){
			$('.popularize_search').submit();
		});
        
        //添加图层
		$(".btn_add").click(function(){
			layer.open({
				type: 1,
                title: '版本信息',
				content: $('.add'), //这里content是一个普通的String
				scrollbar: false,
				area: '700px',
				cancel: function(index, layero){
					$('.add').attr('style', 'display:none')
				}
			})
		});

        //提交添加FORM
        $(".btn_add_submit").click(function(){
            $.ajax({
                type: "POST",
				url: "/admin/app/add",
                dataType: "json",
				data: $('.form_add').serialize(),
				success: function (result) {
					if (result['code'] == 200) {
						layer.msg('添加成功', {anim: 6, icon:1});
						setTimeout(function(){
							location.reload();
						}, 1000);
					} else {
						layer.msg(result.message, {anim: 6, icon:2})
                    }
				}
            })
		});

        //编辑图层
		$(".btn_edit").click(function(){
            var id = $(this).attr('kid');
            var type = $('.type'+id).attr('kid');
			var status = $('.status'+id).attr('kid');
			var version = $('.version'+id).html();
            var download_url = $(".download_url"+id+" a").html();
            var log = $('.log'+id).attr('kv');
				var html = '<div class="layui-row edit" style="">'+
					'<div class="layui-col-xs6 layui-col-sm6 layui-col-md11">'+
					'<form class="layui-form form_edit" action="javascript:void(0)" method="post">'+
					'<input type="hidden" name="id" value='+id+'>'+
					'<div class="layui-form-item">'+
					'<label class="layui-form-label">类型</label>'+
					'<div class="layui-input-block">'+
					// '<select class="my-select select-hand type" name="type"> <option value="1">安卓稳定版</option> <option value="2">安卓公测版</option> <option value="3">安卓内测版</option><option value="4">IOS稳定版</option> <option value="5">IOS公测版</option><option value="6">IOS内测版</option></select>'+
					'<select class="my-select select-hand type" name="type"> <option value="1">安卓稳定版</option><option value="4">IOS稳定版</option><option value="7">IOS商店版</option></select>'+
                    '</div></div>'+
                    '<div class="layui-form-item">'+
                    '<label class="layui-form-label">状态</label>'+
					'<div class="layui-input-block">'+
					// '<select class="my-select select-hand status" name="status"> <option value="1">上线</option> <option value="2">推荐更新</option> <option value="3">下线</option> </select>'+
                    '<select class="my-select select-hand status" name="status"> <option value="1">上线</option><option value="3">下线</option> </select>'+
                    '</div></div>'+
					'<div class="layui-form-item">'+
					'<label class="layui-form-label">版本</label>'+
					'<div class="layui-input-block">'+
					'<input type="text" name="version" required  lay-verify="required" placeholder="" autocomplete="off" class="layui-input" value='+version+'>'+
					'</div></div>'+
					'<div class="layui-form-item layui-form-text">'+
					'<label class="layui-form-label">下载地址</label>'+
					'<div class="layui-input-block">'+
					'<input type="text" name="download_url" placeholder="" autocomplete="off" class="layui-input" value='+download_url+'>'+
					'</div></div>'+
					'<div class="layui-form-item layui-form-text">'+
					'<label class="layui-form-label">更新日志</label>'+
					'<div class="layui-input-block">'+
					'<textarea name="log" placeholder="请输入内容" class="layui-textarea">'+log+'</textarea>'+
					'</div> </div>'+
					'<div class="layui-form-item">'+
					'<div class="layui-input-block">'+
					'<button class="layui-btn btn_edit_submit" lay-submit lay-filter="formDemo">立即提交</button>'+
					'<button type="reset" class="layui-btn layui-btn-primary">重置</button>'+
					'</div> </div></form> </div> </div>';
				//打开编辑图层
				layer.open({
                    type: 1,
					content: html,
				    title: '矿机信息',
				    scrollbar: false,
				    area: '700px',
				    cancel: function(index, layero){
				    	$('.edit').attr('style', 'display:none');
				    }
				});
                $(".type option[value= '"+type+"']").attr("selected","selected");
                $(".status option[value= '"+status+"']").attr("selected","selected");

				//提交FORM
				$(".btn_edit_submit").click(function(){
					$.ajax({
						type: "POST",
						url: "/admin/app/edit",
						dataType: "json",
						data: $('.form_edit').serialize(),
						success: function (result) {
							if (result['code'] == 200) {
								layer.msg('修改成功', {anim: 6, icon:1});
								setTimeout(function(){
									location.reload();
								}, 1000);
							} else {
								layer.msg(result.message, {anim: 6, icon:2})
							}
						}
					})
				});
		});
        //查看详情图层
		$(".btn_detail").click(function(){
            var id = $(this).attr('kid');
            var type = $('.type'+id).attr('kid');
			var status = $('.status'+id).attr('kid');
			var version = $('.version'+id).html();
			var download_url = $(".download_url"+id+" a").html();
			var log = $('.log'+id).attr('kv');
			var html = '<div class="layui-row edit" style="">'+
					'<div class="layui-col-xs6 layui-col-sm6 layui-col-md11">'+
					'<form class="layui-form form_edit" action="javascript:void(0)" method="post">'+
					'<input type="hidden" name="id" value='+id+'>'+
					'<div class="layui-form-item">'+
					'<label class="layui-form-label">类型</label>'+
					'<div class="layui-input-block">'+
					'<select class="my-select select-hand type" name="type" disabled="disabled"> <option value="1">安卓稳定版</option> <option value="2">安卓公测版</option> <option value="3">安卓内测版</option><option value="4">IOS稳定版</option> <option value="5">IOS公测版</option><option value="6">IOS内测版</option><option value="7">IOS商店版</option></select>'+
					'</div></div>'+
                    '<div class="layui-form-item">'+
                    '<label class="layui-form-label">状态</label>'+
					'<div class="layui-input-block">'+
					'<select class="my-select select-hand status" name="status" disabled="disabled"> <option value="1">上线</option> <option value="2">推荐更新</option> <option value="3">下线</option> </select>'+
					'</div></div>'+
					'<div class="layui-form-item">'+
					'<label class="layui-form-label">版本</label>'+
					'<div class="layui-input-block">'+
					'<input type="text" name="version"  disabled="disabled" required  lay-verify="required" placeholder="" autocomplete="off" class="layui-input" value='+version+'>'+
					'</div></div>'+
					'<div class="layui-form-item layui-form-text">'+
					'<label class="layui-form-label">下载地址</label>'+
					'<div class="layui-input-block">'+
					'<input type="text" name="download_url"  disabled="disabled" required  lay-verify="required" placeholder="" autocomplete="off" class="layui-input" value='+download_url+'>'+
					'</div></div>'+
					'<div class="layui-form-item layui-form-text">'+
					'<label class="layui-form-label">更新日志</label>'+
					'<div class="layui-input-block">'+
					'<textarea name="log" placeholder="请输入内容"  disabled="disabled" class="layui-textarea">'+log+'</textarea>'+
					'</div> </div>'+
					'<div class="layui-form-item">'+
					'<div class="layui-input-block">'+
					'</div> </div></form> </div> </div>';	
				
				//打开查看图层
				layer.open({
					type: 1,
					content: html,
					title: '矿机信息',
					scrollbar: false,
					area: '700px',
					cancel: function(index, layero){
						$('.edit').attr('style', 'display:none');
					}
				});
				$(".type option[value= '"+type+"']").attr("selected","selected");
				$(".status option[value= '"+status+"']").attr("selected","selected");		
		});

	});
</script>
<body>
    <div>
        <a class="btn btn-primary btn_add">添加<i class="fa fa-plus"></i></a>
    </div>

  <div class = "layui-tab layui-tab-card">
    <ul  class = "layui-tab-title">
        <li  class = "layui-this">Android</li>
        <li>IOS</li>
    </ul>
  <div class="layui-tab-content">
    <div class="layui-tab-item layui-show">
        <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>编号</th>
            <th>版本号</th>
            <th>类型</th>
            <th>状态</th>
            <th>下载链接</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
    	<?php
            $getType = [
                1 => '安卓稳定版',
                // 2 => '安卓公测版',
                // 3 => '安卓内测版',
                4 => 'IOS稳定版',
                // 5 => 'IOS公测版',
                // 6 => 'IOS内测版',
                7 => 'IOS商店版',
            ];
            $getStatus = [
                1 => '上线',
                // 2 => '推荐更新',
                3 => '下线'
            ];
            $android = [1, 2, 3];
            $ios = [4, 5, 6, 7];
            $r = isset($data['rows']) ? $data['rows'] : [];
            if ($r) {  
    	    foreach ($r as $key => $value) {
                if (in_array($value['type'], $android)) {
                    $value['updated_at'] = date("Y-m-d H:i:s",strtotime($value['created_at']));
                    $value['log'] = implode(',', $value['log']);
                    echo <<<EOA
    	            <tr>
    	                <td>{$value['id']}</td>
    	                <td class = 'version{$value["id"]}'>{$value['version']}</td>
    	                <td class = 'type{$value["id"]}' kid = "{$value['type']}">{$getType[$value['type']]}</td>
                        <td class = 'status{$value["id"]}' kid = "{$value['status']}">
EOA;
                    switch ($value['status']){
                        case 1:
                            echo  "<font color = 'green'>{$getStatus[$value['status']]}</font>";
                            break;
                        case 2:
                            echo  "<font color = 'orange'>{$getStatus[$value['status']]}</font>";
                            break;    
                        case 3:
                            echo  "<font color = 'grey'>{$getStatus[$value['status']]}</font>";
                            break;
                    }    
                        echo <<<EOF
                        </td>
                        <td class = 'download_url{$value["id"]}'><a href = '{$value["download_url"]}' download = 'android'>{$value['download_url']}</a></td>
                        <td >{$value['updated_at']}</td>
                        <input type = 'hidden' class = 'log{$value["id"]}' kv = "{$value['log']}"/>
                        <td>
                            <a class = "btn_edit" kid = "{$value['id']}"><i class = "layui-icon">&#xe642;</i></a>
                            <a class = "btn_detail" kid  = "{$value['id']}"><i class = "layui-icon">&#xe60a;</i></a>
                            <a class = "btn_delete" kid  = "{$value['id']}"><i class = "layui-icon" style = "color: red">&#xe640;</i></a>
                        </td>
    	            </tr>
    	              
    	     
EOF;
                }
            }
    	    }
    	?>
         </table>
    </div>
  
    <div class="layui-tab-item">
    
     <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>编号</th>
            <th>版本号</th>
            <th>类型</th>
            <th>状态</th>
            <th>下载链接</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
    	<?php
        $r = isset($data['rows']) ? $data['rows'] : [];
            if ($r) {  
    	    foreach ($r as $key => $value) {
                if (in_array($value['type'], $ios)) {
                    $value['updated_at'] = date("Y-m-d H:i:s",strtotime($value['created_at']));
                    $value['log'] = implode(',', $value['log']);
					echo <<<EOA
                    <tr>
                    <td>{$value['id']}</td>
                    <td class = 'version{$value["id"]}'>{$value['version']}</td>
                    <td class = 'type{$value["id"]}' kid = "{$value['type']}">{$getType[$value['type']]}</td>
                    <td class = 'status{$value["id"]}' kid = "{$value['status']}">
EOA;
                    switch ($value['status']){
                        case 1:
                            echo  "<font color = 'green'>{$getStatus[$value['status']]}</font>";
                            break;
                        case 2:
                            echo  "<font color = 'orange'>{$getStatus[$value['status']]}</font>";
                            break;    
                        case 3:
                            echo  "<font color = 'grey'>{$getStatus[$value['status']]}</font>";
                            break;
                    }
                    echo <<<EOF
                    </td>
                    <td class = 'download_url{$value["id"]}'><a href = '{$value["download_url"]}'  download = 'android'>{$value['download_url']}</a></td>
                    <td >{$value['updated_at']}</td>
                    <input type = 'hidden' class = 'log{$value["id"]}' kv = "{$value['log']}"/>
                    <td>
                        <a class = "btn_edit" kid = "{$value['id']}"><i class = "layui-icon">&#xe642;</i></a>
                        <a class = "btn_detail" kid  = "{$value['id']}"><i class = "layui-icon">&#xe60a;</i></a>
                        <a class = "btn_delete" kid  = "{$value['id']}"><i class = "layui-icon" style = "color: red">&#xe640;</i></a>
                    </td>
                </tr>
    	                       
EOF;
                }
    	    }
        }
    	?>
        </table>
    </div>
  </div>
</div>
<div id = 'page'></div>
<!-- 添加页面 -->
<div class="layui-row add" style="display: none">
    <div class="layui-col-xs6 layui-col-sm6 layui-col-md11">
        <form class="layui-form form_add" action="javascript:void(0)" method="post">
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                   <!-- <?= Html::dropDownList('type', null, [1 => '安卓稳定版', 2 => '安卓公测版', 3 => '安卓内测版', 4 => 'IOS稳定版',  5 => 'IOS公测版', 6 => 'IOS内测版'],['class' => 'my-select select-hand'])?> -->
                   <?= Html::dropDownList('type', null, [1 => '安卓稳定版', 4 => 'IOS稳定版', 7 => 'IOS商店版'],['class' => 'my-select select-hand'])?>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                      <!-- <?= Html::dropDownList('status', null, [1 => '上线', 2 => '推荐更新', 3 => '下线'],['class' => 'my-select select-hand'])?> -->
                      <?= Html::dropDownList('status', null, [1 => '上线', 3 => '下线'],['class' => 'my-select select-hand'])?>
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">版本</label>
                <div class="layui-input-block">
                    <input type="text" name="version" required  lay-verify="required" placeholder="" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">下载链接</label>
                <div class="layui-input-block">
                    <input type="text" name="download_url" placeholder="" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">更新日志</label>
                <div class="layui-input-block">
                    <textarea name="log" placeholder="请输入内容" class="layui-textarea"></textarea>
                </div>
            </div>
             <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn btn_add_submit" lay-submit lay-filter="formDemo">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>

