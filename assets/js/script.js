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
		var menuButton = document.getElementById('menu-button');
		// Assicurati di aggiungere l'event listener correttamente
		menuButton.addEventListener('click', onShowSearchClick);
	});
})(document);
