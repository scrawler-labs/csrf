<?php

if (!function_exists('csrf')) {
    function csrf(): Scrawler\Csrf\CSRF
    {
        if (class_exists('\Scrawler\App')) {
            if (Scrawler\App::engine()->has('csrf')) {
                return Scrawler\App::engine()->get('csrf');
            } else {
                $csrf = new Scrawler\Csrf\CSRF();
                Scrawler\App::engine()->register('csrf', $csrf);

                return $csrf;
            }
        }

        return new Scrawler\Csrf\CSRF();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return csrf()->input();
    }
}
