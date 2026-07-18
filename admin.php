<?php
// PaganLinux – Admin Panel
require __DIR__.'/includes/core.php';
$L = lang();

function admin_view(string $tpl, array $vars=[]): string {
    foreach ($vars as $k => $v) $$k = $v;
    $L = $GLOBALS['L'] ?? 'pl';
    ob_start(); include __DIR__."/templates/admin/$tpl.php";
    $body = ob_get_clean();
    ob_start(); include __DIR__.'/templates/admin/layout.php';
    return ob_get_clean();
}

// ═══ LOGIN ═══
if (isset($_GET['login'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $s = db()->prepare("SELECT * FROM users WHERE login=?");
        $s->execute([$_POST['user'] ?? '']);
        $u = $s->fetch(PDO::FETCH_ASSOC);
        if ($u && password_verify($_POST['pass'] ?? '', $u['pw'])) {
            $_SESSION['user'] = $u['login'];
            header('Location: /admin.php');
            exit;
        }
        $err = 'Błędny login lub hasło';
    }
    include __DIR__.'/templates/admin/login.php';
    exit;
}

auth();

// ═══ LOGOUT ═══
if (isset($_GET['logout'])) { session_destroy(); header('Location: /'); exit; }

$act = $_GET['act'] ?? 'dash';

// ═══ DASHBOARD ═══
if ($act === 'dash') {
    $pc = db()->query("SELECT COUNT(*) FROM pages")->fetchColumn();
    $pt = db()->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    echo admin_view('dash', ['pages' => $pc, 'posts' => $pt]);
}

// ═══ PAGES ═══
elseif ($act === 'pages') {
    if ($_POST && isset($_POST['title'])) {
        $slug = $_POST['slug'] ?: preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($_POST['title'])));
        $ex = db()->prepare("SELECT id FROM pages WHERE slug=? AND lang=?");
        $ex->execute([$slug, $_POST['lang'] ?? 'pl']);
        if ($ex->fetch()) {
            db()->prepare("UPDATE pages SET title=?,body=?,pub=?,updated=datetime('now') WHERE slug=? AND lang=?")
               ->execute([$_POST['title'], $_POST['body'] ?? '', (int)($_POST['pub'] ?? 1), $slug, $_POST['lang'] ?? 'pl']);
        } else {
            db()->prepare("INSERT INTO pages(slug,title,body,lang,pub) VALUES(?,?,?,?,?)")
               ->execute([$slug, $_POST['title'], $_POST['body'] ?? '', $_POST['lang'] ?? 'pl', (int)($_POST['pub'] ?? 1)]);
        }
        header('Location: /admin.php?act=pages'); exit;
    }
    if (isset($_GET['del'])) { db()->prepare("DELETE FROM pages WHERE id=?")->execute([(int)$_GET['del']]); header('Location: /admin.php?act=pages'); exit; }
    $items = db()->query("SELECT * FROM pages ORDER BY slug, lang")->fetchAll(PDO::FETCH_ASSOC);
    $edit = null;
    if (isset($_GET['edit'])) { $s = db()->prepare("SELECT * FROM pages WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(PDO::FETCH_ASSOC); }
    echo admin_view('crud', ['items' => $items, 'edit' => $edit, 'type' => 'pages']);
}

// ═══ POSTS ═══
elseif ($act === 'posts') {
    if ($_POST && isset($_POST['title'])) {
        $slug = $_POST['slug'] ?: preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($_POST['title'])));
        $ex = db()->prepare("SELECT id FROM posts WHERE slug=? AND lang=?");
        $ex->execute([$slug, $_POST['lang'] ?? 'pl']);
        if ($ex->fetch()) {
            db()->prepare("UPDATE posts SET title=?,body=?,excerpt=?,pub=?,updated=datetime('now') WHERE slug=? AND lang=?")
               ->execute([$_POST['title'], $_POST['body'] ?? '', $_POST['excerpt'] ?? '', (int)($_POST['pub'] ?? 1), $slug, $_POST['lang'] ?? 'pl']);
        } else {
            db()->prepare("INSERT INTO posts(slug,title,body,excerpt,lang,pub) VALUES(?,?,?,?,?,?)")
               ->execute([$slug, $_POST['title'], $_POST['body'] ?? '', $_POST['excerpt'] ?? '', $_POST['lang'] ?? 'pl', (int)($_POST['pub'] ?? 1)]);
        }
        header('Location: /admin.php?act=posts'); exit;
    }
    if (isset($_GET['del'])) { db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$_GET['del']]); header('Location: /admin.php?act=posts'); exit; }
    $items = db()->query("SELECT * FROM posts ORDER BY created DESC")->fetchAll(PDO::FETCH_ASSOC);
    $edit = null;
    if (isset($_GET['edit'])) { $s = db()->prepare("SELECT * FROM posts WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(PDO::FETCH_ASSOC); }
    echo admin_view('crud', ['items' => $items, 'edit' => $edit, 'type' => 'posts']);
}

// ═══ SETTINGS ═══
elseif ($act === 'settings') {
    if ($_POST) {
        foreach ($_POST as $k => $v) {
            if (str_starts_with($k, 's_')) {
                $parts = explode('_', substr($k, 2)); $l = array_pop($parts); $key = implode('_', $parts);
                if (strlen($l) !== 2) { $key .= '_' . $l; $l = 'pl'; }
                db()->prepare("INSERT OR REPLACE INTO settings(key,lang,val) VALUES(?,?,?)")->execute([$key, $l, $v]);
            }
        }
        header('Location: /admin.php?act=settings'); exit;
    }
    $sets = db()->query("SELECT * FROM settings ORDER BY key, lang")->fetchAll(PDO::FETCH_ASSOC);
    echo admin_view('settings', ['settings' => $sets]);
}

// ═══ GIT ═══
elseif ($act === 'git') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? 'status';
    $target = $input['target'] ?? 'site';
    
    if ($target === 'packages') {
        $dir = '/opt/packages';
        $branch = S('pkg_branch', 'pl') ?: 'main';
    } else {
        $dir = '/var/www/html';
        $branch = S('git_branch', 'pl') ?: 'main';
    }
    
    $token = S('git_token', 'pl') ?: '';
    
    if ($action === 'status') {
        $c1 = trim(shell_exec("cd $dir && /usr/bin/git status --short 2>&1"));
        $c2 = trim(shell_exec("cd $dir && /usr/bin/git log -1 --format='%h %s (%cr)' 2>&1"));
        shell_exec("cd $dir && /usr/bin/git fetch origin 2>&1");
        $c3 = trim(shell_exec("cd $dir && /usr/bin/git log HEAD..origin/$branch --oneline 2>&1"));
        echo json_encode(['changes'=>$c1,'last'=>$c2,'behind'=>$c3,'target'=>$target,'dir'=>$dir]);
    } elseif ($action === 'pull') {
        $r = shell_exec("cd $dir && /usr/bin/git pull origin $branch 2>&1");
        echo json_encode(['ok'=>1,'msg'=>trim($r),'target'=>$target]);
    }
    exit;
}

else { header('Location: /admin.php?act=dash'); }
