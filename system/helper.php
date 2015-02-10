<?php
/**
 * Created by PhpStorm.
 * User: grzegorzgurzeda
 * Date: 10.02.15
 * Time: 18:37
 */

class Helper {
    public static function sanitize($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return '';
        }

        return $text;
    }
} 