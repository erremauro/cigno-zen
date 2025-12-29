(function (document) {
  function onShowSearchClick(event) {
    // Seleziona l'elemento con ID search-bar
    var searchBar = document.getElementById("search-bar");

    // Controlla lo stato attuale della visibilità
    if (searchBar.style.display === "none" || searchBar.style.display === "") {
      // Se è nascosto, mostralo
      searchBar.style.display = "block";
    } else {
      // Se è visibile, nascondilo
      searchBar.style.display = "none";
    }

    // Nasconde l'etichetta del menu
    document
      .querySelectorAll(".menu-label")
      .forEach((el) => el.classList.toggle("hidden"));

    // Ruota l'immagine aggiungendo o rimuovendo la classe
    var menuButton = document.getElementById("menu-button");
    menuButton.classList.toggle("rotated");
  }

  document.addEventListener("DOMContentLoaded", function () {
    var menuButton = document.getElementById("site-menu-toggle");
    // Assicurati di aggiungere l'event listener correttamente
    menuButton.addEventListener("click", onShowSearchClick);
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
  function $(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }
  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function resolveTarget(el) {
    var sel =
      el.getAttribute("data-toggle-target") ||
      "#" + (el.getAttribute("aria-controls") || "").trim();
    if (!sel) return null;
    try {
      return document.querySelector(sel);
    } catch (_) {
      return null;
    }
  }

  function getScrollTarget(wrapper) {
    var sel = (wrapper.getAttribute("data-scroll-target") || "").trim();
    if (!sel) return null;
    try {
      return document.querySelector(sel);
    } catch (_) {
      return null;
    }
  }

  function setState(wrapper, expanded) {
    var chevron = wrapper.querySelector(".more-link-button");
    var topLbl = wrapper.querySelector(".more-link-lable-top");
    var botLbl = wrapper.querySelector(".more-link-lable-bottom");

    wrapper.setAttribute("aria-expanded", String(expanded));
    if (chevron) chevron.classList.toggle("rotated", expanded);
    if (topLbl) topLbl.classList.toggle("hidden", expanded);
    if (botLbl) botLbl.classList.toggle("hidden", !expanded);

    var target = resolveTarget(wrapper);
    if (target) {
      if (expanded) target.removeAttribute("hidden");
      else target.setAttribute("hidden", "");
    }
  }

  function smoothScrollTo(el) {
    if (!el || !("scrollIntoView" in el)) return;
    try {
      el.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch (_) {
      el.scrollIntoView(true);
    }
  }

  function toggle(e) {
    if (e) e.preventDefault();
    var w = e.currentTarget;
    var newExpanded = !(w.getAttribute("aria-expanded") === "true");

    // Apply state to clicked wrapper first
    setState(w, newExpanded);

    // Mirror all wrappers controlling the same target (without causing duplicate scrolls)
    var targetSel =
      w.getAttribute("data-toggle-target") ||
      "#" + (w.getAttribute("aria-controls") || "").trim();
    if (targetSel) {
      qsa(".js-toggle").forEach(function (other) {
        if (other === w) return;
        var sel =
          other.getAttribute("data-toggle-target") ||
          "#" + (other.getAttribute("aria-controls") || "").trim();
        if (sel === targetSel) setState(other, newExpanded);
      });
    }

    // If we just collapsed, and a scroll target is defined, scroll once
    if (!newExpanded) {
      var scrollEl = getScrollTarget(w);
      if (!scrollEl && targetSel) {
        // try to find any sibling toggle with a scroll target for the same panel
        var peer = qsa(".js-toggle").find(function (other) {
          if (other === w) return false;
          var sel =
            other.getAttribute("data-toggle-target") ||
            "#" + (other.getAttribute("aria-controls") || "").trim();
          return sel === targetSel && other.hasAttribute("data-scroll-target");
        });
        if (peer) scrollEl = getScrollTarget(peer);
      }
      if (scrollEl) smoothScrollTo(scrollEl);
    }
  }

  function onKey(e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      toggle(e);
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    qsa(".js-toggle").forEach(function (w) {
      // Initialize UI to collapsed
      setState(w, false);
      w.addEventListener("click", toggle);
      w.addEventListener("keydown", onKey);
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
  const cssEscape = (str) =>
    window.CSS && CSS.escape
      ? CSS.escape(str)
      : (str || "").replace(/[^a-zA-Z0-9_\-]/g, "\\$&");

  const getHashId = (href) => {
    try {
      const hash = href && href.includes("#") ? href.split("#").pop() : "";
      return decodeURIComponent((hash || "").trim());
    } catch {
      return "";
    }
  };

  // Regola la soglia per la paginazione delle note (numero di caratteri)
  const FOOTNOTE_PAGINATION_THRESHOLD = 256;

  // ---------------- Cleaners ----------------
  const stripLeadingMarker = (el, id) => {
    const isElementMarker = (node) => {
      if (!node || node.nodeType !== 1) return false;
      const t = (node.textContent || "").trim();
      const looksDigits = /^\[?\(?\d+\)?[\.\:\]]?$/.test(t);
      if (!looksDigits) return false;

      if (node.tagName === "A") {
        const href = (node.getAttribute("href") || "").trim();
        const cls = node.className || "";
        if (href.includes("#")) {
          const hash = href.split("#").pop();
          if (
            hash === "fnref" + id.replace(/^fn/, "") ||
            /fnref|ref|note/i.test(hash) ||
            /fnref|ref|note/i.test(cls)
          )
            return true;
        }
        return false;
      }
      return ["SUP", "SPAN", "EM", "STRONG", "B", "I"].includes(node.tagName);
    };

    let guard = 3;
    while (el.firstChild && guard-- > 0) {
      if (isElementMarker(el.firstChild)) {
        el.removeChild(el.firstChild);
        continue;
      }
      break;
    }
  };

  const stripBackrefs = (container) => {
    qsa("a", container).forEach((a) => {
      const href = a.getAttribute("href") || "";
      const cls = a.className || "";
      const role = a.getAttribute("role") || "";
      const ariaLabel = (
        a.getAttribute("aria-label") ||
        a.getAttribute("title") ||
        ""
      ).toLowerCase();
      const text = (a.textContent || "").replace(/[\uFE0E\uFE0F]/g, "").trim();
      const hasHash = href.includes("#");
      const hash = hasHash ? href.split("#").pop() : "";

      const looksFnRef = /(^|\s)fnref(\s|$)/i.test(cls) || /^fnref/i.test(hash);
      const looksBack =
        /backlink|backref|return|footnote[-_]?return/i.test(cls) ||
        role.toLowerCase() === "doc-backlink" ||
        ariaLabel.includes("back") ||
        ariaLabel.includes("ritorna") ||
        ariaLabel.includes("torna") ||
        /↩|↪|↑|⬆/.test(text);

      if ((hasHash && (looksFnRef || looksBack)) || looksBack) a.remove();
    });
  };

  // ---------------- Extract note HTML ----------------
  const getFootnoteHTML = (id) => {
    if (!id) return "";
    let node = document.querySelector(`p.footnote#${cssEscape(id)}`);
    if (!node) {
      const any = document.getElementById(id);
      if (any)
        node = any.matches("p.footnote")
          ? any
          : any.querySelector("p.footnote, p");
    }
    if (!node) return "";

    const base = node.cloneNode(true);
    stripBackrefs(base);
    const baseHTML = base.innerHTML.trim();

    const clone = node.cloneNode(true);
    stripLeadingMarker(clone, id);
    stripBackrefs(clone);
    const html = (clone.textContent || "").trim()
      ? clone.innerHTML.trim()
      : baseHTML;

    return html || baseHTML;
  };

  // ---------------- Label del popup ----------------
  const getFootnoteLabel = (anchor, id) => {
    const onlyDigits = (s) => {
      const m = String(s || "")
        .trim()
        .match(/^\s*[\[\(]?(\d+)[\]\)\.:]?\s*$/);
      return m ? m[1] : "";
    };

    let label = onlyDigits(anchor.textContent || anchor.innerText || "");
    if (!label && anchor.closest("sup")) {
      label = onlyDigits(anchor.closest("sup").textContent || "");
    }
    if (!label) {
      const m = (id || "").match(/(\d+)(?!.*\d)/);
      label = m ? m[1] : anchor.textContent || id || "";
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
    document.removeEventListener("keydown", onKeydown);
    window.removeEventListener("resize", onReflow);
    window.removeEventListener("scroll", onReflow, true);
  };

  const onKeydown = (e) => {
    if (e.key === "Escape") closePopup();
  };
  const onReflow = () => {
    if (current.popup && current.anchor)
      positionPopup(current.popup, current.anchor);
  };

  const buildPopup = (html, label) => {
    const overlay = document.createElement("div");
    overlay.className = "footnote-overlay";
    overlay.addEventListener("click", closePopup, { passive: true });

    const popup = document.createElement("div");
    popup.className = "footnote-popup";
    popup.setAttribute("role", "dialog");
    popup.setAttribute("aria-modal", "true");

    const titleId = `footnote-popup-title-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
    popup.setAttribute("aria-labelledby", titleId);

    const btn = document.createElement("button");
    btn.className = "footnote-popup-close";
    btn.type = "button";
    btn.setAttribute("aria-label", "Chiudi nota");
    btn.textContent = "×";
    btn.addEventListener("click", closePopup);

    const title = document.createElement("div");
    title.className = "footnote-popup-title";
    title.id = titleId;
    title.textContent = `${label}`;

    const content = document.createElement("div");
    content.className = "footnote-popup-content";
    const pages = paginateFootnoteHTML(html, FOOTNOTE_PAGINATION_THRESHOLD);
    let pagination = null;
    if (pages.length > 1) {
      content.classList.add("footnote-popup-content--paged");
      content.style.overflow = "hidden";
      content.style.maxHeight = "none";
      pages.forEach((page, index) => {
        if (index !== 0) page.hidden = true;
        content.appendChild(page);
      });
      pagination = buildPaginationControls(pages);
    } else {
      content.innerHTML = html;
    }

    popup.appendChild(btn);
    popup.appendChild(title);
    popup.appendChild(content);
    if (pagination) popup.appendChild(pagination);

    return { popup, overlay };
  };

  const paginateFootnoteHTML = (html, threshold) => {
    const container = document.createElement("div");
    container.innerHTML = html || "";
    const nodes = Array.from(container.childNodes);
    const pages = [];
    let page = document.createElement("div");
    page.className = "footnote-page";
    let remaining = threshold;

    const getNodeTextLength = (node) => (node.textContent || "").length;

    const splitTextNode = (node, maxChars) => {
      if (maxChars <= 0) return [null, node];
      const text = node.nodeValue || "";
      if (text.length <= maxChars) return [node, null];

      let cut = maxChars;
      while (cut > 0 && !/\s/.test(text.charAt(cut - 1))) cut -= 1;
      if (cut === 0) cut = maxChars;

      const head = document.createTextNode(text.slice(0, cut));
      const tailText = text.slice(cut).replace(/^\s+/, "");
      const tail = tailText ? document.createTextNode(tailText) : null;
      return [head, tail];
    };

    const splitNodeByChars = (node, maxChars) => {
      if (maxChars <= 0) return [null, node];
      if (node.nodeType === 3) return splitTextNode(node, maxChars);
      if (node.nodeType !== 1) return [node, null];

      const head = node.cloneNode(false);
      const tail = node.cloneNode(false);
      let remainingChars = maxChars;
      const children = Array.from(node.childNodes);

      for (let i = 0; i < children.length; i += 1) {
        const child = children[i];
        const childLen = getNodeTextLength(child);

        if (childLen <= remainingChars) {
          head.appendChild(child);
          remainingChars -= childLen;
          continue;
        }

        if (remainingChars > 0) {
          const [childHead, childTail] = splitNodeByChars(
            child,
            remainingChars,
          );
          if (childHead) head.appendChild(childHead);
          if (childTail) tail.appendChild(childTail);
        } else {
          tail.appendChild(child);
        }

        for (let j = i + 1; j < children.length; j += 1) {
          tail.appendChild(children[j]);
        }
        break;
      }

      return [
        head.childNodes.length ? head : null,
        tail.childNodes.length ? tail : null,
      ];
    };

    const pushPage = () => {
      if (page.childNodes.length) pages.push(page);
      page = document.createElement("div");
      page.className = "footnote-page";
      remaining = threshold;
    };

    let idx = 0;
    while (idx < nodes.length) {
      let node = nodes[idx];
      const textLen = getNodeTextLength(node);

      if (textLen <= remaining || textLen === 0) {
        page.appendChild(node);
        remaining -= textLen;
        idx += 1;
        continue;
      }

      const [head, tail] = splitNodeByChars(node, remaining);
      if (head) page.appendChild(head);
      pushPage();
      if (tail) {
        node = tail;
      } else {
        idx += 1;
        continue;
      }

      while (node) {
        const len = getNodeTextLength(node);
        if (len <= remaining || len === 0) {
          page.appendChild(node);
          remaining -= len;
          node = null;
          idx += 1;
          break;
        }
        const [h, t] = splitNodeByChars(node, remaining);
        if (h) page.appendChild(h);
        pushPage();
        node = t;
      }
    }
    if (page.childNodes.length) pages.push(page);

    return pages.length ? pages : [page];
  };

  const buildPaginationControls = (pages) => {
    let index = 0;
    const nav = document.createElement("div");
    nav.className = "footnote-pagination";

    const prev = document.createElement("button");
    prev.type = "button";
    prev.className = "footnote-pagination-prev";
    prev.setAttribute("aria-label", "Pagina precedente");
    prev.textContent = "←";

    const next = document.createElement("button");
    next.type = "button";
    next.className = "footnote-pagination-next";
    next.setAttribute("aria-label", "Pagina successiva");
    next.textContent = "→";

    const indicator = document.createElement("span");
    indicator.className = "footnote-pagination-indicator";

    const update = () => {
      pages.forEach((page, i) => {
        page.hidden = i !== index;
      });
      prev.disabled = index === 0;
      next.disabled = index === pages.length - 1;
      indicator.textContent = `${index + 1}/${pages.length}`;
    };

    prev.addEventListener("click", () => {
      if (index > 0) {
        index -= 1;
        update();
      }
    });

    next.addEventListener("click", () => {
      if (index < pages.length - 1) {
        index += 1;
        update();
      }
    });

    nav.appendChild(prev);
    nav.appendChild(indicator);
    nav.appendChild(next);
    update();

    return nav;
  };

  const MOBILE_BREAKPOINT = 680;

  const positionPopup = (popup, anchor) => {
    popup.style.minWidth = "240px";
    popup.style.visibility = "hidden";
    popup.style.left = "0px";
    popup.style.top = "0px";
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
    left = Math.max(
      window.scrollX + margin,
      Math.min(left, window.scrollX + vw - pr.width - margin),
    );

    popup.style.left = `${left}px`;
    popup.style.top = `${top}px`;
    popup.style.visibility = "visible";

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
    document.addEventListener("keydown", onKeydown);
    window.addEventListener("resize", onReflow);
    window.addEventListener("scroll", onReflow, true);
    (popup.querySelector("button") || popup).focus({ preventScroll: true });
  };

  // ---------------- Delegation riferimenti ----------------
  const initFootnotePopups = () => {
    document.addEventListener("click", (e) => {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;

      const a = e.target.closest(
        'sup.fn a[href], a.footnote-ref[href], sup a[href^="#fn"]',
      );
      if (!a) return;

      const id = getHashId(a.getAttribute("href") || "");
      if (!id) return;

      const hasTarget =
        document.querySelector(`p.footnote#${cssEscape(id)}`) ||
        document.getElementById(id);
      if (!hasTarget) return;

      e.preventDefault();
      openFootnote(a, id);
    });
  };

  // ---------------- Toggle blocchi footnotes ----------------
  const createChevron = () => {
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", "0 0 24 24");
    svg.setAttribute("width", "32");
    svg.setAttribute("height", "32");
    svg.setAttribute("aria-hidden", "true");
    svg.style.flex = "0 0 auto";
    svg.style.transition = "transform .2s ease";
    svg.classList.add("footnotes-chevron");
    const p = document.createElementNS("http://www.w3.org/2000/svg", "path");
    p.setAttribute(
      "d",
      "M6.23 8.97a1 1 0 0 1 1.41 0L12 13.34l4.36-4.37a1 1 0 1 1 1.41 1.42l-5.06 5.06a1 1 0 0 1-1.41 0L6.23 10.4a1 1 0 0 1 0-1.42z",
    );
    p.setAttribute("fill", "currentColor");
    svg.appendChild(p);
    return svg;
  };

  const initFootnotesToggleFor = (wrapper) => {
    if (!wrapper || wrapper.__czFootnotesReady) return;

    let heading = wrapper.querySelector(":scope > [id]");
    if (!heading || !/^H[2-6]$/.test(heading.tagName)) {
      heading = wrapper.querySelector(
        ":scope > h2, :scope > h3, :scope > h4, :scope > h5, :scope > h6",
      );
    }
    const content = wrapper.querySelector(":scope > .footnotes-content");
    if (!heading || !content) return;

    heading.setAttribute("role", "button");
    heading.setAttribute("tabindex", "0");
    heading.setAttribute("aria-expanded", "false");

    if (!heading.querySelector(".footnotes-chevron")) {
      const chev = createChevron();
      const wrap = document.createElement("span");
      wrap.className = "footnotes-toggle-text";
      while (heading.firstChild) wrap.appendChild(heading.firstChild);
      heading.appendChild(chev);
      heading.appendChild(wrap);
      heading.style.display = "inline-flex";
      heading.style.alignItems = "center";
      heading.style.gap = ".5rem";
      chev.style.transform = "rotate(-90deg)";
    }

    content.hidden = true;

    const setOpen = (open) => {
      const chev = heading.querySelector(".footnotes-chevron");
      heading.setAttribute("aria-expanded", open ? "true" : "false");
      content.hidden = !open;
      if (chev) chev.style.transform = open ? "rotate(0deg)" : "rotate(-90deg)";
    };

    const handler = (e) => {
      e.preventDefault();
      setOpen(content.hidden);
    };

    heading.addEventListener("click", handler);
    heading.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        handler(e);
      }
    });

    wrapper.__czFootnotesReady = true;
  };

  const initFootnotesToggle = (root = document) => {
    qsa("div.footnotes", root).forEach(initFootnotesToggleFor);
  };

  // ---------------- Init ----------------
  const init = () => {
    initFootnotesToggle();
    initFootnotePopups();
  };
  if (document.readyState !== "loading") init();
  else document.addEventListener("DOMContentLoaded", init);

  const mo = new MutationObserver((muts) => {
    for (const m of muts) {
      m.addedNodes &&
        m.addedNodes.forEach((n) => {
          if (!n || n.nodeType !== 1) return;
          if (typeof n.querySelectorAll !== "function") return;
          if (n.matches && n.matches("div.footnotes"))
            initFootnotesToggleFor(n);
          else initFootnotesToggle(n);
        });
    }
  });
  try {
    mo.observe(document.documentElement, { childList: true, subtree: true });
  } catch (_) {}
})();

/* ========== THEME TOGGLE (time-based with per-day override + legacy reset) ========== */
(function () {
  var LEGACY_KEY = "cz-theme"; // old key to remove
  var OVERRIDE_KEY = "cz-theme-override"; // { theme: "light"|"dark", exp: <ms since epoch at local 23:59:59.999> }
  var root = document.documentElement;
  var btn = document.getElementById("theme-toggle");
  if (!btn) return;

  // --- Helpers ---
  function setTheme(t) {
    root.setAttribute("data-theme", t);
    btn.setAttribute("aria-pressed", t === "dark");
  }

  function endOfTodayMs() {
    var d = new Date();
    d.setHours(23, 59, 59, 999);
    return d.getTime();
  }

  function nowMs() {
    return Date.now();
  }

  function readOverride() {
    try {
      var raw = localStorage.getItem(OVERRIDE_KEY);
      if (!raw) return null;
      var obj = JSON.parse(raw);
      if (!obj || (obj.exp || 0) < nowMs() || (obj.theme !== "light" && obj.theme !== "dark")) {
        localStorage.removeItem(OVERRIDE_KEY);
        return null;
      }
      return obj.theme;
    } catch (_) {
      // If corrupted, wipe it
      try { localStorage.removeItem(OVERRIDE_KEY); } catch (__){}
      return null;
    }
  }

  function writeOverride(theme) {
    try {
      localStorage.setItem(OVERRIDE_KEY, JSON.stringify({ theme: theme, exp: endOfTodayMs() }));
    } catch (_) {}
  }

  function clearLegacy() {
    // Remove legacy storage and any accidental stale attributes managed by old script
    try { localStorage.removeItem(LEGACY_KEY); } catch (_) {}
  }

  function scheduledTheme() {
    // Light from 07:00 (inclusive) to 17:59:59, Dark otherwise
    var h = new Date().getHours();
    return (h >= 7 && h < 18) ? "light" : "dark";
  }

  // --- Init ---
  clearLegacy();

  var activeTheme = readOverride() || scheduledTheme();
  setTheme(activeTheme);

  // Keep animation behavior from your previous script
  var DUR = 720; // ms (match .7s CSS transition)
  var timer;

  btn.addEventListener("click", function () {
    if (btn.classList.contains("animating")) return; // prevent double click during animation

    var current = root.getAttribute("data-theme") || scheduledTheme();
    var next = current === "dark" ? "light" : "dark";
    var dirClass = next === "dark" ? "anim-to-dark" : "anim-to-light";

    btn.classList.add("animating", dirClass);

    // Next animation frame to allow CSS to pick up classes
    requestAnimationFrame(function () {
      // Apply chosen theme immediately
      setTheme(next);
      // Persist override only for the rest of the current day
      writeOverride(next);

      clearTimeout(timer);
      timer = setTimeout(function () {
        btn.classList.remove("animating", "anim-to-dark", "anim-to-light");
      }, DUR);
    });
  });

  // Optional: at page visibility change, re-apply schedule if override expired while tab was hidden
  document.addEventListener("visibilitychange", function () {
    if (document.visibilityState !== "visible") return;
    var o = readOverride();
    if (o) return;
    setTheme(scheduledTheme());
  });
})();


/* ===== COLLAPSABLE CONTENT — default: OPEN ===== */

(function () {
  function rootOf(ctx) {
    return ctx && typeof ctx.querySelectorAll === "function" ? ctx : document;
  }
  function $(sel, ctx) {
    return rootOf(ctx).querySelector(sel);
  }
  function qsa(sel, ctx) {
    return Array.from(rootOf(ctx).querySelectorAll(sel));
  }
  const isButtonLike = (el) =>
    el && (el.tagName === "BUTTON" || el.getAttribute("role") === "button");

  const createChevron = () => {
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", "0 0 24 24");
    svg.setAttribute("width", "48");
    svg.setAttribute("height", "48");
    svg.setAttribute("aria-hidden", "true");
    svg.style.flex = "0 0 auto";
    svg.style.transition = "transform .2s ease";
    svg.classList.add("collapsable-chevron");
    const p = document.createElementNS("http://www.w3.org/2000/svg", "path");
    p.setAttribute(
      "d",
      "M6.23 8.97a1 1 0 0 1 1.41 0L12 13.34l4.36-4.37a1 1 0 1 1 1.41 1.42l-5.06 5.06a1 1 0 0 1-1.41 0L6.23 10.4a1 1 0 0 1 0-1.42z",
    );
    p.setAttribute("fill", "currentColor");
    svg.appendChild(p);
    return svg;
  };

  const setOpen = (section, open) => {
    const toggle = $(".collapsable-toggle", section);
    const content = $(".collapsable-content", section);
    if (!toggle || !content) return;

    const chev = $(".collapsable-chevron", toggle);
    const wasOpen = section.classList.contains("is-open");
    if (open === wasOpen) return;

    section.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    content.setAttribute("aria-hidden", open ? "false" : "true");
    content.style.display = open ? "" : "none";
    if (chev) chev.style.transform = open ? "rotate(0deg)" : "rotate(-90deg)";
  };

  const ensureOperable = (el) => {
    if (!isButtonLike(el)) {
      el.setAttribute("role", "button");
      el.setAttribute("tabindex", "0");
    }
    el.style.cursor = "pointer";
  };

  const ensureId = (el, prefix = "collapsable-content-") => {
    if (!el.id) el.id = prefix + Math.random().toString(36).slice(2, 9);
    return el.id;
  };

  const initSection = (section) => {
    if (!section || section.__collapsableReady) return;
    const toggle = $(".collapsable-toggle", section);
    const content = $(".collapsable-content", section);
    if (!toggle || !content) return;

    // Inject chevron (rotation set by setOpen)
    if (!$(".collapsable-chevron", toggle)) {
      const chev = createChevron();
      const wrap = document.createElement("span");
      wrap.className = "collapsable-toggle-text";
      while (toggle.firstChild) wrap.appendChild(toggle.firstChild);
      toggle.appendChild(wrap);
      toggle.insertBefore(chev, wrap);
      toggle.style.display = "inline-flex";
      toggle.style.alignItems = "center";
      toggle.style.gap = ".5rem";
    }

    ensureOperable(toggle);
    ensureId(content);
    toggle.setAttribute("aria-controls", content.id);

    // === DEFAULT OPEN ===
    // Se presente data-initial="closed" parte chiuso, altrimenti aperto.
    const initial = (section.getAttribute("data-initial") || "").toLowerCase();
    setOpen(section, initial === "closed" ? false : true);

    const handler = (e) => {
      e.preventDefault();
      setOpen(section, !section.classList.contains("is-open"));
    };
    toggle.addEventListener("click", handler);
    toggle.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        handler(e);
      }
    });

    section.__collapsableReady = true;
  };

  const initAll = (root) => {
    qsa(".collapsable-section", root).forEach(initSection);
  };

  if (document.readyState !== "loading") initAll();
  else document.addEventListener("DOMContentLoaded", initAll);

  // Guarded MutationObserver (skip nodes without querySelectorAll)
  const mo = new MutationObserver((muts) => {
    for (const m of muts) {
      m.addedNodes &&
        m.addedNodes.forEach((n) => {
          if (!n || n.nodeType !== 1) return;
          if (typeof n.querySelectorAll !== "function") return;
          if (n.matches && n.matches(".collapsable-section")) initSection(n);
          else initAll(n);
        });
    }
  });
  try {
    mo.observe(document.documentElement, { childList: true, subtree: true });
  } catch (_) {}
})();

/* ======= LATEST ARTICLES CAROUSEL ======= */
(function () {
  const ROOT = document.getElementById("latest-articles");
  if (!ROOT) return;

  function init() {
    const wrap = ROOT.querySelector(".cz-carousel");
    if (!wrap) return;
    const track = wrap.querySelector(".cz-carousel-track");
    const slides = Array.from(track.querySelectorAll(".article-card"));
    const dots = Array.from(wrap.querySelectorAll(".cz-carousel-dot"));
    const mq = window.matchMedia("(max-width: 587px)");
    let index = 0;

    function applyTransform() {
      if (!mq.matches) {
        // Desktop/tablet: no transform (3 columns grid)
        track.style.transform = "none";
        return;
      }
      track.style.transform = "translateX(" + -index * 100 + "%)";
    }

    function setActiveDot(i) {
      dots.forEach((d, k) => {
        d.classList.toggle("is-active", k === i);
        d.setAttribute("aria-selected", k === i ? "true" : "false");
      });
    }

    dots.forEach((btn) => {
      btn.addEventListener("click", function () {
        const i = Math.max(
          0,
          Math.min(slides.length - 1, parseInt(this.dataset.index || "0", 10)),
        );
        index = i;
        applyTransform();
        setActiveDot(index);
      });
    });

    mq.addEventListener("change", applyTransform);
    window.addEventListener("resize", applyTransform);

    applyTransform();
    setActiveDot(index);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

/* ===== SIGING BOWL ===== */
(function () {
  "use strict";

  /* ---------------------------------------
   *  Utils (from your ESM)
   * ------------------------------------- */
  function __rest(s, e) {
    var t = {};
    for (var p in s)
      if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function") {
      for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
        if (
          e.indexOf(p[i]) < 0 &&
          Object.prototype.propertyIsEnumerable.call(s, p[i])
        )
          t[p[i]] = s[p[i]];
      }
    }
    return t;
  }

  /* ---------------------------------------
   *  ClassicCurve (unchanged)
   * ------------------------------------- */
  class ClassicCurve {
    constructor(ctrl, definition) {
      this.ATT_FACTOR = 4;
      this.GRAPH_X = 2;
      this.AMPLITUDE_FACTOR = 0.6;
      this.ctrl = ctrl;
      this.definition = definition;
    }
    globalAttFn(x) {
      return Math.pow(
        this.ATT_FACTOR / (this.ATT_FACTOR + Math.pow(x, this.ATT_FACTOR)),
        this.ATT_FACTOR,
      );
    }
    xPos(i) {
      return this.ctrl.width * ((i + this.GRAPH_X) / (this.GRAPH_X * 2));
    }
    yPos(i) {
      return (
        this.AMPLITUDE_FACTOR *
        (this.globalAttFn(i) *
          (this.ctrl.heightMax * this.ctrl.amplitude) *
          (1 / this.definition.attenuation) *
          Math.sin(this.ctrl.opt.frequency * i - this.ctrl.phase))
      );
    }
    draw() {
      const { ctx } = this.ctrl;
      ctx.moveTo(0, 0);
      ctx.beginPath();
      const finalColor = this.definition.color || this.ctrl.color;
      const colorHex = finalColor.replace(/rgb\(/g, "").replace(/\)/g, "");
      ctx.strokeStyle = `rgba(${colorHex},${this.definition.opacity})`;
      ctx.lineWidth = this.definition.lineWidth;
      for (
        let i = -this.GRAPH_X;
        i <= this.GRAPH_X;
        i += this.ctrl.opt.pixelDepth
      ) {
        ctx.lineTo(this.xPos(i), this.ctrl.heightMax + this.yPos(i));
      }
      ctx.stroke();
    }
    static getDefinition() {
      return [
        { attenuation: -2, lineWidth: 1, opacity: 0.1 },
        { attenuation: -6, lineWidth: 1, opacity: 0.2 },
        { attenuation: 4, lineWidth: 1, opacity: 0.4 },
        { attenuation: 2, lineWidth: 1, opacity: 0.6 },
        { attenuation: 1, lineWidth: 1.5, opacity: 1 },
      ];
    }
  }

  /* ---------------------------------------
   *  iOS9Curve (unchanged)
   * ------------------------------------- */
  class iOS9Curve {
    constructor(ctrl, definition) {
      this.GRAPH_X = 25;
      this.AMPLITUDE_FACTOR = 0.8;
      this.SPEED_FACTOR = 1;
      this.DEAD_PX = 2;
      this.ATT_FACTOR = 4;
      this.DESPAWN_FACTOR = 0.02;
      this.DEFAULT_NOOFCURVES_RANGES = [2, 5];
      this.DEFAULT_AMPLITUDE_RANGES = [0.3, 1];
      this.DEFAULT_OFFSET_RANGES = [-3, 3];
      this.DEFAULT_WIDTH_RANGES = [1, 3];
      this.DEFAULT_SPEED_RANGES = [0.5, 1];
      this.DEFAULT_DESPAWN_TIMEOUT_RANGES = [500, 2000];
      this.ctrl = ctrl;
      this.definition = definition;
      this.noOfCurves = 0;
      this.spawnAt = 0;
      this.prevMaxY = 0;
      this.phases = [];
      this.offsets = [];
      this.speeds = [];
      this.finalAmplitudes = [];
      this.widths = [];
      this.amplitudes = [];
      this.despawnTimeouts = [];
      this.verses = [];
    }
    getRandomRange(e) {
      return e[0] + Math.random() * (e[1] - e[0]);
    }
    spawnSingle(ci) {
      var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k;
      this.phases[ci] = 0;
      this.amplitudes[ci] = 0;
      this.despawnTimeouts[ci] = this.getRandomRange(
        (_b =
          (_a = this.ctrl.opt.ranges) === null || _a === void 0
            ? void 0
            : _a.despawnTimeout) !== null && _b !== void 0
          ? _b
          : this.DEFAULT_DESPAWN_TIMEOUT_RANGES,
      );
      this.offsets[ci] = this.getRandomRange(
        (_d =
          (_c = this.ctrl.opt.ranges) === null || _c === void 0
            ? void 0
            : _c.offset) !== null && _d !== void 0
          ? _d
          : this.DEFAULT_OFFSET_RANGES,
      );
      this.speeds[ci] = this.getRandomRange(
        (_f =
          (_e = this.ctrl.opt.ranges) === null || _e === void 0
            ? void 0
            : _e.speed) !== null && _f !== void 0
          ? _f
          : this.DEFAULT_SPEED_RANGES,
      );
      this.finalAmplitudes[ci] = this.getRandomRange(
        (_h =
          (_g = this.ctrl.opt.ranges) === null || _g === void 0
            ? void 0
            : _g.amplitude) !== null && _h !== void 0
          ? _h
          : this.DEFAULT_AMPLITUDE_RANGES,
      );
      this.widths[ci] = this.getRandomRange(
        (_k =
          (_j = this.ctrl.opt.ranges) === null || _j === void 0
            ? void 0
            : _j.width) !== null && _k !== void 0
          ? _k
          : this.DEFAULT_WIDTH_RANGES,
      );
      this.verses[ci] = this.getRandomRange([-1, 1]);
    }
    getEmptyArray(count) {
      return new Array(count);
    }
    spawn() {
      var _a, _b;
      this.spawnAt = Date.now();
      this.noOfCurves = Math.floor(
        this.getRandomRange(
          (_b =
            (_a = this.ctrl.opt.ranges) === null || _a === void 0
              ? void 0
              : _a.noOfCurves) !== null && _b !== void 0
            ? _b
            : this.DEFAULT_NOOFCURVES_RANGES,
        ),
      );
      this.phases = this.getEmptyArray(this.noOfCurves);
      this.offsets = this.getEmptyArray(this.noOfCurves);
      this.speeds = this.getEmptyArray(this.noOfCurves);
      this.finalAmplitudes = this.getEmptyArray(this.noOfCurves);
      this.widths = this.getEmptyArray(this.noOfCurves);
      this.amplitudes = this.getEmptyArray(this.noOfCurves);
      this.despawnTimeouts = this.getEmptyArray(this.noOfCurves);
      this.verses = this.getEmptyArray(this.noOfCurves);
      for (let ci = 0; ci < this.noOfCurves; ci++) this.spawnSingle(ci);
    }
    globalAttFn(x) {
      return Math.pow(
        this.ATT_FACTOR / (this.ATT_FACTOR + Math.pow(x, 2)),
        this.ATT_FACTOR,
      );
    }
    sin(x, phase) {
      return Math.sin(x - phase);
    }
    yRelativePos(i) {
      let y = 0;
      for (let ci = 0; ci < this.noOfCurves; ci++) {
        let t = 4 * (-1 + (ci / (this.noOfCurves - 1)) * 2);
        t += this.offsets[ci];
        const k = 1 / this.widths[ci];
        const x = i * k - t;
        y += Math.abs(
          this.amplitudes[ci] *
            this.sin(this.verses[ci] * x, this.phases[ci]) *
            this.globalAttFn(x),
        );
      }
      return y / this.noOfCurves;
    }
    yPos(i) {
      return (
        this.AMPLITUDE_FACTOR *
        this.ctrl.heightMax *
        this.ctrl.amplitude *
        this.yRelativePos(i) *
        this.globalAttFn((i / this.GRAPH_X) * 2)
      );
    }
    xPos(i) {
      return this.ctrl.width * ((i + this.GRAPH_X) / (this.GRAPH_X * 2));
    }
    drawSupportLine() {
      const { ctx } = this.ctrl;
      const coo = [0, this.ctrl.heightMax, this.ctrl.width, 1];
      const gradient = ctx.createLinearGradient.apply(ctx, coo);
      gradient.addColorStop(0, "transparent");
      gradient.addColorStop(0.1, "rgba(255,255,255,.5)");
      gradient.addColorStop(1 - 0.1 - 0.1, "rgba(255,255,255,.5)");
      gradient.addColorStop(1, "transparent");
      ctx.fillStyle = gradient;
      ctx.fillRect.apply(ctx, coo);
    }
    draw() {
      const { ctx } = this.ctrl;
      ctx.globalAlpha = 0.7;
      ctx.globalCompositeOperation = this.ctrl.opt.globalCompositeOperation;
      if (this.spawnAt === 0) this.spawn();
      if (this.definition.supportLine) return this.drawSupportLine();

      for (let ci = 0; ci < this.noOfCurves; ci++) {
        if (this.spawnAt + this.despawnTimeouts[ci] <= Date.now())
          this.amplitudes[ci] -= this.DESPAWN_FACTOR;
        else this.amplitudes[ci] += this.DESPAWN_FACTOR;
        this.amplitudes[ci] = Math.min(
          Math.max(this.amplitudes[ci], 0),
          this.finalAmplitudes[ci],
        );
        this.phases[ci] =
          (this.phases[ci] +
            this.ctrl.speed * this.speeds[ci] * this.SPEED_FACTOR) %
          (2 * Math.PI);
      }
      let maxY = -Infinity;
      for (const sign of [1, -1]) {
        ctx.beginPath();
        for (
          let i = -this.GRAPH_X;
          i <= this.GRAPH_X;
          i += this.ctrl.opt.pixelDepth
        ) {
          const x = this.xPos(i);
          const y = this.yPos(i);
          ctx.lineTo(x, this.ctrl.heightMax - sign * y);
          maxY = Math.max(maxY, y);
        }
        ctx.closePath();
        ctx.fillStyle = `rgba(${this.definition.color}, 1)`;
        ctx.strokeStyle = `rgba(${this.definition.color}, 1)`;
        ctx.fill();
      }
      if (maxY < this.DEAD_PX && this.prevMaxY > maxY) this.spawnAt = 0;
      this.prevMaxY = maxY;
      return null;
    }
    static getDefinition() {
      return [
        { color: "255,255,255", supportLine: false },
        { color: "15, 82, 169" }, // blue
        { color: "173, 57, 76" }, // red
        { color: "48, 220, 155" }, // green
      ];
    }
  }

  /* ---------------------------------------
   *  SiriWave (unchanged API)
   * ------------------------------------- */
  class SiriWave {
    constructor(_a) {
      var { container } = _a,
        rest = __rest(_a, ["container"]);
      this.phase = 0;
      this.run = false;
      this.curves = [];
      const csStyle = window.getComputedStyle(container);
      this.opt = Object.assign(
        {
          container,
          style: "ios",
          ratio: window.devicePixelRatio || 1,
          speed: 0.2,
          amplitude: 1,
          frequency: 6,
          color: "#fff",
          cover: false,
          width: parseInt(csStyle.width.replace("px", ""), 10),
          height: parseInt(csStyle.height.replace("px", ""), 10),
          autostart: true,
          pixelDepth: 0.02,
          lerpSpeed: 0.1,
          globalCompositeOperation: "lighter",
        },
        rest,
      );
      this.speed = Number(this.opt.speed);
      this.amplitude = Number(this.opt.amplitude);
      this.width = Number(this.opt.ratio * this.opt.width);
      this.height = Number(this.opt.ratio * this.opt.height);
      this.heightMax = Number(this.height / 2) - 6;
      this.color = `rgb(${this.hex2rgb(this.opt.color)})`;
      this.interpolation = { speed: this.speed, amplitude: this.amplitude };
      this.canvas = document.createElement("canvas");
      const ctx = this.canvas.getContext("2d");
      if (ctx === null) throw new Error("Unable to create 2D Context");
      this.ctx = ctx;
      this.canvas.width = this.width;
      this.canvas.height = this.height;
      if (this.opt.cover === true) {
        this.canvas.style.width = this.canvas.style.height = "100%";
      } else {
        this.canvas.style.width = `${this.width / this.opt.ratio}px`;
        this.canvas.style.height = `${this.height / this.opt.ratio}px`;
      }
      switch (this.opt.style) {
        case "ios9":
          this.curves = (
            this.opt.curveDefinition || iOS9Curve.getDefinition()
          ).map((def) => new iOS9Curve(this, def));
          break;
        case "ios":
        default:
          this.curves = (
            this.opt.curveDefinition || ClassicCurve.getDefinition()
          ).map((def) => new ClassicCurve(this, def));
          break;
      }
      this.opt.container.appendChild(this.canvas);
      if (this.opt.autostart) this.start();
    }
    hex2rgb(hex) {
      const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
      hex = hex.replace(shorthandRegex, (m, r, g, b) => r + r + g + g + b + b);
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result
        ? `${parseInt(result[1], 16).toString()},${parseInt(result[2], 16).toString()},${parseInt(result[3], 16).toString()}`
        : null;
    }
    intLerp(v0, v1, t) {
      return v0 * (1 - t) + v1 * t;
    }
    lerp(propertyStr) {
      const prop = this.interpolation[propertyStr];
      if (prop !== null) {
        this[propertyStr] = this.intLerp(
          this[propertyStr],
          prop,
          this.opt.lerpSpeed,
        );
        if (this[propertyStr] - prop === 0)
          this.interpolation[propertyStr] = null;
      }
      return this[propertyStr];
    }
    clear() {
      this.ctx.globalCompositeOperation = "destination-out";
      this.ctx.fillRect(0, 0, this.width, this.height);
      this.ctx.globalCompositeOperation = "source-over";
    }
    draw() {
      this.curves.forEach((curve) => curve.draw());
    }
    startDrawCycle() {
      this.clear();
      this.lerp("amplitude");
      this.lerp("speed");
      this.draw();
      this.phase = (this.phase + (Math.PI / 2) * this.speed) % (2 * Math.PI);
      if (window.requestAnimationFrame) {
        this.animationFrameId = window.requestAnimationFrame(
          this.startDrawCycle.bind(this),
        );
      } else {
        this.timeoutId = setTimeout(this.startDrawCycle.bind(this), 20);
      }
    }
    start() {
      if (!this.canvas)
        throw new Error(
          "This instance of SiriWave has been disposed, please create a new instance",
        );
      this.phase = 0;
      if (!this.run) {
        this.run = true;
        this.startDrawCycle();
      }
    }
    stop() {
      this.phase = 0;
      this.run = false;
      if (this.animationFrameId)
        window.cancelAnimationFrame(this.animationFrameId);
      if (this.timeoutId) clearTimeout(this.timeoutId);
    }
    dispose() {
      this.stop();
      if (this.canvas) {
        this.canvas.remove();
        this.canvas = null;
      }
    }
    set(propertyStr, value) {
      this.interpolation[propertyStr] = value;
    }
    setSpeed(value) {
      this.set("speed", value);
    }
    setAmplitude(value) {
      this.set("amplitude", value);
    }
  }

  /* ---------------------------------------
   *  Hook to your template
   * ------------------------------------- */
  function setupBowl(section) {
    var btn = section.querySelector(".cz-bowl-btn");
    var audio = section.querySelector(".cz-bowl-audio");

    if (!btn || !audio) return;

    // Ensure wave container exists (centered under button)
    var wave = section.querySelector(".cz-bowl-wave");
    if (!wave) {
      wave = document.createElement("div");
      wave.className = "cz-bowl-wave";
      // insert right after button
      btn.insertAdjacentElement("afterend", wave);
    }

    var waveInstance = null;
    var disposeTimer = null;

    function ensureWaveStarted() {
      if (!waveInstance) {
        waveInstance = new SiriWave({
          container: wave,
          style: "ios9",
          cover: true,
          autostart: true,
          speed: 0.2,
          amplitude: 24,
        });
      } else {
        waveInstance.start();
      }
      if (disposeTimer) {
        clearTimeout(disposeTimer);
        disposeTimer = null;
      }
      wave.style.display = "block";
      section.classList.add("is-playing");
      // Small ramp up
      waveInstance.setAmplitude(1);
    }

    function stopAndDisposeWave() {
      if (!waveInstance) return;
      // Fade out amplitude then dispose
      waveInstance.setAmplitude(0);
      disposeTimer = setTimeout(function () {
        if (waveInstance) {
          waveInstance.dispose();
          waveInstance = null;
        }
        wave.style.display = "none";
        section.classList.remove("is-playing");
      }, 0);
    }

    // Click = play from start
    btn.addEventListener("click", function () {
      try {
        audio.currentTime = 0;
      } catch (e) {}
      audio.play().catch(function () {
        /* ignore autoplay errors */
      });
    });

    // Audio events
    audio.addEventListener("play", ensureWaveStarted);
    audio.addEventListener("playing", ensureWaveStarted);
    audio.addEventListener("pause", stopAndDisposeWave);
    audio.addEventListener("ended", stopAndDisposeWave);

    // Optional: space/enter triggers button
    btn.addEventListener("keydown", function (e) {
      if (e.key === " " || e.key === "Enter") {
        e.preventDefault();
        btn.click();
      }
    });
  }

  // Init all instances on DOM ready
  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }
  ready(function () {
    document.querySelectorAll(".cz-bowl").forEach(setupBowl);
  });
})();
