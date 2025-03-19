<?php

namespace App\Services;

use GuzzleHttp\Client;
use DiDom\Document;
use DiDom\Element;

class CheckUrlService
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


        $res = $this->client->request('GET', $url, ['http_errors' => true]);
        $data['status_code'] = $res->getStatusCode();

        $htmlFromUrl = (string) $res->getBody();
        $document = new Document($htmlFromUrl);

        $data['title'] = $this->extractText($document, 'title');
        $data['h1'] = $this->extractH1($document);
        $data['description'] = $this->extractAttribute($document, 'meta[name="description"]', 'content');

        return $data;
    }


    private function extractText(Document $document, string $selector): ?string
    {
        $element = $document->first($selector);
        return $element->text();
    }

    private function extractH1(Document $document): ?string
    {
        $h1Element = $document->first('h1');

        return mb_substr($h1Element->text(), 0, 255);
    }

    private function extractAttribute(Document $document, string $selector, string $attribute): ?string
    {
        $element = $document->first($selector);
        return $element instanceof Element ? $element->getAttribute($attribute) : null;
    }
}
