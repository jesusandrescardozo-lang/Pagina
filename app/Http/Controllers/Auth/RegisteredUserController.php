<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shelter;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Registro de nuevos empleados desde la pantalla de login.
 *
 * Las cuentas creadas aquí quedan con el rol 'operador' (solo lectura y
 * registro propio) hasta que un administrador asigne un rol con permisos.
 * Por seguridad solo se aceptan correos de dominios institucionales
 * (config: app.dominios_registro / variable REGISTRO_DOMINIOS).
 */
class RegisteredUserController extends Controller
{
    /**
     * Muestra el formulario de registro (vista de login con modo registro).
     */
    public function create()
    {
        // Coherencia con store(): si no hay dominios autorizados, el
        // formulario tampoco debe ser accesible por URL directa.
        if (empty(config('app.dominios_registro', []))) {
            return redirect()->route('login')
                ->with('error', 'El registro de cuentas está deshabilitado. Contacte al administrador de la red.');
        }

        $shelters = Shelter::orderBy('codigo')->get();
        return view('auth.login', compact('shelters'));
    }

    /**
     * Registra un nuevo empleado.
     */
    public function store(Request $request)
    {
        $dominios = config('app.dominios_registro', []);

        if (empty($dominios)) {
            return redirect()->route('login')
                ->with('error', 'El registro de cuentas está deshabilitado. Contacte al administrador de la red.');
        }

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'email' => [
                    'required', 'string', 'email', 'max:255', 'unique:users,email',
                    function (string $attribute, mixed $value, \Closure $fail) use ($dominios) {
                        $dominio = strtolower(substr(strrchr($value, '@'), 1));
                        $permitidos = array_map('strtolower', $dominios);

                        if (! in_array($dominio, $permitidos, true)) {
                            $fail('El registro es exclusivo para correos institucionales de: ' . implode(', ', $permitidos) . '.');
                        }
                    },
                ],
                'cargo' => ['required', 'string', 'max:100'],
                'shelter_id' => ['nullable', 'exists:shelters,id'],
                'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // La vista de login arranca en MODO LOGIN, así que un error de
            // registro quedaba invisible (o peor: aparecía dentro del formulario
            // de inicio de sesión, haciendo creer al usuario que su LOGIN falló).
            // El fragmento #registro hace que el JavaScript de la vista abra el
            // modo registro y muestre los errores donde corresponde.
            return redirect('/login#registro')->withInput()->withErrors($e->errors());
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cargo' => $validated['cargo'],
            'shelter_id' => $validated['shelter_id'] ?? null,
            'rol' => 'operador',
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        AuditService::log(
            'autenticacion',
            'registro_empleado',
            "Nuevo empleado registrado: {$user->name} ({$user->email}) con rol operador"
        );

        return redirect()->route('login')
            ->with('info', "Cuenta creada para {$user->name}. Inicie sesión con su correo y contraseña.");
    }
}
