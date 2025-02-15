<?php

namespace Scrawler\Csrf\Middleware;

class Csrf implements \Scrawler\Interfaces\MiddlewareInterface
{
    public function run(\Scrawler\Http\Request $request, \Closure $next): \Scrawler\Http\Response
    {
        if ('POST' === $request->getMethod()) {
            if (!csrf()->validate()) {
                return new \Scrawler\Http\Response('Invalid CSRF token', 403);
            }
        }

        return $next($request);
    }
}
