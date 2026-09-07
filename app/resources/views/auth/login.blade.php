@extends('layouts.app')
@section('title', 'Login — Innovatech Library')
@section('content')
<div class="row justify-content-center">
<div class="col-md-6 col-lg-4">
<div class="card"><div class="card-body p-4">
<h4 class="fw-bold mb-1">Welcome back</h4>
<p class="text-muted mb-4">Login to borrow books and manage your loans.</p>
<form method="POST" action="{{ route('login') }}">@csrf
<div class="mb-3"><label class="form-label">Email address</label>
<input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus></div>
<div class="mb-3"><label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required></div>
<div class="mb-3 form-check"><input type="checkbox" name="remember" id="remember" class="form-check-input" @checked(old('remember'))>
<label class="form-check-label" for="remember">Remember me</label></div>
<button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
</form>
<hr>
<p class="text-center text-muted mb-0">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
</div></div>
<div class="card mt-3"><div class="card-body">
<h6 class="fw-bold"><i class="bi bi-info-circle me-1"></i>Demo accounts</h6>
<ul class="small text-muted mb-2">
<li><strong>Admin:</strong> admin@library.test / password</li>
<li><strong>Librarian:</strong> librarian@library.test / password</li>
<li><strong>Member:</strong> member@library.test / password</li>
</ul>
<p class="small text-muted mb-0">Available after running <code>php artisan db:seed</code>.</p>
</div></div>
</div>
</div>
@endsection
