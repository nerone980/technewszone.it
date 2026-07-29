# Notifiche Push — Guida all'attivazione

Le notifiche richiedono alcuni passaggi una tantum. Seguili in ordine.
**Requisito fondamentale:** il sito deve girare in **HTTPS** (le push non funzionano in HTTP).

---

## Passo 1 — Installa la libreria (Composer)

Sul server, dalla cartella del sito, esegui:

```
composer require minishlink/web-push
```

Questo crea la cartella `vendor/`. Serve **PHP 8.0+** e l'estensione **gmp** attiva.
Se il tuo hosting non ha Composer da terminale, installa Composer localmente sul tuo PC,
lancia il comando lì, e carica la cartella `vendor/` generata insieme agli altri file.

---

## Passo 2 — Genera le chiavi VAPID (una sola volta)

1. Apri nel browser: `https://iltuosito/generate_keys.php`
2. Compaiono due chiavi (pubblica e privata).
3. Aprile `push_config.php` e incolla i due valori in:
   - `VAPID_PUBLIC_KEY`
   - `VAPID_PRIVATE_KEY`
4. Sempre in `push_config.php`, metti la tua email reale in `VAPID_SUBJECT`
   (formato: `mailto:tuoindirizzo@esempio.it`).
5. **CANCELLA** `generate_keys.php` dal server (per sicurezza).

---

## Passo 3 — Permessi di scrittura

Questi file vengono creati/aggiornati dal sistema: assicurati che la cartella
sia scrivibile (CHMOD 664/666 sui file o 775 sulla cartella):
- `push_subscriptions.json`  (elenco browser iscritti)
- `push_state.json`          (memoria anti-doppione)

Non devi crearli a mano: nascono da soli al primo utilizzo.

---

## Passo 4 — Imposta il CRON

Le notifiche partono da `send_push.php`, che va lanciato periodicamente.
Ideale: subito dopo l'aggiornamento notizie. Esempio (ogni 20 minuti):

```
*/20 * * * * /usr/bin/php /percorso/assoluto/del/sito/update_cache.php >/dev/null 2>&1
*/20 * * * * /usr/bin/php /percorso/assoluto/del/sito/send_push.php  >/dev/null 2>&1
```

(Metti `send_push.php` un minuto dopo `update_cache.php` se vuoi essere sicuro
che le notizie siano già aggiornate.)

---

## Passo 5 — Prova

1. Apri il sito, clicca la **campanella** in alto. Concedi il permesso.
2. La campanella diventa ambra = sei iscritto.
3. Per un test immediato, apri `https://iltuosito/send_push.php` nel browser:
   se c'è una notizia recente non ancora notificata, ricevi la notifica.

---

## Come decidi cosa è "breaking"

In `push_config.php` regoli:
- `PUSH_MAX_AGE_MINUTES` — quanto recente deve essere una notizia (default 60 min).
- `PUSH_MAX_PER_RUN` — max notifiche per giro, anti-spam (default 3).

Il sistema non invia mai due volte la stessa notizia (memoria in `push_state.json`).

---

## Riepilogo file del sistema notifiche

| File | Ruolo |
|------|-------|
| `composer.json` | dichiara la libreria web-push |
| `push_config.php` | chiavi VAPID e impostazioni |
| `generate_keys.php` | genera le chiavi (poi va cancellato) |
| `vapid_public.php` | passa la chiave pubblica al browser |
| `subscribe.php` | salva/rimuove le iscrizioni |
| `send_push.php` | il cron che invia le notifiche |
| `push.js` | pulsante e logica lato browser |
| `service-worker.js` | riceve e mostra le notifiche |
