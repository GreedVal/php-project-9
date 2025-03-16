<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;
use DiDom\Document;
use DiDom\Element;
use Valitron\Validator;
use Throwable;

class CheckUrlServices
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function checkUrl(string $url, int $id): array
    {
        $data = [
            'url_id' => $id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $res = $this->client->request('GET', $url, ['http_errors' => true]);
            $data['status_code'] = $res->getStatusCode();
        } catch (ClientException | ConnectException | TransferException $e) {
            return $this->handleException($e, $data, 'Ошибка при проверке URL');
        }

        $htmlFromUrl = (string) $res->getBody();
        $document = new Document($htmlFromUrl);

        $data['title'] = $this->extractText($document, 'title');
        $data['h1'] = $this->extractH1($document);
        $data['description'] = $this->extractAttribute($document, 'meta[name="description"]', 'content');

        return $data;
    }

    private function handleException(Throwable $e, array &$data, string $message): array
    {
        if ($e instanceof RequestException && $e->getResponse()) {
            $data['status_code'] = $e->getResponse()->getStatusCode();
        } else {
            $data['status_code'] = 500;
        }
        
        return $data;
    }

    private function extractText(Document $document, string $selector): ?string
    {
        $element = $document->first($selector);
        return $element instanceof Element ? $element->text() : null;
    }

    private function extractH1(Document $document): ?string
    {
        $h1Element = $document->first('h1');

        if ($h1Element instanceof Element) {
            $text = $h1Element->text();
            $validator = new Validator(['h1' => $text]);
            $validator->rule('lengthMax', 'h1', 255);

            return $validator->validate() ? $text : mb_substr($text, 0, 255);
        }

        return null;
    }

    private function extractAttribute(Document $document, string $selector, string $attribute): ?string
    {
        $element = $document->first($selector);
        return $element instanceof Element ? $element->getAttribute($attribute) : null;
    }
}
