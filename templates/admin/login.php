<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login – PaganLinux</title><link rel="stylesheet" href="/assets/style.css?v=2"></head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg)">
<form method="post" style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:48px;width:360px;box-shadow:0 4px 24px rgba(0,0,0,0.06)">
<h1 style="font-size:1.3rem;text-align:center;margin-bottom:20px;color:var(--text)">🐺 PaganLinux CMS</h1>
<?php if(isset($err)):?><p style="color:#e74c3c;text-align:center;font-size:0.9rem;margin-bottom:12px"><?=$err?></p><?php endif?>
<input name="user" placeholder="Login" required style="width:100%;padding:12px;margin:6px 0;border:1px solid var(--border);border-radius:8px;font-size:0.95rem;background:#fafbfc">
<input name="pass" type="password" placeholder="Hasło" required style="width:100%;padding:12px;margin:6px 0;border:1px solid var(--border);border-radius:8px;font-size:0.95rem;background:#fafbfc">
<button class="btn" style="width:100%;margin-top:12px;background:var(--primary);color:#fff;border:none;padding:12px;font-size:0.95rem;border-radius:8px">Zaloguj się</button>
</form></body></html>
