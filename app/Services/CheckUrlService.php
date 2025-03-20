<?php

namespace App\Services;

use DI\Container;
use DiDom\Element;
use DiDom\Document;
use GuzzleHttp\Client;

class CheckUrlService
{
    private Client $client;

    public function __construct(Container $container)
    {
        $this->client = $container->get('client');
    }

    public function checkUrl(string $url, int $id): array
    {
        $data = [
            'url_id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
            'status_code' => null,
            'error' => null
        ];

        try {
            $res = $this->client->request('GET', $url, ['http_errors' => false]); 
            $data['status_code'] = $res->getStatusCode();

            if ($data['status_code'] >= 400) {
                $data['error'] = "HTTP error: " . $data['status_code'];
                return $data;
            }

            $htmlFromUrl = (string) $res->getBody();
            $document = new Document($htmlFromUrl);

            $data['title'] = $this->extractText($document, 'title');
            $data['h1'] = $this->extractH1($document);
            $data['description'] = $this->extractAttribute($document, 'meta[name="description"]', 'content');

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['error'] = 'RequestException: ' . $e->getMessage();
        } catch (\Exception $e) {
            $data['error'] = 'Exception: ' . $e->getMessage();
        }

        return $data;
    }


    private function extractText(Document $document, string $selector): string
    {
        $element = $document->first($selector);
        return $element instanceof Element ? $element->text() : '';
    }

    private function extractH1(Document $document): string
    {
        $h1Element = $document->first('h1');

        return $h1Element instanceof Element ? mb_substr($h1Element->text(), 0, 255) : '';
    }

    private function extractAttribute(Document $document, string $selector, string $attribute): ?string
    {
        $element = $document->first($selector);
        return $element instanceof Element ? $element->getAttribute($attribute) : null;
    }
}
