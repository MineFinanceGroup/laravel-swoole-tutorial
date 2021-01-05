<?php

namespace App\Traits\Swoole;

use Swoole\Coroutine\Http\Client;

use Exception;

trait FetcherTrait
{
    public function fetch(string $url, array $data = [], string $method = 'GET', array $customHeaders = []): ?object
    {
        $urlInfo = $this->parseUrl($url);

        $client = new Client($urlInfo['host'], $urlInfo['port'], $urlInfo['ssl']);
        $baseHeaders = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $headers = array_replace(
            $baseHeaders,
            $customHeaders
        );

        $client->setHeaders($headers);

        try {
            switch (strtolower($method)) {
                case 'get':
                    $url = (!empty($data)) ? ($urlInfo['path']. '?' . http_build_query($data)) : $urlInfo['path'];
                    $client->get($url);
                    break;
                case 'post':
                    $client->post($urlInfo['path'], json_encode($data));
                    break;
                default:
                    throw new Exception("Method \"{$method}\" unsupported");
            }
        } catch (Exception $e){
            $client->close();
            throw new Exception("Http client error: {$e->getMessage()}");
        }

        if (isset($client->errCode) && $client->errCode == 110) {
            $client->close();
            throw new Exception("Error: Connection timeout");
        }

        $body = $client->getBody();
        $client->close();
        if (!$body){
            throw new Exception("Can't get body on {$url}");
        }

        $decodedBody = json_decode($body);
        if (!$decodedBody){
            throw new Exception("Can't decode body:\r\n {$decodedBody}");
        }

        return $decodedBody;
    }

    private function parseUrl(string $url): array
    {
        $urlInfo = parse_url($url);

        $response = [
            'host' => $urlInfo['host'],
            'port' => 80,
            'ssl'  => false,
            'path' => $urlInfo['path']
        ];

        if (!empty($urlInfo['query'])) {
            $response['path'] .= "?{$urlInfo['query']}";
        }

        if (isset($urlInfo['scheme']) && $urlInfo['scheme'] == 'https') {
            $response['port'] = 443;
            $response['ssl']  = true;
        }

        return $response;
    }
}
