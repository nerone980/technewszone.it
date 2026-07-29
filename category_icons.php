<?php
// =================================================================
// FILE: category_icons.php — Mappa categoria → icona Font Awesome
// =================================================================
// Associa a ogni categoria la sua icona, senza sporcare i nomi.
// Per cambiare un'icona: sostituisci la classe (es. 'fa-microchip').
// Elenco icone: https://fontawesome.com/search  (usa quelle "free").
//
// La chiave è il nome PULITO della categoria (senza emoji).
// Se una categoria non è in lista, usa l'icona di default.
// =================================================================

$CATEGORY_ICONS = [
    'Home'              => 'fa-bolt',
    'Tecnologia'        => 'fa-microchip',
    'Offerte'           => 'fa-tag',
    'Crypto'            => 'fa-coins',
    'Finanza'           => 'fa-chart-line',
    'Gaming'            => 'fa-gamepad',
    'Apple'             => 'fa-apple',            // brand (fab)
    'Android'           => 'fa-android',          // brand (fab)
    'Auto & Motori'     => 'fa-car-side',
    'Scienza & Spazio'  => 'fa-rocket',
    'Cybersecurity'     => 'fa-shield-halved',
    'AI & Innovazione'  => 'fa-brain',
    'Mondo Tech'        => 'fa-globe',
];

// Icone che appartengono al set "brand" (fab) invece che "solid" (fas)
$CATEGORY_ICONS_BRAND = ['fa-apple', 'fa-android'];

// Restituisce l'HTML <i> dell'icona per una categoria
function category_icon_html($catName) {
    global $CATEGORY_ICONS, $CATEGORY_ICONS_BRAND;
    $icon = $CATEGORY_ICONS[$catName] ?? 'fa-newspaper'; // default
    $set  = in_array($icon, $CATEGORY_ICONS_BRAND, true) ? 'fab' : 'fas';
    return '<i class="' . $set . ' ' . $icon . ' cat-ico" aria-hidden="true"></i>';
}
