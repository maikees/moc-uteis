<?php

namespace MOCUtils\Helpers;

use GuzzleHttp\Client;

/**
 * Class SlackException
 * @package MOCUtils\Helpers
 */
class SlackException extends \Exception
{
    private $options;
    private $client;
    private $apiUrl;

    /**
     * SlackException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->apiUrl = "https://slack.com/api/files.upload";

        $this->options = ['headers' =>
            [
//                "Content-Type" => "application/json; charset=utf-8",
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getToken(),
            ]
        ];

        $this->client = new Client(['verify' => false]);

        return $this->enviarNotificacao();
    }

    /**
     * @return mixed
     */
    private function getServer()
    {
        $server = @$_SERVER;

        unset($server['HTTP_COOKIE']);
        unset($server['PATH']);
        unset($server['HTTP_ACCEPT_ENCODING']);
        unset($server['HTTP_ACCEPT_LANGUAGE']);
        unset($server['SystemRoot']);
        unset($server['COMSPEC']);
        unset($server['PATHEXT']);
        unset($server['WINDIR']);

        return $server;
    }

    /**
     * @return mixed
     */
    private function getSession()
    {
        return @$_SESSION;
    }

    /**
     * @return mixed
     */
    private function getToken()
    {
        return env('SLACK_TOKEN');
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function enviarNotificacao()
    {
        $text = [
            "Message" => $this->getMessage(),
            "Session" => $this->getSession(),
            "Request" => $this->getServer(),
            "Exception" => $this->getTrace()
        ];

        $obj = $this->setObject($text);

        $this->options['multipart'] = [
            [
                "name" => "file",
                "contents" => $obj,
                "filename" => "Detalhes.json"
            ],
            [
                "name" => "channels",
                "contents" => "app_errors"
            ]
        ];

        try {
            $res = $this->client->post($this->apiUrl, $this->options);

            $result = $res->getBody()->getContents();

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $object
     * @return false|string
     */
    private function setObject($object)
    {
//        $this->options["body"] = $object;
        return json_encode($object, JSON_PRETTY_PRINT);
    }
}
