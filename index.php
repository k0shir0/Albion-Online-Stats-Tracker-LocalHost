<?php
// made by koshiro :)

set_time_limit(12);
date_default_timezone_set('UTC');

$cfgFile = __DIR__ . '/cfg.json';
$serverRoot = "https://gameinfo.albiononline.com/api/gameinfo";
$cacheDir = __DIR__ . '/cache';
$cacheTTL = 180;
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);

function h($s){return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function fmt($n){return number_format((int)($n?:0));}

function curl_get($url){
    $ch=curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_TIMEOUT=>7,
        CURLOPT_CONNECTTIMEOUT=>4,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_HTTPHEADER=>['Accept: application/json','User-Agent: Local-Albion-Dashboard']
    ]);
    $res=curl_exec($ch);
    $err=curl_errno($ch)?curl_error($ch):null;
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['ok'=>$res!==false&&$code>=200&&$code<300,'body'=>$res,'err'=>$err];
}
function safe_json($s){$d=@json_decode($s,true);return is_array($d)?$d:null;}
function cached_fetch($url,$cache,$ttl){
    if(file_exists($cache)&&(time()-filemtime($cache)<$ttl)){
        $j=@file_get_contents($cache);$d=safe_json($j);
        if($d!==null)return['data'=>$d,'source'=>'cache','ts'=>filemtime($cache)];
    }
    $r=curl_get($url);
    if($r['ok']){$d=safe_json($r['body']);if($d!==null){@file_put_contents($cache,$r['body']);return['data'=>$d,'source'=>'api','ts'=>time()];}}
    if(file_exists($cache)){$j=@file_get_contents($cache);$d=safe_json($j);if($d!==null)return['data'=>$d,'source'=>'stale','ts'=>filemtime($cache)];}
    return['data'=>null,'source'=>'error'];
}

if(!file_exists($cfgFile)){
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $id=trim($_POST['playerId']??'');
        if($id!==''){
            file_put_contents($cfgFile,json_encode(['playerId'=>$id,'pfp'=>'']));
            header("Location: ./");exit;
        }
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Setup</title>
    <style>body{background:#0a101a;color:#fff;font-family:Segoe UI;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh}
    input{padding:8px;border-radius:6px;border:none;width:280px;margin-top:8px;}
    button{margin-top:14px;padding:8px 18px;border:none;border-radius:8px;background:#ffa500;color:#000;font-weight:700;cursor:pointer;}
    </style></head><body>
    <h2>First Time Setup</h2>
    <form method="POST">
    <label>Enter your Albion Player ID:</label><br>
    <input name="playerId" required><br>
    <p>Optionally, put a file named <b>pfp.png</b> or <b>pfp.jpg</b> in this folder to use as your profile image.</p>
    <button type="submit">Save</button>
    </form></body></html>';exit;
}

$cfg=safe_json(file_get_contents($cfgFile));
$playerId=$cfg['playerId']??'';
$pfpFile=null;foreach(['pfp.png','pfp.jpg','pfp.jpeg'] as $x){if(file_exists(__DIR__."/$x")){$pfpFile=$x;break;}}

$player=cached_fetch("$serverRoot/players/$playerId",$cacheDir."/p_$playerId.json",$cacheTTL);
$kills=cached_fetch("$serverRoot/players/$playerId/topkills?range=week&limit=50",$cacheDir."/k_$playerId.json",$cacheTTL);
$p=$player['data']??[];
$k=is_array($kills['data'])?$kills['data']:[];
usort($k,function($a,$b){return(strtotime($b['TimeStamp']??'')?:0)<=>(strtotime($a['TimeStamp']??'')?:0);});

$l=$p['LifetimeStatistics']??[];
$pve=$l['PvE']['Total']??0;$gath=$l['Gathering']['All']['Total']??0;$craft=$l['Crafting']['Total']??0;$total=$pve+$gath+$craft;
$last=max($player['ts']??0,$kills['ts']??0);$updated=$last?gmdate('Y-m-d H:i:s \U\T\C',$last):'unknown';
?><!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo h($p['Name']??'Albion Player');?> — Local Dashboard</title>
<style>
:root{--bg:#07101a;--card:#0e1620;--muted:#a0acb9;--accent:#ffa500;
--green:#4caf50;--red:#f44336;--blue:#2196f3;--purple:#9c27b0;--teal:#009688;}
body{margin:0;background:var(--bg);color:#e8f0f6;font-family:Inter,Segoe UI,system-ui,Arial;display:flex;justify-content:center;padding:28px;}
.wrap{width:100%;max-width:1000px}
.head{display:flex;gap:18px;align-items:center;background:linear-gradient(180deg,rgba(255,255,255,0.03),transparent);padding:20px;border-radius:12px;border:1px solid rgba(255,255,255,0.04);}
.avatar{width:90px;height:90px;border-radius:12px;overflow:hidden;background:#111;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.6rem;color:#fff;}
.avatar img{width:100%;height:100%;object-fit:cover;border-radius:12px;}
.info h1{margin:0;color:var(--accent);font-size:1.6rem;}
.info .sub{color:var(--muted);margin-top:5px;}
.grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-top:20px;}
.stat{background:var(--card);padding:16px;border-radius:12px;text-align:center;transition:all .3s ease;box-shadow:0 0 15px rgba(0,0,0,0.5);}
.stat small{display:block;color:var(--muted);font-size:0.78rem;}
.stat span{display:block;margin-top:6px;font-size:1.5rem;font-weight:800;}
.stat:hover{transform:scale(1.04);}
.kf{box-shadow:0 0 14px var(--green);border:2px solid var(--green);}
.df{box-shadow:0 0 14px var(--red);border:2px solid var(--red);}
.tf{box-shadow:0 0 14px var(--blue);border:2px solid var(--blue);}
.cf{box-shadow:0 0 14px var(--purple);border:2px solid var(--purple);}
.gf{box-shadow:0 0 14px var(--teal);border:2px solid var(--teal);}
.kills{margin-top:26px;}
.klist{display:flex;flex-direction:column;gap:10px;margin-top:12px;}
.kill{background:var(--card);padding:12px;border-radius:10px;display:flex;justify-content:space-between;align-items:center;border-left:6px solid rgba(255,255,255,0.05);}
.kleft{display:flex;gap:12px;align-items:center;}
.mini{width:46px;height:46px;border-radius:8px;background:#0e1620;display:flex;align-items:center;justify-content:center;}
.who{font-weight:700;}
.when{color:var(--muted);font-size:0.88rem;}
.fame{font-weight:900;color:var(--accent);min-width:120px;text-align:right;}
.debug{margin-top:16px;color:var(--muted);font-size:0.86rem;}
@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:560px){.grid{grid-template-columns:1fr;}}
</style></head><body><div class="wrap">
<div class="head">
<div class="avatar"><?php if($pfpFile):?><img src="<?php echo h($pfpFile);?>"><?php else:?><?php echo h(substr($p['Name']??'NA',0,2));?><?php endif;?></div>
<div class="info">
<h1><?php echo h($p['Name']??'Unknown Player');?></h1>
<div class="sub">[<?php echo h($p['AllianceName']??'—');?>] <?php echo h($p['GuildName']??'No Guild');?></div>
<div class="grid" style="margin-top:12px;">
<div class="stat kf"><small>Kill Fame</small><span><?php echo fmt($p['KillFame']??0);?></span></div>
<div class="stat df"><small>Death Fame</small><span><?php echo fmt($p['DeathFame']??0);?></span></div>
<div class="stat tf"><small>Total Fame (calc)</small><span><?php echo fmt($total);?></span></div>
<div class="stat cf"><small>Crafting Fame</small><span><?php echo fmt($craft);?></span></div>
<div class="stat gf"><small>Gathering Fame</small><span><?php echo fmt($gath);?></span></div>
</div></div></div>
<div class="kills"><h3>Recent Kills</h3><div class="klist">
<?php if(empty($k)):?><div class="kill">No kills found or API returned none.</div><?php else:
foreach($k as $kill){$v=$kill['Victim']??[];$n=$v['Name']??($kill['VictimName']??'Unknown');
$ip=$v['AverageItemPower']??'0';$time=$kill['TimeStamp']??'';$when=$time?date('M j, Y - H:i',strtotime($time)).' UTC':'unknown';
$fame=$kill['KillFame']??$kill['TotalVictimKillFame']??$kill['Fame']??0;
echo '<div class="kill"><div class="kleft"><div class="mini">'.h(substr($n,0,2)).'</div><div><div class="who">'.h($n).' <span style="color:var(--muted);font-weight:700;">(IP '.h($ip).')</span></div><div class="when">'.h($when).'</div></div></div><div class="fame">+'.fmt($fame).' Fame</div></div>'; }
endif;?>
</div></div>
<div class="debug">Last updated: <?php echo h($updated);?> · Region: Americas</div>
</div></body></html>
