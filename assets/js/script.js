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

		// Ruota l'immagine aggiungendo o rimuovendo la classe
		var searchButton = document.getElementById('search-button');
		searchButton.classList.toggle('rotated');

		// Imposta il focus sul campo di ricerca
		var searchField = document.getElementById('search-field');
		searchField.focus();
	}

	document.addEventListener('DOMContentLoaded', function() {
		var searchButton = document.getElementById('search-button');
		// Assicurati di aggiungere l'event listener correttamente
		searchButton.addEventListener('click', onShowSearchClick);
	});
})(document);
