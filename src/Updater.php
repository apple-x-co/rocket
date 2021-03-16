<?php

namespace Rocket;

use Rocket\Updater\Result;

class Updater
{
    const URL = 'https://api.github.com/repos/apple-x-co/rocket/releases/latest';

    /** @var string */
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return Result
     */
    public function upgrade()
    {
        $latest = $this->getReleaseLatest();
        if ($latest === null) {
            return Result::failure('No versions.');
        }

        if (version_compare(Main::VERSION, $latest['version'], '>=')) {
            return Result::failure('Already have the latest version.');
        }

        // Download
        $tempfile = $this->download($latest['url']);

        // Move
        $temp_path = stream_get_meta_data($tempfile)['uri'];
        $file_path = $this->dir . '/' . basename($latest['url']);
        rename($temp_path, $file_path);

        return Result::success($file_path);
    }

    private function download($url)
    {
        $tempfile = tmpfile();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: rocket.phar/' . Main::VERSION
        ]);
        curl_setopt($ch, CURLOPT_FILE, $tempfile);
        $result = curl_exec($ch);
        curl_close($ch);

        return $tempfile;
    }

    /**
     * @return array|null
     */
    private function getReleaseLatest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: rocket.phar/' . Main::VERSION
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if (! isset($result['tag_name'], $result['assets'][0]['browser_download_url'])) {
            return null;
        }

        return [
            'version' => $result['tag_name'],
            'url' => $result['assets'][0]['browser_download_url']
        ];
    }
}
