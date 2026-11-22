(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const KEY = 'muga_cart';
  const rp = n => 'Rp' + n.toLocaleString('id-ID');
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---- Status pesanan: polling otomatis ----
  const track = $('[data-track]');
  if (track) {
    const order = ['baru', 'diproses', 'diantar', 'selesai'];
    const paint = s => {
      const cur = order.indexOf(s);
      $$('li', track).forEach((li, i) => li.className = i < cur ? 'done' : i === cur ? 'now' : '');
    };
    const poll = () => fetch(track.dataset.poll).then(r => r.json()).then(d => {
      if (!d.status) return;
      if (d.status === 'batal' || d.pay !== track.dataset.pay) return location.reload();
      paint(d.status);
    }).catch(() => {});
    setInterval(poll, 5000);
  }

  // ---- Asisten AI (CS) ----
  const cs = $('[data-cs]');
  if (cs) {
    const log = $('[data-cs-log]', cs), form = $('[data-cs-form]', cs), input = $('input', form);
    const add = (txt, cls) => { const p = document.createElement('p'); p.className = cls; p.textContent = txt; log.append(p); log.scrollTop = log.scrollHeight; return p; };
    const ask = async q => {
      add(q, 'cs-me'); const wait = add('Mengetik\u2026', 'cs-bot cs-wait');
      const body = new URLSearchParams({ q, csrf: cs.dataset.csrf });
      try {
        const r = await fetch(cs.dataset.endpoint, { method: 'POST', body });
        const d = await r.json(); wait.className = 'cs-bot'; wait.textContent = d.reply || 'Maaf, ada gangguan.';
      } catch (e) { wait.className = 'cs-bot'; wait.textContent = 'Maaf, koneksi bermasalah. Coba lagi.'; }
      log.scrollTop = log.scrollHeight;
    };
    document.addEventListener('click', e => {
      if (e.target.closest('[data-cs-open]')) { cs.hidden = false; input.focus(); }
      if (e.target.closest('[data-cs-close]')) cs.hidden = true;
      const quick = e.target.closest('.cs-quick button'); if (quick) ask(quick.textContent);
    });
    form.addEventListener('submit', e => { e.preventDefault(); const q = input.value.trim(); if (!q) return; input.value = ''; ask(q); });
  }

  const cartEl = $('[data-cart]');
  if (!cartEl) return;

  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) {}
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(cart)); } catch (e) {} };

  const list = $('[data-cart-items]'), empty = $('[data-cart-empty]'), form = $('[data-order-form]');
  const overlay = $('[data-overlay]'), bar = $('[data-cart-bar]');

  function render() {
    const count = cart.reduce((a, c) => a + c.qty, 0);
    const total = cart.reduce((a, c) => a + c.qty * c.price, 0);
    list.replaceChildren(...cart.map(c => {
      const li = document.createElement('li');
      const nm = document.createElement('span'); nm.className = 'ci-name'; nm.textContent = c.name;
      const pr = document.createElement('span'); pr.className = 'ci-price'; pr.textContent = rp(c.price * c.qty);
      const q = document.createElement('span'); q.className = 'qty';
      const mk = (t, d, label) => { const b = document.createElement('button'); b.type = 'button'; b.textContent = t; b.setAttribute('aria-label', label + ' ' + c.name); b.onclick = () => change(c.id, d); return b; };
      const n = document.createElement('b'); n.textContent = c.qty;
      q.append(mk('−', -1, 'Kurangi'), n, mk('+', 1, 'Tambah'));
      li.append(nm, pr, q); return li;
    }));
    empty.hidden = cart.length > 0;
    form.hidden = cart.length === 0;
    $$('[data-cart-count]').forEach(e => e.textContent = count);
    $('[data-cart-count2]').textContent = count;
    $('[data-cart-total]').textContent = rp(total);
    $('[data-cart-total2]').textContent = rp(total);
    bar.hidden = count === 0;
    $('[data-items-json]').value = JSON.stringify(cart.map(c => ({ id: c.id, qty: c.qty })));
    save();
  }
  function change(id, d) {
    const c = cart.find(x => x.id === id); if (!c) return;
    c.qty += d; if (c.qty < 1) cart = cart.filter(x => x.id !== id);
    if (c.qty > 20) c.qty = 20;
    render();
  }
  function open() { cartEl.hidden = overlay.hidden = false; document.body.classList.add('lock'); requestAnimationFrame(() => cartEl.classList.add('open')); }
  function close() { cartEl.classList.remove('open'); overlay.hidden = true; document.body.classList.remove('lock'); setTimeout(() => cartEl.hidden = true, reduce ? 0 : 280); }

  // Delegasi event: tombol tambah + animasi centang
  document.addEventListener('click', e => {
    const add = e.target.closest('[data-add]');
    if (add) {
      const id = +add.dataset.id, c = cart.find(x => x.id === id);
      if (c) c.qty = Math.min(20, c.qty + 1); else cart.push({ id, name: add.dataset.name, price: +add.dataset.price, qty: 1 });
      render();
      add.classList.add('added'); add.textContent = '✓';
      clearTimeout(add._t); add._t = setTimeout(() => { add.classList.remove('added'); add.textContent = '+'; }, 900);
      $$('[data-cart-count]').forEach(b => { b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump'); });
      return;
    }
    if (e.target.closest('[data-cart-open]')) open();
    if (e.target.closest('[data-cart-close]') || e.target === overlay) close();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !cartEl.hidden) close(); });
  form.addEventListener('submit', () => { const b = $('[data-submit]'); b.disabled = true; b.textContent = 'Mengirim…'; });

  // Menandai kategori yang sedang dilihat pada navigasi menu
  const links = $$('.cat-nav a');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(es => es.forEach(en => {
      if (en.isIntersecting) links.forEach(a => a.classList.toggle('on', a.getAttribute('href') === '#' + en.target.id));
    }), { rootMargin: '-40% 0px -55% 0px' });
    $$('.cat').forEach(s => io.observe(s));
  }
  render();
})();
