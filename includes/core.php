<?php
// PaganLinux – Database & Core
define('DB_FILE', __DIR__.'/../data/site.db');

function db(): PDO {
    static $p = null;
    if (!$p) { $p = new PDO('sqlite:'.DB_FILE); $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); $p->exec('PRAGMA journal_mode=WAL'); }
    return $p;
}

function init_db() {
    $d = db();
    $d->exec("CREATE TABLE IF NOT EXISTS settings(key TEXT,lang TEXT DEFAULT'pl',val TEXT,PRIMARY KEY(key,lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS pages(id INTEGER PRIMARY KEY AUTOINCREMENT,slug TEXT,title TEXT,body TEXT DEFAULT'',lang TEXT DEFAULT'pl',pub INTEGER DEFAULT 1,created DATETIME DEFAULT(datetime('now')),updated DATETIME DEFAULT(datetime('now')),UNIQUE(slug,lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS posts(id INTEGER PRIMARY KEY AUTOINCREMENT,slug TEXT,title TEXT,body TEXT DEFAULT'',excerpt TEXT DEFAULT'',lang TEXT DEFAULT'pl',pub INTEGER DEFAULT 1,created DATETIME DEFAULT(datetime('now')),updated DATETIME DEFAULT(datetime('now')),UNIQUE(slug,lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT,login TEXT UNIQUE,pw TEXT)");
    $d->exec("CREATE TABLE IF NOT EXISTS build_log(id INTEGER PRIMARY KEY AUTOINCREMENT,bid TEXT DEFAULT'',msg TEXT,lvl TEXT DEFAULT'info',ts DATETIME DEFAULT(datetime('now')))");
    if (!$d->query("SELECT COUNT(*) FROM users")->fetchColumn()) {
        $d->prepare("INSERT INTO users(login,pw) VALUES(?,?)")->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}

session_start();
init_db();

function lang(): string { return $_COOKIE['lang'] ?? 'pl'; }
function S(string $k, ?string $l=null): string {
    $l ??= lang();
    $r = db()->prepare("SELECT val FROM settings WHERE key=? AND lang=?");
    $r->execute([$k, $l]);
    return $r->fetchColumn() ?: 'PaganLinux';
}
function logged(): bool { return !empty($_SESSION['user']); }
function auth() { if (!logged()) { header('Location:/admin.php?login'); exit; } }
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
