@extends('layouts.app')
@section('title', 'Loans — Innovatech Library')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
<div><h4 class="fw-bold mb-0">Loans</h4><p class="text-muted mb-0">Every check-out and check-in across the library.</p></div>
</div>
<div class="card mb-3"><div class="card-body py-2">
<ul class="nav nav-pills">
@php $tabs = ['active'=>'Active','overdue'=>'Overdue','returned'=>'Returned']; @endphp
@foreach($tabs as $key => $label)
<li class="nav-item"><a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.loans.index', array_merge(request()->except(['status','page']), ['status'=>$key])) }}">{{ $label }}
<span class="badge ms-1 {{ $status === $key ? 'text-bg-light' : 'text-bg-secondary' }}">{{ $counts[$key] }}</span></a></li>
@endforeach
</ul>
</div></div>
<form method="GET" class="row g-2 mb-3">
<input type="hidden" name="status" value="{{ $status }}">
<div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by book title, author, member…"></div>
<div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Search</button></div>
@if(request()->filled('q'))<div class="col-auto"><a href="{{ route('admin.loans.index', ['status'=>$status]) }}" class="btn btn-outline-secondary btn-sm">Clear</a></div>@endif
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Book</th><th>Member</th><th>Checked out</th><th>Due</th><th>Returned</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($loans as $loan)
<tr>
<td><a href="{{ route('catalog.show', $loan->book) }}" class="fw-semibold text-decoration-none">{{ $loan->book->title }}</a><br><small class="text-muted">{{ $loan->book->author }}</small></td>
<td>{{ $loan->user->name }}<br><small class="text-muted">{{ $loan->user->email }}</small></td>
<td>{{ $loan->borrowed_at->format('M j, Y') }}</td>
<td>{{ $loan->due_at->format('M j, Y') }}</td>
<td>{{ $loan->returned_at?->format('M j, Y') ?? '—' }}</td>
<td>
@if($loan->status === 'returned')<span class="badge text-bg-secondary">Returned</span>
@elseif($loan->isOverdue())<span class="badge text-bg-danger">Overdue</span>
@else<span class="badge text-bg-success">Active</span>@endif
</td>
<td>@if(!$loan->isReturned())
<form method="POST" action="{{ route('loans.checkin', $loan) }}">@csrf<button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-return-left me-1"></i>Return</button></form>
@endif</td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-4">No loans found.</td></tr>
@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $loans->links() }}</div>
@endsection
