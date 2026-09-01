<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username(): string
    {
        return 'username';
    }

    /**
     * Mantiene el limitador nativo de intentos y evita revelar si una cuenta
     * existe, esta inactiva o tiene el acceso suspendido.
     */
    protected function credentials(Request $request): array
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['access' => 1, 'status' => 1]
        );
    }

    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
