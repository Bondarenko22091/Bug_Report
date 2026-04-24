<?php

namespace App\DTO;

class GeneratedReport
{
    public function __construct(
        public string $title,
        public string $description,
        public array $steps_to_reproduce,
        public string $expected_result,
        public string $actual_result,
        public string $severity,
        public readonly ?string $visual_analysis = null,
    ) {}
}