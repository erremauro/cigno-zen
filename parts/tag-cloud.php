<section class="tag-cloud-section" id="tag-cloud">
    <?php
        $total_tags = wp_count_terms([
            'taxonomy'   => 'post_tag',
            'hide_empty' => false, // include anche quelli mai usati
        ]);
        get_template_part(
            'parts/cta-title-link',
            null,
            [
                "title" => "Argomenti",
                "desc"  => "Gli Argomenti più discussi tra gli oltre " . $total_tags . " lemmi del dizionario buddhista presenti.",
            ]
        );
    ?>
<?php 

$args = $args['args'] ?? [];
cz_tag_cloud($args);
?>
</section>
