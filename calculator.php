<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Interactive Calculator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg:#0f1724; --card:#0b1220; --accent:#4fd1c5; --muted:#94a3b8; --text:#e6eef6;
    }
    [data-theme="light"]{
      --bg:#f4f7fb; --card:#ffffff; --accent:#0ea5a7; --muted:#6b7280; --text:#0b1220;
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family:Inter,Segoe UI,Arial; background:linear-gradient(180deg,var(--bg),#071023);
      color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .card{
      width:100%; max-width:720px; background:linear-gradient(180deg,var(--card),rgba(255,255,255,0.02));
      border-radius:12px; padding:20px; box-shadow:0 8px 30px rgba(2,6,23,0.6);
      display:grid; grid-template-columns:1fr 260px; gap:18px; align-items:start;
    }
    .left{padding:6px}
    h1{margin:0 0 8px 0; font-size:20px; color:var(--text)}
    form{display:flex; flex-wrap:wrap; gap:8px; align-items:center}
    input[type="number"]{
      width:calc(50% - 8px); padding:12px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.06);
      background:transparent; color:var(--text); font-size:15px; outline:none;
    }
    select{padding:12px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.06); background:transparent; color:var(--text)}
    .ops{display:flex; gap:8px; margin:6px 0 0 0}
    .op-btn{
      padding:10px 12px; border-radius:8px; border:none; cursor:pointer; background:rgba(255,255,255,0.03); color:var(--muted);
      font-weight:600; transition:all .14s;
    }
    .op-btn.active{background:var(--accent); color:#042023; transform:translateY(-2px)}
    .actions{display:flex; gap:8px; margin-left:auto}
    button[type="submit"]{
      background:var(--accent); color:#022; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:700;
    }
    .result{
      margin-top:14px; padding:14px; border-radius:10px; background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border:1px solid rgba(255,255,255,0.03); font-size:18px;
    }
    .result .value{font-size:28px; font-weight:700; color:var(--accent)}
    .right{border-left:1px dashed rgba(255,255,255,0.03); padding-left:16px}
    .history{max-height:280px; overflow:auto; padding-right:6px}
    .history-item{padding:8px; border-radius:8px; margin-bottom:8px; background:rgba(255,255,255,0.02); font-size:13px; color:var(--muted)}
    .controls{display:flex; gap:8px; margin-top:12px}
    .ghost{background:transparent;border:1px solid rgba(255,255,255,0.06); color:var(--muted); padding:8px 10px; border-radius:8px; cursor:pointer}
    .theme-toggle{margin-left:auto}
    footer{margin-top:12px; font-size:12px; color:var(--muted)}
    @media(max-width:720px){
      .card{grid-template-columns:1fr; padding:14px}
      .right{border-left:none; border-top:1px dashed rgba(255,255,255,0.03); padding-top:12px; margin-top:8px}
      input[type="number"]{width:100%}
    }
  </style>
</head>
<body>
  <div class="card" id="app">
    <div class="left">
      <h1>Interactive Calculator</h1>
      <form id="calcForm" method="post" novalidate>
        <input id="num1" name="num1" type="number" step="any" placeholder="Enter first number" required>
        <input id="num2" name="num2" type="number" step="any" placeholder="Enter second number" required>
        <div class="ops" role="toolbar" aria-label="operators">
          <button type="button" class="op-btn active" data-op="+">+</button>
          <button type="button" class="op-btn" data-op="-">−</button>
          <button type="button" class="op-btn" data-op="*">×</button>
          <button type="button" class="op-btn" data-op="/">÷</button>
        </div>

        <div style="display:flex; gap:8px; margin-top:8px; width:100%; align-items:center">
          <select id="operator" name="operator" aria-label="operator" style="flex:1">
            <option value="+">+</option>
            <option value="-">-</option>
            <option value="*">*</option>
            <option value="/">/</option>
          </select>

          <div class="actions">
            <button type="submit" name="submit" value="1">Calculate</button>
          </div>
        </div>

        <div class="result" aria-live="polite" id="resultBox" style="display:none;">
          <div>Result:</div>
          <div class="value" id="resultValue">0</div>
        </div>
      </form>

      <footer>Tip: use operator buttons or the dropdown. Press Enter to calculate.</footer>
    </div>

    <aside class="right">
      <div style="display:flex; align-items:center; gap:8px">
        <strong>History</strong>
        <button id="clearHistory" class="ghost" title="Clear history">Clear</button>
        <button id="toggleTheme" class="ghost theme-toggle" title="Toggle theme">Theme</button>
      </div>

      <div class="history" id="historyList" aria-live="polite" style="margin-top:10px">
        <!-- history items -->
      </div>
    </aside>
  </div>

<?php
// Server-side fallback: compute safely if form is submitted (for non-JS clients).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $n1 = isset($_POST['num1']) ? floatval($_POST['num1']) : 0.0;
  $n2 = isset($_POST['num2']) ? floatval($_POST['num2']) : 0.0;
  $op = isset($_POST['operator']) ? $_POST['operator'] : '+';
  $res = null;
  switch($op){
    case '+': $res = $n1 + $n2; break;
    case '-': $res = $n1 - $n2; break;
    case '*': $res = $n1 * $n2; break;
    case '/':
      if($n2 == 0){ $res = "Error: Division by zero"; }
      else { $res = $n1 / $n2; }
      break;
    default: $res = "Invalid operator";
  }
  // print a small script to populate result for non-JS fallback or progressive enhancement.
  echo "<script>document.addEventListener('DOMContentLoaded',function(){";
  $safe = json_encode($res);
  echo "var r = $safe; var box=document.getElementById('resultBox'); if(box){box.style.display='block'; document.getElementById('resultValue').textContent = r;}});</script>";
}
?>

<script>
  (function(){
    const num1 = document.getElementById('num1');
    const num2 = document.getElementById('num2');
    const operator = document.getElementById('operator');
    const opButtons = document.querySelectorAll('.op-btn');
    const form = document.getElementById('calcForm');
    const resultBox = document.getElementById('resultBox');
    const resultValue = document.getElementById('resultValue');
    const historyList = document.getElementById('historyList');
    const clearHistory = document.getElementById('clearHistory');
    const toggleTheme = document.getElementById('toggleTheme');
    const app = document.documentElement;

    // Theme
    const savedTheme = localStorage.getItem('calc-theme') || 'dark';
    if(savedTheme === 'light') document.documentElement.setAttribute('data-theme','light');

    toggleTheme.addEventListener('click',()=>{
      const isLight = document.documentElement.getAttribute('data-theme') === 'light';
      document.documentElement.setAttribute('data-theme', isLight ? '': 'light');
      localStorage.setItem('calc-theme', isLight ? 'dark' : 'light');
    });

    // Operators: buttons toggle + sync dropdown
    opButtons.forEach(b=>{
      b.addEventListener('click', ()=>{
        opButtons.forEach(x=>x.classList.remove('active'));
        b.classList.add('active');
        operator.value = b.dataset.op;
        operator.focus();
      });
    });
    operator.addEventListener('change', ()=>{
      opButtons.forEach(x=> x.classList.toggle('active', x.dataset.op === operator.value));
    });

    // History handling (localStorage)
    const HISTORY_KEY = 'calc-history-v1';
    function loadHistory(){
      const raw = localStorage.getItem(HISTORY_KEY);
      return raw ? JSON.parse(raw) : [];
    }
    function saveHistory(h){
      localStorage.setItem(HISTORY_KEY, JSON.stringify(h.slice(0,50)));
    }
    function renderHistory(){
      const h = loadHistory();
      historyList.innerHTML = h.length ? h.map(item => `<div class="history-item">${escapeHtml(item)}</div>`).join('') : '<div style="color:var(--muted)">No history yet</div>';
    }
    clearHistory.addEventListener('click', ()=>{
      localStorage.removeItem(HISTORY_KEY);
      renderHistory();
    });

    function pushHistory(entry){
      const h = loadHistory();
      h.unshift(entry);
      saveHistory(h);
      renderHistory();
    }

    // simple escape
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];}); }

    // animate numeric count (handles non-numeric by simple set)
    function animateResult(toVal){
      if(typeof toVal === 'number' && isFinite(toVal)){
        const start = 0;
        const end = toVal;
        const duration = 540;
        const startTime = performance.now();
        cancelAnimationFrame(animateResult._raf || 0);
        function frame(t){
          const p = Math.min(1, (t - startTime) / duration);
          const eased = 1 - Math.pow(1 - p, 3);
          const cur = start + (end - start) * eased;
          resultValue.textContent = (Math.abs(cur) >= 1e9 ? cur.toExponential(6) : Number(cur.toFixed(8))).replace(/\.?0+$/, '');
          if(p < 1) animateResult._raf = requestAnimationFrame(frame);
        }
        animateResult._raf = requestAnimationFrame(frame);
      } else {
        resultValue.textContent = String(toVal);
      }
      resultBox.style.display = 'block';
    }

    function compute(a,b,op){
      if(!isFinite(a) || !isFinite(b)) return 'Invalid input';
      switch(op){
        case '+': return a + b;
        case '-': return a - b;
        case '*': return a * b;
        case '/': return b === 0 ? 'Error: Division by zero' : a / b;
        default: return 'Invalid operator';
      }
    }

    // Form submit: compute in JS, but still allow fallback (we prevent default to use client-side UI)
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const a = parseFloat(num1.value);
      const b = parseFloat(num2.value);
      const op = operator.value;
      if(Number.isNaN(a) || Number.isNaN(b)){ resultBox.style.display='block'; animateResult('Please enter valid numbers'); return; }
      const res = compute(a,b,op);
      animateResult(res);
      pushHistory(`${a} ${op} ${b} = ${res}`);
      // optionally, also send to server in background (non-blocking) to keep PHP fallback updated
      // navigator.sendBeacon can be used, but not needed for this sample.
    });

    // Keyboard support: Enter triggers submit, keys for operator
    document.addEventListener('keydown', (ev)=>{
      if(ev.key === 'Enter' && (document.activeElement === num1 || document.activeElement === num2 || document.activeElement === operator)){
        ev.preventDefault();
        form.dispatchEvent(new Event('submit', {cancelable:true, bubbles:true}));
      }
      if(['+','-','*','/'].includes(ev.key)){
        operator.value = ev.key;
        opButtons.forEach(x=> x.classList.toggle('active', x.dataset.op === ev.key));
      }
      if(ev.key === 'Escape'){ num1.value=''; num2.value=''; resultBox.style.display='none'; }
    });

    // initialize
    renderHistory();

    // Progressive enhancement: sync dropdown and buttons at load
    operator.dispatchEvent(new Event('change'));
  })();
</script>
</body>
</html>