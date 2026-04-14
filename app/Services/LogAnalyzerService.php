<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\ArraySchema;
use App\DTO\GeneratedReport;
use Illuminate\Support\Facades\Log;

class LogAnalyzerService
{
    public function analyze(string $text): GeneratedReport
{
    $schema = new ObjectSchema(
        name: 'bug_report',
        description: 'Структурированный отчет об ошибке',
        properties: [
            new StringSchema('title', 'Краткое и понятное название ошибки (до 10 слов)'),
            new StringSchema('description', 'Подробное описание проблемы и контекст'),
            new ArraySchema(
                'steps_to_reproduce', 
                'Пошаговая инструкция для воспроизведения',
                new StringSchema('step', 'Описание шага') 
            ),
            new StringSchema('expected_result', 'Что должно было произойти'),
            new StringSchema('actual_result', 'Что произошло на самом деле'),
            new StringSchema(
                'severity', 
                'Уровень критичности. Допустимые значения: low, medium, high, critical'
            ),

        ],
        requiredFields: ['title', 'description', 'steps_to_reproduce', 'expected_result', 'actual_result', 'severity']
    );

        $prompt = "Проанализируй следующий лог ошибки и верни структурированный отчет строго по схеме на русском языке. Лог:\n\n" . $text;

        try {
            $response = Prism::structured()
                ->using('openrouter', 'grok-4-1-fast-non-reasoning')
                ->withSchema($schema)
                ->withSystemPrompt('Ты — Senior разработчик. Твоя задача — извлекать из логов структурированную информацию. Отвечай только на русском языке, и ТОЛЬКО JSON.')
                ->withPrompt($prompt)
                ->withMaxTokens(400)
                ->usingTemperature(0.6)
                ->asStructured();

            $data = $response->structured;
            
            return new GeneratedReport(
                title: $data['title'],
                description: $data['description'],
                steps_to_reproduce: $data['steps_to_reproduce'],
                expected_result: $data['expected_result'],
                actual_result: $data['actual_result'],
                severity: $data['severity'],
            );

        } catch (\Exception $e) {
            Log::error('Structured AI error', ['message' => $e->getMessage()]);
            
            return new GeneratedReport(
                title: 'Ошибка: ' . substr($text, 0, 50),
                description: $text,
                steps_to_reproduce: ['1. Выполнить действие', '2. Наблюдать ошибку'],
                expected_result: 'Ожидалась корректная работа',
                actual_result: $text,
                severity: 'medium',
            );
        }
    }
}