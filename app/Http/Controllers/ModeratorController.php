<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AdminLog;
use App\Models\InviteKey;
use App\Models\Item;
use App\Models\Ban;
use Carbon\Carbon;
use Log;

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
}