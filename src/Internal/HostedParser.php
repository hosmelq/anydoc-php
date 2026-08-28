<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Internal;

use function Safe\json_encode;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use HosmelQ\Anydoc\Exceptions\HostedException;
use JsonException;
use stdClass;

final readonly class HostedParser
{
    private const string API_URL = 'https://api.firecrawl.dev';

    private const int TIMEOUT_SECONDS = 300;

    public function __construct(private ClientInterface $client = new Client())
    {
    }

    public function markdown(
        string $bytes,
        string $filename,
        null|string $apiKey,
        null|string $apiUrl,
    ): string {
        $apiKey ??= $this->environment('FIRECRAWL_API_KEY');
        $apiUrl ??= $this->environment('FIRECRAWL_API_URL') ?? self::API_URL;
        $filename = str_replace(['"', "\r", "\n"], '_', $filename);

        try {
            $response = $this->client->request('POST', mb_rtrim($apiUrl, '/').'/v2/parse', [
                RequestOptions::HEADERS => (bool) $apiKey ? ['Authorization' => "Bearer {$apiKey}"] : [],
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::MULTIPART => [
                    [
                        'contents' => json_encode([
                            'origin' => 'anydoc@'.(InstalledVersions::getPrettyVersion('hosmelq/anydoc') ?? 'unknown'),
                            'parsers' => [[
                                'mode' => 'auto',
                                'type' => 'pdf',
                            ]],
                        ]),
                        'name' => 'options',
                    ],
                    [
                        'contents' => $bytes,
                        'filename' => $filename,
                        'headers' => ['Content-Type' => 'application/pdf'],
                        'name' => 'file',
                    ],
                ],
                RequestOptions::TIMEOUT => self::TIMEOUT_SECONDS,
            ]);
        } catch (GuzzleException $guzzleException) {
            throw new HostedException(
                "Firecrawl Parse: {$guzzleException->getMessage()}",
                $guzzleException->getCode(),
                previous: $guzzleException,
            );
        }

        $status = $response->getStatusCode();
        $reply = $this->decode((string) $response->getBody());
        $success = $reply instanceof stdClass ? $reply->success ?? null : null;

        if ($status !== 200 || $success !== true) {
            $error = $reply instanceof stdClass ? $reply->error ?? null : null;
            $detail = is_string($error) ? $error : "HTTP {$status}";

            throw new HostedException($this->describe($status, $detail, (bool) $apiKey));
        }

        $data = $reply->data ?? null;
        $markdown = $data instanceof stdClass ? $data->markdown ?? null : null;

        if (! is_string($markdown) || $markdown === '') {
            throw new HostedException('Firecrawl Parse returned no Markdown');
        }

        return str_ends_with($markdown, "\n") ? $markdown : "{$markdown}\n";
    }

    private function decode(string $response): null|stdClass
    {
        try {
            $reply = json_decode($response, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return $reply instanceof stdClass ? $reply : null;
    }

    private function describe(int $status, string $detail, bool $keyed): string
    {
        return match ($status) {
            401 => "Firecrawl Parse rejected the API key: {$detail}",
            402 => "Firecrawl Parse is out of credits: {$detail}",
            429 => $keyed
                ? "Firecrawl Parse rate limit reached: {$detail}"
                : "Firecrawl Parse keyless limit reached, set FIRECRAWL_API_KEY: {$detail}",
            default => "Firecrawl Parse: {$detail}",
        };
    }

    private function environment(string $name): null|string
    {
        $value = getenv($name);

        return is_string($value) ? $value : null;
    }
}
