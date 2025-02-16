<?php

if (!function_exists('csrf')) {
    function csrf(): Scrawler\Csrf\Csrf
    {
        if(class_exists('Scrawler\App')) {
            if(!app()->has('csrf')) {
                app()->register('csrf', new Scrawler\Csrf\Csrf());            
            }
            return app()->csrf();
        }
    
        return new Scrawler\Csrf\Csrf();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return csrf()->input();
    }
}
