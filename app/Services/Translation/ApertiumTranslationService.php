<?php

namespace App\Services\Translation;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApertiumTranslationService
{
    public function translate(string $text, array $extraProtectedTerms = [], ?string $langpair = null): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        $baseUrl = rtrim((string) config('services.apertium.base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Translation service is not configured.');
        }

        [$preparedText, $protectedTerms] = $this->protectTerms($text, $extraProtectedTerms);

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout((int) config('services.apertium.timeout', 10))
                ->acceptJson()
                ->get('/translate', [
                    'langpair' => $langpair ?: (string) config('services.apertium.langpair', 'spa|cat_valencia'),
                    'q' => $preparedText,
                    'format' => 'txt',
                    'markUnknown' => 'no',
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Unable to connect to translation service.', 0, $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Translation service returned an unexpected response.');
        }

        $translatedText = data_get($response->json(), 'responseData.translatedText');

        if (! is_string($translatedText) || trim($translatedText) === '') {
            throw new RuntimeException('Translation service did not return translated text.');
        }

        return strtr($translatedText, $protectedTerms);
    }

    private function protectTerms(string $text, array $extraProtectedTerms = []): array
    {
        $protectedTerms = [];
        $terms = array_merge((array) config('services.apertium.protected_terms', []), $extraProtectedTerms);
        $terms = array_values(array_unique(array_filter(array_map(
            static fn ($term) => is_string($term) ? trim($term) : '',
            $terms
        ))));

        usort($terms, static fn (string $left, string $right) => mb_strlen($right) <=> mb_strlen($left));

        foreach ($terms as $index => $term) {
            if (! is_string($term) || trim($term) === '') {
                continue;
            }

            $placeholder = "__TERM_{$index}__";
            $pattern = '/'.preg_quote($term, '/').'/u';

            if (preg_match($pattern, $text) !== 1) {
                continue;
            }

            $text = preg_replace($pattern, $placeholder, $text);
            $protectedTerms[$placeholder] = $term;
        }

        return [$text, $protectedTerms];
    }
}
