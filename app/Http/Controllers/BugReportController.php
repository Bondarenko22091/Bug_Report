<?php

namespace App\Http\Controllers;

use App\Services\GithubIssuesService;
use App\Services\LogAnalyzerService;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    public function store(
        Request $request, 
        GithubIssuesService $githubService, 
        LogAnalyzerService $aiService
    ) {
        $text = $request->input('text', 'Пустой отчет');

        $report = $aiService->analyze($text);
        
        $steps = "";
        foreach ($report->steps_to_reproduce as $i => $step) {
            $steps .= ($i + 1) . ". " . $step . "\n";
        }
        
        $issueBody = "## {$report->title}\n\n"
                   . "### Описание\n{$report->description}\n\n"
                   . "### Шаги воспроизведения\n{$steps}\n\n"
                   . "### Ожидаемый результат\n{$report->expected_result}\n\n"
                   . "### Фактический результат\n{$report->actual_result}\n\n"
                   . "### Критичность\n**" . strtoupper($report->severity) . "**\n\n"
                   . "---\n*Сгенерировано автоматически с помощью AI*\n"
                   . "**Исходный лог:**\n```\n{$text}\n```";
        
        $result = $githubService->createIssue($report->title, $issueBody);

        return response()->json([
            'status' => 'success',
            'github_url' => $result['html_url'] ?? 'error',
            'structured_report' => [
                'title' => $report->title,
                'severity' => $report->severity,
                'steps_count' => count($report->steps_to_reproduce),
            ]
        ]);
    }
}