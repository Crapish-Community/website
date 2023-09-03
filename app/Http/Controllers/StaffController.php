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

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
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
        return view('staff.banlist')->with(['bans' => $bans->orderBy('updated_at', 'DESC')->paginate(10)->appends($request->all())]);
    }

    public function ban(Request $request) {        
        return view('staff.ban');
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
            return redirect('/staff/ban')->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        if ($checkforban) {
            return redirect('/staff/ban')->with('error', 'That user is already banned. Reason: ' . $user->ban_reason);
        }

        if ($user->isStaff()) {
            return redirect('/staff/ban')->with('error', 'If you do not like another staff member, you should probably bring it up.');
        }

        if ($request->user()->id == $user->id) {
            return redirect('/staff/ban')->with('error', 'You\'re trying to ban yourself?');
        }

        $ban = new Ban;
        $ban->user_id = $user->id;
        $ban->banned = true;
        $ban->ban_reason = $request['banreason'];
        $ban->banned_until = Carbon::parse($request['unbandate']);
        $ban->save();

        AdminLog::log($request->user(), sprintf('Banned user %s. (USER ID: %s)', $ban->user->username, $ban->user->id));

        return redirect('/staff/ban')->with('success', $user->username . '  has been banned until ' . $ban->banned_until);
    }

    public function unban(Request $request) {
        return view('staff.unban');
    }

    public function unbanuser(Request $request) {
        $request->validate([
            'username' => ['required', 'string']
        ]);

        $user = User::where('username', $request['username'])->first();
        $ban = Ban::where(['user_id' => $user->id, 'banned' => true])->first();

        if (!$user) {
            return redirect('/staff/unban')->with('error', 'That user does not exist. Name: ' . $request['username']);
        }

        if (!$ban) {
            return redirect('/staff/unban')->with('error', 'That user is not banned.');
        }

        if ($request->user()->id == $user->id) {
            return redirect('/staff/unban')->with('error', 'but... but... but... you are not banned......');
        }

        $ban->banned = false;
        $ban->pardon_user_id = $request->user()->id;
        $ban->save();

        AdminLog::log($request->user(), sprintf('Unbanned user %s. (USER ID: %s)', $ban->user->username, $ban->user->id));

        return redirect('/staff/unban')->with('success', $user->username . '  has been unbanned.');
    }

    function assets(Request $request) {
        $unapproved = Item::where('approved', 0);
        if (request('search')) {
            $unapproved->where('name', 'LIKE', '%' . request('search') . '%');
        }
        return view('staff.assets', ['items' => $unapproved->paginate(18)]);
    }

    function approve(Request $request, $id) {
        $item = Item::find($id);        
        if($item) {
			$approved = ($request->submit === 'Approve');
            $item->update([
                'approved' => ($approved ? 1 : 2),
            ]);

            AdminLog::log($request->user(), sprintf('%s asset %s. (ITEM ID: %s)', ($approved ? 'Approved' : 'Denied'), $item->name, $item->id));
        } else {
            abort(404);
        }

        return back();
    }

    public function xmlitem(Request $request)
    {
        return view('staff.newxmlitem');
    }

    public function createxmlitem(Request $request)
    {
        $shouldHatch = $request->has('shouldhatch');
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:999999'],
			'stock_circulating' => ['integer', 'min:5', 'max:1000'],
            'xml' => ['required', 'string'],
            'type' => ['required', 'string'],
            'hatchname' => ['string', 'max:100', 'nullable'],
            'hatchdesc' => ['string', 'max:2000', 'nullable'],
            'hatchxml' => ['string', 'nullable'],
            'hatchdate' => ['date', 'nullable'],
            'hatchtype' => ['string', 'nullable']
        ]);
		
		// Kinda iffy but radio button :/
		switch($request->marketplace_type)
		{
			case "boosters":
				$limitedU     = false;
				$boostersOnly = true;
				break;
			case "limitedu":
				$limitedU     = true;
				$boostersOnly = false;
				break;
			default:
				$limitedU     = false;
				$boostersOnly = false;
				break;
		}

        $item = Item::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'creator' => $request->user()->id,
            'thumbnail_url' => $request['thumbnailurl'],
            'price' => $request['price'],
			'stock_circulating' => $request->stock_circulating,
            'type' => $request['type'],
			'is_limitedu' => $limitedU,
			'is_boosters_only' => $boostersOnly,
            'hatchtype' => ($shouldHatch ? $request['hatchtype'] : null),
            'hatchdate' => ($shouldHatch ? Carbon::parse($request['hatchdate']) : null),
            'hatchname' => ($shouldHatch ? $request['hatchname'] : null),
            'hatchdesc' => ($shouldHatch ? $request['hatchdesc'] : null),
            'sales' => 0,
            'onsale' => $request->has('onsale'),
            'approved' => (config('app.assets_approved_by_default') ? 1 : ($request->user()->isStaff() ? 1 : 0))
        ]);

        if($shouldHatch) {
            Storage::disk('public')->put('hatch_items/' . $item->id, $request['hatchxml']);
        }
        Storage::disk('public')->put('items/' . $item->id, $request['xml']);

        if ($item->type == "Hat" || $item->type == "Model" || $item->type == "Gear") {
            $this->dispatch(new RenderJob('xml', $item->id));
        }

        if($item->type == "Head") {
            $this->dispatch(new RenderJob('head', $item->id));
        }

        if ($item->type == "Package") {
            $this->dispatch(new RenderJob('clothing', $item->id));
        }

        OwnedItems::create([
            'user_id' => $request->user()->id,
            'item_id' => $item->id,
            'wearing' => false
        ]);

        // BUGGY but lets check
        if (config('app.discord_webhook_enabled') && $request->has('announce')) {
            // sanitize title/desc for basic all pings
            $name = str_replace('@here', '`@here`', str_replace('@everyone', '`@everyone`', $request['name']));
            $description = str_replace('@here', '`@here`', str_replace('@everyone', '`@everyone`', $request['description']));
            $response = Http::post(sprintf('https://discord.com/api/webhooks/%s/%s', config('app.discord_webhook_id'), config('app.discord_webhook_token')), [
                'content' => '**New item:**',
                'embeds' => [
                    [
                        'title' => $item->name,
                        'description' => $item->description,
                        'url' => url(route('item.view', $item->id)),
                        'color' => 255,
                        'timestamp' => date("Y-m-d\TH:i:s.u"),
                        'thumbnail' => [ url(route('client.itemthumbnail', ['itemId' => $item->id], false) . sprintf('?tick=%d', time())) ],
                        'footer' => [
                            'text' => sprintf("%s %s", config('app.name'), $item->type)
                        ],
                        'author' => [
                            'name' => $request->user()->username,
                            'url' => url(route('users.profile', $request->user()->id)),
                            'icon_url' => url(route('client.userthumbnail', ['userId' => $request->user()->id]), false) . sprintf('?tick=%d', time())
                        ],
                        'fields' => [
                            [
                                'name' => 'Price',
                                'value' => sprintf('<:dahllor:1145276776117960734> %s %s', $item->price, config('app.currency_name_multiple')),
                                'inline' => false
                            ]
                        ]
                    ]
                ]
            ]);
        }

        AdminLog::log($request->user(), sprintf('Created XML item %s. (ITEM ID: %s)', $item->name, $item->id));

        return redirect(route('item.view', $item->id))->with('message', ($shouldHatch ? 'XML asset successfully created and scheduled to hatch.' : 'XML asset successfully created.'));
    }

    public function robloxitemdata(Request $request, $id)
    {
        $response = Http::asForm()->get('https://api.roblox.com/marketplace/productinfo', [
            "assetId" => $id
        ]);

        return $response;
    }

    public function robloxxmldata(Request $request, $id, $version)
    {
        $response = Http::get('https://assetdelivery.roblox.com/v1/asset?id=' . intval($id) . "&version=" . intval($version));

        return $response;
    }

    public function invitekeys(Request $request) {
        $invitekeys = InviteKey::query();

        return view('staff.invitekeys')->with('invitekeys', $invitekeys->orderBy('created_at', 'DESC')->paginate(10)->appends($request->all()));
    }

    public function createinvitekey(Request $request) {
        return view('staff.createinvitekey');
    }

    public function generateinvitekey(Request $request) {
        $request->validate([
            'uses' => ['required', 'min:1', 'max:50', 'integer']
        ]);

        $inviteKey = InviteKey::create([
            'creator' => $request->user()->id,
            'token' => sprintf('%sKey-%s', config('app.name'), Str::random(25)),
            'uses' => $request['uses']
        ]);

        AdminLog::log($request->user(), sprintf('Created invite key %s with %s uses.', $inviteKey->token, $inviteKey->uses));

        return redirect('/staff/createinvitekey')->with('success', 'Created invite key. Key: "' . $inviteKey->token  . '"');
    }

    public function disableinvitekey(Request $request, $id) {
        $invitekey = InviteKey::find($id);

        if (!$invitekey) {
            return abort(404);
        }

        $invitekey->uses = 0;
        $invitekey->save();

        AdminLog::log($request->user(), sprintf('Disabled invite key %s.', $invitekey->token));

        return redirect('/staff/invitekeys')->with('message', 'Invite key ID: ' . $invitekey->id . ', Token: ' . $invitekey->token . ' disabled.');
    }
}