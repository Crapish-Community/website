<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Ban;

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
}