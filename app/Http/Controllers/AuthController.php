<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\UserInvitation;

class AuthController extends Controller
{
    public function storeUserInvitation(Request $request) {
        $user = auth()->user();
        
        // Solo 1 invitación por usuario no-admin
        if (!$user->is_admin) {
            $existing = UserInvitation::where('created_by_user_id', $user->id)->exists();
            if ($existing) {
                return back()->withErrors(['partner_email' => 'Ya has usado tu invitación permitida.']);
            }
        }

        $request->validate([
            'email' => 'required|email|unique:users,email|unique:user_invitations,email',
        ]);

        $token = \Illuminate\Support\Str::random(40);
        
        UserInvitation::create([
            'email' => $request->email,
            'token' => $token,
            'status' => 'pending',
            'created_by_user_id' => $user->id
        ]);

        return back()->with('status', 'Link de invitación generado con éxito.');
    }

    public function showRegisterByToken($token) {
        $invitation = UserInvitation::where('token', $token)->where('status', 'pending')->firstOrFail();
        return view('auth.register', ['email' => $invitation->email, 'token' => $token]);
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]);
    }

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->filled('token')) {
            $invitation = UserInvitation::where('token', $request->token)
                ->where('email', $request->email)
                ->first();
            if ($invitation) {
                $invitation->status = 'accepted';
                $invitation->save();

                // Vincular automáticamente si fue creado por un usuario
                if ($invitation->created_by_user_id) {
                    $creator = User::find($invitation->created_by_user_id);
                    if ($creator) {
                        $user->partner_id = $creator->id;
                        $user->save();
                        
                        $creator->partner_id = $user->id;
                        $creator->save();
                    }
                }
            }
        }

        Auth::login($user);

        return redirect('/');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function linkPartner(Request $request) {
        $validated = $request->validate([
            'partner_email' => 'required|email|exists:users,email',
        ]);

        $partner = User::where('email', $validated['partner_email'])->first();
        $user = auth()->user();
        
        if ($partner->id === $user->id) {
            return back()->withErrors(['partner_email' => 'No puedes vincularte contigo mismo.']);
        }

        // Verificar si ya hay una invitación pendiente
        $exists = \App\Models\PartnerInvitation::where('sender_id', $user->id)
            ->where('receiver_id', $partner->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->withErrors(['partner_email' => 'Ya has enviado una invitación a esta persona.']);
        }

        \App\Models\PartnerInvitation::create([
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'status' => 'pending'
        ]);

        return back()->with('status', 'Invitación enviada con éxito. Espera a que tu pareja la acepte.');
    }

    public function acceptInvitation($id) {
        $invitation = \App\Models\PartnerInvitation::findOrFail($id);
        
        if ($invitation->receiver_id !== auth()->id()) {
            return back()->withErrors(['msg' => 'No autorizado.']);
        }

        $sender = User::find($invitation->sender_id);
        $user = auth()->user();

        // Vincular ambos
        $user->partner_id = $sender->id;
        $user->save();

        $sender->partner_id = $user->id;
        $sender->save();

        $invitation->status = 'accepted';
        $invitation->save();

        return back()->with('status', '¡Cuentas vinculadas con éxito!');
    }

    public function rejectInvitation($id) {
        $invitation = \App\Models\PartnerInvitation::findOrFail($id);
        
        if ($invitation->receiver_id !== auth()->id()) {
            return back()->withErrors(['msg' => 'No autorizado.']);
        }

        $invitation->status = 'rejected';
        $invitation->save();

        return back()->with('status', 'Invitación rechazada.');
    }

    public function unlinkPartner() {
        $user = auth()->user();
        if ($user->partner_id) {
            $partner = User::find($user->partner_id);
            if ($partner) {
                $partner->partner_id = null;
                $partner->save();
            }
            $user->partner_id = null;
            $user->save();
        }
        return back()->with('status', 'Se ha desvinculado la cuenta.');
    }

    public function updateSalary(Request $request) {
        $validated = $request->validate([
            'salary' => 'required|numeric|min:0',
            'salary_period' => 'required|in:semanal,quincenal,mensual',
        ]);

        $user = auth()->user();
        $user->salary = $validated['salary'];
        $user->salary_period = $validated['salary_period'];
        $user->save();

        return back()->with('status', 'Sueldo actualizado con éxito.');
    }
}
