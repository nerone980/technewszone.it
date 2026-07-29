// =================================================================
// FILE: cookie-consent.js — Banner consenso cookie (Accetta / Rifiuta)
// =================================================================
// Mostra un banner discreto finché l'utente non sceglie.
// Salva la scelta e non ripropone il banner.
// Espone window.cookieConsent per sapere se attivare script di marketing.
//
// COME USARLO per gli script di marketing (es. AdSense in futuro):
//   if (window.cookieConsent === 'accepted') { ...carica lo script... }
// oppure ascolta l'evento:
//   window.addEventListener('cookie-consent-updated', e => {
//       if (e.detail === 'accepted') { ...carica... }
//   });
// =================================================================

(function () {
  var KEY = 'tnz_cookie_consent';
  var choice = null;
  try { choice = localStorage.getItem(KEY); } catch (e) {}

  // Rendi disponibile la scelta al resto del sito
  window.cookieConsent = choice; // 'accepted' | 'rejected' | null

  // Se ha già scelto, non mostrare nulla
  if (choice === 'accepted' || choice === 'rejected') return;

  function save(value) {
    try { localStorage.setItem(KEY, value); } catch (e) {}
    window.cookieConsent = value;
    // avvisa eventuali script in ascolto
    window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: value }));
    var bar = document.getElementById('cookie-bar');
    if (bar) { bar.style.transform = 'translateY(120%)'; setTimeout(function () { bar.remove(); }, 400); }
  }

  function build() {
    var bar = document.createElement('div');
    bar.id = 'cookie-bar';
    bar.setAttribute('role', 'dialog');
    bar.setAttribute('aria-label', 'Avviso cookie');
    bar.style.cssText = [
      'position:fixed', 'left:16px', 'right:16px', 'bottom:16px', 'z-index:5000',
      'max-width:680px', 'margin:0 auto',
      'background:#0f1318', 'border:1px solid #1c2128', 'border-left:3px solid #e8b04b',
      'border-radius:12px', 'padding:16px 18px',
      'display:flex', 'flex-wrap:wrap', 'align-items:center', 'gap:14px',
      'box-shadow:0 8px 30px rgba(0,0,0,.5)',
      "font-family:'Space Grotesk',system-ui,sans-serif",
      'transform:translateY(0)', 'transition:transform .4s ease'
    ].join(';');

    var text = document.createElement('div');
    text.style.cssText = 'flex:1;min-width:200px;color:#8b949e;font-size:.86rem;line-height:1.5';
    text.innerHTML = 'Usiamo cookie tecnici e servizi di terze parti per far funzionare il sito e, con il tuo consenso, per i link partner. ' +
      '<a href="privacy.php" style="color:#e8b04b;text-decoration:none">Dettagli</a>.';

    var btns = document.createElement('div');
    btns.style.cssText = 'display:flex;gap:10px;flex-shrink:0';

    var reject = document.createElement('button');
    reject.textContent = 'Rifiuta';
    reject.style.cssText = 'background:transparent;border:1px solid #1c2128;color:#8b949e;' +
      'font-family:inherit;font-size:.84rem;font-weight:600;padding:9px 18px;border-radius:8px;cursor:pointer';
    reject.onmouseover = function () { reject.style.borderColor = '#5a6473'; reject.style.color = '#e6edf3'; };
    reject.onmouseout = function () { reject.style.borderColor = '#1c2128'; reject.style.color = '#8b949e'; };
    reject.onclick = function () { save('rejected'); };

    var accept = document.createElement('button');
    accept.textContent = 'Accetta';
    accept.style.cssText = 'background:#e8b04b;border:1px solid #e8b04b;color:#0a0c0f;' +
      'font-family:inherit;font-size:.84rem;font-weight:700;padding:9px 22px;border-radius:8px;cursor:pointer';
    accept.onclick = function () { save('accepted'); };

    btns.appendChild(reject);
    btns.appendChild(accept);
    bar.appendChild(text);
    bar.appendChild(btns);
    document.body.appendChild(bar);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', build);
  } else {
    build();
  }
})();
