@extends('layouts.app')
@section('title', 'Manage Books — Innovatech Library')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
<div><h4 class="fw-bold mb-0">Manage books</h4><p class="text-muted mb-0">Add, edit and remove titles from the catalog.</p></div>
<a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add book</a>
</div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 align-items-center mb-3">
<div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search title, author, ISBN…"></div>
<div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Search</button></div>
@if(request()->filled('q'))<div class="col-auto"><a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a></div>@endif
</form>
<div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Book</th><th>Category</th><th>Copies</th><th>Available</th><th>Loans</th><th class="text-end">Actions</th></tr></thead>
<tbody>
@forelse($books as $book)
<tr>
<td><a href="{{ route('catalog.show', $book) }}" class="fw-semibold text-decoration-none">{{ $book->title }}</a><br><small class="text-muted">{{ $book->author }}</small></td>
<td>{{ $book->category ?? '—' }}</td>
<td>{{ $book->total_copies }}</td>
<td>@if($book->available_copies > 0)<span class="badge text-bg-success">{{ $book->available_copies }}</span>@else<span class="badge text-bg-danger">0</span>@endif</td>
<td>{{ $book->loans_count }}</td>
<td class="text-end text-nowrap">
<a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
<form method="POST" action="{{ route('admin.books.destroy', $book) }}" class="d-inline" onsubmit="return confirm('Delete “{{ $book->title }}”? This removes its loan history.');">@csrf @method('DELETE')
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
</td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No books found.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $books->links() }}</div>
</div></div>
@endsection
