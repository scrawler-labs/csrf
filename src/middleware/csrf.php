<?php

namespace Scrawler\Csrf\Middleware;

class Csrf implements \Scrawler\Interfaces\MiddlewareInterface
{
    public function run(\Scrawler\Http\Request $request, \Closure $next): \Scrawler\Http\Response
    {
        if ('POST' === $request->getMethod()) {
            if (!csrf()->validate()) {
                return app()->container()->call(app()->getHandler('419'));
            }
        }

        return $next($request);
    }
}
