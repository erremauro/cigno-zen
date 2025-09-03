(function(document) {
  function onShowSearchClick(event) {
    // Seleziona l'elemento con ID search-bar
    var searchBar = document.getElementById('search-bar');

                // Controlla lo stato attuale della visibilità
    if (searchBar.style.display === 'none' || searchBar.style.display === '') {
      // Se è nascosto, mostralo
      searchBar.style.display = 'block';
    } else {
      // Se è visibile, nascondilo
      searchBar.style.display = 'none';
    }

                // Nasconde l'etichetta del menu
                document.querySelectorAll('.menu-label').forEach(el => el.classList.toggle('hidden'));

    // Ruota l'immagine aggiungendo o rimuovendo la classe
    var menuButton = document.getElementById('menu-button');
    menuButton.classList.toggle('rotated');
  }

  document.addEventListener('DOMContentLoaded', function() {
    var menuButton = document.getElementById('site-menu-toggle');
    // Assicurati di aggiungere l'event listener correttamente
    menuButton.addEventListener('click', onShowSearchClick);
  });
})(document);

/* Generic content toggle with optional smooth scroll on collapse.
 * - Works on any ".js-toggle" wrapper.
 * - Uses data-toggle-target (CSS selector) or aria-controls to find the panel.
 * - Swaps labels: top (collapsed) <-> bottom (expanded).
 * - Toggles .rotated on .more-link-button (the chevron).
 * - Optional data-scroll-target="#id" → scrollIntoView({behavior:'smooth'}) when collapsing.
 * - Keyboard accessible (Enter/Space).
 */
(function () {
  function $(sel, ctx){ return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx){ return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function resolveTarget(el) {
    var sel = el.getAttribute('data-toggle-target') || ('#' + (el.getAttribute('aria-controls') || '').trim());
    if (!sel) return null;
    try { return document.querySelector(sel); } catch(_) { return null; }
  }

  function getScrollTarget(wrapper){
    var sel = (wrapper.getAttribute('data-scroll-target') || '').trim();
    if (!sel) return null;
    try { return document.querySelector(sel); } catch(_) { return null; }
  }

  function setState(wrapper, expanded) {
    var chevron = wrapper.querySelector('.more-link-button');
    var topLbl  = wrapper.querySelector('.more-link-lable-top');
    var botLbl  = wrapper.querySelector('.more-link-lable-bottom');

    wrapper.setAttribute('aria-expanded', String(expanded));
    if (chevron) chevron.classList.toggle('rotated', expanded);
    if (topLbl)  topLbl.classList.toggle('hidden', expanded);
    if (botLbl)  botLbl.classList.toggle('hidden', !expanded);

    var target  = resolveTarget(wrapper);
    if (target) {
      if (expanded) target.removeAttribute('hidden');
      else target.setAttribute('hidden', '');
    }
  }

  function smoothScrollTo(el){
    if (!el || !('scrollIntoView' in el)) return;
    try { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(_) { el.scrollIntoView(true); }
  }

  function toggle(e) {
    if (e) e.preventDefault();
    var w = e.currentTarget;
    var newExpanded = !(w.getAttribute('aria-expanded') === 'true');

    // Apply state to clicked wrapper first
    setState(w, newExpanded);

    // Mirror all wrappers controlling the same target (without causing duplicate scrolls)
    var targetSel = w.getAttribute('data-toggle-target') || ('#' + (w.getAttribute('aria-controls') || '').trim());
    if (targetSel) {
      qsa('.js-toggle').forEach(function(other){
        if (other === w) return;
        var sel = other.getAttribute('data-toggle-target') || ('#' + (other.getAttribute('aria-controls') || '').trim());
        if (sel === targetSel) setState(other, newExpanded);
      });
    }

    // If we just collapsed, and a scroll target is defined, scroll once
    if (!newExpanded) {
      var scrollEl = getScrollTarget(w);
      if (!scrollEl && targetSel) {
        // try to find any sibling toggle with a scroll target for the same panel
        var peer = qsa('.js-toggle').find(function(other){
          if (other === w) return false;
          var sel = other.getAttribute('data-toggle-target') || ('#' + (other.getAttribute('aria-controls') || '').trim());
          return sel === targetSel && other.hasAttribute('data-scroll-target');
        });
        if (peer) scrollEl = getScrollTarget(peer);
      }
      if (scrollEl) smoothScrollTo(scrollEl);
    }
  }

  function onKey(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggle(e);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    qsa('.js-toggle').forEach(function(w){
      // Initialize UI to collapsed
      setState(w, false);
      w.addEventListener('click', toggle);
      w.addEventListener('keydown', onKey);
    });
  });
})();

/**
 * Footnotes plugin – cigno-zen
 * Struttura prevista dai tuoi shortcode:
 * - Riferimento inline: <sup class="fn"><a id="fnref1" href="#fn1">1</a></sup>
 * - Definizione: <p class="footnote" id="fn1"><a class="fnref" href="#fnref1">1</a> ... <a class="backlink" href="#fnref1">↩</a></p>
 * - Wrapper: <div class="footnotes"><h2 id="...">Note</h2><div class="footnotes-content">...</div></div>
 */
(() => {
  // ---------------- Helpers ----------------
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const $ = (sel, root = document) => root.querySelector(sel);
  const cssEscape = (str) => (window.CSS && CSS.escape) ? CSS.escape(str) : (str || '').replace(/[^a-zA-Z0-9_\-]/g, '\\$&');

  const getHashId = (href) => {
    try {
      const hash = href && href.includes('#') ? href.split('#').pop() : '';
      return decodeURIComponent((hash || '').trim());
    } catch { return ''; }
  };

  // ---------------- Cleaners ----------------
  const stripLeadingMarker = (el, id) => {
    const isElementMarker = (node) => {
      if (!node || node.nodeType !== 1) return false;
      const t = (node.textContent || '').trim();
      const looksDigits = /^\[?\(?\d+\)?[\.\:\]]?$/.test(t);

      if (!looksDigits) return false;

      if (node.tagName === 'A') {
        const href = (node.getAttribute('href') || '').trim();
        const cls  = node.className || '';
        if (href.includes('#')) {
          const hash = href.split('#').pop();
          if (hash === ('fnref' + (id.replace(/^fn/, ''))) || /fnref|ref|note/i.test(hash) || /fnref|ref|note/i.test(cls)) return true;
        }
        return false;
      }
      return ['SUP','SPAN','EM','STRONG','B','I'].includes(node.tagName);
    };

    let guard = 3;
    while (el.firstChild && guard-- > 0) {
      if (isElementMarker(el.firstChild)) { el.removeChild(el.firstChild); continue; }
      break;
    }
  };

  const stripBackrefs = (container) => {
    qsa('a', container).forEach(a => {
      const href      = (a.getAttribute('href') || '');
      const cls       = (a.className || '');
      const role      = (a.getAttribute('role') || '');
      const ariaLabel = ((a.getAttribute('aria-label') || a.getAttribute('title') || '')).toLowerCase();
      const text      = (a.textContent || '').replace(/[\uFE0E\uFE0F]/g, '').trim();
      const hasHash   = href.includes('#');
      const hash      = hasHash ? href.split('#').pop() : '';

      const looksFnRef = /(^|\s)fnref(\s|$)/i.test(cls) || /^fnref/i.test(hash);
      const looksBack  = /backlink|backref|return|footnote[-_]?return/i.test(cls) ||
                         role.toLowerCase() === 'doc-backlink' ||
                         ariaLabel.includes('back') || ariaLabel.includes('ritorna') || ariaLabel.includes('torna') ||
                         /↩|↪|↑|⬆/.test(text);

      if ((hasHash && (looksFnRef || looksBack)) || looksBack) a.remove();
    });
  };

  // ---------------- Extract note HTML ----------------
  const getFootnoteHTML = (id) => {
    if (!id) return '';
    // id è tipicamente "fn1"
    let node = document.querySelector(`p.footnote#${cssEscape(id)}`);
    if (!node) {
      const any = document.getElementById(id);
      if (any) node = any.matches('p.footnote') ? any : any.querySelector('p.footnote, p');
    }
    if (!node) return '';

    const base = node.cloneNode(true);
    stripBackrefs(base);
    const baseHTML = base.innerHTML.trim();

    const clone = node.cloneNode(true);
    stripLeadingMarker(clone, id);
    stripBackrefs(clone);
    const html = (clone.textContent || '').trim() ? clone.innerHTML.trim() : baseHTML;

    return html || baseHTML;
  };

  // ---------------- Label del popup ----------------
  const getFootnoteLabel = (anchor, id) => {
    const onlyDigits = (s) => {
      const m = String(s || '').trim().match(/^\s*[\[\(]?(\d+)[\]\)\.:]?\s*$/);
      return m ? m[1] : '';
    };

    let label = onlyDigits(anchor.textContent || anchor.innerText || '');
    if (!label && anchor.closest('sup')) {
      label = onlyDigits(anchor.closest('sup').textContent || '');
    }
    if (!label) {
      const m = (id || '').match(/(\d+)(?!.*\d)/);
      label = m ? m[1] : (anchor.textContent || id || '');
    }
    return label;
  };

  // ---------------- Popup ----------------
  let current = { anchor: null, popup: null, overlay: null };

  const closePopup = () => {
    current.popup?.remove();
    current.overlay?.remove();
    current.anchor?.focus?.({ preventScroll: true });
    current = { anchor: null, popup: null, overlay: null };
    document.removeEventListener('keydown', onKeydown);
    window.removeEventListener('resize', onReflow);
    window.removeEventListener('scroll', onReflow, true);
  };
  const onKeydown = (e) => { if (e.key === 'Escape') closePopup(); };
  const onReflow = () => { if (current.popup && current.anchor) positionPopup(current.popup, current.anchor); };

  const buildPopup = (html, label) => {
    const overlay = document.createElement('div');
    overlay.className = 'footnote-overlay';
    overlay.addEventListener('click', closePopup, { passive: true });

    const popup = document.createElement('div');
    popup.className = 'footnote-popup';
    popup.setAttribute('role', 'dialog');
    popup.setAttribute('aria-modal','true');

    const titleId = `footnote-popup-title-${Date.now().toString(36)}-${Math.random().toString(36).slice(2,7)}`;
    popup.setAttribute('aria-labelledby', titleId);

    const btn = document.createElement('button');
    btn.className = 'footnote-popup-close';
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Chiudi nota');
    btn.textContent = '×';
    btn.addEventListener('click', closePopup);

    const title = document.createElement('div');
    title.className = 'footnote-popup-title';
    title.id = titleId;
    title.textContent = `${label}`;

    const content = document.createElement('div');
    content.className = 'footnote-popup-content';
    content.innerHTML = html;

    popup.appendChild(btn);
    popup.appendChild(title);
    popup.appendChild(content);

    return { popup, overlay };
  };

  const MOBILE_BREAKPOINT = 680;

  const positionPopup = (popup, anchor) => {
    popup.style.minWidth = '240px';
    popup.style.visibility = 'hidden';
    popup.style.left = '0px';
    popup.style.top = '0px';
    document.body.appendChild(popup);

    const rect = anchor.getBoundingClientRect();
    const vw = document.documentElement.clientWidth;
    const vh = document.documentElement.clientHeight;
    const margin = 8;

    const pr = popup.getBoundingClientRect();
    let top = window.scrollY + rect.bottom + margin;
    if (rect.bottom + pr.height + margin > vh) {
      top = window.scrollY + rect.top - pr.height - margin;
      if (top < window.scrollY + margin) top = window.scrollY + margin;
    }
    let left = window.scrollX + rect.left + (rect.width - pr.width) / 2;
    left = Math.max(window.scrollX + margin, Math.min(left, window.scrollX + vw - pr.width - margin));

    popup.style.left = `${left}px`;
    popup.style.top  = `${top}px`;
    popup.style.visibility = 'visible';

    if (vw <= MOBILE_BREAKPOINT) {
      popup.style.right = `${left}px`; // centratura mobile grezza
    }
  };

  const openFootnote = (anchor, id) => {
    const html = getFootnoteHTML(id);
    if (!html) return;
    const label = getFootnoteLabel(anchor, id);

    closePopup();
    const { popup, overlay } = buildPopup(html, label);
    document.body.appendChild(overlay);
    current = { anchor, popup, overlay };
    positionPopup(popup, anchor);
    document.addEventListener('keydown', onKeydown);
    window.addEventListener('resize', onReflow);
    window.addEventListener('scroll', onReflow, true);
    (popup.querySelector('button') || popup).focus({ preventScroll: true });
  };

  // ---------------- Delegation riferimenti ----------------
  const initFootnotePopups = () => {
    document.addEventListener('click', (e) => {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;

      // Riferimenti inline: <sup class="fn"><a ... href="#fnX">X</a></sup>
      const a = e.target.closest('sup.fn a[href], a.footnote-ref[href], sup a[href^="#fn"]');
      if (!a) return;

      const id = getHashId(a.getAttribute('href') || '');
      if (!id) return;

      // Apri popup solo se l'ancora punta ad una definizione esistente
      const hasTarget = document.querySelector(`p.footnote#${cssEscape(id)}`) || document.getElementById(id);
      if (!hasTarget) return;

      e.preventDefault();
      openFootnote(a, id);
    });
  };

  // ---------------- Toggle blocchi footnotes ----------------
  const createChevron = () => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('width', '32');
    svg.setAttribute('height', '32');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.flex = '0 0 auto';
    svg.style.transition = 'transform .2s ease';
    svg.classList.add('footnotes-chevron');
    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d','M6.23 8.97a1 1 0 0 1 1.41 0L12 13.34l4.36-4.37a1 1 0 1 1 1.41 1.42l-5.06 5.06a1 1 0 0 1-1.41 0L6.23 10.4a1 1 0 0 1 0-1.42z');
    p.setAttribute('fill','currentColor');
    svg.appendChild(p);
    return svg;
  }

  const initFootnotesToggleFor = (wrapper) => {
    if (!wrapper || wrapper.__czFootnotesReady) return;

    // heading: primo elemento con id al livello diretto, altrimenti il primo h2..h6
    let heading = wrapper.querySelector(':scope > [id]');
    if (!heading || !/^H[2-6]$/.test(heading.tagName)) {
      heading = wrapper.querySelector(':scope > h2, :scope > h3, :scope > h4, :scope > h5, :scope > h6');
    }
    const content = wrapper.querySelector(':scope > .footnotes-content');
    if (!heading || !content) return;

    // Accessibilità
    heading.setAttribute('role', 'button');
    heading.setAttribute('tabindex', '0');
    heading.setAttribute('aria-expanded', 'false');

    // Chevron
    if (!heading.querySelector('.footnotes-chevron')) {
      const chev = createChevron();
      // Inserisci a destra del testo
      const wrap = document.createElement('span');
      wrap.className = 'footnotes-toggle-text';
      while (heading.firstChild) wrap.appendChild(heading.firstChild);
      heading.appendChild(chev);
      heading.appendChild(wrap);
      heading.style.display = 'inline-flex';
      heading.style.alignItems = 'center';
      heading.style.gap = '.5rem';
      // Stato iniziale: chiuso
      chev.style.transform = 'rotate(-90deg)';
    }

    // Stato iniziale: collassato (contenuto nascosto)
    content.hidden = true;

    const setOpen = (open) => {
      const chev = heading.querySelector('.footnotes-chevron');
      heading.setAttribute('aria-expanded', open ? 'true' : 'false');
      content.hidden = !open;
      if (chev) chev.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
    };

    const handler = (e) => {
      e.preventDefault();
      setOpen(content.hidden); // se hidden -> apri, altrimenti chiudi
    };

    heading.addEventListener('click', handler);
    heading.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handler(e); }
    });

    wrapper.__czFootnotesReady = true;
  };

  const initFootnotesToggle = (root = document) => {
    qsa('div.footnotes', root).forEach(initFootnotesToggleFor);
  };

  // ---------------- Init ----------------
  const init = () => {
    initFootnotesToggle();
    initFootnotePopups();
  };
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);

  // Osserva DOM dinamico (es. blocchi caricati in AJAX)
  const mo = new MutationObserver((muts) => {
    for (const m of muts) {
      m.addedNodes && m.addedNodes.forEach(n => {
        if (!n || n.nodeType !== 1) return;
        if (typeof n.querySelectorAll !== 'function') return;
        if (n.matches && n.matches('div.footnotes')) initFootnotesToggleFor(n);
        else initFootnotesToggle(n);
      });
    }
  });
  try { mo.observe(document.documentElement, { childList: true, subtree: true }); } catch(_) {}
})();


/* ========== THEME TOGGLE ========== */
(function(){
  var KEY='cz-theme';
  var root=document.documentElement;
  var btn=document.getElementById('theme-toggle');
  if(!btn) return;

  function setTheme(t){
    root.setAttribute('data-theme', t);
    try{ localStorage.setItem(KEY, t); }catch(e){}
    btn.setAttribute('aria-pressed', t==='dark');
  }

  // init
  var saved=localStorage.getItem(KEY);
  if(saved){ setTheme(saved); }
  else {
    var prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
    setTheme(prefersLight ? 'light' : 'dark');
  }

  var DUR = 720; // ms (matcha la transition .7s)
  var timer;

  btn.addEventListener('click', function(){
    if (btn.classList.contains('animating')) return; // evita doppio click
    var current = root.getAttribute('data-theme') || 'dark';
    var next = current === 'dark' ? 'light' : 'dark';
    var dirClass = next === 'dark' ? 'anim-to-dark' : 'anim-to-light';

    btn.classList.add('animating', dirClass);

    // 1 frame per applicare la classe d’animazione “da destra”
    requestAnimationFrame(function(){
      // cambia tema: le regole sopra faranno entrare da destra e uscire a sinistra
      setTheme(next);

      clearTimeout(timer);
      timer = setTimeout(function(){
        btn.classList.remove('animating', 'anim-to-dark', 'anim-to-light');
      }, DUR);
    });
  });
})();

/* ===== COLLAPSABLE CONTENT — default: OPEN ===== */

(function () {
  function rootOf(ctx){ return (ctx && typeof ctx.querySelectorAll === 'function') ? ctx : document; }
  function $(sel, ctx){ return rootOf(ctx).querySelector(sel); }
  function qsa(sel, ctx){ return Array.from(rootOf(ctx).querySelectorAll(sel)); }
  const isButtonLike = (el) => el && (el.tagName === 'BUTTON' || el.getAttribute('role') === 'button');

  const createChevron = () => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('width', '48');
    svg.setAttribute('height', '48');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.flex = '0 0 auto';
    svg.style.transition = 'transform .2s ease';
    svg.classList.add('collapsable-chevron');
    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d','M6.23 8.97a1 1 0 0 1 1.41 0L12 13.34l4.36-4.37a1 1 0 1 1 1.41 1.42l-5.06 5.06a1 1 0 0 1-1.41 0L6.23 10.4a1 1 0 0 1 0-1.42z');
    p.setAttribute('fill','currentColor');
    svg.appendChild(p);
    return svg;
  };

  const setOpen = (section, open) => {
    const toggle  = $('.collapsable-toggle', section);
    const content = $('.collapsable-content', section);
    if (!toggle || !content) return;

    const chev = $('.collapsable-chevron', toggle);
    const wasOpen = section.classList.contains('is-open');
    if (open === wasOpen) return;

    section.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    content.setAttribute('aria-hidden', open ? 'false' : 'true');
    content.style.display = open ? '' : 'none';
    if (chev) chev.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
  };

  const ensureOperable = (el) => {
    if (!isButtonLike(el)) { el.setAttribute('role','button'); el.setAttribute('tabindex','0'); }
    el.style.cursor = 'pointer';
  };

  const ensureId = (el, prefix='collapsable-content-') => {
    if (!el.id) el.id = prefix + Math.random().toString(36).slice(2,9);
    return el.id;
  };

  const initSection = (section) => {
    if (!section || section.__collapsableReady) return;
    const toggle  = $('.collapsable-toggle', section);
    const content = $('.collapsable-content', section);
    if (!toggle || !content) return;

    // Inject chevron (rotation set by setOpen)
    if (!$('.collapsable-chevron', toggle)) {
      const chev = createChevron();
      const wrap = document.createElement('span');
      wrap.className = 'collapsable-toggle-text';
      while (toggle.firstChild) wrap.appendChild(toggle.firstChild);
      toggle.appendChild(wrap);
      toggle.insertBefore(chev, wrap);
      toggle.style.display = 'inline-flex';
      toggle.style.alignItems = 'center';
      toggle.style.gap = '.5rem';
    }

    ensureOperable(toggle);
    ensureId(content);
    toggle.setAttribute('aria-controls', content.id);

    // === DEFAULT OPEN ===
    // Se presente data-initial="closed" parte chiuso, altrimenti aperto.
    const initial = (section.getAttribute('data-initial') || '').toLowerCase();
    setOpen(section, initial === 'closed' ? false : true);

    const handler = (e)=>{ e.preventDefault(); setOpen(section, !section.classList.contains('is-open')); };
    toggle.addEventListener('click', handler);
    toggle.addEventListener('keydown', (e)=>{ if (e.key==='Enter'||e.key===' ') { e.preventDefault(); handler(e); } });

    section.__collapsableReady = true;
  };

  const initAll = (root) => { qsa('.collapsable-section', root).forEach(initSection); };

  if (document.readyState !== 'loading') initAll();
  else document.addEventListener('DOMContentLoaded', initAll);

  // Guarded MutationObserver (skip nodes without querySelectorAll)
  const mo = new MutationObserver((muts) => {
    for (const m of muts) {
      m.addedNodes && m.addedNodes.forEach(n => {
        if (!n || n.nodeType !== 1) return;
        if (typeof n.querySelectorAll !== 'function') return;
        if (n.matches && n.matches('.collapsable-section')) initSection(n);
        else initAll(n);
      });
    }
  });
  try { mo.observe(document.documentElement, { childList: true, subtree: true }); } catch(_) {}
})();

