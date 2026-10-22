<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/22
 * Time: 10:57
 */

namespace console\controllers;
use common\models\psources\Coin;
use yii\console\Controller;


class CrawlerController extends Controller
{

    //更新发行时间
    public function actionCoinReleaseTime()
    {

        $sidItems = array_column(Coin::find()->select('sid')->asArray()->all(),'sid');
        foreach($sidItems as $sid){
            if($sid){
                $coinInfo = Coin::find()->where(['sid' => $sid])->one();
                $release = $this->getReleaseTime($sid);
                $isDate = $this->isDateTime($release);
                if(!$isDate){
                   $release = '';
                }
                $coinInfo->release = $release;
                $coinInfo->save();
                echo '币种名称: '.$coinInfo->name.'--'.$coinInfo->nickname.'--sid: '.$sid.PHP_EOL;
                echo '发行时间: '.$release.' 更新成功'.PHP_EOL;
            }
        }

    }

    //更新上架交易所
    public function actionCoinExchanges()
    {

        $sidItems = array_column(Coin::find()->select('sid')->asArray()->all(),'sid');
        foreach($sidItems as $sid){
            if($sid){
                $coinInfo = Coin::find()->where(['sid' => $sid])->one();
                $exchange = $this->getExchange($sid);
                $coinInfo->exchange = $exchange;
                $coinInfo->save();
                echo '币种名称: '.$coinInfo->name.'--'.$coinInfo->nickname.'--sid: '.$sid.PHP_EOL;
                echo '上架交易所数量: '.$exchange.' 更新成功'.PHP_EOL;
            }
        }

    }

    private function getReleaseTime($sid)
    {
        try{
            $feixiaohao_url = "https://www.feixiaohao.com/currencies/".$sid;
            $content = file_get_contents($feixiaohao_url);
            //preg_match 只匹配一次就返回
            $patt = "/<li><span class=tit>发行时间：<\/span> <span class=value>(.+?)<\/span><li>/";
            preg_match($patt, $content, $res);
            $release_time = $res[1];
            return $release_time;
        }catch (\Exception $e){
            echo $sid.'-----非小号上不存在';
            return '';
        }

    }

    private function getExchange($sid)
    {
        try{
            $feixiaohao_url = "https://www.feixiaohao.com/currencies/".$sid;
            $content = file_get_contents($feixiaohao_url);
            //preg_match 只匹配一次就返回
            $patt = "/<li><span class=tit>上架交易所：<\/span><span class=value> <a href=#tickerlist> (\d+)家 <\/a> <\/span><li>/";
            preg_match($patt, $content, $res);
            $exchange = $res[1];
            return $exchange;
        }catch (\Exception $e){
            echo $sid.'-----非小号上不存在';
            return 0;
        }

    }
    private function isDateTime($dateTime){
        $ret = strtotime($dateTime);
        return $ret !== FALSE && $ret != -1;
    }




}