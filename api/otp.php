<?php
// Pure OTP (One-Time Pad) logic: letter<->number conversion, validation,
// encryption and decryption. No HTML/output here so this can be reused
// or tested independently of the web front end.

declare(strict_types=1);

function letter_to_num(string $letter): int
{
    return ord(strtoupper($letter)) - 65;
}

function num_to_letter(int $num): string
{
    $wrapped = (($num % 26) + 26) % 26;
    return chr($wrapped + 65);
}

/**
 * @return string[] list of human-readable error messages (empty = valid)
 */
function validate_input(string $text, string $key): array
{
    $errors = [];

    if ($text === '') {
        $errors[] = 'Error: The text field cannot be empty.';
    }

    if ($key === '') {
        $errors[] = 'Error: The key cannot be empty.';
    }

    if ($text !== '' && $key !== '' && strlen($key) !== strlen($text)) {
        $errors[] = 'Error: The key must have the same length as the plaintext.';
    }

    if ($text !== '' && !preg_match('/^[A-Za-z]+$/', $text)) {
        $errors[] = 'Error: The text may only contain letters A-Z.';
    }

    if ($key !== '' && !preg_match('/^[A-Za-z]+$/', $key)) {
        $errors[] = 'Error: The key may only contain letters A-Z.';
    }

    return $errors;
}

/**
 * Encrypts $plaintext with $key using OTP addition mod 26.
 *
 * @return array{ciphertext: string, rows: array<int, array{
 *     letter: string, value: string, key_letter: string, key_value: string,
 *     combined: int, mod: string, result: string
 * }>}
 */
function otp_encrypt(string $plaintext, string $key): array
{
    $plaintext = strtoupper($plaintext);
    $key = strtoupper($key);

    $rows = [];
    $ciphertext = '';

    for ($i = 0; $i < strlen($plaintext); $i++) {
        $pLetter = $plaintext[$i];
        $kLetter = $key[$i];

        $p = letter_to_num($pLetter);
        $k = letter_to_num($kLetter);
        $sum = $p + $k;
        $mod = $sum % 26;
        $cipherLetter = num_to_letter($mod);

        $rows[] = [
            'letter' => $pLetter,
            'value' => sprintf('%02d', $p),
            'key_letter' => $kLetter,
            'key_value' => sprintf('%02d', $k),
            'combined' => $sum,
            'mod' => sprintf('%02d', $mod),
            'result' => $cipherLetter,
        ];

        $ciphertext .= $cipherLetter;
    }

    return ['ciphertext' => $ciphertext, 'rows' => $rows];
}

/**
 * Decrypts $ciphertext with $key using OTP subtraction mod 26.
 *
 * @return array{plaintext: string, rows: array<int, array{
 *     letter: string, value: string, key_letter: string, key_value: string,
 *     combined: int, mod: string, result: string
 * }>}
 */
function otp_decrypt(string $ciphertext, string $key): array
{
    $ciphertext = strtoupper($ciphertext);
    $key = strtoupper($key);

    $rows = [];
    $plaintext = '';

    for ($i = 0; $i < strlen($ciphertext); $i++) {
        $cLetter = $ciphertext[$i];
        $kLetter = $key[$i];

        $c = letter_to_num($cLetter);
        $k = letter_to_num($kLetter);
        $diff = $c - $k;
        $mod = (($diff % 26) + 26) % 26;
        $plainLetter = num_to_letter($mod);

        $rows[] = [
            'letter' => $cLetter,
            'value' => sprintf('%02d', $c),
            'key_letter' => $kLetter,
            'key_value' => sprintf('%02d', $k),
            'combined' => $diff,
            'mod' => sprintf('%02d', $mod),
            'result' => $plainLetter,
        ];

        $plaintext .= $plainLetter;
    }

    return ['plaintext' => $plaintext, 'rows' => $rows];
}
