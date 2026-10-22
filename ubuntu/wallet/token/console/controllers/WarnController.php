<?php

namespace console\controllers;

use linslin\yii2\curl\Curl;
use voku\helper\HtmlDomParser;
use yii\console\Controller;

class WarnController extends Controller
{
    public function actionAuto()
    {
        $redis = \Yii::$app->redis_user;
        if (!$redis->EXISTS("status")) {

            $curl = new Curl();
            $url = 'http://www.offcn.com/sydw/2020/0813/571289.html?www.offcn.com-hengtong';

            $response = $curl->get($url);
            $html = HtmlDomParser::str_get_html($response);
            $tbody = ($html->find('tbody'));
            if ($tbody->find('tr')) {
                foreach ($tbody->find('tr') as $key => $e) {
                    if ($key == 2) {
                        $string = HtmlDomParser::str_get_html($e->innertext);
                        if ($string->find('td')) {
                            if (strpos($string->find('td')[2]->innertext, '暂未公布') !== false) {
                                return 0;
                            } else {
                                $redis->set('status', date('Y-m-d H:i:s'));
                                try {
                                    $mail = \Yii::$app->mailer->compose();
                                    $mail->setFrom('sjm');
                                    $mail->setTo('sjm');
                                    $mail->setSubject("2020浙江事业单位统考笔试成绩");
                                    $mail->setHtmlBody("<h1>笔试成绩已发布!,进面最高分:" . $string->find('td')[3]->innertext . ";进面最低分:" . $string->find('td')[4]->innertext . "</h1>");
                                    $mail->send();
                                } catch (\ErrorException $e) {
                                    return 0;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
