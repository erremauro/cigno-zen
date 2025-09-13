
<?php get_template_part('parts/home-welcome') ?>
<?php get_template_part('patterns/template-home-articles'); ?>
<?php get_template_part('patterns/template-home-readings'); ?>
<?php get_template_part('patterns/template-home-authors'); ?>
<?php get_template_part('patterns/template-home-volumes'); ?>
<?php get_template_part('patterns/template-home-categories'); ?>
<?php get_template_part('parts/tag-cloud', null, [
    'args' => [
        'number' => 80,
        'order'  => 'DESC',
        'scale'  => 'log',
        'show_count' => false,
        'min_font'      => 1.25,
        'max_font'      => 1.25,
    ]
]);
?>

<div class="random-article"><strong>Non sai da dove partire?</strong><br> <a href="/?random=1">Lasciati ispirare</a> da uno dei molti articoli presenti.</div>

<?php get_template_part('parts/dhamma-gift'); ?>
<?php get_template_part('parts/singing-bowl'); ?>
