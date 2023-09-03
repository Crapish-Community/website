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
				<li><a href="{{ route('admin.assets') }}">Asset Approval</a></li>
				<li><a href="{{ route('admin.xmlitem') }}">New XML Item</a></li>
				<li><a href="{{ route('admin.renderasset') }}">Re-render Asset</a></li>
			</div>
			
			<div class="col-sm-3 my-2">
				<h3 class="text-muted">Users</h3>
				<li><a href="{{ route('moderator.banlist') }}">Ban List</a></li>
				<li><a href="{{ route('moderator.ban') }}">Ban User</a></li>
				<li><a href="{{ route('moderator.unban') }}">Unban User</a></li>
			</div>
			
			<div class="col-sm-3 my-2">
				<h3 class="text-muted">Site</h3>
				<li><a href="{{ route('admin.invitekeys') }}">Manage Existing Invite Keys</a></li>
				<li><a href="{{ route('admin.createinvitekey') }}">Create New Invite Key</a></li>
			</div>
	</div>
</div>
@endsection
