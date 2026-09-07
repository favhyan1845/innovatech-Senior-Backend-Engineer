@extends('layouts.app')
@section('title', ($book ? 'Edit' : 'New').' book — Innovatech Library')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card"><div class="card-body p-4">
<div class="mb-4">
<h4 class="fw-bold mb-0">{{ $book ? 'Edit book' : 'Add a new book' }}</h4>
<p class="text-muted mb-0">Fields marked * are required.</p>
</div>
@if($book)
<form method="POST" action="{{ route('admin.books.update', $book) }}">@csrf @method('PUT')
@else
<form method="POST" action="{{ route('admin.books.store') }}">@csrf
@endif
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Title *</label>
<input type="text" name="title" value="{{ old('title', $book->title ?? '') }}" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Category</label>
<input type="text" name="category" value="{{ old('category', $book->category ?? '') }}" class="form-control" placeholder="e.g. Fiction"></div>
<div class="col-md-8"><label class="form-label">Author *</label>
<input type="text" name="author" value="{{ old('author', $book->author ?? '') }}" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">ISBN</label>
<input type="text" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" class="form-control" placeholder="978-3-16-148410-0"></div>
<div class="col-md-4"><label class="form-label">Publisher</label>
<input type="text" name="publisher" value="{{ old('publisher', $book->publisher ?? '') }}" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Language</label>
<select name="language" class="form-select">
@foreach(['en'=>'English','es'=>'Spanish','fr'=>'French','de'=>'German','pt'=>'Portuguese','it'=>'Italian'] as $code => $label)
<option value="{{ $code }}" @selected(old('language', $book->language ?? 'en') === $code)>{{ $label }}</option>
@endforeach
</select></div>
<div class="col-md-4"><label class="form-label">Published year</label>
<input type="number" name="published_year" min="1000" max="2100" value="{{ old('published_year', $book->published_year ?? '') }}" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Total copies *</label>
<input type="number" name="total_copies" min="1" max="10000" value="{{ old('total_copies', $book->total_copies ?? 1) }}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Cover image URL</label>
<input type="url" name="cover_url" value="{{ old('cover_url', $book->cover_url ?? '') }}" class="form-control" placeholder="https://…"></div>
<div class="col-12"><label class="form-label">Description</label>
<textarea name="description" rows="4" class="form-control">{{ old('description', $book->description ?? '') }}</textarea></div>
</div>
<div class="d-flex gap-2 mt-4">
<button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>{{ $book ? 'Save changes' : 'Create book' }}</button>
<a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div>
</div>
</div>
@endsection
