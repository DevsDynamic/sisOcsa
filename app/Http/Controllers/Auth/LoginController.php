<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username'; // Campo de inicio de sesión, puedes personalizarlo
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Obtener las credenciales del formulario
        $credentials = $this->credentials($request);

        // Obtener el usuario por su email (o cualquier campo de inicio de sesión)
        $user = User::where($this->username(), $credentials[$this->username()])->first();

        if (!$user) {
            // Si el usuario no existe
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([
                    $this->username() => 'El usuario no existe.',
                ]);
        }

        if ($user->access == 0) {
            // Si el usuario tiene acceso deshabilitado
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([
                    $this->username() => 'El acceso está deshabilitado.',
                ]);
        }

        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            // Si la contraseña es incorrecta
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([
                    'password' => 'La contraseña es incorrecta.',
                ]);
        }

        // Si el intento de inicio de sesión tiene éxito
        return $this->sendLoginResponse($request);
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function authenticated(Request $request, $user)
    {
        // Personalización después de que el usuario se autentique correctamente
        // Ejemplo: redirigir a una página específica dependiendo del rol
        
        // if ($user->hasRole('Administrador')) {
        //     return redirect()->intended('/admin/dashboard');
        // }

        return redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => trans('auth.failed'),
            ]);
    }
}