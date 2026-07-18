<?php
// PaganLinux – Router
require __DIR__.'/includes/core.php';

$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$L = lang();

function view(string $tpl, array $vars=[]): string {
    foreach ($vars as $k => $v) $$k = $v;
    $L = $GLOBALS['L'] ?? 'pl';
    ob_start(); include __DIR__."/templates/$tpl.php"; return ob_get_clean();
}

// ═══ ROUTES ═══
if ($path === '' || $path === 'index.php') {
    $posts = db()->prepare("SELECT * FROM posts WHERE lang=? AND pub=1 ORDER BY created DESC LIMIT 6");
    $posts->execute([$L]);
    echo view('home', ['posts' => $posts->fetchAll(PDO::FETCH_ASSOC)]);
}
elseif ($path === 'download') {
    $files = [];
    $dir = '/var/www/download.paganlinux.eu';
    if (is_dir($dir)) foreach (scandir($dir) as $f) {
        if ($f[0] === '.' || !is_file("$dir/$f")) continue;
        $s = filesize("$dir/$f");
        $files[] = ['n' => $f, 's' => $s > 1048576 ? round($s/1048576).' MB' : round($s/1024).' KB'];
    }
    echo view('download', ['files' => $files]);
}
elseif ($path === 'build') {
    $b = db()->query("SELECT DISTINCT bid FROM build_log WHERE bid!='' ORDER BY id DESC LIMIT 30");
    echo view('build', ['builds' => $b->fetchAll(PDO::FETCH_ASSOC)]);
}
elseif ($path === 'stream') {
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache');
    $last = (int)($_GET['last'] ?? 0);
    while (!connection_aborted()) {
        $s = db()->prepare("SELECT * FROM build_log WHERE id > ? ORDER BY id");
        $s->execute([$last]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo "data:" . json_encode(['msg' => $row['msg'], 'lvl' => $row['lvl'], 'ts' => $row['ts']]) . "\n\n";
            $last = $row['id'];
        }
        ob_flush(); flush();
        sleep(2);
    }
    exit;
}
elseif ($path === 'repo') {
    echo view('repo');
}
else {
    $s = db()->prepare("SELECT id,slug,title,body,lang,created FROM pages WHERE slug=? AND lang=? AND pub=1
                        UNION ALL
                        SELECT id,slug,title,body,lang,created FROM posts WHERE slug=? AND lang=? AND pub=1 LIMIT 1");
    $s->execute([$path, $L, $path, $L]);
    $page = $s->fetch(PDO::FETCH_ASSOC);
    if ($page) echo view('page', ['p' => $page]);
    else { http_response_code(404); echo view('404'); }
}
