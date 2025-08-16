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
