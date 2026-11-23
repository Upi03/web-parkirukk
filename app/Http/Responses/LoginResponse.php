<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $role = $request->user()->role ?? null;

        if ($role === 'petugas') {
            return redirect()->route('petugas.dashboard');
        } elseif ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->intended('/dashboard');
    }
}
