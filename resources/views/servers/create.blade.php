@extends('layouts.app')

@section('title', 'Create Server')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-primary">
                <i class="fas fa-info mr-1"></i> If you don't know how to host, follow <a href="#" data-toggle="modal" data-target="#guideModal">this guide</a>.
            </div>
            <div class="card shadow-sm">
                <div class="card-header">{{ __('Create Server') }}</div>
                <div class="card-body">
                    @if (config('app.server_creation_enabled'))
                        <form method="POST" action="{{ route('servers.create') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Server Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('Description') }}</label>

                                <div class="col-md-6">
                                    <textarea id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}"></textarea>

                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="version" class="col-md-4 col-form-label text-md-right">{{ __('Version') }}</label>

                                <div class="col-md-6">
                                    <select class="form-control" id="version" name="version" required>
                                        @foreach (config('app.clients') as $client => $version)
                                            <option>{{ $client }}</option>
                                        @endforeach
                                    </select>
                                    @error('version')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="ipaddress" class="col-md-4 col-form-label text-md-right">{{ __('IP Address') }}</label>

                                <div class="col-md-6">
                                    <input id="ipaddress" type="text" class="form-control @error('ipaddress') is-invalid @enderror" name="ipaddress" required value="{{ Auth::user()->last_ip }}">

                                    @error('ipaddress')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="loopbackip" class="col-md-4 col-form-label text-md-right">{{ __('Loopback IP') }}</label>

                                <div class="col-md-6">
                                    <input id="loopbackip" type="text" class="form-control @error('loopbackip') is-invalid @enderror" name="loopbackip" placeholder="Local IP address">

                                    @error('loopbackip')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="port" class="col-md-4 col-form-label text-md-right">{{ __('Port') }}</label>

                                <div class="col-md-6">
                                    <input id="port" type="number" onwheel="this.blur()" class="form-control" name="port" required value="53640">
                                </div>
                            </div>

                            <hr>

                            <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="unlisted" id="unlisted">

                                        <label class="form-check-label" for="unlisted">
                                            {{ __('Unlisted') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="guest" id="guest">
    
                                        <label class="form-check-label" for="guest">
                                            {{ __('Allow Guests (Insecure)') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="maxplayers" class="col-md-4 col-form-label text-md-right">{{ __('Max Players') }}</label>

                                <div class="col-md-6">
                                    <input id="maxplayers" type="number" onwheel="this.blur()" class="form-control" name="maxplayers" value="" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="chattype" class="col-md-4 col-form-label text-md-right">{{ __('Chat Type') }}</label>

                                <div class="col-md-6">
                                    <select class="form-control @error('chattype') is-invalid @enderror" id="chattype" name="chattype" required>
                                        <option value="0">Classic</option>
                                        <option value="1">Bubble</option>
                                        <option value="2">Classic and Bubble</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="place" class="col-md-4 col-form-label text-md-right @error('place') is-invalid @enderror">{{ __('Place') }}</label>

                                <div class="col-md-6">
                                    <input type="file" class="form-control-file @error('place') is-invalid @enderror" name="place" required>

                                    @error('place')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col">
                                    <button type="submit" class="w-100 btn btn-primary shadow-sm">
                                        <i class="fas fa-plus mr-1"></i>Create
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <h2 class="text-center">Server creation disabled</h2>
                        <p class="text-center">Sorry, server creation has been disabled. Check back later.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="guideModal" tabindex="-1" role="dialog" aria-labelledby="guideModalLbl">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title" id="wearingItemsLbl">
                        How to host servers
                    </p>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="row mx-0 py-2 px-2">
                    <p class="p-0 m-0 w-100 d-flex text-muted">To host a server you can either port forward or use playit.</p>
                    <p class="p-0 m-0 w-100 d-flex text-muted">This guide will focus on using playit as it will be the most common form of hosting.</p>
                    <ul>
                        <li>First we will go to <a href="https://playit.gg/">playit</a> and make an account.</li>
                        <li>We will want to make a tunnel by going to the tunnels tab and clicking add tunnel. For the tunnel type we will want TCP + UDP.</li>
                        <li>The port count of the tunnel will be 1 and the local port can be left as default.</li>
                        <li>After the tunnel is created, go to the configuration page of the tunnel.</li>
                        <li>You must then grab the port assigned to your tunnel, scroll down and paste it inside "Local Port". After that, click save.</li>
                        <li>Then you can go to downloads and download the playit program and run it. Make sure to authorise it with your account.</li>
                        <li>You can then go back to Crapish. For the IP and port, we will go back to playit and copy them. Leave the loopback IP blank.</li>
                        <li>Lastly, go on your server page, scroll down and click host server. Make sure you have the <a href="/download">Crapish Launcher</a> installed!
                    </ul>
                    <p class="p-0 m-0 w-100 d-flex text-muted">Then you should be good to go!</p>
                    <br>
                    <p class="p-0 m-0 w-100 d-flex text-muted">Make sure to run playit everytime you host and if you have any problems feel free to send a message to a staff member.</p>                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
