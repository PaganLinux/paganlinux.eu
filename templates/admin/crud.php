<h1 style="font-size:1.5rem;margin-bottom:20px"><?=$type==='pages'?'📄 Strony':'📝 Posty'?></h1>
<?php if($edit):?>
<form method="post" style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:24px;margin-bottom:24px;max-width:640px">
<input name="title" value="<?=h($edit['title'])?>" placeholder="Tytuł" required style="width:100%;padding:10px;margin:8px 0;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
<input name="slug" value="<?=h($edit['slug'])?>" placeholder="slug" style="width:100%;padding:10px;margin:8px 0;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
<select name="lang" style="width:100%;padding:10px;margin:8px 0;border:1px solid var(--border);border-radius:8px;font-size:0.9rem"><option value="pl" <?=($edit['lang']??'')==='pl'?'selected':''?>>Polski</option><option value="en" <?=($edit['lang']??'')==='en'?'selected':''?>>English</option></select>
<textarea name="body" rows="10" style="width:100%;padding:10px;margin:8px 0;border:1px solid var(--border);border-radius:8px;font-size:0.85rem;font-family:monospace"><?=h($edit['body'])?></textarea>
<?php if($type==='posts'):?><input name="excerpt" value="<?=h($edit['excerpt']??'')?>" placeholder="Zajawka" style="width:100%;padding:10px;margin:8px 0;border:1px solid var(--border);border-radius:8px"><?php endif?>
<button class="btn" style="background:var(--primary);color:#fff;border:none">💾 Zapisz</button>
<a href="?act=<?=$type?>" class="btn">Anuluj</a>
</form>
<?php else:?>
<a href="?act=<?=$type?>&edit=0" class="btn" style="background:var(--primary);color:#fff;border:none;margin-bottom:16px">➕ Nowy</a>
<?php endif?>
<table style="background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden"><thead><tr style="background:var(--bg)"><th>Tytuł</th><th>Slug</th><th>Język</th><th>Pub</th><th style="width:100px"></th></tr></thead>
<?php foreach($items as $i):?><tr><td><?=h($i['title'])?></td><td style="color:var(--muted)"><?=h($i['slug'])?></td><td><?=$i['lang']?></td><td><?=$i['pub']?'✅':'—'?></td>
<td><a href="?act=<?=$type?>&edit=<?=$i['id']?>" class="btn" style="padding:4px 10px;font-size:0.8rem">✏️</a>
<a href="?act=<?=$type?>&del=<?=$i['id']?>" class="btn" style="padding:4px 10px;font-size:0.8rem;color:#e74c3c" onclick="return confirm('Usunąć?')">🗑</a></td></tr><?php endforeach?></table>
