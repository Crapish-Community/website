game:Load("http://%SITE%/game/serverplace/%PLACEID%?secret=%SERVERSECRET%")
--[[
    THIS IS A TEST, DO NOT PUSH THIS TO THE SITE.
    THIS IS A TEST, DO NOT PUSH THIS TO THE SITE.
    THIS IS A TEST, DO NOT PUSH THIS TO THE SITE.
    THIS IS A TEST, DO NOT PUSH THIS TO THE SITE.
]]
local a={"Texture","TextureId","SoundId","MeshId","SkyboxUp","SkyboxLf","SkyboxBk","SkyboxRt","SkyboxFt","SkyboxDn","PantsTemplate","ShirtTemplate","Graphic","Image","LinkedSource","AnimationId"}local b={"http://www%.roblox%.com/asset/%?id=","http://www%.roblox%.com/asset%?id=","http://%roblox%.com/asset/%?id=","http://%roblox%.com/asset%?id="}function GetDescendants(c)local d={}function FindChildren(e)for f,g in pairs(e:GetChildren())do table.insert(d,g)FindChildren(g)end end;FindChildren(c)return d end;local h=0;for i,g in pairs(GetDescendants(game))do for f,j in pairs(a)do pcall(function()if g[j]and not g:FindFirstChild(j)then assetText=string.lower(g[j])for f,k in pairs(b)do g[j],matches=string.gsub(assetText,k,"http://crapish.fun/asset/%?id=")if matches>0 then h=h+1;print("Replaced "..j.." asset link for "..g.Name)break end end end end)end end;print("DONE! Replaced "..h.." properties")
local ServerPort = %SERVERPORT%

game:SetPlaceId(%PLACEID%)
game:SetCreatorId(%CREATOR%, Enum.CreatorType.User)

game:GetService("HttpService").HttpEnabled = true

-- starterscript
game:GetService("ScriptContext"):AddCoreScript(1320, game.CoreGui, "CoreScripts/StarterScript")

--InsertService, ScriptInformationProvider
game:GetService("ScriptInformationProvider"):SetAssetUrl("http://%SITE%/asset/")
game:GetService("InsertService"):SetBaseSetsUrl("http://%SITE%/Game/Tools/InsertAsset.ashx?nsets=10&type=base")
game:GetService("InsertService"):SetUserSetsUrl("http://%SITE%/Game/Tools/InsertAsset.ashx?nsets=20&type=user&userid=%d&t=2")
game:GetService("InsertService"):SetCollectionUrl("http://%SITE%/Game/Tools/InsertAsset.ashx?sid=%d")
game:GetService("InsertService"):SetAssetUrl("http://%SITE%/asset/?id=%d")
game:GetService("InsertService"):SetAssetVersionUrl("http://%SITE%/Asset/?assetversionid=%d")
game:GetService("InsertService"):SetTrustLevel(0)

-- disable sound for server window
settings()["Game Options"].SoundEnabled = false

local deathSounds = {
	"http://%SITE%/audio/cans.mp3"
}

local function Destroy(instance)
    game:GetService("Debris"):AddItem(instance, 0)
end

local NetworkServer = game:GetService("NetworkServer")
local success, err = pcall(function()
	NetworkServer:Start(ServerPort)
end)
if not success then
	local message = Instance.new("Message", workspace)
	message.Text = err
end

local RunService = game:GetService("RunService")
RunService:Run()

local Players = game:GetService("Players")
Players.MaxPlayers = %MAXPLAYERS%

Players.PlayerAdded:connect(function(Player)
    if Players.MaxPlayers < #Players:GetChildren() then
        print("Too many players, kicking " .. Player.Name)
        player:Kick("This server is full.")
    end
	
	Player.Chatted:connect(function(Message)
        -- 1 : needs semicolon
        -- 2 : doesn't need semicolon
        local commands = {
            ["ec"] = 1,
            ["energycell"] = 1,
            ["reset"] = 1,
            ["kys"] = 1,
            ["xlxi"] = 1,
            ["gibson"] = 1,
            ["wagness"] = 1,
			["astros"] = 1,
            ["kyle"] = 1,
            ["brent"] = 1,
            ["egg"] = 2,
            ["pog"] = 2,
            ["poggers"] = 2
        }

        if commands[Message:sub(2):lower()] == 1 or commands[Message:lower()] == 2 then
            if Player.Character then
				local Head = Player.Character:FindFirstChild("Head")
				if Head then
					local Sound = Instance.new("Sound", Head)
					Sound.SoundId = deathSounds[math.random(1,#deathSounds)]
					Sound:Play()
				end
				
                Player.Character:BreakJoints()
            end
        end
    end)
end)

NetworkServer.ChildAdded:connect(function(child)
    child.Name = "Connection"
	while not child:GetPlayer() do
		wait()
	end
	
	-- some security stuff?
	-- we disable processing packets for connections without a player
	-- and we disable processing packets while the player is being authenticated and reenable upon success
	
	if(child:GetPlayer()) then
		local Player = child:GetPlayer()
		
		-- process packets for a bit to receive our token instance
		child:EnableProcessPackets()
		
		-- we can't really auth on FE because the client can't create the token instance
		-- in the future, we should set the player's name as the token on the client, and change it on the server (to add support for FE)
		if (workspace.FilteringEnabled) then
            local success, error = pcall(function()
                local PlayerToken = Player.Name
                if PlayerToken then
                    -- we have the token instance, let's disable packets until it's properly verified
                    child:DisableProcessPackets()
    
                    local success, err = pcall(function()
                    local verify = game:HttpGet('http://%SITE%/server/verifyuserfe/' .. PlayerToken, true)
                    if verify ~= "invalid" then
                        Player.Name = verify
                        child:EnableProcessPackets()
                        Player.Changed:connect(function(property)
							if property == "Name" then
								Player:Kick()
                            end
						end)
                    else
                        print("Invalid new player, kicking: " .. Player.Name .. " - " .. VerifyRequest)
						pcall(function() child:CloseConnection() end)
						Player:Kick()
                    end
                end)
                if not success then
                    Player:Kick()
                end
            end)
        end
		if(not workspace.FilteringEnabled) then
			local success, error = pcall(function()
				local PlayerToken = Player:FindFirstChild("token")
				if PlayerToken then
					-- we have the token instance, let's disable packets until it's properly verified
					child:DisableProcessPackets()
					
					local success, err = pcall(function()
						local VerifyRequest = game:HttpGet('http://%SITE%/server/verifyuser/' .. PlayerToken.Value .. '?username=' .. Player.Name, true)
						if VerifyRequest == "valid" then
							-- now we process packets again
							child:EnableProcessPackets()
							print("New player: " .. Player.Name)
							Player.Changed:connect(function(property)
								if property == "Name" then
									Player:Kick()
								end
							end)
						else
							print("Invalid new player, kicking: " .. Player.Name .. " - " .. VerifyRequest)
							pcall(function() child:CloseConnection() end)
							Player:Kick()
						end
					end)

					if not success then
						Player:Kick()
					end
				else					
					print("Player joined without token, kicking: " .. Player.Name)
					child:DisableProcessPackets()
					pcall(function() child:CloseConnection() end)
					Player:Kick()
				end
			end)
			if not success then
				print("Error occurred while validating " .. Player.Name .. ": " .. error)
				child:DisableProcessPackets()
				pcall(function() child:CloseConnection() end)
				Player:Kick()
			end
		end		
	else
		-- only player connections allowed
		child:DisableProcessPackets()
	end
end)

local SitePingerCoro = coroutine.create(function()
	local success, error = pcall(function()
		while true do
			-- UGLY HACK BECAUSE FOR SOME REASON HTTPPOST AND POSTASYNC CRASHES??
			-- WHAT THE FUCK?
			local playerIds = ""
			local players = game.Players:GetChildren()
			for i, player in pairs(players) do
				if player.ClassName == "Player" then
					if i ~= #players then
						playerIds = playerIds .. player.userId .. ","
					else
						playerIds = playerIds .. player.userId
					end
				end
			end
			
			game:HttpGet('http://%SITE%/server/ping/%SERVERSECRET%?players=' .. playerIds)
	
			wait(60)
		end
	end)

	if not success then
		local message = Instance.new("Message", workspace)
		message.Text = err
	end
end)
coroutine.resume(SitePingerCoro)
--game:Load("rbxassetid://2253")
--local admin = game.TadahRServer
--admin.Parent = workspace
--admin.Disabled = false
