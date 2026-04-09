<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class LogAnalyzerService
{
    public function analyze(string $text): string
    {
        return retry(3, function () use ($text) {
            return Prism::text()
                ->using(Provider::OpenRouter, 'grok-4-1-fast-non-reasoning') 
                ->withSystemPrompt(
                    'Ты — опытный Senior Laravel разработчик и эксперт по инфраструктуре. ' .
                    'Твоя задача: проанализировать входящее сообщение. ' .
                    '1. Если это технический лог ошибки — кратко назови причину и дай готовый код решения. ' .
                    '2. Если это вопрос словами — ответь на него как эксперт, задав уточняющие вопросы, если информации мало. ' .
                    'Отвечай на русском языке, используй Markdown для оформления кода.'
                )
                ->withPrompt($text)
                ->asText() 
                ->text;
        }, 100); 
    }
}
