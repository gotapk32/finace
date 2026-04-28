<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        $invitations = UserInvitation::orderBy('id', 'desc')->get();
        
        $totalTransactions = \App\Models\Expense::count();
        $totalMoney = \App\Models\Expense::all()->sum(fn($e) => (float)$e->amount);

        return view('admin.dashboard', compact('users', 'invitations', 'totalTransactions', 'totalMoney'));
    }

    public function storeInvitation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:user_invitations,email',
        ]);

        $token = Str::random(40);
        
        UserInvitation::create([
            'email' => $request->email,
            'token' => $token,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Invitación generada correctamente.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        $user->delete();
        return back()->with('status', 'Usuario eliminado correctamente.');
    }

    public function deleteInvitation($id)
    {
        $invitation = UserInvitation::findOrFail($id);
        $invitation->delete();
        return back()->with('status', 'Invitación eliminada correctamente.');
    }
}
