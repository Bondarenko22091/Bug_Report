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
        $request->validate([
            'text' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'documents' => 'nullable|array|max:3',
            'documents.*' => 'file|mimes:pdf|max:10240',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->getRealPath();
            }
        }

        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPaths[] = $document->getRealPath();
            }
        }

        $text = $request->input('text');
        $report = $aiService->analyze($text, $imagePaths, $documentPaths);
        
        $steps = "";
        foreach ($report->steps_to_reproduce as $i => $step) {
            $steps .= ($i + 1) . ". " . $step . "\n";
        }
        
        $issueBody = "## {$report->title}\n\n"
                   . "### Описание\n{$report->description}\n\n"
                   . "### Шаги воспроизведения\n{$steps}\n"
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
                'images_count' => count($imagePaths),
                'documents_count' => count($documentPaths),
            ]
        ]);
    }
}