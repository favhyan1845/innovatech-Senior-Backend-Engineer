@extends('layouts.app')
@section('title', $book->title.' — Innovatech Library')
@section('content')
<div class="row g-4">
<div class="col-lg-4 col-xl-3">
@if($book->cover_url)<img src="{{ $book->cover_url }}" class="book-cover rounded-4" style="height:340px" alt="{{ $book->title }}">
@else<div class="book-cover rounded-4" style="height:340px;font-size:6rem"><span>{{ mb_strtoupper(mb_substr($book->title, 0, 1)) }}</span></div>@endif
<div class="card mt-3">
<div class="card-body">
<h6 class="fw-bold">Availability</h6>
@if($book->available_copies > 0)
<p class="text-success mb-2"><i class="bi bi-check-circle me-1"></i><strong>{{ $book->available_copies }}</strong> of {{ $book->total_copies }} copies available</p>
@auth
<form method="POST" action="{{ route('loans.checkout', $book) }}">@csrf
<button class="btn btn-success w-100" {{ $activeLoanForCurrentUser ? 'disabled' : '' }}>
<i class="bi bi-bag-plus me-1"></i>{{ $activeLoanForCurrentUser ? 'You already have it' : 'Check out' }}
</button>
</form>
@if(!$activeLoanForCurrentUser)<small class="text-muted d-block mt-2 text-center">Due {{ now()->addDays(14)->format('M j, Y') }}</small>@endif
@else
<a href="{{ route('login') }}" class="btn btn-outline-primary w-100">Login to borrow</a>
@endauth
@else
<p class="text-danger mb-2"><i class="bi bi-x-circle me-1"></i>All {{ $book->total_copies }} copies are checked out.</p>
@endif
@if($activeLoanForCurrentUser)
<hr>
<form method="POST" action="{{ route('loans.checkin', $activeLoanForCurrentUser) }}">@csrf
<button class="btn btn-outline-success w-100"><i class="bi bi-arrow-return-left me-1"></i>Return this copy</button>
</form>
<small class="text-muted d-block mt-2 text-center">Due {{ $activeLoanForCurrentUser->due_at->format('M j, Y') }} ({{ $activeLoanForCurrentUser->isOverdue() ? 'overdue' : 'on time' }})</small>
@endif
@auth
@if(auth()->user()->canManageCatalog())
<hr>
<div class="d-flex gap-2">
<a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-pencil me-1"></i>Edit</a>
<form method="POST" action="{{ route('admin.books.destroy', $book) }}" class="flex-fill" onsubmit="return confirm('Delete this book?');">@csrf @method('DELETE')
<button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete</button></form>
</div>
@endif
@endauth
</div>
</div>
</div>
<div class="col-lg-8 col-xl-9">
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Catalog</a></li><li class="breadcrumb-item active">{{ $book->title }}</li></ol></nav>
<div class="card"><div class="card-body p-4">
<div class="d-flex flex-wrap gap-2 mb-3">
@if($book->category)<span class="badge text-bg-primary">{{ $book->category }}</span>@endif
@if($book->language)<span class="badge badge-soft">{{ strtoupper($book->language) }}</span>@endif
@if($book->published_year)<span class="badge badge-soft">{{ $book->published_year }}</span>@endif
@if($book->isbn)<span class="badge badge-soft">ISBN {{ $book->isbn }}</span>@endif
</div>
<h1 class="fw-bold mb-1">{{ $book->title }}</h1>
<p class="text-muted mb-3"><i class="bi bi-person me-1"></i>{{ $book->author }}@if($book->publisher)<span class="ms-3"><i class="bi bi-building me-1"></i>{{ $book->publisher }}</span>@endif</p>
@if($book->description)
<h6 class="fw-bold">Description</h6>
<p class="text-secondary" style="white-space:pre-line">{{ $book->description }}</p>
@endif
<hr>
<div class="row text-center">
<div class="col-3"><div class="fw-bold fs-4">{{ $book->total_copies }}</div><small class="text-muted">Total copies</small></div>
<div class="col-3"><div class="fw-bold fs-4">{{ $book->available_copies }}</div><small class="text-muted">Available</small></div>
<div class="col-3"><div class="fw-bold fs-4">{{ $book->active_loans_count }}</div><small class="text-muted">Checked out</small></div>
<div class="col-3"><div class="fw-bold fs-4">{{ $book->loans_count }}</div><small class="text-muted">All-time loans</small></div>
</div>
</div></div>
@if($similar->isNotEmpty())
<div class="card mt-4"><div class="card-body">
<h5 class="fw-bold"><i class="bi bi-stars me-2 text-warning"></i>Readers also like</h5>
<div class="row g-3">
@foreach($similar as $s)
<div class="col-6 col-lg-3">
<div class="card h-100 card-hover">
<a href="{{ route('catalog.show', $s) }}" class="text-decoration-none">
@if($s->cover_url)<img src="{{ $s->cover_url }}" class="book-cover" style="height:110px" alt="{{ $s->title }}">
@else<div class="book-cover" style="height:110px;font-size:1.6rem"><span>{{ mb_strtoupper(mb_substr($s->title, 0, 1)) }}</span></div>@endif
</a>
<div class="card-body p-2">
<a href="{{ route('catalog.show', $s) }}" class="small text-decoration-none text-dark fw-semibold">{{ $s->title }}</a>
<p class="text-muted small mb-0">{{ $s->author }}</p>
</div>
</div>
</div>
@endforeach
</div>
</div></div>
@endif
</div>
</div>
@endsection
