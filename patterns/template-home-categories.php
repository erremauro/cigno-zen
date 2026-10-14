<section class="home-section" id="categories">
    <?php
        get_template_part(
            'parts/cta-title-link',
            null,
            [
                "url"   => "/categorie",
                "title" => "Categorie",
                "desc"  => "Sfoglia gli articoli per categoria"
            ]
        );
    ?>

    <div class="categories-grid">
      <a href="/categoria/approfondimento" class="category-card">Approfondimenti</a>
      <a href="/categoria/sutra/canone-pali" class="category-card">Canone Pali</a>
      <a href="/categoria/sutra/sutra-mahayana" class="category-card">Sutra Mahayana</a>
      <a href="/categoria/saggio" class="category-card">Saggi</a>
      <a href="/categoria/poema" class="category-card">Poemi</a>
      <a href="/categoria/commentario" class="category-card">Commentari</a>
    </div>
</section>
