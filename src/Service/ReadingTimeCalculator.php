<?php

namespace App\Service;

class ReadingTimeCalculator
{
    private int $wordsPerMinute = 200;

    public function calculate(string $text): int
    {
        $wordCount = str_word_count(strip_tags($text));
        return (int) ceil($wordCount / $this->wordsPerMinute);
    }
}