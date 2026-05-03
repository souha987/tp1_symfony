<?php
// src/Twig/ReadingTimeExtension.php
namespace App\Twig;

use App\Service\ReadingTimeCalculator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ReadingTimeExtension extends AbstractExtension
{
    public function __construct(private ReadingTimeCalculator $calculator) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('reading_time', [$this, 'readingTime']),
        ];
    }

    public function readingTime(string $text): string
    {
        $minutes = $this->calculator->calculate($text);
        return match($minutes) {
            0 => 'Moins de 1 min',
            1 => '1 min de lecture',
            default => $minutes . ' min de lecture'
        };
    }
}
