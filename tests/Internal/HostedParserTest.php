<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use HosmelQ\Anydoc\Exceptions\HostedException;
use HosmelQ\Anydoc\Internal\HostedParser;

it('reports the keyless hosted limit', function (): void {
    $apiKey = getenv('FIRECRAWL_API_KEY');
    $handler = new MockHandler([
        new Response(429, body: '{"success":false,"error":"Rate limit exceeded"}'),
    ]);
    $parser = new HostedParser(new Client(['handler' => $handler]));
    putenv('FIRECRAWL_API_KEY');

    try {
        expect(fn (): string => $parser->markdown(
            anydocMixedPdf(),
            'document.pdf',
            null,
            'https://api.test',
        ))
            ->toThrow(
                HostedException::class,
                'Firecrawl Parse keyless limit reached, set FIRECRAWL_API_KEY: Rate limit exceeded',
            );
    } finally {
        is_string($apiKey) ? putenv("FIRECRAWL_API_KEY={$apiKey}") : putenv('FIRECRAWL_API_KEY');
    }
});

it('prefers explicit hosted configuration', function (): void {
    $apiKey = getenv('FIRECRAWL_API_KEY');
    $apiUrl = getenv('FIRECRAWL_API_URL');
    $handler = new MockHandler([
        new Response(body: '{"success":true,"data":{"markdown":"# Read by the hosted parser"}}'),
    ]);
    $parser = new HostedParser(new Client(['handler' => $handler]));
    putenv('FIRECRAWL_API_KEY=wrong-api-key');
    putenv('FIRECRAWL_API_URL=https://wrong.test/');

    try {
        expect($parser->markdown(
            anydocMixedPdf(),
            'document.pdf',
            'test-api-key',
            'https://api.test/',
        ))
            ->toBe("# Read by the hosted parser\n");

        $request = $handler->getLastRequest();

        expect((string) $request?->getUri())
            ->toBe('https://api.test/v2/parse')
            ->and($request?->getHeaderLine('Authorization'))
            ->toBe('Bearer test-api-key');
    } finally {
        is_string($apiKey) ? putenv("FIRECRAWL_API_KEY={$apiKey}") : putenv('FIRECRAWL_API_KEY');
        is_string($apiUrl) ? putenv("FIRECRAWL_API_URL={$apiUrl}") : putenv('FIRECRAWL_API_URL');
    }
});

it('converts documents through Firecrawl Parse', function (): void {
    $apiKey = getenv('FIRECRAWL_API_KEY');
    $apiUrl = getenv('FIRECRAWL_API_URL');
    $handler = new MockHandler([
        new Response(body: '{"success":true,"data":{"markdown":"# Read by the hosted parser"}}'),
    ]);
    $parser = new HostedParser(new Client(['handler' => $handler]));
    putenv('FIRECRAWL_API_KEY=test-api-key');
    putenv('FIRECRAWL_API_URL=https://api.test/');

    try {
        expect($parser->markdown(anydocMixedPdf(), 'document.pdf', null, null))
            ->toBe("# Read by the hosted parser\n");

        $request = $handler->getLastRequest();
        $body = (string) $request?->getBody();

        expect($request?->getMethod())
            ->toBe('POST')
            ->and((string) $request?->getUri())
            ->toBe('https://api.test/v2/parse')
            ->and($request?->getHeaderLine('Authorization'))
            ->toBe('Bearer test-api-key')
            ->and($request?->getHeaderLine('Content-Type'))
            ->toStartWith('multipart/form-data; boundary=')
            ->and($body)
            ->toContain('%PDF-', '"mode":"auto"', '"origin":"anydoc@', 'filename="document.pdf"');
    } finally {
        is_string($apiKey) ? putenv("FIRECRAWL_API_KEY={$apiKey}") : putenv('FIRECRAWL_API_KEY');
        is_string($apiUrl) ? putenv("FIRECRAWL_API_URL={$apiUrl}") : putenv('FIRECRAWL_API_URL');
    }
});

it('sanitizes hosted multipart filenames', function (): void {
    $handler = new MockHandler([
        new Response(body: '{"success":true,"data":{"markdown":"# Read by the hosted parser"}}'),
    ]);
    $parser = new HostedParser(new Client(['handler' => $handler]));

    expect($parser->markdown(
        anydocMixedPdf(),
        "bad\"\r\nX-Evil: yes.pdf",
        '',
        'https://api.test',
    ))->toBe("# Read by the hosted parser\n")
        ->and((string) $handler->getLastRequest()?->getBody())
        ->toContain('filename="bad___X-Evil: yes.pdf"');
});
