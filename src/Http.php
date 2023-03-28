<?php

namespace Rocket;

class Http
{
    /** @var 'TLSv1_0'|'TLSv1_1'|'TLSv1_2'|'TLSv1_3'|null */
    private $ssl;

    /** @var bool */
    private $ignoreVerify;

    /**
     * @param string|null $ssl
     * @param bool        $ignoreVerify
     */
    public function __construct($ssl, $ignoreVerify = true)
    {
        $this->ssl = $ssl;
        $this->ignoreVerify = $ignoreVerify;
    }

    public function setupCurl($ch)
    {
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: rocket.phar/' . Version::ROCKET_VERSION]);

        if ($this->ignoreVerify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($this->ssl === null) {
            return;
        }

        if ($this->ssl === 'TLSv1_0') {
            if (defined('CURL_SSLVERSION_TLSv1_0')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);
            } else {
                error_log('Not supported TLSv1_0', 3, 'php://stderr');
            }

            return;
        }

        if ($this->ssl === 'TLSv1_1') {
            if (defined('CURL_SSLVERSION_TLSv1_1')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
            } else {
                error_log('Not supported TLSv1_1', 3, 'php://stderr');
            }

            return;
        }

        if ($this->ssl === 'TLSv1_2') {
            if (defined('CURL_SSLVERSION_TLSv1_2')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            } else {
                error_log('Not supported TLSv1_2', 3, 'php://stderr');
            }

            return;
        }

        if ($this->ssl === 'TLSv1_3') {
            if (defined('CURL_SSLVERSION_TLSv1_3')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_3);
            } else {
                error_log('Not supported TLSv1_3', 3, 'php://stderr');
            }
        }
    }

    /**
     * @param string $url
     *
     * @return false|resource
     */
    public function download($url)
    {
        $tempfile = tmpfile();

        $ch = curl_init();
        $this->setupCurl($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, false);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FILE, $tempfile);

        $result = curl_exec($ch);
        curl_close($ch);

        return $tempfile;
    }

    /**
     * @param string $url
     *
     * @return bool|string
     */
    public function get($url)
    {
        $ch = curl_init();
        $this->setupCurl($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, false);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $url
     * @param string $contentType
     * @param array  $data
     *
     * @return bool|string
     */
    public function post($url, $contentType, $data)
    {
        $ch = curl_init();
        $this->setupCurl($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: ' . $contentType]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
