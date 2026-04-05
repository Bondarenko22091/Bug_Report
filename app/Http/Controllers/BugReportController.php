<?php

namespace App\Http\Controllers;

use App\Services\GithubIssuesService;
use App\Services\LogAnalyzerService;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    public function store(Request $request, GithubIssuesService $githubService, LogAnalyzerService $aiService) 
    {
        $text = $request->input('text', 'Пустой отчет');

        try {
            $aiAnalysis = $aiService->analyze($text);
        } catch (\Exception $e) {
            $aiAnalysis = "Анализ временно недоступен: " . $e->getMessage();
        }

        $issueBody = "## Отчет\n\n" . $text . "\n\n---\n### Анализ (AI):\n" . $aiAnalysis;

        $result = $githubService->createIssue(
            title: 'Bug Report: ' . now()->format('Y-m-d H:i'),
            body: $issueBody
        );

        if (isset($result['message']) && $result['message'] == 'Requires authentication') {
            return response()->json(['status' => 'error', 'message' => 'GitHub Token invalid'], 401);
        }

        return response()->json([
            'status' => 'success',
            'github_url' => $result['html_url'] ?? 'error',
            'ai_summary' => $aiAnalysis
        ]);
    }
}
