<!DOCTYPE html><html lang="<?=$L?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=S('site_title')?> – <?=S('site_desc')?></title>
<link rel="stylesheet" href="/assets/style.css"></head><body>
<header><nav><a href="/" class="logo">🐺 <?=h(S('site_title'))?></a>
<div class="nav-links">
<a href="/"><?=$L=='pl'?'Start':'Home'?></a>
<a href="/download"><?=$L=='pl'?'Pobierz':'Download'?></a>
<a href="/repo">Repo</a>
<a href="/build">Build</a>
<?php if(logged()): ?><a href="/admin.php">Panel</a><?php else: ?><a href="/admin.php?login">Zaloguj</a><?php endif; ?>
<span class="lang"><a href="#" onclick="document.cookie='lang=pl;path=/;location.reload()" class="<?=$L=='pl'?'on':''?>">PL</a> <a href="#" onclick="document.cookie='lang=en;path=/;location.reload()" class="<?=$L=='en'?'on':''?>">EN</a></span>
</div></nav></header>
<main>
<section class="hero"><div class="wrap">
<h1><?=h(S('site_title'))?></h1>
<p><?=h(S('site_desc'))?></p>
<a href="/download" class="btn">⬇ <?=$L=='pl'?'Pobierz':'Download'?></a>
</div></section>
<section class="feat wrap"><div class="cards">
<div><h3>🐺 pag</h3><p><?=$L=='pl'?'Szybki menedżer pakietów z GPG – atomowe transakcje, pełny rollback.':'Fast package manager with GPG – atomic transactions, full rollback.'?></p></div>
<div><h3>🔐 Bezpieczeństwo</h3><p><?=$L=='pl'?'Podpisy GPG, SHA256 per-plik, weryfikacja repo.':'GPG signatures, SHA256 per file, repo verification.'?></p></div>
<div><h3>⚡ XFCE 4.20</h3><p><?=$L=='pl'?'Lekki pulpit domyślnie – szybki nawet na starszym sprzęcie.':'Lightweight desktop by default – fast even on older hardware.'?></p></div>
<div><h3>📦 4800+ pakietów</h3><p><?=$L=='pl'?'Pełne repozytorium – paczki z Solus Linux.':'Full repository – packages from Solus Linux.'?></p></div>
</div></section>
<?php if (!empty($posts)): ?><section class="blog wrap"><h2><?=$L=='pl'?'Ostatnie wpisy':'Latest posts'?></h2><div class="cards"><?php foreach($posts as $p):?><article class="card"><a href="/<?=h($p['slug'])?>" class="title"><?=h($p['title'])?></a><time><?=substr($p['created'],0,10)?></time><p><?=h(mb_substr($p['excerpt']?:strip_tags($p['body']),0,180))?>…</p></article><?php endforeach?></div></section><?php endif?>
</main><footer><div class="wrap">&copy; 2026 PaganLinux · <a href="https://github.com/PaganLinux">GitHub</a></div></footer></body></html>
