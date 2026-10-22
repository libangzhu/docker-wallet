<?php

\backend\assets\TreeAsset::register($this);
?>

    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-tree font-dark-sunglo"></i>
                <span class="caption-subject font-dark sbold uppercase">菜单树</span>
            </div>
            <div class="actions">

            </div>
        </div>
        <div class="portlet-body">
            <div id="tree_3" class="tree-demo" style="min-width: 190px">

            </div>
        </div>
    </div>

    <!-- 定义数据块 -->
<?php $this->beginBlock('test'); ?>
    jQuery(document).ready(function() {

    $("#tree_3").jstree({
    "plugins" : [ "themes", "state", "types"],
    "core" : {
    'multiple' : false,
    'data': {
    "url" : "<?= yii\helpers\Url::toRoute('/menu/tree'); ?>",
    "dataType" : "json"
    },
    },
    "state" : { "key" : "demo2" },

    });

    $("#tree_3").on('activate_node.jstree', function(e, data){
    var currentNode = data.node;
    console.log(currentNode);
    });

    });
<?php $this->endBlock() ?>
    <!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>