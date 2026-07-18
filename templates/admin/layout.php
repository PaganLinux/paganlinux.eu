<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin – PaganLinux</title><link rel="stylesheet" href="/assets/style.css?v=2">
<style>
.admin-body{display:flex;min-height:100vh}
.admin-sidebar{width:240px;background:#fff;border-right:1px solid var(--border);padding:24px 0;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto}
.admin-sidebar a{display:block;padding:10px 24px;color:var(--muted);text-decoration:none;font-size:0.9rem}
.admin-sidebar a:hover{background:var(--bg);color:var(--text)}
.admin-main{flex:1;padding:32px 40px;background:#fafbfc;overflow-x:auto;min-width:0}
@media(max-width:768px){.admin-sidebar{width:100%;position:static;height:auto;border-right:none;border-bottom:1px solid var(--border)}.admin-body{flex-direction:column}}
</style></head>
<body class="admin-body">
<aside class="admin-sidebar">
<div style="padding:0 24px 20px;font-size:1.05rem;font-weight:700;border-bottom:1px solid var(--border);margin-bottom:8px"><a href="/admin.php" style="color:var(--text);text-decoration:none">🐺 Admin</a></div>
<a href="/admin.php?act=dash">📊 Dashboard</a>
<a href="/admin.php?act=pages">📄 Strony</a>
<a href="/admin.php?act=posts">📝 Posty</a>
<a href="/admin.php?act=settings">⚙ Ustawienia</a>
<div style="border-top:1px solid var(--border);margin:12px 0"></div>
<a href="/">← Strona</a>
<a href="/admin.php?logout" style="color:#e74c3c">🚪 Wyloguj</a>
</aside>
<main class="admin-main"><?=$body?></main>
</body></html>
