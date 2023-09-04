@extends('layouts.app')

@section('meta')
<meta property="og:title" content="{{ config('app.name') }} - Welcome">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current(); }}">
<meta property="og:image" content="/images/logos/small.png">
<meta property="og:description" content="{{ config('app.name') }} is a place to be.">
<meta name="theme-color" content="#0000FF">
@endsection

@section('content')
<main class="landing-page vw-100 vh-100 justify-content-center align-items-center d-flex">
    <div class="container-fluid text-center">
        <iframe width="25%" height="15%" src="https://www.youtube-nocookie.com/embed/g9znsWIdu6M?controls=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        <p>
        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg shadow-lg mr-3"><i class="fas fa-sign-in-alt mr-1"></i>Login</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg shadow-lg"><i class="fas fa-user-plus mr-1"></i>Sign Up</a>
        </p>
    </div>
</main>
@endsection
