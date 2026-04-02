<?php

namespace App\Http\Controllers;

use App\Services\GithubIssuesService;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    public function store(Request $request, GithubIssuesService $githubService)
    {
        $text = $request->input('text', 'Пустой отчет');

        $result = $githubService->createIssue(
            title: 'Новый баг-репорт: ' . now()->format('Y-m-d H:i'),
            body: $text
        );

        return response()->json([
            'status' => 'success',
            'github_url' => $result['html_url'] ?? 'error'
        ]);
    }
}
