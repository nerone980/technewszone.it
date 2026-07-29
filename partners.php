<?php
// =================================================================
// FILE: partners.php — Le tue offerte referral / sponsorizzazioni
// =================================================================
//
// COME AGGIUNGERE O TOGLIERE UN'OFFERTA:
// Ogni offerta è un blocco tra parentesi graffe [ ... ].
// Copia un blocco, incollalo, cambia i valori. Per toglierne una,
// cancella il suo blocco (dalla [ alla ], virgola inclusa).
//
// CAMPI:
//   'name'    => Nome dell'azienda (es. 'Revolut')
//   'tagline' => Frase breve che descrive il vantaggio
//   'bonus'   => Il vantaggio in evidenza (es. '€20 di bonus')
//   'url'     => IL TUO link referral personale (quello del tuo account!)
//   'color'   => Colore accento del brand (es. '#0075EB')
//   'logo'    => (opzionale) URL di un logo, oppure lascia '' per usare l'iniziale
//   'cta'     => Testo del pulsante (es. 'Iscriviti')
//   'active'  => true per mostrarla, false per nasconderla senza cancellarla
//
// IMPORTANTE: metti in 'url' il TUO link referral, non il sito generico.
// Verifica sempre che il programma consenta la promozione pubblica sul web.
// =================================================================

$PARTNERS = [

    [
        'name'    => 'Revolut',
        'tagline' => 'Conto, carta e cambio valuta senza costi nascosti',
        'bonus'   => 'Carta gratis + vantaggi',
        'url'     => 'https://revolut.com/referral/?referral-code=marcoryzy!JUL1-26-AR&geo-redirect',
        'color'   => '#0075EB',
        'logo'    => '',
        'cta'     => 'Attiva ora',
        'active'  => true,
    ],

    [
        'name'    => 'Satispay',
        'tagline' => 'Pagamenti e cashback direttamente dallo smartphone',
        'bonus'   => 'Bonus di benvenuto',
        'url'     => 'https://web.satispay.com/promocode/186a9e0c-6858-4f6d-b946-3817e6b13028',
        'color'   => '#F94B4B',
        'logo'    => '',
        'cta'     => 'Scarica',
        'active'  => true,
    ],

    [
        'name'    => 'Trade Republic',
        'tagline' => 'Investi in azioni ed ETF con commissioni minime',
        'bonus'   => 'Azione in regalo',
        'url'     => 'https://traderepublic.com/IL-TUO-CODICE',
        'color'   => '#FFFFFF',
        'logo'    => '',
        'cta'     => 'Inizia',
        'active'  => false,
    ],

    [
        'name'    => 'Curve',
        'tagline' => 'Iscriviti tramite il mio link e ottieni 1% di cashback per 30 giorni',
        'bonus'   => 'Vantaggio nuovi utenti',
        'url'     => 'https://www.curve.com/join#1NMOV68GD',
        'color'   => '#00D2AA',
        'logo'    => '',
        'cta'     => 'Scopri',
        'active'  => true, 
    ],
    [
        'name'    => 'Trade Republic',
        'tagline' => 'Unisciti a Trade Republic, il modo più intelligente di investire, spendere e usufruire di servizi bancari. Crea un conto tramite il link per ricevere un bonus di benvenuto.',
        'bonus'   => 'Vantaggio nuovi utenti',
        'url'     => 'https://refnocode.trade.re/x95hqh3b',
        'color'   => '#0FD23A',
        'logo'    => '',
        'cta'     => 'Scopri',
        'active'  => true, 
    ],
];

// Restituisce solo le offerte attive
function get_active_partners() {
    global $PARTNERS;
    return array_values(array_filter($PARTNERS, fn($p) => !empty($p['active'])));
}