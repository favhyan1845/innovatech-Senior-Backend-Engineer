@extends('layouts.app')
@section('title', 'Dashboard — Innovatech Library')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
<div>
<h4 class="fw-bold mb-0">Hi, {{ $user->name }} 👋</h4>
<p class="text-muted mb-0">@if($user->isStaff())Here is what is happening at the library today.@elseManage your current loans and reading history.@endif</p>
</div>
@if($user->isStaff())
<a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add book</a>
@endif
</div>
@if($user->isStaff())
<div class="row g-3 mb-4">
@php $cards = [['title'=>'Books','value'=>$stats['total_books'],'icon'=>'bi-collection','color'=>'primary','link'=>route('admin.books.index')],['title'=>'Copies','value'=>$stats['total_copies'],'icon'=>'bi-layers','color'=>'secondary','link'=>null],['title'=>'Available','value'=>$stats['available_copies'],'icon'=>'bi-check-circle','color'=>'success','link'=>null],['title'=>'Active loans','value'=>$stats['active_loans'],'icon'=>'bi-arrow-left-right','color'=>'info','link'=>route('admin.loans.index')],['title'=>'Overdue','value'=>$stats['overdue_loans'],'icon'=>'bi-exclamation-octagon','color'=>'danger','link'=>route('admin.loans.index', ['status'=>'overdue'])],['title'=>'Members','value'=>$stats['total_members'],'icon'=>'bi-people','color'=>'dark','link'=>route('admin.users.index')]]; @endphp
@foreach($cards as $c)
<div class="col-6 col-md-4 col-lg-2">
<div class="card text-center h-100"><div class="card-body">
<a href="{{ $c['link'] ?? '#' }}" class="text-decoration-none text-{{ $c['color'] }}"><i class="bi {{ $c['icon'] }} fs-2"></i></a>
<div class="fs-3 fw-bold">{{ $c['value'] }}</div>
<div class="small text-muted">{{ $c['title'] }}</div>
</div></div>
</div>
@endforeach
</div>
<div class="row g-4">
<div class="col-lg-8">
<div class="card"><div class="card-header bg-white fw-semibold"><i class="bi bi-arrow-left-right me-2"></i>Active loans</div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Book</th><th>Member</th><th>Due</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($activeLoans as $loan)
<tr>
<td><a href="{{ route('catalog.show', $loan->book) }}" class="fw-semibold text-decoration-none">{{ $loan->book->title }}</a><br><small class="text-muted">{{ $loan->book->author }}</small></td>
<td>{{ $loan->user->name }}</td>
<td class="text-nowrap">{{ $loan->due_at->format('M j, Y') }}</td>
<td>@if($loan->isOverdue())<span class="badge text-bg-danger">Overdue</span>@else<span class="badge text-bg-success">On time</span>@endif</td>
<td><form method="POST" action="{{ route('loans.checkin', $loan) }}">@csrf
<button class="btn btn-sm btn-outline-success">Return</button></form></td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">No active loans right now. 🎉</td></tr>
@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-4">
<div class="card mb-3"><div class="card-body">
<h6 class="fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Top categories</h6>
@if(!empty($stats['top_categories']))
@foreach($stats['top_categories'] as $category => $total)
<div class="d-flex justify-content-between small mb-1"><span>{{ $category }}</span><span class="fw-semibold">{{ $total }}</span></div>
@endforeach
@else<p class="text-muted small mb-0">No categories yet.</p>@endif
</div></div>
<div class="card"><div class="card-body">
<h6 class="fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Quick actions</h6>
<ul class="list-unstyled small mb-0">
<li class="mb-2"><a href="{{ route('admin.books.index') }}" class="text-decoration-none"><i class="bi bi-collection me-2"></i>Manage books</a></li>
<li class="mb-2"><a href="{{ route('admin.loans.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left-right me-2"></i>All loans</a></li>
@if($user->isAdmin())<li class="mb-2"><a href="{{ route('admin.users.index') }}" class="text-decoration-none"><i class="bi bi-people me-2"></i>Manage users</a></li>@endif
</ul>
</div></div>
</div>
</div>
@else
<div class="row g-4">
<div class="col-lg-7">
<div class="card"><div class="card-header bg-white fw-semibold"><i class="bi bi-bag me-2"></i>Currently checked out</div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($currentLoans as $loan)
<tr>
<td><a href="{{ route('catalog.show', $loan->book) }}" class="fw-semibold text-decoration-none">{{ $loan->book->title }}</a><br><small class="text-muted">{{ $loan->book->author }}</small></td>
<td>{{ $loan->borrowed_at->format('M j, Y') }}</td>
<td class="text-nowrap">{{ $loan->due_at->format('M j, Y') }}</td>
<td>@if($loan->isOverdue())<span class="badge text-bg-danger">Overdue</span>@else<span class="badge text-bg-success">On time</span>@endif</td>
<td><form method="POST" action="{{ route('loans.checkin', $loan) }}">@csrf<button class="btn btn-sm btn-outline-success">Return</button></form></td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">Nothing checked out. <a href="{{ route('home') }}">Browse the catalog</a>!</td></tr>
@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-body">
<h6 class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Reading history</h6>
@if($history->isEmpty())<p class="text-muted small mb-0">Books you return will appear here.</p>@endif
<ul class="list-unstyled small mb-0">
@foreach($history as $loan)
<li class="d-flex justify-content-between border-bottom pb-2 mb-2">
<span><a href="{{ route('catalog.show', $loan->book) }}" class="text-decoration-none">{{ $loan->book->title }}</a></span>
<span class="text-muted text-nowrap">Returned {{ $loan->returned_at->format('M j, Y') }}</span>
</li>
@endforeach
</ul>
</div></div>
</div>
</div>
@endif
@endsection
