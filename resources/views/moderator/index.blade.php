@inject('user', 'App\Http\Controllers\UsersController')

@extends('layouts.admin')

@section('title', 'Moderator')

@section('content')
<div class="container admin-panel">
    <h1><b>Moderator Panel</b></h1>
<hr>
    <div class="row">
			<div class="col-sm-3 my-2">
				<h3 class="text-muted">Assets</h3>
				<li><a href="{{ route('staff.assets') }}">Asset Approval</a></li>
				<li><a href="{{ route('staff.xmlitem') }}">New XML Item</a></li>
			</div>
			
			<div class="col-sm-3 my-2">
				<h3 class="text-muted">Users</h3>
				<li><a href="{{ route('staff.banlist') }}">Ban List</a></li>
				<li><a href="{{ route('staff.ban') }}">Ban User</a></li>
				<li><a href="{{ route('staff.unban') }}">Unban User</a></li>
			</div>
			
			<div class="col-sm-3 my-2">
				<h3 class="text-muted">Site</h3>
				<li><a href="{{ route('staff.invitekeys') }}">Manage Existing Invite Keys</a></li>
				<li><a href="{{ route('staff.createinvitekey') }}">Create New Invite Key</a></li>
			</div>
	</div>
</div>
@endsection
