@extends('layouts.app')
@section('title', 'Register — Innovatech Library')
@section('content')
<div class="row justify-content-center">
<div class="col-md-6 col-lg-5">
<div class="card"><div class="card-body p-4">
<h4 class="fw-bold mb-1">Create your account</h4>
<p class="text-muted mb-4">Register as a library member to start borrowing books.</p>
<form method="POST" action="{{ route('register') }}">@csrf
<div class="mb-3"><label class="form-label">Full name</label>
<input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus></div>
<div class="mb-3"><label class="form-label">Email address</label>
<input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
<div class="form-text">At least 8 characters.</div></div>
<div class="mb-3"><label class="form-label">Confirm password</label>
<input type="password" name="password_confirmation" class="form-control" required></div>
<button class="btn btn-primary w-100"><i class="bi bi-person-plus me-1"></i>Register</button>
</form>
<hr>
<p class="text-center text-muted mb-0">Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div></div>
</div>
</div>
@endsection
