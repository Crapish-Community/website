<?php
// You're a douchebag
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\Server;
use App\Models\GameToken;
use App\Models\InviteKey;
use App\Models\Item;
use App\Models\OwnedItems;
use App\Models\Ban;
use App\Models\RenderQueue;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Jobs\RenderJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        return view('admin.index');
    }

    public function truncategametokens(Request $request) {
        GameToken::Truncate();
        AdminLog::log($request->user(), 'Cleared all join logs from the database.', true);

        return redirect('/admin')->with('message', 'Cleared all Game Tokens from the database.');
    }

    public function truncateservers(Request $request) {
        Server::Truncate();
        AdminLog::log($request->user(), 'Cleared all servers from the database.', true);

        return redirect('/admin')->with('message', 'Cleared all Servers from the database.');
    }
    
    public function regenalluserthumbs(Request $request)
    {
        if (!$request->user()->id == 1) {
            abort(404);
        }

        AdminLog::log($request->user(), 'Regenerated every user thumbnail. Not sure why this feature still exists. Hi there, if you are reading this.', true);

        $users = User::all();
        $jobs = [];

        foreach ($users as $user) {
            array_push($jobs, new RenderJob('user', $user->id));
        }

        Bus::batch($jobs)->dispatch();

        return "OK";
    }

    public function forcewearitem(Request $request)
    {
        // funny
        // I can just recycle rewardItem for this
        $request->validate([
            'username' => ['required', 'string'],
            'itemid' => ['required', 'integer']
        ]);

        $force = $request->has('force');
        $user = User::where('username', $request->username)->firstOrFail();

        $this->rewarditem($request, true, $force);
        $this->dispatch(new RenderJob('user', $user->id));

        AdminLog::log($request->user(), sprintf('Forced %s to wear an item. (USER ID: %s)', $user->username, $user->id), true);

        return redirect(route('admin.forcewearitem'));
    }

    public function money(Request $request)
    {
        return view('admin.money');
    }

    public function changemoney(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'amount' => ['required', 'integer', 'max:10000']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.money'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        $user->money = $user->money + $request['amount'];
        $user->save();

        AdminLog::log($request->user(), sprintf('Rewarded user %s with %s money. (USER ID: %s)', $user->username, $request['amount'], $user->id), true);

        return redirect(route('admin.money'))->with('success', $user->username . '  has been given ' . $request['amount'] . ' ' . config('app.currency_name_multiple'));
    }

    public function item(Request $request)
    {
        return view('admin.item');
    }

    public function wearitem(Request $request)
    {
        return view('admin.wearitem');
    }

    public function rewarditem(Request $request, $wear = false, $force = false)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'itemid' => ['required', 'integer']
        ]);

        $user = User::where('username', $request['username'])->first();
        $item = Item::where('id', $request['itemid'])->first();
        $ownedItem = OwnedItems::where(['user_id' => $user->id, 'item_id' => $item->id])->first();

        if (!$user) {
            return redirect(route('admin.item'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        if (!$item) {
            return redirect(route('admin.item'))->with('error', 'That item does not exist. Item ID: ' . $request['itemid']);
        }

        if ($ownedItem && !$wear) {
            return redirect(route('admin.item'))->with('error', $user->username . ' already owns item: ' . $item->name);
        }

        if($ownedItem && $wear) {
            $ownedItem->wearing = true;        
            $ownedItem->unequippable = $force;
            $ownedItem->save();
        } else {
            OwnedItems::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'wearing' => $wear,
                'unequippable' => $force
            ]);
        }  

        if($wear)
        {
            $types = ["Shirt", "Pants", "T-Shirt"];            
            if(in_array($item->type, $types))
            {                
                // check if the user is already wearing shirts or pants to unequip it
                $testforclothing = OwnedItems::where(['user_id' => $user->id, 'wearing' => true])->get();
                
                foreach($testforclothing as $wornitem)
                {
                    $itemdb = Item::where('id', $wornitem->item_id)->first();
                    if($itemdb && $item->type == $itemdb->type && $item->id != $itemdb->id)
                    {                        
                        $wornitem->wearing = false;
                        $wornitem->save();
                    }
                }
            }
        }

        AdminLog::log($request->user(), sprintf('Rewarded user %s with item "%s". (USER ID: %s) (ITEM ID: %s)', $user->username, $item->name, $user->id, $item->id), true);

        return redirect(route('admin.item'))->with('success', $user->username . '  has been given: ' . $item->name);
    }

    public function moderator(Request $request)
    {
        return view('admin.moderator');
    }

    public function togglemoderator(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.moderator'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        if ($user->admin == 1) {
            return redirect(route('admin.moderator'))->with('error', 'That user is an admin. Name: ' . $request['username']);
        }

        if($user->admin == 0) {
            $user->admin = 2;
        }
        else {
            $user->admin = 0;
        }

        $user->save();

        AdminLog::log($request->user(), sprintf('Toggled Moderator status for user %s. (USER ID: %s)', $user->username, $user->id), true);

        if ($user->admin == 2) {
            return redirect(route('admin.moderator'))->with('success', $user->username . ' is now a Moderator!');
        } else {
            return redirect(route('admin.moderator'))->with('success', $user->username . ' is no longer a Moderator.');
        }
    }

    public function booster(Request $request)
    {
        return view('admin.booster');
    }
    public function donator(Request $request)
    {
        return view('admin.donator');
    }
    public function togglebooster(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.booster'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        $user->booster = !$user->booster;
        $user->save();

        AdminLog::log($request->user(), sprintf('Toggled Booster status for user %s. (USER ID: %s)', $user->username, $user->id));

        if ($user->booster) {
            return redirect(route('admin.booster'))->with('success', $user->username . ' is now a Booster Club member!');
        } else {
            return redirect(route('admin.booster'))->with('success', $user->username . ' is no longer a Booster Club member.');
        }
    }

    public function hoster(Request $request)
    {
        return view('admin.hoster');
    }

    public function togglehoster(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.hoster'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        $user->verified_hoster = !$user->verified_hoster;
        $user->save();

        AdminLog::log($request->user(), sprintf('Toggled Verified Hoster status for user %s. (USER ID: %s)', $user->username, $user->id));

        if ($user->verified_hoster) {
            return redirect(route('admin.hoster'))->with('success', $user->username . ' is now a Verified Hoster!');
        } else {
            return redirect(route('admin.hoster'))->with('success', $user->username . ' is no longer a Verified Hoster.');
        }
    }
    
    public function toggledonator(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.donator'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        $user->donator = !$user->donator;
        $user->save();

        AdminLog::log($request->user(), sprintf('Toggled Donator status for user %s. (USER ID: %s)', $user->username, $user->id));

        if ($user->booster) {
            return redirect(route('admin.donator'))->with('success', $user->username . ' is now a Donator!');
        } else {
            return redirect(route('admin.donator'))->with('success', $user->username . ' is no longer a Donator.');
        }
    }
    public function clientsettings(Request $request)
    {
        $version = ($request->version != null ? $request->version : '2014');
        $clientsettings = Storage::disk('local')->get(sprintf('fastflags/%s.json', $version));
        $fflags = collect(json_decode($clientsettings, true));
        return view('admin.clientsettings')->with('fflags', $fflags);
    }

    public function togglefflag(Request $request)
    {
        $request->validate([
            'fflag' => ['required', 'string'],
            'version' => Rule::in(['2012', '2014', '2016'])
        ]);

        $clientsettings = Storage::disk('local')->get(sprintf('fastflags/%s.json', $request->version));
        $fflags = collect(json_decode($clientsettings, JSON_PRETTY_PRINT));
       
        $fflag = $fflags->get($request->fflag);
        if($fflag) {
            $value = filter_var($fflag, FILTER_VALIDATE_BOOLEAN);
            $value = Str::ucfirst(var_export(!$value, true));              
            $fflags->forget($request->fflag);
            $fflags->put($request->fflag, $value);
        } else {
            $fflags->put($request->fflag, "True");
        }

        $fflags->toJson(JSON_PRETTY_PRINT);
        $clientsettings = Storage::disk('local')->put(sprintf('fastflags/%s.json', $request->version), $fflags);

        AdminLog::log($request->user(), sprintf('Changed FFlag (%s) to (%s) for version %s.', $request->fflag, $fflag, $request->version), true);

        return redirect(route('admin.clientsettings'))->with('success', sprintf('%s is now %s', $request->fflag, Str::lower($fflags->get($request->fflag))));
    }

    public function sitealert(Request $request)
    {
        return view('admin.sitealert');
    }

    public function createsitealert(Request $request)
    {
        $colors = collect([
            'Red' => 'alert-danger',
            'Yellow' => 'alert-warning',
            'Green' => 'alert-success'
        ]);
        $request->validate([
            'alert' => ['max:100'],
            'color' => Rule::in(['Red', 'Yellow', 'Green'])
        ]);

        if(empty($request->alert))
        {
            Cache::pull('alert');
            AdminLog::log($request->user(), 'Removed the site alert.');
        } else {
            AdminLog::log($request->user(), sprintf('Created site alert "%s"', $request->alert), true);
        }

        Cache::put('alert', ['alert' => $request->alert, 'color' => $colors[$request->color]]);

        return redirect(route('admin.sitealert'))->with('success', 'Successfully created alert.');
    }

    function hatchCheck()
    {
        // we check if it's time to hatch items
        $hatchItems = Item::whereNotNull('hatchdate')->get();
        foreach($hatchItems as $potentialHatch) {
            $hatchDate = Carbon::parse($potentialHatch->hatchdate);
            if($hatchDate->isPast()) {
                // it's time for this item to hatch!
                // set our new type
                if($potentialHatch->hatchtype != null) {
                    $potentialHatch->name = $potentialHatch->hatchname;
                    $potentialHatch->description = $potentialHatch->hatchdesc;          
                    $potentialHatch->type = $potentialHatch->hatchtype;
                    // check if it's a xml
                    if ($potentialHatch->isXmlAsset())
                    {
                        // replace our old xml with the new one, assuming it exists
                        $hatchXml = Storage::disk('public')->get('hatch_items/' . $potentialHatch->id);
                        Log::info($hatchXml);
                        if($hatchXml) {
                            // replace and rerender
                            Storage::disk('public')->put('items/' . $potentialHatch->id, $hatchXml); 
                            $this->dispatch(new RenderJob($potentialHatch->hatchtype, $potentialHatch->id));

                            // reset
                            $potentialHatch->hatchtype = null;
                            $potentialHatch->hatchdate = null;
                            $potentialHatch->hatchname = null;
                            $potentialHatch->hatchdesc = null;
                            $potentialHatch->save();
                            
                            // delete hatch xml
                            Storage::disk('public')->delete('hatch_items/' . $potentialHatch->id);
                        }
                    }                
                }
            }
        }
    }

    public function scribbler(Request $request)
    {
        return view('admin.scribbler');
    }

    public function toggle_scribbler(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return redirect(route('admin.scribbler'))->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        $user->scribbler = !$user->scribbler;
        $user->save();

        AdminLog::log($request->user(), sprintf('Toggled scribbler status for user %s. (USER ID: %s)', $user->username, $user->id));

        if ($user->scribbler) {
            return redirect(route('admin.scribbler'))->with('success', $user->username . ' is now a Scribbler!');
        } else {
            return redirect(route('admin.scribbler'))->with('success', $user->username . ' is no longer a Scribbler.');
        }
    }

    public function alts(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $associatedUsers = User::where('register_ip', $user->register_ip)
            ->orWhere('last_ip', $user->last_ip)
            ->orderBy('joined', 'ASC')
            ->paginate(10);

        return view('admin.alts')->with(['user' => $user, 'associatedUsers' => $associatedUsers]);
    }

    public function gamejoins(Request $request)
    {
        $gamejoins = GameToken::query();

        return view('admin.gamejoins')->with('gamejoins', $gamejoins->orderBy('created_at', 'DESC')->paginate(10)->appends($request->all()));
    }

    public function renderasset(request $request)
    {
        if ($request->isMethod('post'))
        {
            $request->validate([
                'assetid' => ['required'],
                'type' => ['required']
            ]);

            $asset = null;
            $type = null;

            switch($request->type)
            {
                case "Item":
                    $asset = Item::findOrFail((int)$request->assetid);
                    $type = "xml";
                    AdminLog::log($request->user(), sprintf('Re-rendered XML item ID %s.', $request->assetid), true);
                    break;
                case "Place":
                    $asset = Server::where('uuid', $request->assetid)->first();
                    $type = "serverplace";
                    AdminLog::log($request->user(), sprintf('Re-rendered server ID %s.', $request->assetid), true);
                    break;
                case "User":                    
                    $asset = User::findOrFail((int)$request->assetid);
                    $type = "user";
                    AdminLog::log($request->user(), sprintf('Re-rendered user ID %s.', $request->assetid), true);
                    break;
                default:
                    return redirect('/admin/renderasset')->with('error', 'Invalid asset type.');
            }
            
            if($asset)
            {                
                $this->dispatch(new RenderJob($type, $asset->id));
                return redirect('/admin/renderasset')->with('success', sprintf('A Render Job has been dispatched for Asset ID %s.', (string)$request->assetid));
            }
            else
            {
                return redirect('/admin/renderasset')->with('error', 'Invalid asset.');
            }            
        }
        else 
        {
            return view('admin.renderasset');
        }
    }

    public function forceunlinkdiscord(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'username' => ['required', 'string']
            ]);

            $user = User::where('username', $request['username'])->first();

            if (!$user) {
                return redirect(route('admin.forceunlinkdiscord'))->with('error', 'That user does not exist. Name: ' . $request['username']);
            }

            if ($user->isAdmin()) {
                return redirect(route('admin.forceunlinkdiscord'))->with('error', 'You cannot unlink an admin\'s Discord account');
            }

            $user->discord_id = null;
            $user->save();

            AdminLog::log($request->user(), sprintf('Forcefully unlinked Discord for user %s. (USER ID: %s)', $user->username, $user->id), true);

            return redirect(route('admin.forceunlinkdiscord'))->with('success', 'Successfully unlinked Discord account.');
        } else {
            return view('admin.forceunlinkdiscord');
        }
    }

    // i want whoever it is that is screwing with economics on the website to be scared off
    public function log(Request $request)
    {
        $actions = AdminLog::query();

        return view('admin.log')->with('actions', $actions->orderBy('created_at', 'DESC')->paginate(10));
    }
}
