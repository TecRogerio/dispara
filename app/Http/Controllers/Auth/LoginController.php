<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/instancias';

    /**
     * Quantas tentativas antes de bloquear.
     */
    protected int $maxAttempts = 7;

    /**
     * Por quantos minutos bloqueia após estourar tentativas.
     */
    protected int $decayMinutes = 10;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
