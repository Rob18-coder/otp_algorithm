# One-Time Pad (OTP) Algorithm

A PHP web app that implements the One-Time Pad encryption/decryption algorithm, built for the "Developing a One-Time Pad (OTP) Encryption and Decryption Program" activity.

## Features

- Encrypt plaintext with a key of the same length using OTP addition mod 26 (`C = (P + K) mod 26`).
- Decrypt ciphertext with a key using OTP subtraction mod 26 (`P = (C - K) mod 26`), correctly handling negative differences.
- A–Z ↔ 00–25 conversion reference table.
- Step-by-step computation tables for both encryption and decryption.
- Input validation: empty fields, mismatched key/text length, and non A–Z characters, each with a descriptive error message.
- Reset/clear button for each form.

## Project structure

```
api/
  index.php   # HTML form + request handling + rendering
  otp.php     # Pure OTP logic: conversion, validation, encrypt, decrypt
vercel.json   # Routes all requests to api/index.php via the vercel-php runtime
```

## Running locally

Requires PHP 7.4+.

```bash
cd api
php -S 127.0.0.1:8000 index.php
```

Then open http://127.0.0.1:8000/ in a browser.

## Deploying to Vercel

This project uses the [`vercel-php`](https://github.com/vercel-community/php) community runtime so PHP can run as a Vercel serverless function. `vercel.json` is already configured:

```json
{
  "functions": {
    "api/*.php": { "runtime": "vercel-php@0.9.0" }
  },
  "routes": [
    { "src": "/(.*)", "dest": "/api/index.php" }
  ]
}
```

Import this repository into Vercel (no build step or environment variables are required) and deploy — the root route will serve `api/index.php`.

## Example (from the activity)

- Plaintext: `GAHOD`, Key: `FXIVL` → Ciphertext: `LXPJO`
- Ciphertext: `LXPJO`, Key: `FXIVL` → Plaintext: `GAHOD`
