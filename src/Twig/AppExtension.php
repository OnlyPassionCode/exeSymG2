<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
        ];
    }

    public function truncate(string $text, int $length = 300): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, strrpos(substr($text, 0, $length), ' ')) . '...';
    }
}