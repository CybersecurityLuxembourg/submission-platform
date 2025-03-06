<?php

namespace App\Helpers;

class MarkdownHelper
{
    /**
     * Convert markdown text to HTML
     *
     * @param string|null $text
     * @return string
     */
    public static function toHtml(?string $text): string
    {
        if (!$text) {
            return '';
        }

        // First escape the text to prevent XSS
        $text = e($text);
        
        // Convert **bold** to <strong>bold</strong>
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        
        // Convert *italic* to <em>italic</em>
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        
        // Process bullet points - we do this before nl2br to handle the bullet points correctly
        $lines = explode("\n", $text);
        $inList = false;
        $result = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (strpos($trimmedLine, '- ') === 0) {
                // This is a bullet point
                if (!$inList) {
                    $result[] = '<ul class="list-disc pl-5 space-y-1 my-2">';
                    $inList = true;
                }
                $result[] = '<li class="text-sm">' . substr($trimmedLine, 2) . '</li>';
            } else {
                if ($inList) {
                    $result[] = '</ul>';
                    $inList = false;
                }
                $result[] = $line;
            }
        }
        
        // Close any open list
        if ($inList) {
            $result[] = '</ul>';
        }
        
        // Join the lines back together
        $text = implode("\n", $result);
        
        // Now convert new lines to <br> after handling bullet points
        $text = nl2br($text);
        
        return $text;
    }
} 