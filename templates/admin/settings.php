<h1 style="font-size:1.5rem;margin-bottom:20px">⚙ Ustawienia</h1>
<form method="post" style="max-width:560px">
<?php foreach($settings as $s):?>
<label style="display:block;margin:12px 0 4px;font-size:0.85rem;color:var(--muted)"><?=h($s['key'])?> [<?=$s['lang']?>]</label>
<input name="s_<?=h($s['key'])?>_<?=$s['lang']?>" value="<?=h($s['val'])?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
<?php endforeach?>
<button class="btn" style="background:var(--primary);color:#fff;border:none;margin-top:20px;padding:12px 24px">💾 Zapisz wszystkie</button>
</form>
