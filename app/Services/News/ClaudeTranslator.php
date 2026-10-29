<?php
declare(strict_types=1);

namespace App\Services\News;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\RawMessageDeltaEvent;
use Anthropic\Messages\TextDelta;
use RuntimeException;

/**
 * სტატიის თარგმანი ინგლისურიდან ქართულად Claude API-ით (Anthropic PHP SDK): სათაური, ამონარიდი და HTML ტექსტი
 * ერთ მოთხოვნაში, JSON სქემით (structured output). გრძელი პასუხისთვის — stream.
 */
final class ClaudeTranslator
{
    private const MAX_TOKENS = 64000;

    private const SYSTEM = <<<'TXT'
        You translate English articles about web development, design and AI into Georgian for the blog of a Georgian web studio.

        - Keep the HTML structure exactly: every tag, attribute and URL stays as it is; translate only the human-readable text between tags and the alt/title attributes.
        - Never translate the contents of <pre> and <code>, product, library and company names, commands, file names or identifiers.
        - Write natural, modern Georgian as a Georgian developer would. Use established Georgian technical terms; keep an English term (API, framework, React…) where Georgian developers normally use it untranslated.
        - Keep the author's meaning, tone and first-person voice; do not add, summarize or omit content.
        - The title should read as a Georgian headline, not a word-for-word copy.
        TXT;

    private ?Client $client = null;

    public function __construct(private readonly string $apiKey, private readonly string $model)
    {
    }

    /**
     * @return array{title: string, excerpt: string, body: string}
     * @throws RuntimeException პასუხი შეწყდა, უარყოფილია ან არასწორი JSON-ია
     */
    public function translate(string $title, string $excerpt, string $bodyHtml): array
    {
        try {
            return $this->request($title, $excerpt, $bodyHtml);
        } catch (APIStatusException $e) {
            // SDK-ის შეტყობინებაში მთელი JSON პასუხია — ადმინისთვის მხოლოდ API-ის ტექსტი (მაგ. „credit balance is too low“)
            $body = json_decode((string)strstr($e->getMessage(), '{'), true);
            $message = $body['body']['error']['message'] ?? strtok($e->getMessage(), "\n");
            throw new RuntimeException('Claude API: ' . $message, 0, $e);
        }
    }

    /** @return array{title: string, excerpt: string, body: string} */
    private function request(string $title, string $excerpt, string $bodyHtml): array
    {
        $payload = json_encode(['title' => $title, 'excerpt' => $excerpt, 'body' => $bodyHtml], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $stream = $this->client()->messages->createStream(
            model: $this->model,
            maxTokens: self::MAX_TOKENS,
            system: self::SYSTEM,
            messages: [[
                'role'    => 'user',
                'content' => "Translate this article into Georgian. Return the same three fields.\n\n" . $payload,
            ]],
            outputConfig: [
                'effort' => 'low',
                'format' => [
                    'type'   => 'json_schema',
                    'schema' => [
                        'type'       => 'object',
                        'properties' => [
                            'title'   => ['type' => 'string'],
                            'excerpt' => ['type' => 'string'],
                            'body'    => ['type' => 'string'],
                        ],
                        'required'             => ['title', 'excerpt', 'body'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        );

        $text = '';
        $stopReason = null;
        foreach ($stream as $event) {
            if ($event instanceof RawContentBlockDeltaEvent && $event->delta instanceof TextDelta) {
                $text .= $event->delta->text;
            } elseif ($event instanceof RawMessageDeltaEvent) {
                $stopReason = $event->delta->stopReason ?? $stopReason;
            }
        }
        $stopReason = is_object($stopReason) ? $stopReason->value : $stopReason;
        if ($stopReason === 'refusal') {
            throw new RuntimeException('Claude declined to translate this article.');
        }
        if ($stopReason === 'max_tokens') {
            throw new RuntimeException('Translation was cut off (article too long for one response).');
        }

        $data = json_decode($text, true);
        if (!is_array($data) || !isset($data['title'], $data['excerpt'], $data['body']) || trim((string)$data['title']) === '') {
            throw new RuntimeException('Claude returned an invalid translation.');
        }
        return ['title' => trim((string)$data['title']), 'excerpt' => trim((string)$data['excerpt']), 'body' => (string)$data['body']];
    }

    private function client(): Client
    {
        return $this->client ??= new Client(apiKey: $this->apiKey);
    }
}
