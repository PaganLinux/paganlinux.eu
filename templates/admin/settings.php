<h1 style="font-size:1.5rem;margin-bottom:20px">⚙ Ustawienia</h1>
<form method="post" style="max-width:560px">
<h3 style="margin-bottom:8px">🌐 Strona</h3>
<?php foreach($settings as $s): if(str_starts_with($s['key'],'git_')) continue; ?>
<label style="display:block;margin:12px 0 4px;font-size:0.85rem;color:var(--muted)"><?=h($s['key'])?> [<?=$s['lang']?>]</label>
<input name="s_<?=h($s['key'])?>_<?=$s['lang']?>" value="<?=h($s['val'])?>" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font-size:0.9rem">
<?php endforeach?>

<h3 style="margin:28px 0 8px">🔄 Git – repozytorium</h3>
<p style="color:var(--muted);font-size:0.85rem;margin-bottom:12px">Skonfiguruj repozytorium GitHub aby system sam sprawdzał aktualizacje.</p>
<label style="display:block;margin:12px 0 4px;font-size:0.85rem;color:var(--muted)">URL repozytorium</label>
<input name="s_git_repo_pl" value="<?=h(S('git_repo','pl')?:'https://github.com/PaganLinux/paganlinux.eu.git')?>" placeholder="https://github.com/user/repo.git" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px">
<label style="display:block;margin:12px 0 4px;font-size:0.85rem;color:var(--muted)">Gałąź (branch)</label>
<input name="s_git_branch_pl" value="<?=h(S('git_branch','pl')?:'main')?>" placeholder="main" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px">
<label style="display:block;margin:12px 0 4px;font-size:0.85rem;color:var(--muted)">Token (Personal Access Token)</label>
<input name="s_git_token_pl" value="<?=h(S('git_token','pl')?:'')?>" placeholder="ghp_xxxxxxxxxxxx" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px">
<p style="color:var(--muted);font-size:0.8rem;margin-top:4px">Utwórz token na GitHub → Settings → Developer settings → Personal access tokens → repo</p>

<button class="btn" style="background:var(--primary);color:#fff;border:none;margin-top:20px;padding:12px 24px">💾 Zapisz wszystkie</button>
</form>
