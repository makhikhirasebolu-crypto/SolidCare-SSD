<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\TokenMismatchException;
use App\Http\Middleware\EnsureStudentEmailIsVerified;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'student.verified' => EnsureStudentEmailIsVerified::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'email/verification-notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return redirect()
                ->to($request->headers->get('referer') ?: route('login'))
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('status', 'Your session expired. Please try again.');
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $target = Auth::guard('web')->check()
                ? route('verification.notice')
                : route('login');

            return redirect()
                ->to($request->headers->get('referer') ?: $target)
                ->with('status', 'Your session expired. Please refresh the page and try again.');
        });
    })->create();
