<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Si no esta logeado te redirige a login
         if (!auth()->check()) {
            return redirect()->route('login');
        }
        //Si está logeado, pero el usuario no tiene el rol administrador, te redirige a la pantalla pública de index.
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('public.bookings.index');
        }

        return $next($request);
    }
}
