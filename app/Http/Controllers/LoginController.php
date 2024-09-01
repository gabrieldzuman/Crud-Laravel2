<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Exibe a página de login.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('login.index');
    }

    /**
     * Processa o login do usuário.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        if (!Auth::attempt($request->only('email', 'password'))) {
            return Redirect::back()->withErrors([
                'login_error' => 'Usuário ou senha inválidos',
            ])->withInput();
        }
        return redirect()->route('series.index');
    }

    /**
     * Processa o logout do usuário.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
