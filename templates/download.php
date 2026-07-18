<!DOCTYPE html><html lang="<?=$L?>"><head><meta charset="utf-8"><title>Download – <?=S('site_title')?></title><link rel="stylesheet" href="/assets/style.css?v=2"></head><body>
<header><nav><a href="/" class="logo">🐺 <?=h(S('site_title'))?></a><a href="/">← <?=$L=='pl'?'Strona główna':'Home'?></a></nav></header>
<main class="wrap"><h1>📥 Download</h1>
<?php foreach($files as $f):?><div class="row"><span><?=h($f['n'])?></span><span style="color:var(--muted)"><?=$f['s']?></span></div>
<?php endforeach; ?></main></body></html>
