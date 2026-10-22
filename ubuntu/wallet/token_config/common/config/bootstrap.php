<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 15:51:07
 * @authors rlgy <rlgyzhcn@qq.com>
 */

// 代码库别名
Yii::setAlias('@api', WALLET_PROJECT_PATH . '/api');
Yii::setAlias('@common', WALLET_PROJECT_PATH . '/common');
Yii::setAlias('@backend', WALLET_PROJECT_PATH . '/backend');
Yii::setAlias('@console', WALLET_PROJECT_PATH . '/console');
Yii::setAlias('@vendor', WALLET_PROJECT_PATH . '/vendor');
Yii::setAlias('@h5', WALLET_PROJECT_PATH . '/h5');
Yii::setAlias('@wallet', WALLET_PROJECT_PATH . '/wallet');

// 运行目录别名
Yii::setAlias('@backend/runtime', WALLET_RUNTIME_PATH . '/backend');
Yii::setAlias('@console/runtime', WALLET_RUNTIME_PATH . '/console');
Yii::setAlias('@api/runtime', WALLET_RUNTIME_PATH . '/api');
Yii::setAlias('@h5/runtime', WALLET_RUNTIME_PATH . '/h5');
Yii::setAlias('@wallet/runtime', WALLET_RUNTIME_PATH . '/wallet');
