<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GithubIssuesService
{
    private $token;
    private $repo;

    public function __construct()
    {
        $this->token = config('services.github.token');
        $this->repo = config('services.github.repo');
    }

    public function createIssue(string $title, string $body)
    {
        $url = "https://api.github.com/repos/{$this->repo}/issues";

        $response = Http::withToken($this->token)
            ->withHeaders(['User-Agent' => 'Laravel-App'])
            ->post($url, [
                'title' => $title,
                'body'  => $body,
            ]);

        return $response->json();
    }
}