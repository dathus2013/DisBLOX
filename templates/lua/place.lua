x = {x}
y = {y}
assetid = {assetid}

game:GetService("ScriptInformationProvider"):SetAssetUrl("http://www.watrbx.xyz/asset/")
game:GetService("InsertService"):SetAssetUrl("http://www.watrbx.xyz/asset/?id=%d")
game:GetService("InsertService"):SetAssetVersionUrl("http://www.watrbx.xyz/Asset/?assetversionid=%d")
game:GetService("ContentProvider"):SetBaseUrl("http://www.watrbx.xyz")

-- do this twice for security
game:GetService("ScriptContext").ScriptsDisabled = true
game:GetService("StarterGui").ShowDevelopmentGui = false
game:Load("http://www.watrbx.xyz/asset/?id=" .. assetid)

game:GetService("ScriptContext").ScriptsDisabled = true
game:GetService("StarterGui").ShowDevelopmentGui = false
local result = game:GetService("ThumbnailGenerator"):Click("PNG", x, y, false)

return result