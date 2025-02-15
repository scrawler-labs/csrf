<?php

if (!function_exists('csrf')) {
    function csrf(): Scrawler\Csrf\CSRF
    {
    
        return new Scrawler\Csrf\CSRF();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return csrf()->input();
    }
}
