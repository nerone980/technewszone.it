// =================================================================
// FILE: push.js — Gestione iscrizione notifiche push (frontend)
// Incluso sia da desktop che da mobile.
// =================================================================

(function () {
  const btn = document.getElementById('pushBtn');
  if (!btn) return;

  const supported = ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
  if (!supported) { btn.style.display = 'none'; return; }

  // base64url -> Uint8Array (richiesto da applicationServerKey)
  function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const out = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
  }

  function setState(on) {
    btn.classList.toggle('push-on', on);
    btn.title = on ? 'Notifiche attive — tocca per disattivare' : 'Attiva notifiche breaking news';
    const icon = btn.querySelector('i');
    if (icon) icon.className = on ? 'fas fa-bell' : 'far fa-bell';
    const lbl = btn.querySelector('.push-label');
    if (lbl) lbl.textContent = on ? 'Notifiche ON' : 'Notifiche';
  }

  async function getReg() {
    return await navigator.serviceWorker.register('service-worker.js');
  }

  async function refreshState() {
    try {
      const reg = await navigator.serviceWorker.ready;
      const sub = await reg.pushManager.getSubscription();
      setState(!!sub);
    } catch (e) { setState(false); }
  }

  async function subscribe() {
    const perm = await Notification.requestPermission();
    if (perm !== 'granted') {
      alert('Per ricevere le notifiche devi consentirle nel browser.');
      return;
    }
    const reg = await getReg();
    await navigator.serviceWorker.ready;

    // chiave pubblica dal server
    const { publicKey } = await (await fetch('vapid_public.php')).json();
    if (!publicKey || publicKey.indexOf('INCOLLA') === 0) {
      alert('Notifiche non ancora configurate sul server (chiave VAPID mancante).');
      return;
    }

    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlB64ToUint8Array(publicKey)
    });

    await fetch('subscribe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'subscribe', subscription: sub })
    });
    setState(true);
  }

  async function unsubscribe() {
    const reg = await navigator.serviceWorker.ready;
    const sub = await reg.pushManager.getSubscription();
    if (sub) {
      await fetch('subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'unsubscribe', subscription: sub })
      });
      await sub.unsubscribe();
    }
    setState(false);
  }

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    try {
      const reg = await navigator.serviceWorker.ready;
      const sub = await reg.pushManager.getSubscription();
      if (sub) await unsubscribe(); else await subscribe();
    } catch (e) {
      console.error('Push error:', e);
      alert('Si è verificato un errore con le notifiche.');
    } finally {
      btn.disabled = false;
    }
  });

  // stato iniziale
  getReg().then(refreshState).catch(() => setState(false));
})();
