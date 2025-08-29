(function(document) {
  console.log("[cigno-zen] script loaded!");

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
 * Footnotes Plugins
 */
(() => {
  // ---------- Small helpers ----------
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const $ = (sel, root = document) => root.querySelector(sel);
  const cssEscape = (str) => (window.CSS && CSS.escape) ? CSS.escape(str) : str.replace(/[^a-zA-Z0-9_\-]/g, '\\$&');

  const getHashId = (href) => {
    try {
      const hash = href && href.includes('#') ? href.split('#').pop() : '';
      return decodeURIComponent((hash || '').trim());
    } catch { return ''; }
  };

  // ---------- Footnote extraction & cleanup ----------
  const stripLeadingMarker = (el, id) => {
  const isElementMarker = (node) => {
    if (node.nodeType !== 1) return false;
    const t = (node.textContent || '').trim();
    const digitsLike = /^\[?\(?\d+\)?[\.\:\]]?$/.test(t);

    if (!digitsLike) return false;

    if (node.tagName === 'A') {
      const href = (node.getAttribute('href') || '').trim();
      const cls  = node.className || '';
      // treat as marker only if it clearly points to a ref for THIS footnote
      if (href.includes('#')) {
        const hash = href.split('#').pop();
        if (hash === id || /fnref|ref|note/i.test(hash) || /fnref|ref|note/i.test(cls)) return true;
      }
      return false;
    }

    // Typical wrappers for indices
    return ['SUP','SPAN','EM','STRONG'].includes(node.tagName);
  };

  // Strip up to a couple of leading element markers
  let guard = 3;
  while (el.firstChild && guard-- > 0) {
    const n = el.firstChild;
    if (isElementMarker(n)) { el.removeChild(n); continue; }
    break;
  }
};

  const stripBackrefs = (container) => {
  const anchors = Array.from(container.querySelectorAll('a'));
  anchors.forEach(a => {
    const href      = (a.getAttribute('href') || '');
    const cls       = (a.className || '');
    const role      = (a.getAttribute('role') || '');
    const ariaLabel = ((a.getAttribute('aria-label') || a.getAttribute('title') || '')).toLowerCase();
    // Normalize text (remove variation selectors like U+FE0E/U+FE0F)
    const text = (a.textContent || '').replace(/[\uFE0E\uFE0F]/g, '').trim();

    const hasHash     = href.includes('#');
    const hashPart    = hasHash ? href.split('#').pop() : '';
    const looksFnRef  = /fnref|footnote.*ref|reversefootnote/i.test(cls) ||
                        /fnref|footnote.*ref|back|return/i.test(hashPart);
    const looksBack   = /backref|return|footnote[-_]?return/i.test(cls) ||
                        role.toLowerCase() === 'doc-backlink' ||
                        ariaLabel.includes('back') || ariaLabel.includes('ritorna') || ariaLabel.includes('torna');
    const arrowChar   = /↩|↪|↑|⬆/.test(text);

    if ((hasHash && (looksFnRef || looksBack)) || arrowChar) {
      a.remove();
    }
  });
};

  const getFootnoteHTML = (id) => {
  let node = document.querySelector(`p.footnote#${cssEscape(id)}`);
  if (!node) {
    const any = document.getElementById(id);
    if (any) node = any.matches('p.footnote') ? any : any.querySelector('p.footnote, p');
  }
  if (!node) return '';

  // Version with only backrefs removed (safe fallback)
  const base = node.cloneNode(true);
  stripBackrefs(base);
  const baseHTML = base.innerHTML.trim();

  // Version with marker + backrefs removed (preferred)
  const clone = node.cloneNode(true);
  stripLeadingMarker(clone, id);
  stripBackrefs(clone);

  // If we accidentally stripped too much (empty), fall back
  const html = clone.textContent.trim() ? clone.innerHTML.trim() : baseHTML;
  return html || baseHTML;
};

  // ---------- Label/Title for popup ----------
  const getFootnoteLabel = (anchor, id) => {
    const cleanDigits = (s) => {
      const t = (s || '').trim();
      // If it's like "1", "[1]", "1.", "1)" -> return only digits; else return t as-is
      const m = t.match(/^\s*[\[\(]?(\d+)[\]\)\.:]?\s*$/);
      return m ? m[1] : t;
    };

    let label = cleanDigits(anchor.textContent || anchor.innerText || '');

    if ((!label || !/^\d+$/.test(label)) && anchor.closest('sup')) {
      label = cleanDigits(anchor.closest('sup').textContent || '');
    }
    if (!label || !/^\d+$/.test(label)) {
      const m = (id || '').match(/(\d+)(?!.*\d)/); // last digits in id
      label = m ? m[1] : (label || id || '');
    }
    return label;
  };

  // ---------- Popup machinery ----------
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

    const titleId = `footnote-popup-title-${Date.now().toString(36)}-${Math.random().toString(36).slice(2,7)}`;
    popup.setAttribute('aria-labelledby', titleId);
    popup.setAttribute('aria-modal','true')

    const btn = document.createElement('button');
    btn.className = 'footnote-popup-close';
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Chiudi nota');
    btn.innerHTML = '×';
    btn.addEventListener('click', closePopup);

    const title = document.createElement('div');
    title.className = 'footnote-popup-title';
    title.id = titleId;
    title.textContent = `${label}`; // change to just `label` if you want only the number

    const content = document.createElement('div');
    content.className = 'footnote-popup-content';
    content.innerHTML = html;

    // Order doesn't matter if close button is absolutely positioned
    popup.appendChild(btn);
    popup.appendChild(title);
    popup.appendChild(content);

    return { popup, overlay };
  };

  const MOBILE_BREAKPOINT = 680;

  const positionPopup = (popup, anchor) => {
    popup.style.minWidth = "240px";
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

    // Center the pop-up on mobile
    if (vw <= MOBILE_BREAKPOINT) {
      popup.style.right = `${left}px`;
    }
  };

  const openFootnote = (anchor, id) => {
    const html = getFootnoteHTML(id);
    if (!html) return; // fallback to default behavior if not found
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

  // ---------- Footnote refs: event delegation ----------
  const initFootnotePopups = () => {
    document.addEventListener('click', (e) => {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;

      const a = e.target.closest('sup a[href], a.footnote-ref[href]');
      if (!a) return;

      const id = getHashId(a.getAttribute('href') || '');
      if (!id) return;

      const hasTarget = document.querySelector(`p.footnote#${cssEscape(id)}`) || document.getElementById(id);
      if (!hasTarget) return; // let normal anchor behavior happen

      e.preventDefault();
      openFootnote(a, id);
    });
  };

  // ---------- Collapsible footnotes block ----------
  const initFootnotesToggle = () => {
    const container = $('div.footnotes');
    const toggle = $('#footnotes-toggle'); // <h2 id="footnotes-toggle">Note</h2>
    if (!toggle || !container) return;

    // Hide on load
    container.classList.add('hidden');

    // Make the header act like a button
    toggle.setAttribute('role', 'button');
    toggle.setAttribute('tabindex', '0');
    toggle.setAttribute('aria-expanded', 'false');

    // Inject chevron icon if missing
    let chev = $('.footnotes-toggle-chevron', toggle);
    if (!chev) {
      chev = document.createElement('img');
      chev.className = 'footnotes-toggle-chevron';
      chev.src = '/wp-content/themes/cigno-zen/assets/images/chevron-down.svg';
      chev.width = "32";
      chev.height = "32";
      chev.alt = '';
      chev.setAttribute('aria-hidden', 'true');
      // Initial rotation to point RIGHT (closed state)
      chev.style.transform = 'rotate(-90deg)';
      // You can style spacing in CSS; minimal inline margin here for safety
      chev.style.marginLeft = '0.5rem';
      toggle.appendChild(chev);
    } else {
      // Ensure initial closed rotation
      chev.style.transform = 'rotate(-90deg)';
    }

    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        container.classList.remove('hidden');
        chev.style.transform = ''; // points DOWN (0deg)
      } else {
        container.classList.add('hidden');
        chev.style.transform = 'rotate(-90deg)'; // points RIGHT
      }
    };

    toggle.addEventListener('click', () => setOpen(container.classList.contains('hidden')));
    toggle.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setOpen(container.classList.contains('hidden'));
      }
    });
  };

  // ---------- Init ----------
  const init = () => {
    initFootnotesToggle();
    initFootnotePopups();
  };
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
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

