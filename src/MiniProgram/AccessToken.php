<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 12:09 AM
 */

namespace EasySwoole\WeChat\MiniProgram;

use EasySwoole\WeChat\Exception\RequestError;
use EasySwoole\WeChat\Utility\HttpClient;
use EasySwoole\WeChat\Exception\MiniProgramError;

/**
 * Class AccessToken
 *
 * @package EasySwoole\WeChat\MiniProgram
 */
class AccessToken extends MinProgramBase
{
    /**
     * getToken
     *  默认刷新一次
     *
     * @param int $refreshTimes
     * @return string|null
     * @throws MiniProgramError
     * @throws RequestError
     */
    public function getToken($refreshTimes = 1): ?string
    {
        if ($refreshTimes < 0) {
            return null;
        }
        $data = $this->getMiniProgram()->getConfig()->getStorage()->get('access_token');
        if (!empty($data)) {
            return $data;
        } else {
            $this->refresh();
            return $this->getToken($refreshTimes - 1);
        }
    }

    /**
     * refresh
     *
     * @return string
     * @throws MiniProgramError
     * @throws RequestError
     */
    public function refresh(): string
    {
        $config = $this->getMiniProgram()->getConfig();
        $url = ApiUrl::generateURL(ApiUrl::ACCESS_TOKEN, [
            'APPID'     => $config->getAppId(),
            'APP_SECRET' => $config->getAppSecret()
        ]);

        $responseArray = HttpClient::getForJson($url);
        $ex = MiniProgramError::hasException($responseArray);

        if ($ex) {
            throw $ex;
        }

        $token = $responseArray['access_token'];
        /*
         * 这里故意设置为7180
         */
        $config->getStorage()->set('access_token', $token, time() + 7180);

        return $token;
    }
}