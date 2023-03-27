<?php

namespace Rocket;

use Rocket\Updater\Result;

class Updater
{
    const URL = 'https://api.github.com/repos/apple-x-co/rocket/releases/latest';

    /** @var string */
    private $dir;

    /** @var Http */
    private $http;

    /**
     * @param string $dir
     * @param Http $http
     */
    public function __construct($dir, $http)
    {
        $this->dir = $dir;
        $this->http = $http;
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

        if (version_compare(Version::ROCKET_VERSION, $latest['version'], '>=')) {
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
        return $this->http->download($url);
    }

    /**
     * @return array|null
     */
    private function getReleaseLatest()
    {
        $result = $this->http->get(self::URL);
        $result = json_decode($result, true);

        if (! isset($result['tag_name'], $result['assets'][0]['browser_download_url'])) {
            return null;
        }

        $version = $result['tag_name'];
        if (strpos($version, 'v') === 0) {
            $version = substr($version, 1);
        }

        return [
            'version' => $version,
            'url' => $result['assets'][0]['browser_download_url']
        ];
    }
}
