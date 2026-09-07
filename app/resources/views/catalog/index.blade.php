@extends('layouts.app')
@section('title', 'Catalog — Innovatech Library')
@section('content')
<div class="hero rounded-4 p-4 p-md-5 mb-4">
<h1 class="fw-bold">Find your next great read</h1>
<p class="lead mb-4 text-white-50">Browse the collection, check availability and borrow books online.</p>
<form method="GET" action="{{ route('home') }}" id="catalogForm">
<div class="input-group input-group-lg mb-3">
<input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by title, author, ISBN, publisher or category…">
<button class="btn btn-light fw-semibold" type="submit"><i class="bi bi-search me-1"></i>Search</button>
</div>
<div class="row g-2 align-items-end">
<div class="col-md-4">
<label class="form-label small fw-semibold text-white-50">Category</label>
<select name="category" class="form-select" onchange="this.form.submit()">
<option value="">All categories</option>
@foreach($categories as $category)
<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
@endforeach
</select>
</div>
<div class="col-md-4">
<label class="form-label small fw-semibold text-white-50">Sort by</label>
<select name="sort" class="form-select" onchange="this.form.submit()">
@php $sorts = ['newest'=>'Newest arrivals','oldest'=>'Oldest first','title'=>'Title A→Z','author'=>'Author A→Z','popular'=>'Most borrowed','available'=>'Most available']; @endphp
@foreach($sorts as $key => $label)
<option value="{{ $key }}" @selected((request('sort', 'newest') === $key))>{{ $label }}</option>
@endforeach
</select>
</div>
<div class="col-md-4">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="available" value="1" id="availableSwitch" @checked(request()->boolean('available')) onchange="this.form.submit()">
<label class="form-check-label text-white-50" for="availableSwitch">Only show available books</label>
</div>
</div>
</div>
</form>
</div>
@if(request()->filled('q') || request()->filled('category'))
<div class="mb-3"><a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear filters</a></div>
@endif
@if($books->isEmpty())
<div class="text-center py-5"><i class="bi bi-emoji-frown display-4 text-muted"></i><p class="lead mt-3">No books match your search.</p><a href="{{ route('home') }}" class="btn btn-primary">Browse all books</a></div>
@else
<div class="row g-4">
@foreach($books as $book)
<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
<div class="card h-100 card-hover">
<a href="{{ route('catalog.show', $book) }}" class="text-decoration-none">
@if($book->cover_url)<img src="{{ $book->cover_url }}" class="book-cover" alt="{{ $book->title }}">
@else<div class="book-cover"><span>{{ mb_strtoupper(mb_substr($book->title, 0, 1)) }}</span></div>@endif
</a>
<div class="card-body d-flex flex-column">
<span class="badge badge-soft align-self-start mb-2">{{ $book->category ?? 'General' }}</span>
<h6 class="card-title mb-1"><a href="{{ route('catalog.show', $book) }}" class="text-decoration-none text-dark">{{ $book->title }}</a></h6>
<p class="text-muted small mb-2">{{ $book->author }}@if($book->published_year) <span class="text-secondary">· {{ $book->published_year }}</span>@endif</p>
<div class="mt-auto">
@if($book->available_copies > 0)<span class="badge text-bg-success mb-2">{{ $book->available_copies }} available</span>
@else<span class="badge text-bg-danger mb-2">Unavailable</span>@endif
<div class="d-grid"><a href="{{ route('catalog.show', $book) }}" class="btn btn-sm btn-outline-primary">Details</a></div>
</div>
</div>
</div>
</div>
@endforeach
</div>
<div class="mt-4">{{ $books->links() }}</div>
@endif
@if($featured->isNotEmpty())
<section class="mt-5">
<h5 class="fw-bold"><i class="bi bi-fire me-2 text-warning"></i>Most popular right now</h5>
<div class="row g-3">
@foreach($featured as $book)
<div class="col-6 col-lg-2 col-md-3">
<div class="card h-100 card-hover text-center">
<a href="{{ route('catalog.show', $book) }}" class="text-decoration-none">
@if($book->cover_url)<img src="{{ $book->cover_url }}" class="book-cover" style="height:130px" alt="{{ $book->title }}">
@else<div class="book-cover" style="height:130px;font-size:2rem"><span>{{ mb_strtoupper(mb_substr($book->title, 0, 1)) }}</span></div>@endif
</a>
<div class="card-body p-2"><a href="{{ route('catalog.show', $book) }}" class="small text-decoration-none text-dark fw-semibold">{{ $book->title }}</a></div>
</div>
</div>
@endforeach
</div>
</section>
@endif
@endsection
