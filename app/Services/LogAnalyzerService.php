<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\ArraySchema;
use App\DTO\GeneratedReport;
use Illuminate\Support\Facades\Log;
use Prism\Prism\ValueObjects\Media\Image;
use Smalot\PdfParser\Parser;

class LogAnalyzerService
{
    public function analyze(string $text, array $imagePaths = [], array $documentPaths = []): GeneratedReport
    {
        $media = [];
        $pdfText = "";
        $tempFiles = [];

        foreach ($imagePaths as $path) {
            if (file_exists($path)) {
                $media[] = Image::fromLocalPath($path);
                $tempFiles[] = $path;
            }
        }

        $parser = new Parser();
        foreach ($documentPaths as $path) {
            if (file_exists($path)) {
                try {
                    $pdf = $parser->parseFile($path);
                    $content = trim($pdf->getText());
                    if (!empty($content)) {
                        $pdfText .= "\n[ДАННЫЕ ИЗ PDF ФАЙЛА]:\n" . $content . "\n[КОНЕЦ ДАННЫХ ИЗ PDF]\n";
                    }
                } catch (\Exception $e) {
                    Log::warning("PDF parse error: " . $e->getMessage());
                }
                $tempFiles[] = $path;
            }
        }

        $schema = new ObjectSchema(
            name: 'bug_report',
            description: 'Структурированный технический отчет об ошибке',
            properties: [
                new StringSchema('title', 'Краткое название ошибки'),
                new StringSchema('description', 'Подробный технический анализ проблемы'),
                new ArraySchema(
                    'steps_to_reproduce', 
                    'Шаги для воспроизведения',
                    new StringSchema('step', 'Описание шага') 
                ),
                new StringSchema('expected_result', 'Ожидаемое поведение системы'),
                new StringSchema('actual_result', 'Фактическая ошибка с конкретными деталями из файлов'),
                new StringSchema('severity', 'Уровень критичности: low, medium, high, critical'),
            ],
            requiredFields: ['title', 'description', 'steps_to_reproduce', 'expected_result', 'actual_result', 'severity']
        );

        $prompt = "Твоя задача — проанализировать логи и приложенные файлы.\n\n"
                . "ИСХОДНЫЙ ТЕКСТ ЛОГА:\n" . $text . "\n"
                . $pdfText . "\n"
                . "ИНСТРУКЦИЯ:\n"
                . "1. Если в блоке [ДАННЫЕ ИЗ PDF ФАЙЛА] есть информация, ты ОБЯЗАН использовать её в анализе.\n"
                . "2. Если есть скриншоты, проанализируй их визуально.\n"
                . "3. Если данных в файлах действительно нет, напиши: 'Файлы не содержат дополнительной информации'.\n"
                . "4. Создай подробный баг-репорт на русском языке.";

        try {
            $response = Prism::structured()
                ->using('xai', 'grok-4-1-fast-non-reasoning')
                ->withSchema($schema)
                ->withSystemPrompt('Ты — ведущий разработчик. Делай глубокий анализ технических логов и скриншотов.')
                ->withPrompt($prompt, $media)
                ->withMaxTokens(1000)
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
                visual_analysis: null
            );

        } catch (\Exception $e) {
            Log::error('AI analysis error', ['message' => $e->getMessage()]);
            
            return new GeneratedReport(
                title: 'Ошибка анализа',
                description: $e->getMessage(),
                steps_to_reproduce: ['Повторить запрос'],
                expected_result: 'Успешный анализ',
                actual_result: 'Ошибка: ' . $e->getMessage(),
                severity: 'medium',
                visual_analysis: null
            );
        } finally {
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                    Log::info('Temp file deleted', ['path' => $file]);
                }
            }
        }
    }
}
