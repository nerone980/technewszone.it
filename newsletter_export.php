<?php
// =================================================================
// FILE: newsletter_export.php — Scarica la lista email (protetta)
// =================================================================
// Apri nel browser: chiede una password, poi scarica il CSV.
// CAMBIA la password qui sotto prima di caricarlo!
// =================================================================

$PASSWORD = 'CAMBIA_QUESTA_PASSWORD'; // <-- imposta una tua password

$file = __DIR__ . '/newsletter_emails.csv';

session_start();

// Login semplice
if (isset($_POST['pwd'])) {
    if ($_POST['pwd'] === $PASSWORD) {
        $_SESSION['nl_auth'] = true;
    } else {
        $err = 'Password errata';
    }
}

$authed = !empty($_SESSION['nl_auth']);

// Download del CSV se autenticato
if ($authed && isset($_GET['download'])) {
    if (file_exists($file)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter_emails.csv"');
        readfile($file);
    } else {
        echo "Nessuna email raccolta finora.";
    }
    exit;
}

// Conta iscritti
$count = 0;
if (file_exists($file)) {
    $count = count(file($file, FILE_SKIP_EMPTY_LINES));
}
?>
<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8">
<title>Export Newsletter</title>
<style>
body{background:#0a0c0f;color:#e6edf3;font-family:system-ui,sans-serif;display:flex;
    align-items:center;justify-content:center;min-height:100vh;margin:0}
.box{background:#0f1318;border:1px solid #1c2128;border-radius:12px;padding:32px;width:340px}
h2{margin:0 0 20px;font-size:1.2rem}
input{width:100%;padding:11px;background:#06080a;border:1px solid #1c2128;border-radius:8px;
    color:#e6edf3;font-size:.95rem;box-sizing:border-box;margin-bottom:14px}
button{width:100%;padding:11px;background:#e8b04b;border:none;border-radius:8px;color:#0a0c0f;
    font-weight:700;font-size:.95rem;cursor:pointer}
a.dl{display:block;text-align:center;background:#e8b04b;color:#0a0c0f;text-decoration:none;
    padding:12px;border-radius:8px;font-weight:700;margin-top:10px}
.err{color:#f85149;font-size:.85rem;margin-bottom:12px}
.count{color:#8b949e;font-size:.9rem;margin-bottom:18px}
</style></head><body>
<div class="box">
<?php if (!$authed): ?>
    <h2>Export Newsletter</h2>
    <?php if (!empty($err)): ?><div class="err"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="post">
        <input type="password" name="pwd" placeholder="Password" autofocus>
        <button type="submit">Entra</button>
    </form>
<?php else: ?>
    <h2>Lista iscritti</h2>
    <div class="count"><?php echo $count; ?> email raccolte</div>
    <a class="dl" href="?download=1">Scarica CSV</a>
<?php endif; ?>
</div>
</body></html>
