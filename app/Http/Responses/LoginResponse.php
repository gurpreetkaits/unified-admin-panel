<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $redirect = $this->getRedirectUrl($request);

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($redirect);
    }

    protected function getRedirectUrl(Request $request): string
    {
        $redirect = $request->query('redirect');

        if ($redirect && $this->isValidRedirect($redirect)) {
            return $redirect;
        }

        return config('fortify.home');
    }

    protected function isValidRedirect(string $redirect): bool
    {
        // Only allow internal redirects (starting with /)
        return str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//');
    }
}
