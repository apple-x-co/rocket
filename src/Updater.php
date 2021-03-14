<?php

namespace Rocket;

class Updater
{
    const URL = 'https://api.github.com/repos/apple-x-co/rocket/releases/latest';

    /** @var ArchiverInterface */
    private $archiver = null;

    /**
     * Updater constructor.
     *
     * @param ArchiverInterface $archiver
     */
    public function __construct(ArchiverInterface $archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * @return UpdaterResult
     */
    public function upgrade()
    {
        $latest = $this->getReleaseLatest();
        if ($latest === null) {
            return UpdaterResult::failure('No versions.');
        }

        if (version_compare(Main::VERSION, $latest['version'], '>=')) {
            return UpdaterResult::failure('Already have the latest version.');
        }

        // DOWNLOAD
        $tempfile = $this->download($latest['url']);

        // UNZIP
        $file_path = stream_get_meta_data($tempfile)['uri'];
        $this->archiver->unarchive($file_path);

        return UpdaterResult::success();
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

        if (! isset($result['tag_name'], $result['zipball_url'])) {
            return null;
        }

        return [
            'version' => substr($result['tag_name'], 1),
            'url'     => $result['zipball_url']
        ];
    }
}

class UpdaterResult {

    /** @var boolean */
    private $success;

    /** @var string */
    private $error;

    public static function success()
    {
        $instance = new static();
        $instance->success = true;

        return $instance;
    }

    /**
     * @param string $error
     *
     * @return UpdaterResult
     */
    public static function failure($error)
    {
        $instance = new static();
        $instance->success = false;
        $instance->error = $error;

        return $instance;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
