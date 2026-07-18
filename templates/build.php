<!DOCTYPE html><html lang="<?=$L?>"><head><meta charset="utf-8"><title>Build – <?=S('site_title')?></title><link rel="stylesheet" href="/assets/style.css?v=2"></head><body>
<header><nav><a href="/" class="logo">🐺 <?=h(S('site_title'))?></a><a href="/">← <?=$L=='pl'?'Strona główna':'Home'?></a></nav></header>
<main class="wrap"><h1>📜 Build Logs</h1>
<div id="live" class="log"><?=$L=='pl'?'Oczekiwanie…':'Waiting…'?></div>
<h3 style="margin-top:24px"><?=$L=='pl'?'Historia':'History'?></h3>
<?php foreach($builds as $b):?><div class="row"><code><?=h($b['bid'])?></code></div><?php endforeach?></main>
<script>const e=document.getElementById('live'),s=new EventSource('/stream');s.onmessage=o=>{try{const d=JSON.parse(o.data);if(!d.msg)return;const x=document.createElement('div');x.textContent=`[${d.ts||''}] ${d.msg}`;e.appendChild(x);if(e.children.length>80)e.firstChild.remove();e.scrollTop=e.scrollHeight}catch(ex){}}</script></body></html>
