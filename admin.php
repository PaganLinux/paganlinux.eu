<?php
// PaganLinux Admin Panel – standalone, self-contained
require __DIR__.'/includes/core.php';

// ═══ LOGIN ═══
if (isset($_GET['login'])) {
    $err = '';
    if ($_POST) {
        $s = db()->prepare("SELECT * FROM users WHERE login=?");
        $s->execute([$_POST['user'] ?? '']);
        $u = $s->fetch();
        if ($u && password_verify($_POST['pass'] ?? '', $u['pw'])) {
            $_SESSION['user'] = $u['login'];
            header('Location: ?'); exit;
        }
        $err = 'Błędny login lub hasło';
    }
    ?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login – PaganLinux</title><style>
    *{margin:0;padding:0;box-sizing:border-box}body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f4f5f7;font-family:system-ui,sans-serif}
    form{background:#fff;padding:40px;border-radius:12px;border:1px solid #e2e4e9;width:360px;box-shadow:0 2px 16px rgba(0,0,0,0.04)}
    h1{text-align:center;font-size:1.2rem;margin-bottom:20px;color:#1a1d26}
    input{width:100%;padding:11px;margin:6px 0;border:1px solid #e2e4e9;border-radius:8px;font-size:0.95rem}
    button{width:100%;padding:11px;margin-top:10px;background:#5b5fef;color:#fff;border:none;border-radius:8px;font-size:0.95rem;cursor:pointer}
    .err{color:#dc2626;text-align:center;font-size:0.9rem;margin-bottom:8px}
    </style></head><body><form method="post">
    <h1>🐺 PaganLinux CMS</h1>
    <?php if($err):?><p class="err"><?=$err?></p><?php endif?>
    <input name="user" placeholder="Login" required>
    <input name="pass" type="password" placeholder="Hasło" required>
    <button>Zaloguj się</button>
    </form></body></html><?php
    exit;
}

// ═══ AUTH ═══
auth();
if (isset($_GET['logout'])) { session_destroy(); header('Location: ?login'); exit; }

// ═══ ACTIONS ═══
$act = $_GET['act'] ?? '';

if ($_POST && $act === 'page_save') {
    $slug = $_POST['slug'] ?: preg_replace('/[^a-z0-9-]+/','-', strtolower(trim($_POST['title'])));
    $id = $_POST['id'] ?? 0;
    if ($id) { db()->prepare("UPDATE pages SET slug=?,title=?,body=?,lang=?,pub=?,updated=datetime('now') WHERE id=?")->execute([$slug,$_POST['title'],$_POST['body']??'',$_POST['lang']??'pl',(int)($_POST['pub']??1),(int)$id]); }
    else { db()->prepare("INSERT INTO pages(slug,title,body,lang,pub) VALUES(?,?,?,?,?)")->execute([$slug,$_POST['title'],$_POST['body']??'',$_POST['lang']??'pl',1]); }
    header('Location: ?act=pages'); exit;
}
if ($act === 'page_del' && ($id = $_GET['id'] ?? 0)) { db()->prepare("DELETE FROM pages WHERE id=?")->execute([(int)$id]); header('Location: ?act=pages'); exit; }

if ($_POST && $act === 'post_save') {
    $slug = $_POST['slug'] ?: preg_replace('/[^a-z0-9-]+/','-', strtolower(trim($_POST['title'])));
    $id = $_POST['id'] ?? 0;
    if ($id) { db()->prepare("UPDATE posts SET slug=?,title=?,body=?,excerpt=?,lang=?,pub=?,updated=datetime('now') WHERE id=?")->execute([$slug,$_POST['title'],$_POST['body']??'',$_POST['excerpt']??'',$_POST['lang']??'pl',(int)($_POST['pub']??1),(int)$id]); }
    else { db()->prepare("INSERT INTO posts(slug,title,body,excerpt,lang,pub) VALUES(?,?,?,?,?,?)")->execute([$slug,$_POST['title'],$_POST['body']??'',$_POST['excerpt']??'',$_POST['lang']??'pl',1]); }
    header('Location: ?act=posts'); exit;
}
if ($act === 'post_del' && ($id = $_GET['id'] ?? 0)) { db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$id]); header('Location: ?act=posts'); exit; }

if ($_POST && $act === 'settings') {
    foreach ($_POST as $k => $v) {
        if (str_starts_with($k, 's_')) {
            $p = explode('_', substr($k,2)); $l = array_pop($p); $key = implode('_',$p);
            if (strlen($l) !== 2) { $key .= '_'.$l; $l = 'pl'; }
            db()->prepare("INSERT OR REPLACE INTO settings(key,lang,val) VALUES(?,?,?)")->execute([$key,$l,$v]);
        }
    }
    header('Location: ?act=settings'); exit;
}

if ($act === 'git') {
    header('Content-Type: application/json');
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $a = $in['action'] ?? 'status'; $t = $in['target'] ?? 'site';
    $dir = $t === 'packages' ? '/opt/packages' : '/var/www/html';
    
    if ($a === 'clone') {
        if (!is_dir("$dir/.git")) {
            $url = 'https://github.com/PaganLinux/pagan-repo.git';
            @shell_exec("/usr/bin/git clone $url $dir 2>&1");
            @shell_exec("chown -R www-data:www-data $dir 2>&1");
        }
        header('Location: ?'); exit;
    }
    
    if ($a === 'status') {
        if (!is_dir("$dir/.git")) { echo json_encode(['last'=>'','behind'=>'','target'=>$t,'norepo'=>true]); exit; }
        $c1 = trim(@shell_exec("cd $dir && /usr/bin/git status --short 2>&1"));
        $c2 = trim(@shell_exec("cd $dir && /usr/bin/git log -1 --format='%h %s (%cr)' 2>&1"));
        @shell_exec("cd $dir && /usr/bin/git fetch origin 2>&1");
        $c3 = trim(@shell_exec("cd $dir && /usr/bin/git log HEAD..origin/main --oneline 2>&1"));
        echo json_encode(['changes'=>$c1,'last'=>$c2,'behind'=>$c3,'target'=>$t]);
    } elseif ($a === 'pull') {
        $r = @shell_exec("cd $dir && /usr/bin/git pull origin main 2>&1");
        echo json_encode(['ok'=>1,'msg'=>trim($r)]);
    } elseif ($a === 'build') {
        $dir = '/opt/packages';
        if (is_file("$dir/pagsync")) {
            @shell_exec("cd $dir && python3 pagsync build all > /dev/null 2>&1 &");
            echo json_encode(['ok'=>1,'msg'=>'Build started']);
        } else {
            echo json_encode(['ok'=>0,'msg'=>'pagsync not found in /opt/packages']);
        }
    }
    exit;
}

// ═══ DATA ═══
$pc = db()->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$pt = db()->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$pages = $act === 'pages' ? db()->query("SELECT * FROM pages ORDER BY slug,lang")->fetchAll() : [];
$posts = $act === 'posts' ? db()->query("SELECT * FROM posts ORDER BY created DESC")->fetchAll() : [];
$sets = $act === 'settings' ? db()->query("SELECT * FROM settings ORDER BY key,lang")->fetchAll() : [];
$edit = null;
if ($act === 'pages' && ($eid = $_GET['edit'] ?? 0)) { $s = db()->prepare("SELECT * FROM pages WHERE id=?"); $s->execute([(int)$eid]); $edit = $s->fetch(); }
if ($act === 'posts' && ($eid = $_GET['edit'] ?? 0)) { $s = db()->prepare("SELECT * FROM posts WHERE id=?"); $s->execute([(int)$eid]); $edit = $s->fetch(); }
?><!DOCTYPE html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin – PaganLinux</title>
<style>
:root{--bg:#f4f5f7;--s:#fff;--b:#e2e4e9;--t:#1a1d26;--m:#6b7280;--p:#5b5fef;--g:#16a34a;--r:#dc2626}
*{margin:0;padding:0;box-sizing:border-box}
body{display:flex;min-height:100vh;font-family:system-ui,sans-serif;font-size:14px;background:var(--bg);color:var(--t)}
aside{width:220px;background:var(--s);border-right:1px solid var(--b);padding:20px 0;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:10}
aside .hd{padding:0 18px 16px;font-weight:700;font-size:1rem;border-bottom:1px solid var(--b);margin-bottom:8px;color:var(--t)}
aside a{display:block;padding:9px 18px;color:var(--m);text-decoration:none;font-size:0.9rem;transition:.1s}
aside a:hover,aside a.on{background:var(--bg);color:var(--t)}
main{margin-left:220px;flex:1;padding:28px 32px;background:var(--bg);min-width:0}
h1{font-size:1.3rem;margin-bottom:20px}
h2{font-size:1.05rem;margin:16px 0 10px}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px}
.card{background:var(--s);border:1px solid var(--b);border-radius:10px;padding:22px;text-align:center}
.card .n{font-size:2rem;font-weight:700}.card .l{color:var(--m);font-size:0.85rem;margin-top:2px}
.box{background:var(--s);border:1px solid var(--b);border-radius:10px;padding:18px;margin-bottom:14px}
.box h3{font-size:0.95rem;margin-bottom:8px}
.btn{display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:7px;font-size:0.85rem;font-weight:500;cursor:pointer;border:1px solid var(--b);background:var(--s);color:var(--t);text-decoration:none;transition:.15s}
.btn:hover{background:#eef0f2}.btn-p{background:var(--p);color:#fff;border-color:var(--p)}.btn-p:hover{filter:brightness(1.1)}.btn-r{color:var(--r)}.btn-s{padding:3px 8px;font-size:0.78rem}
table{width:100%;border-collapse:collapse;background:var(--s);border:1px solid var(--b);border-radius:10px;overflow:hidden;font-size:0.88rem}
th,td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--b)}th{background:#f9fafb;color:var(--m);font-weight:600;font-size:0.8rem}
tr:last-child td{border-bottom:none}
.inp{width:100%;padding:9px 12px;margin:5px 0;border:1px solid var(--b);border-radius:7px;font-size:0.88rem;font-family:inherit;background:var(--s)}.inp:focus{outline:none;border-color:var(--p)}
textarea.inp{font-family:monospace;font-size:0.82rem;min-height:120px}
.info{color:var(--m);font-size:0.85rem}.warn{color:var(--r);font-weight:600}
pre{background:#f9fafb;padding:10px;border-radius:7px;font-size:0.78rem;margin-top:8px;display:none;max-height:150px;overflow:auto}
</style></head><body>
<aside>
<div class="hd">🐺 Admin</div>
<a href="?" class="<?=$act===''?'on':''?>">📊 Dashboard</a>
<a href="?act=pages" class="<?=$act==='pages'?'on':''?>">📄 Strony</a>
<a href="?act=posts" class="<?=$act==='posts'?'on':''?>">📝 Posty</a>
<a href="?act=settings" class="<?=$act==='settings'?'on':''?>">⚙ Ustawienia</a>
<div style="border-top:1px solid var(--b);margin:12px 0"></div>
<a href="/">← Strona</a>
<a href="?logout" style="color:var(--r)">🚪 Wyloguj</a>
</aside>
<main>
<?php if ($act === '' || $act === 'dash'): ?>
<h1>📊 Dashboard</h1>
<div class="cards">
<div class="card"><div class="n" style="color:var(--p)"><?=$pc?></div><div class="l">Stron</div></div>
<div class="card"><div class="n" style="color:var(--g)"><?=$pt?></div><div class="l">Postów</div></div>
</div>

<div class="box"><h3>🌐 Git – strona (paganlinux.eu)</h3>
<p class="info" id="gi-site">⏳ Sprawdzanie...</p>
<button class="btn" onclick="git('status','site')">📊 Status</button>
<button class="btn btn-p" onclick="git('pull','site')">⬇ Pull</button>
</div>

<div class="box"><h3>📦 Git – pakiety (pagan-repo)</h3>
<p class="info" id="gi-pkg">⏳ Sprawdzanie...</p>
<button class="btn" onclick="git('status','packages')">📊 Status</button>
<button class="btn btn-p" onclick="git('pull','packages')">⬇ Pull</button>
<button class="btn btn-p" style="background:var(--g)" onclick="gitClone()">📥 Klonuj repo</button>
<button class="btn" style="background:#f59e0b;color:#fff;border:none" onclick="buildPkg()">🔨 Buduj pakiety</button>
<p id="build-msg" style="margin-top:8px;font-size:0.85rem"></p>
</div>
<pre id="go"></pre>

<?php elseif ($act === 'pages'): ?>
<h1>📄 Strony</h1>
<form method="post" action="?act=page_save" style="max-width:600px;margin-bottom:20px">
<?php if($edit):?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif?>
<input class="inp" name="title" value="<?=h($edit['title']??'')?>" placeholder="Tytuł" required>
<input class="inp" name="slug" value="<?=h($edit['slug']??'')?>" placeholder="slug">
<select class="inp" name="lang"><option value="pl" <?=($edit['lang']??'')==='pl'?'selected':''?>>Polski</option><option value="en" <?=($edit['lang']??'')==='en'?'selected':''?>>English</option></select>
<textarea class="inp" name="body" placeholder="Treść (HTML)"><?=h($edit['body']??'')?></textarea>
<button class="btn btn-p" style="margin-top:10px">💾 Zapisz</button>
<?php if($edit):?><a href="?act=pages" class="btn">Anuluj</a><?php endif?>
</form>
<?php if(!$edit):?><a href="?act=pages&edit=0" class="btn btn-p">➕ Nowa</a><?php endif?>

<table style="margin-top:12px"><tr><th>Tytuł</th><th>Slug</th><th>Język</th><th></th></tr>
<?php foreach($pages as $p):?><tr><td><?=h($p['title'])?></td><td style="color:var(--m)"><?=h($p['slug'])?></td><td><?=$p['lang']?></td>
<td><a href="?act=pages&edit=<?=$p['id']?>" class="btn btn-s">✏️</a> <a href="?act=page_del&id=<?=$p['id']?>" class="btn btn-s btn-r" onclick="return confirm('Usunąć?')">🗑</a></td></tr>
<?php endforeach?></table>

<?php elseif ($act === 'posts'): ?>
<h1>📝 Posty</h1>
<form method="post" action="?act=post_save" style="max-width:600px;margin-bottom:20px">
<?php if($edit):?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif?>
<input class="inp" name="title" value="<?=h($edit['title']??'')?>" placeholder="Tytuł" required>
<input class="inp" name="slug" value="<?=h($edit['slug']??'')?>" placeholder="slug">
<select class="inp" name="lang"><option value="pl" <?=($edit['lang']??'')==='pl'?'selected':''?>>Polski</option><option value="en" <?=($edit['lang']??'')==='en'?'selected':''?>>English</option></select>
<textarea class="inp" name="body" placeholder="Treść (HTML)"><?=h($edit['body']??'')?></textarea>
<input class="inp" name="excerpt" value="<?=h($edit['excerpt']??'')?>" placeholder="Zajawka">
<button class="btn btn-p" style="margin-top:10px">💾 Zapisz</button>
<?php if($edit):?><a href="?act=posts" class="btn">Anuluj</a><?php endif?>
</form>
<?php if(!$edit):?><a href="?act=posts&edit=0" class="btn btn-p">➕ Nowy</a><?php endif?>

<table style="margin-top:12px"><tr><th>Tytuł</th><th>Slug</th><th>Język</th><th>Data</th><th></th></tr>
<?php foreach($posts as $p):?><tr><td><?=h($p['title'])?></td><td style="color:var(--m)"><?=h($p['slug'])?></td><td><?=$p['lang']?></td><td style="color:var(--m);font-size:0.82rem"><?=substr($p['created'],0,10)?></td>
<td><a href="?act=posts&edit=<?=$p['id']?>" class="btn btn-s">✏️</a> <a href="?act=post_del&id=<?=$p['id']?>" class="btn btn-s btn-r" onclick="return confirm('Usunąć?')">🗑</a></td></tr>
<?php endforeach?></table>

<?php elseif ($act === 'settings'): ?>
<h1>⚙ Ustawienia</h1>
<form method="post" action="?act=settings" style="max-width:550px">
<h2>🌐 Strona</h2>
<?php foreach($sets as $s): if(str_starts_with($s['key'],'git_')) continue; ?>
<label style="display:block;margin:10px 0 3px;font-size:0.82rem;color:var(--m)"><?=h($s['key'])?> [<?=$s['lang']?>]</label>
<input class="inp" name="s_<?=h($s['key'])?>_<?=$s['lang']?>" value="<?=h($s['val'])?>">
<?php endforeach?>
<h2>🔄 Git – repozytoria</h2>
<label style="display:block;margin:10px 0 3px;font-size:0.82rem;color:var(--m)">URL repozytorium strony</label>
<input class="inp" name="s_git_repo_pl" value="<?=h(S('git_repo','pl')?:'https://github.com/PaganLinux/paganlinux.eu.git')?>">
<label style="display:block;margin:10px 0 3px;font-size:0.82rem;color:var(--m)">Token GitHub</label>
<input class="inp" name="s_git_token_pl" value="<?=h(S('git_token','pl')?:'')?>" placeholder="ghp_... (opcjonalnie)">
<button class="btn btn-p" style="margin-top:16px">💾 Zapisz</button>
</form>

<?php endif?>
</main>
<script>
async function git(a,t){const o=document.getElementById('go');o.style.display='block';o.textContent='⏳…';try{const r=await fetch('?act=git',{method:'POST',body:JSON.stringify({action:a,target:t})});const d=await r.json();o.textContent=JSON.stringify(d,null,2);const el=t==='packages'?document.getElementById('gi-pkg'):document.getElementById('gi-site');if(d.norepo){el.textContent='❌ Repo nie istnieje – kliknij 📥 Klonuj';el.className='warn'}else if(!d.last||d.last.includes('fatal')){el.textContent='❌ Błąd – sprawdź uprawnienia';el.className='warn'}else if(d.behind&&d.behind.trim()&&!d.behind.includes('fatal')){el.textContent='⚠️ '+d.behind.trim().split('\\n').length+' nowych – Pull + Buduj!';el.className='warn'}else{el.textContent='✅ '+d.last;el.className='info'}}catch(e){o.textContent=e.message}}
function gitClone(){location.href='?act=git&action=clone&target=packages'}
async function buildPkg(){const m=document.getElementById('build-msg');m.textContent='⏳ Budowanie...';m.style.color='var(--m)';try{const r=await fetch('?act=git',{method:'POST',body:JSON.stringify({action:'build'})});const d=await r.json();m.textContent=d.ok?'✅ Build rozpoczęty! Sprawdź logi na /build':'❌ Błąd';m.style.color=d.ok?'var(--g)':'var(--r)'}catch(e){m.textContent='❌ '+e.message}}
git('status','site');
</script>
</body></html>
