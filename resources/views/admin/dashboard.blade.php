@extends('layouts.app')

@section('title', 'Administración')

@section('content')
<div class="admin-container">
    <div class="stat-card" style="margin-bottom: 2rem; border-top: 5px solid var(--primary);">
        <h3 style="font-weight: 900; margin-bottom: 1.5rem;">Panel de Control Admin ⚡</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="stat-card" style="background: var(--primary-glow); border: none; padding: 1rem;">
                <p style="font-size: 0.6rem; font-weight: 800; color: var(--primary);">USUARIOS</p>
                <h2 style="font-weight: 900;">{{ $users->count() }}</h2>
            </div>
            <div class="stat-card" style="background: var(--primary-glow); border: none; padding: 1rem;">
                <p style="font-size: 0.6rem; font-weight: 800; color: var(--primary);">INVITACIONES</p>
                <h2 style="font-weight: 900;">{{ $invitations->where('status', 'pending')->count() }}</h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none; padding: 1rem;">
                <p style="font-size: 0.6rem; font-weight: 800; opacity: 0.8;">TRANSACCIONES</p>
                <h2 style="font-weight: 900;">{{ $totalTransactions }}</h2>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; border: none; padding: 1rem;">
                <p style="font-size: 0.6rem; font-weight: 800; opacity: 0.8;">VOLUMEN TOTAL</p>
                <h2 style="font-weight: 900;">${{ number_format($totalMoney, 0) }}</h2>
            </div>
        </div>

        <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Generar Invitación</h4>
        <form action="{{ route('admin.invitations.store') }}" method="POST" style="background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 2rem;">
            @csrf
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="usuario@ejemplo.com" required>
            </div>
            <button type="submit" class="btn-primary" style="background: var(--primary);">GENERAR LINK DE REGISTRO</button>
        </form>

        <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Invitaciones Pendientes</h4>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 2.5rem;">
            @forelse($invitations as $inv)
                <div class="stat-card" style="padding: 1rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #f1f5f9; opacity: {{ $inv->status === 'accepted' ? '0.5' : '1' }};">
                    <div>
                        <h4 style="font-size: 0.85rem; font-weight: 900;">{{ $inv->email }}</h4>
                        <p style="font-size: 0.6rem; font-weight: 800; color: {{ $inv->status === 'pending' ? 'var(--accent)' : 'var(--text-muted)' }}">
                            {{ strtoupper($inv->status) }}
                        </p>
                        @if($inv->status === 'pending')
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                                <input type="text" value="{{ route('invitation.register', $inv->token) }}" readonly style="font-size: 0.6rem; padding: 5px; background: white; border-radius: 5px; width: 200px;">
                                <button onclick="copyLink('{{ route('invitation.register', $inv->token) }}')" class="btn-text" style="color: var(--primary); font-size: 0.6rem; font-weight: 900;">COPIAR</button>
                            </div>
                        @endif
                    </div>
                    <form action="{{ route('admin.invitations.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('¿Eliminar invitación?')">
                        @csrf @method('DELETE')
                        <button class="btn-text" style="color: var(--secondary); font-size: 1rem;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            @empty
                <p style="text-align: center; color: var(--text-muted); font-size: 0.7rem; padding: 1rem;">No hay invitaciones.</p>
            @endforelse
        </div>

        <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--text-muted); margin-bottom: 1.2rem; text-transform: uppercase;">Listado de Usuarios</h4>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($users as $user)
                <div class="stat-card" style="padding: 1rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $user->is_admin ? 'var(--primary)' : '#e2e8f0' }}; color: {{ $user->is_admin ? 'white' : 'var(--text-muted)' }}; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.9rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 900;">{{ $user->name }}</h4>
                            <p style="font-size: 0.6rem; color: var(--text-muted);">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar a este usuario?')">
                                @csrf @method('DELETE')
                                <button class="btn-text" style="color: var(--secondary); font-size: 1rem;"><i class="fas fa-user-times"></i></button>
                            </form>
                        @else
                            <span style="font-size: 0.6rem; font-weight: 900; color: var(--primary); background: var(--primary-glow); padding: 5px 10px; border-radius: 8px;">TÚ (ADMIN)</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
function copyLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        window.showToast('¡Link copiado al portapapeles!');
    });
}
</script>
@endsection
