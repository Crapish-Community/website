@extends('layouts.app')

@section('title', 'Contributors')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header">{{ config('app.name') }} Credits</div>
        <div class="card-body">
            <p>Crapish uses a modified version of the Tadah website.</p>
            <p>Credit goes to the original developers:</p>

            <ul>
                <li><b>kinery</b> - Project Lead</li>
                <li><b>spike</b> - Tadah lead artist, designed the Token icon</li>
                <li><b>taskmanager</b> - Frontend development</li>
                <li><b>Iago</b> - Client and frontend development</li>
                <li><b>hitius</b> - Dedicated servers</li>
                <li><b>Carrot</b> - Backend engineer</li>
                <li><b>pizzaboxer</b> - Client development</li>
                <li><b>Ahead</b> - Backend development</li>
            </ul>

            <h4>Special thanks</h4>
            <ul>
                <li><b>CarlTheObeseCat</b> - Helped with {{ config('app.name') }} logos</li>
            </ul>

            <p>Without these people, {{ config('app.name') }} would not be as good as it is today.</p>
        </div>
    </div>
</div>
@endsection
