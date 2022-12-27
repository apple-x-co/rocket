<?php

namespace Rocket;

use RuntimeException;

class Http
{
    const TLS_V_1_0 = 'TLSv1_0';
    const TLS_V_1_1 = 'TLSv1_1';
    const TLS_V_1_2 = 'TLSv1_2';

    /** @var string|null */
    private $tlsVersion;

    /**
     * @param string|null $tlsVersion
     */
    public function __construct($tlsVersion = null)
    {
        $this->tlsVersion = $tlsVersion;
    }

    /**
     * @param resource $ch
     *
     * @return void
     */
    private function setupCurl($ch)
    {
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: rocket.phar/' . Version::ROCKET_VERSION
        ]);

        if ($this->tlsVersion === null) {
            return;
        }

        if ($this->tlsVersion === self::TLS_V_1_0 && defined('CURL_SSLVERSION_TLSv1_0')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);
        }
        if ($this->tlsVersion === self::TLS_V_1_1 && defined('CURL_SSLVERSION_TLSv1_1')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
        }
        if ($this->tlsVersion === self::TLS_V_1_2 && defined('CURL_SSLVERSION_TLSv1_2')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        }
    }

    /**
     * @param string $url
     *
     * @return resource
     */
    public function download($url)
    {
        $tempfile = tmpfile();
        if ($tempfile === false) {
            throw new RuntimeException();
        }

        $ch = curl_init();
        $this->setupCurl($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, false);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FILE, $tempfile);

        curl_exec($ch);
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
     * @param string                  $url
     * @param string                  $contentType
     * @param array<array-key, mixed> $data
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
