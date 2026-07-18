<?php
// PaganLinux CMS – Database
define('DB_PATH', __DIR__.'/../data/site.db');
define('LANG_COOKIE', 'lang');
define('LANGUAGES', ['pl'=>'Polski','en'=>'English']);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:'.DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("PRAGMA journal_mode=WAL");
    }
    return $pdo;
}

function init_db() {
    $d = db();
    $d->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT NOT NULL, lang TEXT NOT NULL DEFAULT 'pl', value TEXT,
        PRIMARY KEY(key, lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL, title TEXT NOT NULL, body TEXT DEFAULT '',
        lang TEXT DEFAULT 'pl', published INTEGER DEFAULT 1,
        created DATETIME DEFAULT (datetime('now')),
        updated DATETIME DEFAULT (datetime('now')),
        UNIQUE(slug, lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL, title TEXT NOT NULL, body TEXT DEFAULT '',
        excerpt TEXT DEFAULT '', lang TEXT DEFAULT 'pl',
        published INTEGER DEFAULT 1,
        created DATETIME DEFAULT (datetime('now')),
        updated DATETIME DEFAULT (datetime('now')),
        UNIQUE(slug, lang))");
    $d->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL, password TEXT NOT NULL)");
    $d->exec("CREATE TABLE IF NOT EXISTS build_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        build_id TEXT DEFAULT '', message TEXT, level TEXT DEFAULT 'info',
        created DATETIME DEFAULT (datetime('now')))");
    $s = $d->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($s == 0) {
        $d->prepare("INSERT INTO users (username,password) VALUES (?,?)")
          ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}

function lang(): string {
    return $_COOKIE[LANG_COOKIE] ?? 'pl';
}

function setting(string $key, ?string $lg=null): string {
    $lg = $lg ?? lang();
    $s = db()->prepare("SELECT value FROM settings WHERE key=? AND lang=?");
    $s->execute([$key, $lg]);
    $r = $s->fetchColumn();
    return $r ?: 'PaganLinux';
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function require_auth() {
    if (!is_logged_in()) {
        header('Location: /admin.php?login');
        exit;
    }
}

session_start();
init_db();
