<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Socialite;

use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * Registro de nuevo usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'email.unique' => 'El correo electrónico ya está registrado.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/');
    }

    /**
     * Inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('location');
    }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierre de sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function auth_redirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            
            // get user
            $user = Socialite::driver('google')->user();

            // check if exist
            $user = User::where('email', $user->email)->first();

            // if not exist, error
            if (!$user) {

                // return login with error
                return redirect()->route('login')->withErrors([
                    'email' => 'Usuario no registrado.',
                ]);
            }

            // login
            Auth::login($user);

            // redirect
            return redirect()->route('location');

        } catch (\Throwable $th) {
            return redirect()->route('login')->withErrors([
                'email' => 'Error al iniciar sesión.',
            ]);
        }
        
    }

    /**
     * Guardar vehículo del usuario.
     * TODO: Create cars migration and complete the database insert.
     */
    public function storeVehicle(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year'  => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:50',
            'plate' => 'required|string|max:20',
        ]);

        // TODO: Save vehicle to the database once migration is created.
        // Car::create([
        //     'user_id' => Auth::id(),
        //     'brand'   => $request->brand,
        //     'model'   => $request->model,
        //     'year'    => $request->year,
        //     'color'   => $request->color,
        //     'plate'   => $request->plate,
        // ]);

        return redirect()->route('location');
    }
    
}

