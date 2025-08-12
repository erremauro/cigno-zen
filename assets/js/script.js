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

/*
 * Collapsible biography, shared state across both toggles.
 * - Both .js-bio-toggle controls mirror the same expanded/collapsed state.
 * - Chevron rotates; label swaps CONTINUA/RIDUCI.
 * - On collapse, scroll smoothly back to #author-title.
 */
(function() {
	var panel   = document.getElementById('author-full-bio');
	var title   = document.getElementById('author-hero');
	var toggles = Array.prototype.slice.call(document.querySelectorAll('.js-bio-toggle'));

	if (!panel || toggles.length === 0) return;

	var expanded = false; // current state

	function applyState() {
		// ARIA + visibility
		toggles.forEach(function(t){
			t.setAttribute('aria-expanded', String(expanded));
			var chevron = t.querySelector('.more-link-chevron');
			var label   = t.querySelector('.more-link-label');
			if (chevron) {
				chevron.classList.toggle("rotated");
			}
			if (label) label.textContent = expanded ? 'RIDUCI' : 'CONTINUA';
		});

		if (expanded) {
			panel.removeAttribute('hidden');
		} else {
			panel.setAttribute('hidden', '');
			// Smoothly return user to the author title to prevent being stranded mid-page
			if (title && 'scrollIntoView' in title) {
				title.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}
	}

	function toggleState(e) {
		if (e) e.preventDefault();
		expanded = !expanded;
		applyState();
	}

	// Click + keyboard accessibility
	toggles.forEach(function(t){
		t.addEventListener('click', toggleState);
		t.addEventListener('keydown', function(e){
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				toggleState();
			}
		});
	});

	// Auto-expand if landing on #bio
	if (location.hash === '#bio') {
		expanded = true;
		applyState();
	}
})();
