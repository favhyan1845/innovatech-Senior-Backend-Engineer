<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Innovatech Library')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body{background:#f5f6fa}.navbar{background:#1f2a44!important}
.navbar .navbar-brand,.navbar .nav-link,.navbar .dropdown-toggle{color:#dfe6f0!important}
.navbar .nav-link:hover,.navbar .dropdown-toggle:hover{color:#fff!important}
.book-cover{width:100%;height:190px;object-fit:cover;background:linear-gradient(135deg,#34495e,#1f2a44);display:flex;align-items:center;justify-content:center;color:#fff;font-size:3rem;font-weight:700;border-radius:.5rem .5rem 0 0}
.card{border:none;box-shadow:0 .125rem .5rem rgba(31,42,68,.08)}
.card-hover{transition:transform .15s ease,box-shadow .15s ease}
.card-hover:hover{transform:translateY(-3px);box-shadow:0 .5rem 1rem rgba(31,42,68,.15)}
.hero{background:linear-gradient(120deg,#1f2a44,#34495e);color:#fff}
.badge-soft{background:rgba(31,42,68,.08);color:#1f2a44;font-weight:600}
footer{color:#8a93a6;font-size:.85rem}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
<div class="container">
<a class="navbar-brand fw-bold" href="{{ route('home') }}"><i class="bi bi-book-half me-1"></i>Innovatech Library</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNav">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-grid me-1"></i>Catalog</a></li>
@auth
<li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
@if(auth()->user()->canManageCatalog())
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Manage</a>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="{{ route('admin.books.index') }}"><i class="bi bi-collection me-1"></i>Books</a></li>
<li><a class="dropdown-item" href="{{ route('admin.loans.index') }}"><i class="bi bi-arrow-left-right me-1"></i>Loans</a></li>
@if(auth()->user()->isAdmin())
<li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-1"></i>Users</a></li>
@endif
</ul>
</li>
@endif
@endauth
</ul>
<ul class="navbar-nav">
@guest
<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
<li class="nav-item"><a class="btn btn-outline-light btn-sm ms-2 mt-1" href="{{ route('register') }}">Register</a></li>
@else
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }} <span class="badge text-bg-light ms-1">{{ auth()->user()->roleLabel() }}</span></a>
<ul class="dropdown-menu dropdown-menu-end">
<li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
<li><hr class="dropdown-divider"></li>
<li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-1"></i>Logout</button></form></li>
</ul>
</li>
@endguest
</ul>
</div>
</div>
</nav>
<main class="py-4"><div class="container">
@if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Please fix the following errors:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@yield('content')
</div></main>
<footer class="text-center py-4">Innovatech Library &middot; Mini Library Management System &middot; Laravel {{ app()->version() }}</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
