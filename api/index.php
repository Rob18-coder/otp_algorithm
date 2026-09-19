<?php

declare(strict_types=1);

require __DIR__ . '/otp.php';

$mode = $_POST['mode'] ?? null;

$plaintextInput = $_POST['plaintext'] ?? '';
$encryptKeyInput = $_POST['encrypt_key'] ?? '';
$ciphertextInput = $_POST['ciphertext'] ?? '';
$decryptKeyInput = $_POST['decrypt_key'] ?? '';

$encryptErrors = [];
$decryptErrors = [];
$encryptResult = null;
$decryptResult = null;

if ($mode === 'encrypt') {
    $encryptErrors = validate_input($plaintextInput, $encryptKeyInput);
    if (empty($encryptErrors)) {
        $encryptResult = otp_encrypt($plaintextInput, $encryptKeyInput);
    }
} elseif ($mode === 'decrypt') {
    $decryptErrors = validate_input($ciphertextInput, $decryptKeyInput);
    if (empty($decryptErrors)) {
        $decryptResult = otp_decrypt($ciphertextInput, $decryptKeyInput);
    }
}

/** @param array<int, array<string, mixed>> $rows */
function render_table(array $rows, string $inputHeader, string $keyHeader, string $opHeader, string $outputHeader): string
{
    $html = '<table class="otp-table"><thead><tr>'
        . "<th>$inputHeader</th><th>Value</th><th>$keyHeader</th><th>Value</th>"
        . "<th>$opHeader</th><th>Mod 26</th><th>$outputHeader</th>"
        . '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $html .= '<tr>'
            . '<td>' . htmlspecialchars((string) $row['letter']) . '</td>'
            . '<td>' . htmlspecialchars((string) $row['value']) . '</td>'
            . '<td>' . htmlspecialchars((string) $row['key_letter']) . '</td>'
            . '<td>' . htmlspecialchars((string) $row['key_value']) . '</td>'
            . '<td>' . htmlspecialchars((string) $row['combined']) . '</td>'
            . '<td>' . htmlspecialchars((string) $row['mod']) . '</td>'
            . '<td class="result">' . htmlspecialchars((string) $row['result']) . '</td>'
            . '</tr>';
    }

    $html .= '</tbody></table>';
    return $html;
}

function render_errors(array $errors): string
{
    if (empty($errors)) {
        return '';
    }
    $html = '<div class="errors">';
    foreach ($errors as $error) {
        $html .= '<p>' . htmlspecialchars($error) . '</p>';
    }
    $html .= '</div>';
    return $html;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES);
}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>One-Time Pad (OTP)</title>
<style>
    :root {
        --bg: #eef1f5;
        --panel: #ffffff;
        --header: #0d1b2a;
        --accent: #c9a227;
        --result: #cfe8d5;
        --error: #b3261e;
        --border: #d7dde5;
    }
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        background: var(--bg);
        margin: 0;
        padding: 0 0 40px;
        color: #1b1f24;
    }
    header {
        background: var(--header);
        color: #fff;
        text-align: center;
        padding: 20px 10px;
        margin-bottom: 24px;
    }
    header h1 { margin: 0; font-size: 1.6rem; letter-spacing: 1px; }
    main {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 16px;
    }
    section.panel {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
    }
    section.panel h2 {
        margin-top: 0;
        font-size: 1.1rem;
    }
    .field-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        margin-bottom: 12px;
    }
    .field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1 1 200px;
    }
    label { font-size: 0.85rem; font-weight: 600; }
    input[type="text"] {
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 1rem;
        text-transform: uppercase;
    }
    button {
        background: var(--header);
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
    }
    button.secondary {
        background: #6c757d;
    }
    button:hover { opacity: 0.9; }
    .errors {
        background: #fbeaea;
        border: 1px solid var(--error);
        color: var(--error);
        padding: 10px 14px;
        border-radius: 4px;
        margin-bottom: 12px;
    }
    .errors p { margin: 2px 0; }
    table.otp-table {
        width: 100%;
        border-collapse: collapse;
        margin: 14px 0;
        font-size: 0.9rem;
    }
    table.otp-table th {
        background: var(--accent);
        color: #fff;
        padding: 8px;
        text-align: center;
    }
    table.otp-table td {
        border: 1px solid var(--border);
        padding: 6px 8px;
        text-align: center;
    }
    table.otp-table td.result {
        background: var(--result);
        font-weight: 700;
    }
    .summary {
        background: #e8f0fb;
        border: 1px solid #a9c6ec;
        border-radius: 4px;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        margin-top: 8px;
    }
    .reference-table {
        overflow-x: auto;
    }
    .reference-table table {
        border-collapse: collapse;
        margin: 0 auto;
        font-size: 0.8rem;
    }
    .reference-table th, .reference-table td {
        border: 1px solid var(--border);
        padding: 4px 8px;
        text-align: center;
    }
    .reference-table th { background: var(--header); color: #fff; }
    .actions { text-align: right; margin-top: 24px; }
    .actions a { color: #495057; text-decoration: none; font-size: 0.9rem; }
    .actions a:hover { text-decoration: underline; }
</style>
</head>
<body>

<header>
    <h1>ONE-TIME PAD (OTP)</h1>
</header>

<main>

    <section class="panel reference-table">
        <h2>A&ndash;Z &harr; 00&ndash;25 Reference</h2>
        <table>
            <tr>
                <th>Letter</th>
<?php foreach (range('A', 'Z') as $letter): ?>
                <td><?= e($letter) ?></td>
<?php endforeach; ?>
            </tr>
            <tr>
                <th>Value</th>
<?php foreach (range(0, 25) as $value): ?>
                <td><?= sprintf('%02d', $value) ?></td>
<?php endforeach; ?>
            </tr>
        </table>
    </section>

    <section class="panel">
        <h2>Encryption</h2>
        <form method="post">
            <input type="hidden" name="mode" value="encrypt">
            <div class="field-row">
                <div class="field">
                    <label for="plaintext">Plaintext</label>
                    <input type="text" id="plaintext" name="plaintext" value="<?= e($plaintextInput) ?>" placeholder="e.g. GAHOD">
                </div>
                <div class="field">
                    <label for="encrypt_key">Key</label>
                    <input type="text" id="encrypt_key" name="encrypt_key" value="<?= e($encryptKeyInput) ?>" placeholder="e.g. FXIVL">
                </div>
                <button type="submit">ENCRYPT</button>
                <button type="reset" class="secondary" formnovalidate onclick="window.location.href=window.location.pathname">CLEAR</button>
            </div>
        </form>

        <?= render_errors($encryptErrors) ?>

        <?php if ($encryptResult !== null): ?>
            <h3>Step-by-step Encryption</h3>
            <?= render_table($encryptResult['rows'], 'Plain Letter', 'Key Letter', 'P + K', 'Cipher') ?>
            <div class="summary">
                "<?= e(strtoupper($plaintextInput)) ?>" + key "<?= e(strtoupper($encryptKeyInput)) ?>" = ciphertext "<?= e($encryptResult['ciphertext']) ?>"
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Decryption</h2>
        <form method="post">
            <input type="hidden" name="mode" value="decrypt">
            <div class="field-row">
                <div class="field">
                    <label for="ciphertext">Ciphertext</label>
                    <input type="text" id="ciphertext" name="ciphertext" value="<?= e($ciphertextInput) ?>" placeholder="e.g. LXPJO">
                </div>
                <div class="field">
                    <label for="decrypt_key">Key</label>
                    <input type="text" id="decrypt_key" name="decrypt_key" value="<?= e($decryptKeyInput) ?>" placeholder="e.g. FXIVL">
                </div>
                <button type="submit">DECRYPT</button>
                <button type="reset" class="secondary" formnovalidate onclick="window.location.href=window.location.pathname">CLEAR</button>
            </div>
        </form>

        <?= render_errors($decryptErrors) ?>

        <?php if ($decryptResult !== null): ?>
            <h3>Step-by-step Decryption</h3>
            <?= render_table($decryptResult['rows'], 'Cipher Letter', 'Key Letter', 'C − K', 'Plain') ?>
            <div class="summary">
                "<?= e(strtoupper($ciphertextInput)) ?>" &minus; key "<?= e(strtoupper($decryptKeyInput)) ?>" = plaintext "<?= e($decryptResult['plaintext']) ?>"
            </div>
        <?php endif; ?>
    </section>

</main>

</body>
</html>
