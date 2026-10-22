<?php

namespace common\service\trusteeship;

use Yii;
use common\helpers\Curl;

class TrusteeShipService
{
    private $node_params;
    private $header;

    public function __construct($parameter = [], $header = [])
    {
        $platform_id = Yii::$app->request->getPlatformId();
        if (!empty($header)) {
            $this->header = $header;
        }

        if (empty($parameter)) {
            $this->node_params = Yii::$app->params['trusteeship']['node_' . $platform_id]['url'];
        } else {
            $this->node_params = $parameter['url'];
        }
    }

    public function urlBuild($uri = '')
    {
        return $this->node_params . '/' . $uri;
    }


    public function send($method = 'GET', $uri, $params = [])
    {
        $timeout = [
            'connect_timeout' => 50000,
            'timeout_ms' => 40000
        ];
        $ch = new Curl($timeout);
        if (!empty($this->header)) {
            $ch->setHeaders($this->header);
        }

        if (!empty($params)) {
            if ('GET' == strtoupper($method)) {
                $ch->setGetParams($params);
            }
            if ('POST' == strtoupper($method)) {
                $ch->setPostParams($params);
            }
        }

        $result = $ch->$method($this->urlBuild($uri), false);
        if (!$result) {
            return ['code' => -1, 'msg' => $ch->errorText];
        }
        if (200 == $result['code']) {
            return ['code' => $result['code'], 'msg' => $result['data']];
        } else {
            return ['code' => -1, 'msg' => $result['message']];
        }
    }

    public function getUserList($params = [])
    {
        $uri = 'backend/user/user-list';

        return $this->send("GET", $uri, $params);
    }

    public function getWalletBalance($params = [])
    {
        $uri = 'backend/account/wallet-balance';

        return $this->send("GET", $uri, $params);
    }

    public function getFlashListConfig($params = [])
    {
        $uri = 'backend/flash/flash-list-config';

        return $this->send("GET", $uri, $params);
    }

    public function getFlashDefaultConfig($params = [])
    {
        $uri = 'backend/flash/flash-default-config';

        return $this->send("GET", $uri, $params);
    }

    public function flashAddConfig($params = [])
    {
        $uri = 'backend/flash/flash-add-config';

        return $this->send("POST", $uri, $params);
    }

    public function flashUpdateConfig($params = [])
    {
        $uri = 'backend/flash/flash-update-config';

        return $this->send("POST", $uri, $params);
    }

    public function flashDeleteConfig($params = [])
    {
        $uri = 'backend/flash/flash-delete-config';

        return $this->send("POST", $uri, $params);
    }

    public function flashCloneConfig($params = [])
    {
        $uri = 'backend/flash/flash-clone-config';

        return $this->send("POST", $uri, $params);
    }

    public function coinList($params = [])
    {
        $uri = '/backend/coin/coin-list';

        return $this->send("GET", $uri, $params);
    }

    public function getUserAsset($params = [])
    {
        $uri = 'backend/user/asset';

        return $this->send("GET", $uri, $params);
    }

    public function getManualList($params = [])
    {
        $uri = 'backend/user/manual-real-list';

        return $this->send("GET", $uri, $params);
    }

    public function verifyReal($params = [])
    {
        $uri = 'backend/user/verify-real';

        return $this->send("POST", $uri, $params);
    }

    public function refuseReal($params = [])
    {
        $uri = 'backend/user/refuse-real';

        return $this->send("POST", $uri, $params);
    }

    public function revokeReal($params = [])
    {
        $uri = 'backend/user/revoke-real';

        return $this->send("POST", $uri, $params);
    }

    public function handleAsset($uri, $method, $params)
    {
        return $this->send($method, $uri, $params);
    }
}
