<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $sort = (string) $request->input('sort', 'name');
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Limitamos las columnas ordenables para no aceptar cualquier valor por URL.
        $sortableColumns = [
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'name';

        $usuarios = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                });
            });

        // En texto usamos lower(...) para que el abecedario ignore mayúsculas.
        if (in_array($sortColumn, ['name', 'email', 'role'], true)) {
            $usuarios->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $usuarios->orderBy($sortColumn, $direction);
        }

        $usuarios = $usuarios
            ->orderByRaw('LOWER(name) asc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.usuarios.index', compact('usuarios', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.usuarios.create', [
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Ya existe un usuario con ese email.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        User::create($data);

        return redirect()->route('usuarios.index')->with('mensaje', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('admin.usuarios.edit', [
            'usuario' => $usuario,
            'roles' => User::ROLES,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique' => 'Ya existe un usuario con ese email.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        if ($usuario->role === User::ROLE_ADMIN
            && $data['role'] !== User::ROLE_ADMIN
            && User::where('role', User::ROLE_ADMIN)->count() <= 1
        ) {
            return back()->withErrors([
                'role' => 'Debe existir al menos un administrador activo.',
            ])->withInput();
        }

        if ($usuario->id === $request->user()->id && $data['role'] !== User::ROLE_ADMIN) {
            return back()->withErrors([
                'role' => 'No puedes quitarte a ti mismo el rol de administrador.',
            ])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('mensaje', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar tu propia cuenta mientras estás conectado.',
            ]);
        }

        if ($usuario->role === User::ROLE_ADMIN && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->withErrors([
                'delete' => 'Debe existir al menos un administrador activo.',
            ]);
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('mensaje', 'Usuario eliminado correctamente.');
    }
}
