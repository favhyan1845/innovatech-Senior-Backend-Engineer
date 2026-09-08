@extends('layouts.app')
@section('title', 'Users — Innovatech Library')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
<div><h4 class="fw-bold mb-0">Users</h4><p class="text-muted mb-0">Manage members and staff roles.</p></div>
</div>
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2 align-items-center">
<div class="col-md-5"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or email…"></div>
<div class="col-md-4"><select name="role" class="form-select">
<option value="">All roles</option>
@foreach(['admin','librarian','member'] as $role)
<option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}s</option>
@endforeach
</select></div>
<div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Filter</button></div>
@if(request()->filled('q') || request()->filled('role'))<div class="col-auto"><a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a></div>@endif
</form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>User</th><th>Role</th><th>Active loans</th><th>Member since</th><th class="text-end">Change role</th></tr></thead>
<tbody>
@forelse($users as $user)
<tr>
<td><strong>{{ $user->name }}</strong><br><small class="text-muted">{{ $user->email }}</small></td>
<td><span class="badge {{ $user->role === 'admin' ? 'text-bg-danger' : ($user->role === 'librarian' ? 'text-bg-primary' : 'text-bg-secondary') }}">{{ $user->roleLabel() }}</span></td>
<td>{{ $user->loans_count }}</td>
<td>{{ $user->created_at->format('M j, Y') }}</td>
<td class="text-end">
@if($user->id === auth()->id())
<small class="text-muted">You</small>
@else
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="d-inline-flex align-items-center gap-1">@csrf @method('PUT')
<select name="role" class="form-select form-select-sm">
@foreach(['member','librarian','admin'] as $role)
<option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
@endforeach
</select>
<button class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
</form>
@endif
</td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
