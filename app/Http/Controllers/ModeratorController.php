<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Ban;
use Carbon\Carbon;

class ModeratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('mod');
    }

    public function index(Request $request)
    {
        return view('moderator.index');
    }
    
    public function banlist(Request $request)
    {
        $bans = Ban::query();
        if (request('search')) {
            $users = User::where('username', 'LIKE', '%' . request('search') . '%')->get();
            if($users) {
                $bans->whereIn('user_id', $users->pluck('id'))->orderBy('updated_at', 'desc');
            }
        }
        return view('admin.banlist')->with(['bans' => $bans->orderBy('updated_at', 'DESC')->paginate(10)->appends($request->all())]);
    }

    public function ban(Request $request) {        
        return view('admin.ban');
    }

    public function banuser(Request $request) {
        $request->validate([
            'username' => ['required', 'string'],
            'banreason' => ['required', 'max:2000'],
            'unbandate' => ['required', 'date']
        ]);

        $user = User::where('username', $request['username'])->first();

        if($user) {
            $checkforban = Ban::where(['user_id' => $user->id, 'banned' => true])->first();
        }

        if (!$user) {
            return redirect('/moderator/ban')->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        if ($checkforban) {
            return redirect('/moderator/ban')->with('error', 'That user is already banned. Reason: ' . $user->ban_reason);
        }

        if ($user->isStaff()) {
            return redirect('/moderator/ban')->with('error', 'If you do not like another staff member, you should probably bring it up.');
        }

        if ($request->user()->id == $user->id) {
            return redirect('/moderator/ban')->with('error', 'You\'re trying to ban yourself?');
        }

        $ban = new Ban;
        $ban->user_id = $user->id;
        $ban->banned = true;
        $ban->ban_reason = $request['banreason'];
        $ban->banned_until = Carbon::parse($request['unbandate']);
        $ban->save();

        AdminLog::log($request->user(), sprintf('Banned user %s. (USER ID: %s)', $ban->user->username, $ban->user->id));

        return redirect('/moderator/ban')->with('success', $user->username . '  has been banned until ' . $ban->banned_until);
    }
}