<?php
namespace App\Libraries;
use request;

class common
{
    /**
     * 填充默认的header信息
     *
     **/
    private function defaultHeader($header)
    {
        if (app()->runningInConsole()) {
            return array_merge($header, ['logid' => uniqid() . rand(1, 1000), 'trace' => 0]);
        } else {
            parse_str($_SERVER['QUERY_STRING'], $arrQuery);
            $header['logid'] = isset($_SERVER['HTTP_LOGID']) ? $_SERVER['HTTP_LOGID'] : (isset($arrQuery['logid']) ? $arrQuery['logid'] : uniqid() . rand(1, 1000));
            $header['trace'] = isset($_SERVER['HTTP_TRACE']) ? intval($_SERVER['HTTP_TRACE']) + 1 : (isset($arrQuery['trace']) ? intval($arrQuery['trace']) + 1 : 0);

            return $header;
        }
    }
    /**
	 * 添加 xdebug 调试参数
	 *
	 * @param $url
	 *
	 * @return string
	 */
	private function addXdebugParams($url)
	{
		if (extension_loaded('xdebug')) {
			$parsed = parse_url($url);
			$xdebugParams = 'XDEBUG_SESSION_START=' . rand(10000, 20000);
			if (empty($parsed['query'])) {
				$url .= '?' . $xdebugParams;
			} else {
				$url .= '&' . $xdebugParams;
			}
		}

		return $url;
	}
     /**
     * request POST FORM DATA
     * @param $requestUrl
     * @param $param
     * @return mixed
     */
    public function request($requestUrl, $param, $headers = [])
    {
        $headers    = $this->defaultHeader($headers);
        $httpClient = app('HttpClient');
        $startTime  = microtime(true);
	    $requestUrl = $this->addXdebugParams($requestUrl);
	    try {
            $i = 0;
            request:
            $req = $httpClient->request('POST', $requestUrl, ['form_params' => $param, 'headers' => $headers, 'timeout' => 30, 'connect_timeout' => 30]);

            $result = $req->getBody()->getContents();
        } catch (RuntimeException $e) {
            if ($i < 5) {
                $i++;
                goto request;
            } else {
                throw $e;
            }
        }
        return $result;
    }
}