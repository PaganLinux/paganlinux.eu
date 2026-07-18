<h1 style="font-size:1.5rem;margin-bottom:24px">📊 Dashboard</h1>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px">
<div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:24px;text-align:center"><div style="font-size:2.2rem;font-weight:700;color:var(--primary)"><?=$pages?></div><p style="color:var(--muted);margin-top:4px">Stron</p></div>
<div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:24px;text-align:center"><div style="font-size:2.2rem;font-weight:700;color:#27ae60"><?=$posts?></div><p style="color:var(--muted);margin-top:4px">Postów</p></div>
<div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:24px;text-align:center"><div style="font-size:2.2rem;font-weight:700;color:var(--muted)">🐺</div><p style="color:var(--muted);margin-top:4px">PaganLinux</p></div>
</div>
<div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:20px">
<h3 style="margin-bottom:12px">🔄 Git – aktualizacja strony</h3>
<p id="git-info" style="color:var(--muted);font-size:0.85rem;margin-bottom:10px"></p>
<button class="btn" onclick="git('status')">📊 Status</button>
<button class="btn" style="background:var(--primary);color:#fff;border:none" onclick="git('pull')">⬇ Pull z GitHuba</button>
<pre id="go" style="margin-top:12px;background:#fafbfc;padding:12px;border-radius:8px;font-size:0.8rem;display:none;max-height:200px;overflow:auto"></pre>
</div>
<script>
async function git(a){const o=document.getElementById('go');o.style.display='block';o.textContent='⏳…';try{const r=await fetch('/admin.php?act=git',{method:'POST',body:JSON.stringify({action:a})});const d=await r.json();o.textContent=JSON.stringify(d,null,2);if(d.behind&&d.behind.trim()){document.getElementById('git-info').innerHTML='⚠️ <b>Dostępne aktualizacje!</b> '+d.behind.split('\\n').length+' commitów';document.getElementById('git-info').style.color='#e74c3c'}else if(d.last){document.getElementById('git-info').textContent='✅ Aktualne: '+d.last}}catch(e){o.textContent=e.message}}
// Auto-check on load
git('status');
</script>
