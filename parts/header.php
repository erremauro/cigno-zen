<?php
// Questo template permette di specificare
// dei parametri che ne condizionano il contenuto.
//
// Definizione dei parametri di default:
$array_defaults = array(
    'show_menu' => true,
);
$args = wp_parse_args($args, $array_defaults);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<?php get_template_part('parts/head'); ?>

<body <?php body_class(); ?>>
    <?php get_template_part('parts/top-nav-bar'); ?>
