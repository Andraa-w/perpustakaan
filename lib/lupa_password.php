<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lib Digital-X - Lupa Password</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0b0c0f;
    --surface: #111318;
    --surface2: #181b22;
    --border: #252830;
    --border-glow: #c8a96e44;
    --gold: #c8a96e;
    --gold-soft: #d4b87a;
    --gold-dim: #8a7248;
    --text: #e8e2d9;
    --text-muted: #6b6459;
    --text-soft: #9e9488;
    --red: #c0614a;
    --green: #5a8a6a;
    --blue: #4a7a9b;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Mono', monospace;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
  }

  /* ── Background ── */
  .bg-layer {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
  }

  .bg-grid {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(var(--border) 1px, transparent 1px),
      linear-gradient(90deg, var(--border) 1px, transparent 1px);
    background-size: 48px 48px;
    opacity: 0.35;
    mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 100%);
  }

  .bg-glow {
    position: absolute;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, #c8a96e0e 0%, transparent 70%);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: breathe 6s ease-in-out infinite;
  }

  .bg-glow2 {
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, #4a6a9918 0%, transparent 70%);
    top: 15%;
    left: 10%;
    animation: breathe 9s ease-in-out infinite reverse;
  }

  @keyframes breathe {
    0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
  }

  .noise {
    position: absolute;
    inset: 0;
    opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    background-size: 200px 200px;
  }

  /* ── Book Spines ── */
  .spines {
    position: fixed;
    top: 0; bottom: 0;
    width: 48px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    z-index: 1;
    opacity: 0.4;
  }
  .spines.left { left: 0; }
  .spines.right { right: 0; transform: scaleX(-1); }

  .spine {
    flex: 1;
    background: var(--surface2);
    border-right: 1px solid var(--border);
    min-height: 20px;
    max-height: 80px;
    animation: spineLoad 0.8s ease both;
  }
  .spine:nth-child(even) { background: var(--surface); border-right-color: #1e2128; }
  .spine:nth-child(3n) { border-right: 2px solid var(--gold-dim); opacity: 0.6; }

  @keyframes spineLoad {
    from { transform: scaleX(0); opacity: 0; }
    to { transform: scaleX(1); opacity: 1; }
  }

  /* ── Card ── */
  .card {
    position: relative;
    z-index: 10;
    width: 440px;
    background: var(--surface);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow:
      0 0 0 1px #ffffff06,
      0 32px 64px #00000080,
      0 0 80px var(--border-glow);
    animation: cardReveal 1s cubic-bezier(0.16, 1, 0.3, 1) both;
  }

  @keyframes cardReveal {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* ── Corner Decorations ── */
  .corner {
    position: absolute;
    width: 12px;
    height: 12px;
    z-index: 2;
  }
  .corner::before, .corner::after {
    content: '';
    position: absolute;
    background: var(--gold-dim);
  }
  .corner::before { width: 100%; height: 1px; top: 0; }
  .corner::after  { width: 1px; height: 100%; }
  .corner.tl { top: -1px; left: -1px; }
  .corner.tl::after { left: 0; top: 0; }
  .corner.tr { top: -1px; right: -1px; transform: scaleX(-1); }
  .corner.tr::after { left: 0; top: 0; }
  .corner.bl { bottom: -1px; left: -1px; transform: scaleY(-1); }
  .corner.bl::after { left: 0; top: 0; }
  .corner.br { bottom: -1px; right: -1px; transform: scale(-1); }
  .corner.br::after { left: 0; top: 0; }

  /* ── Header ── */
  .card-header {
    padding: 32px 40px 24px;
    border-bottom: 1px solid var(--border);
    position: relative;
    overflow: hidden;
  }

  .card-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
  }

  .catalog-id {
    position: absolute;
    top: 36px;
    right: 40px;
    font-size: 8px;
    letter-spacing: 0.15em;
    color: var(--text-muted);
    opacity: 0.5;
  }

  .header-deco {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    animation: fadeUp 0.8s 0.2s ease both;
  }

  .key-icon {
    width: 30px;
    height: 30px;
    color: var(--gold);
    flex-shrink: 0;
  }

  .divider-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, var(--gold-dim), transparent);
  }

  .brand {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 300;
    letter-spacing: 0.12em;
    color: var(--text);
    line-height: 1;
    animation: fadeUp 0.8s 0.3s ease both;
  }

  .brand em {
    font-style: italic;
    color: var(--gold-soft);
  }

  .subtitle {
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-top: 6px;
    animation: fadeUp 0.8s 0.4s ease both;
  }

  /* ── Step Indicator ── */
  .steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin-top: 20px;
    animation: fadeUp 0.8s 0.45s ease both;
  }

  .step {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
  }

  .step-num {
    width: 22px;
    height: 22px;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    letter-spacing: 0;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.4s;
    position: relative;
  }

  .step-num.active {
    border-color: var(--gold-dim);
    color: var(--gold-soft);
    background: #c8a96e0a;
  }

  .step-num.done {
    border-color: var(--green);
    color: var(--green);
    background: #5a8a6a0a;
  }

  .step-num.done::after {
    content: '✓';
    position: absolute;
    font-size: 10px;
  }

  .step-num.done span { display: none; }

  .step-label {
    font-size: 8px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    transition: color 0.4s;
  }

  .step.active .step-label { color: var(--gold-dim); }
  .step.done .step-label { color: var(--green); opacity: 0.7; }

  .step-connector {
    width: 20px;
    height: 1px;
    background: var(--border);
    margin: 0 4px;
    flex-shrink: 0;
    transition: background 0.4s;
  }

  .step-connector.done { background: var(--green); opacity: 0.4; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ── Body ── */
  .card-body {
    padding: 30px 40px 34px;
    min-height: 260px;
  }

  /* ── Alert ── */
  .alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 11px 14px;
    border: 1px solid;
    margin-bottom: 22px;
    animation: fadeUp 0.3s ease both;
  }

  .alert.error {
    border-color: var(--red);
    background: #c0614a0d;
  }

  .alert.success {
    border-color: var(--green);
    background: #5a8a6a0d;
  }

  .alert.info {
    border-color: var(--blue);
    background: #4a7a9b0d;
  }

  .alert-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    margin-top: 1px;
  }

  .alert.error .alert-icon { color: var(--red); }
  .alert.success .alert-icon { color: var(--green); }
  .alert.info .alert-icon { color: var(--blue); }

  .alert-text {
    font-size: 10px;
    letter-spacing: 0.06em;
    line-height: 1.6;
  }

  .alert.error .alert-text { color: var(--red); }
  .alert.success .alert-text { color: var(--green); }
  .alert.info .alert-text { color: #7aaac8; }

  /* ── Fields ── */
  .field {
    margin-bottom: 20px;
    animation: fadeUp 0.6s ease both;
  }

  label {
    display: block;
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 8px;
    transition: color 0.2s;
  }

  .input-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-icon {
    position: absolute;
    left: 14px;
    width: 14px;
    height: 14px;
    color: var(--text-muted);
    pointer-events: none;
    transition: color 0.2s;
    flex-shrink: 0;
  }

  input[type="text"],
  input[type="email"],
  input[type="password"] {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    font-weight: 300;
    padding: 13px 14px 13px 40px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    letter-spacing: 0.04em;
    caret-color: var(--gold);
    -webkit-appearance: none;
    border-radius: 0;
  }

  input::placeholder { color: var(--text-muted); }

  input:focus {
    border-color: var(--gold-dim);
    background: #14161c;
    box-shadow: 0 0 0 3px #c8a96e10, inset 0 1px 3px #00000040;
  }

  /* ── OTP Input ── */
  .otp-wrap {
    display: flex;
    gap: 8px;
  }

  .otp-wrap input {
    flex: 1;
    padding: 14px 8px;
    text-align: center;
    font-size: 18px;
    letter-spacing: 0;
    font-weight: 400;
  }

  /* ── Password Strength ── */
  .strength-bar {
    display: flex;
    gap: 4px;
    margin-top: 8px;
  }

  .strength-seg {
    flex: 1;
    height: 2px;
    background: var(--border);
    transition: background 0.3s;
  }

  .strength-seg.weak   { background: var(--red); }
  .strength-seg.medium { background: var(--gold-dim); }
  .strength-seg.strong { background: var(--green); }

  .strength-label {
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--text-muted);
    margin-top: 6px;
    text-transform: uppercase;
    transition: color 0.3s;
    min-height: 14px;
  }

  /* ── Hint text ── */
  .field-hint {
    font-size: 9px;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    margin-top: 6px;
    line-height: 1.6;
  }

  .pw-toggle {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color 0.2s;
  }
  .pw-toggle:hover { color: var(--gold-soft); }

  /* ── Resend timer ── */
  .resend-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
    margin-bottom: 24px;
  }

  .resend-timer {
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--text-muted);
    text-transform: uppercase;
  }

  .resend-btn {
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--gold-dim);
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'DM Mono', monospace;
    text-transform: uppercase;
    padding: 0;
    border-bottom: 1px solid transparent;
    transition: color 0.2s, border-color 0.2s;
  }

  .resend-btn:not(:disabled):hover {
    color: var(--gold-soft);
    border-color: var(--gold-dim);
  }

  .resend-btn:disabled {
    color: var(--text-muted);
    cursor: default;
  }

  /* ── Buttons ── */
  .btn-submit {
    width: 100%;
    padding: 15px;
    background: transparent;
    border: 1px solid var(--gold-dim);
    color: var(--gold-soft);
    font-family: 'DM Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: color 0.3s, border-color 0.3s;
    border-radius: 0;
    margin-bottom: 12px;
  }

  .btn-submit::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--gold-dim), #8a7248aa);
    opacity: 0;
    transition: opacity 0.3s;
  }

  .btn-submit:hover { color: var(--bg); border-color: var(--gold); }
  .btn-submit:hover::before { opacity: 1; }
  .btn-submit:disabled { opacity: 0.5; pointer-events: none; }

  .btn-text { position: relative; z-index: 1; }

  .btn-back {
    width: 100%;
    padding: 11px;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-muted);
    font-family: 'DM Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: color 0.2s, border-color 0.2s;
    border-radius: 0;
  }

  .btn-back:hover { color: var(--text-soft); border-color: var(--text-muted); }

  /* ── Success panel ── */
  .success-panel {
    text-align: center;
    padding: 20px 0 10px;
    animation: fadeUp 0.6s ease both;
  }

  .success-icon-wrap {
    width: 56px;
    height: 56px;
    border: 1px solid var(--green);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    position: relative;
    animation: successPop 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
  }

  .success-icon-wrap::before {
    content: '';
    position: absolute;
    inset: -4px;
    border: 1px solid var(--green);
    opacity: 0.3;
  }

  @keyframes successPop {
    from { transform: scale(0.7); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }

  .success-icon { color: var(--green); }

  .success-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 300;
    font-style: italic;
    color: var(--gold-soft);
    margin-bottom: 10px;
    letter-spacing: 0.08em;
  }

  .success-desc {
    font-size: 10px;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    line-height: 1.8;
    margin-bottom: 28px;
  }

  /* ── Footer ── */
  .card-footer {
    padding: 16px 40px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .footer-text {
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--text-muted);
    text-transform: uppercase;
  }

  .back-link {
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--gold-dim);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid transparent;
    transition: color 0.2s, border-color 0.2s;
    padding-bottom: 1px;
  }
  .back-link:hover { color: var(--gold-soft); border-color: var(--gold-dim); }

  /* ── Status dot ── */
  .status-dot {
    position: absolute;
    bottom: 14px;
    right: 14px;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--gold-dim);
    box-shadow: 0 0 6px var(--gold-dim);
    animation: pulse-dot 2s ease-in-out infinite;
  }

  @keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  /* ── Transitions between steps ── */
  .step-panel {
    animation: panelIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
  }

  @keyframes panelIn {
    from { opacity: 0; transform: translateX(16px); }
    to { opacity: 1; transform: translateX(0); }
  }
</style>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// Inisialisasi variabel
$step        = isset($_SESSION['fp_step']) ? $_SESSION['fp_step'] : 1;
$error_msg   = '';
$success_msg = '';
$info_msg    = '';

// ─────────────────────────────────────────────
// STEP 1 → Cek email / username & kirim kode OTP
// ─────────────────────────────────────────────
if (isset($_POST['step1'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error_msg = 'Masukkan email atau username terlebih dahulu.';
        $step = 1;
    } else {
        // Cek apakah email/username ada di database
        $stmt = $koneksi->prepare("SELECT * FROM user WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Generate kode OTP 6 digit
            $otp      = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp_hash = md5($otp); // Hash OTP sebelum disimpan
            $expired  = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Simpan OTP ke session (bisa juga ke database)
            $_SESSION['fp_otp']      = $otp_hash;
            $_SESSION['fp_expired']  = $expired;
            $_SESSION['fp_user_id']  = $user['id'];
            $_SESSION['fp_email']    = $user['email'];
            $_SESSION['fp_step']     = 2;
            $_SESSION['fp_attempts'] = 0;

            // ── Kirim email OTP ──
            // Ganti bagian ini dengan fungsi email yang kamu gunakan (PHPMailer, mail(), dsb.)
            // Contoh menggunakan mail() bawaan PHP:
            $to      = $user['email'];
            $subject = 'Kode Reset Password - Lib Digital-X';
            $message = "Kode OTP reset password Anda: {$otp}\n\nKode berlaku selama 10 menit.\nAbaikan email ini jika Anda tidak meminta reset password.";
            $headers = "From: noreply@libdigitalx.com\r\nX-Mailer: PHP/" . phpversion();

            // mail($to, $subject, $message, $headers);
            // ── Untuk development, tampilkan OTP langsung (hapus di production!) ──
            $info_msg = "Kode OTP telah dikirim ke email Anda. [DEV: {$otp}]";
            // Jika tidak bisa kirim email, hapus baris di atas dan aktifkan mail() di atas

            $step = 2;
        } else {
            $error_msg = 'Email atau username tidak ditemukan di sistem kami.';
            $step = 1;
        }
    }
}

// ─────────────────────────────────────────────
// STEP 2 → Verifikasi kode OTP
// ─────────────────────────────────────────────
if (isset($_POST['step2'])) {
    $otp_input = trim($_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6']);

    if (strlen($otp_input) < 6) {
        $error_msg = 'Masukkan 6 digit kode OTP yang dikirim ke email Anda.';
        $step = 2;
    } elseif (!isset($_SESSION['fp_otp']) || !isset($_SESSION['fp_expired'])) {
        $error_msg = 'Sesi habis. Silakan ulangi dari awal.';
        $step = 1;
        unset($_SESSION['fp_step'], $_SESSION['fp_otp'], $_SESSION['fp_expired'], $_SESSION['fp_user_id'], $_SESSION['fp_email']);
    } elseif (strtotime($_SESSION['fp_expired']) < time()) {
        $error_msg = 'Kode OTP sudah kadaluarsa. Silakan kirim ulang kode.';
        $step = 2;
    } elseif ($_SESSION['fp_attempts'] >= 5) {
        $error_msg = 'Terlalu banyak percobaan. Silakan mulai dari awal.';
        $step = 1;
        unset($_SESSION['fp_step'], $_SESSION['fp_otp'], $_SESSION['fp_expired'], $_SESSION['fp_user_id'], $_SESSION['fp_email'], $_SESSION['fp_attempts']);
    } elseif (md5($otp_input) !== $_SESSION['fp_otp']) {
        $_SESSION['fp_attempts']++;
        $sisa = 5 - $_SESSION['fp_attempts'];
        $error_msg = "Kode OTP salah. Sisa percobaan: {$sisa}x.";
        $step = 2;
    } else {
        // OTP valid
        $_SESSION['fp_step']     = 3;
        $_SESSION['fp_verified'] = true;
        unset($_SESSION['fp_otp']);
        $step = 3;
    }
}

// ─────────────────────────────────────────────
// STEP 3 → Reset password baru
// ─────────────────────────────────────────────
if (isset($_POST['step3'])) {
    if (!isset($_SESSION['fp_verified']) || $_SESSION['fp_verified'] !== true) {
        $error_msg = 'Akses tidak valid. Silakan ulangi dari awal.';
        $step = 1;
        unset($_SESSION['fp_step'], $_SESSION['fp_verified'], $_SESSION['fp_user_id']);
    } else {
        $pw_baru   = $_POST['password_baru'];
        $pw_konfirm = $_POST['password_konfirm'];

        if (strlen($pw_baru) < 6) {
            $error_msg = 'Password baru minimal 6 karakter.';
            $step = 3;
        } elseif ($pw_baru !== $pw_konfirm) {
            $error_msg = 'Konfirmasi password tidak cocok.';
            $step = 3;
        } else {
            $pw_hash  = md5($pw_baru);
            $user_id  = $_SESSION['fp_user_id'];

            $stmt = $koneksi->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $pw_hash, $user_id);

            if ($stmt->execute()) {
                // Bersihkan semua session lupa password
                unset($_SESSION['fp_step'], $_SESSION['fp_verified'], $_SESSION['fp_user_id'], $_SESSION['fp_email'], $_SESSION['fp_attempts']);
                $step = 4; // Step 4 = sukses
            } else {
                $error_msg = 'Terjadi kesalahan. Silakan coba lagi.';
                $step = 3;
            }
        }
    }
}

// ─────────────────────────────────────────────
// Resend OTP
// ─────────────────────────────────────────────
if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['fp_user_id'])) {
        $otp      = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_hash = md5($otp);
        $expired  = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $_SESSION['fp_otp']      = $otp_hash;
        $_SESSION['fp_expired']  = $expired;
        $_SESSION['fp_attempts'] = 0;

        // mail(...) → kirim ulang OTP
        $info_msg = "Kode OTP baru telah dikirim. [DEV: {$otp}]";
        $step = 2;
    }
}

// Sinkronkan step dari session
if ($step !== 4) {
    $_SESSION['fp_step'] = $step;
}
?>

<!-- ── Background ── -->
<div class="bg-layer">
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>
  <div class="bg-glow2"></div>
  <div class="noise"></div>
</div>

<!-- Book Spines -->
<div class="spines left">
  <?php for ($i = 0; $i < 16; $i++): ?>
  <div class="spine" style="max-height:<?= rand(20,80) ?>px;animation-delay:<?= 0.1 + $i * 0.05 ?>s"></div>
  <?php endfor; ?>
</div>
<div class="spines right">
  <?php for ($i = 0; $i < 16; $i++): ?>
  <div class="spine" style="max-height:<?= rand(20,80) ?>px;animation-delay:<?= 0.12 + $i * 0.05 ?>s"></div>
  <?php endfor; ?>
</div>

<!-- Card -->
<div class="card">
  <div class="corner tl"></div>
  <div class="corner tr"></div>
  <div class="corner bl"></div>
  <div class="corner br"></div>

  <!-- Header -->
  <div class="card-header">
    <div class="catalog-id">RST.2026</div>
    <div class="header-deco">
      <svg class="key-icon" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="1.2">
        <circle cx="11" cy="11" r="7"/>
        <path d="M16.5 16.5L26 26" stroke-linecap="round"/>
        <path d="M21 21l3-3" stroke-linecap="round"/>
        <circle cx="11" cy="11" r="2.5" fill="currentColor" opacity="0.3"/>
      </svg>
      <div class="divider-line"></div>
    </div>
    <div class="brand"><em>Lupa Password</em></div>
    <div class="subtitle">Pulihkan akses akun Anda</div>

    <!-- Step Indicator — hanya tampil di step 1-3 -->
    <?php if ($step <= 3): ?>
    <div class="steps" id="step-indicator">
      <div class="step <?= $step == 1 ? 'active' : ($step > 1 ? 'done' : '') ?>">
        <div class="step-num <?= $step == 1 ? 'active' : ($step > 1 ? 'done' : '') ?>"><span>1</span></div>
        <span class="step-label">Email</span>
      </div>
      <div class="step-connector <?= $step > 1 ? 'done' : '' ?>"></div>
      <div class="step <?= $step == 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">
        <div class="step-num <?= $step == 2 ? 'active' : ($step > 2 ? 'done' : '') ?>"><span>2</span></div>
        <span class="step-label">Verifikasi</span>
      </div>
      <div class="step-connector <?= $step > 2 ? 'done' : '' ?>"></div>
      <div class="step <?= $step == 3 ? 'active' : ($step > 3 ? 'done' : '') ?>">
        <div class="step-num <?= $step == 3 ? 'active' : '' ?>"><span>3</span></div>
        <span class="step-label">Reset</span>
      </div>
    </div>
    <?php endif; ?>

    <div class="status-dot"></div>
  </div>

  <!-- Body -->
  <div class="card-body">

    <!-- ── STEP 1: Input Email / Username ── -->
    <?php if ($step == 1): ?>
    <div class="step-panel">

      <?php if ($error_msg): ?>
      <div class="alert error">
        <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4">
          <circle cx="8" cy="8" r="7"/>
          <line x1="8" y1="5" x2="8" y2="8.5"/>
          <circle cx="8" cy="11" r="0.5" fill="currentColor"/>
        </svg>
        <span class="alert-text"><?= htmlspecialchars($error_msg) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="field" style="animation-delay:0.1s">
          <label for="email">Email atau Username</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
              <rect x="1" y="3" width="14" height="10" rx="1"/>
              <path d="M1 4l7 5 7-5"/>
            </svg>
            <input
              type="text"
              id="email"
              name="email"
              placeholder="email@contoh.com atau username"
              autocomplete="email"
              value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
              required
            >
          </div>
          <div class="field-hint">Masukkan email atau username yang terdaftar di sistem.</div>
        </div>

        <input type="hidden" name="step1" value="1">
        <button class="btn-submit" type="submit" id="btn1">
          <span class="btn-text">Kirim Kode Verifikasi</span>
        </button>
      </form>

      <a href="login.php">
        <button class="btn-back" type="button">← Kembali ke Login</button>
      </a>
    </div>

    <!-- ── STEP 2: Verifikasi OTP ── -->
    <?php elseif ($step == 2): ?>
    <div class="step-panel">

      <?php if ($error_msg): ?>
      <div class="alert error">
        <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4">
          <circle cx="8" cy="8" r="7"/>
          <line x1="8" y1="5" x2="8" y2="8.5"/>
          <circle cx="8" cy="11" r="0.5" fill="currentColor"/>
        </svg>
        <span class="alert-text"><?= htmlspecialchars($error_msg) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($info_msg): ?>
      <div class="alert info">
        <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4">
          <circle cx="8" cy="8" r="7"/>
          <line x1="8" y1="7" x2="8" y2="11"/>
          <circle cx="8" cy="5" r="0.5" fill="currentColor"/>
        </svg>
        <span class="alert-text"><?= htmlspecialchars($info_msg) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="" id="otpForm">
        <div class="field" style="animation-delay:0.1s">
          <label>Kode OTP (6 digit)</label>
          <div class="otp-wrap">
            <input type="text" name="otp1" id="otp1" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
            <input type="text" name="otp2" id="otp2" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
            <input type="text" name="otp3" id="otp3" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
            <input type="text" name="otp4" id="otp4" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
            <input type="text" name="otp5" id="otp5" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
            <input type="text" name="otp6" id="otp6" maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="0" autocomplete="off" required>
          </div>
          <div class="field-hint">Kode berlaku selama 10 menit sejak dikirim.</div>
        </div>

        <div class="resend-wrap">
          <span class="resend-timer" id="timer-text">Kirim ulang dalam <span id="countdown">60</span>s</span>
          <form method="POST" action="" style="display:inline">
            <input type="hidden" name="resend_otp" value="1">
            <button class="resend-btn" type="submit" id="resend-btn" disabled>Kirim Ulang</button>
          </form>
        </div>

        <input type="hidden" name="step2" value="1">
        <button class="btn-submit" type="submit">
          <span class="btn-text">Verifikasi Kode</span>
        </button>
      </form>

      <button class="btn-back" type="button" onclick="resetStep()">← Ganti Email</button>
    </div>

    <!-- ── STEP 3: Buat Password Baru ── -->
    <?php elseif ($step == 3): ?>
    <div class="step-panel">

      <?php if ($error_msg): ?>
      <div class="alert error">
        <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4">
          <circle cx="8" cy="8" r="7"/>
          <line x1="8" y1="5" x2="8" y2="8.5"/>
          <circle cx="8" cy="11" r="0.5" fill="currentColor"/>
        </svg>
        <span class="alert-text"><?= htmlspecialchars($error_msg) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="field" style="animation-delay:0.1s">
          <label for="password_baru">Password Baru</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
              <rect x="3" y="7" width="10" height="8" rx="1"/>
              <path d="M5 7V5a3 3 0 016 0v2"/>
              <circle cx="8" cy="11" r="1" fill="currentColor"/>
            </svg>
            <input type="password" id="password_baru" name="password_baru" placeholder="Min. 6 karakter" autocomplete="new-password" oninput="checkStrength(this.value)" required>
            <button class="pw-toggle" onclick="togglePw('password_baru','eye1')" type="button">
              <svg id="eye1" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z"/>
                <circle cx="8" cy="8" r="2"/>
              </svg>
            </button>
          </div>
          <div class="strength-bar">
            <div class="strength-seg" id="seg1"></div>
            <div class="strength-seg" id="seg2"></div>
            <div class="strength-seg" id="seg3"></div>
            <div class="strength-seg" id="seg4"></div>
          </div>
          <div class="strength-label" id="strength-label"></div>
        </div>

        <div class="field" style="animation-delay:0.2s">
          <label for="password_konfirm">Konfirmasi Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
              <rect x="3" y="7" width="10" height="8" rx="1"/>
              <path d="M5 7V5a3 3 0 016 0v2"/>
              <circle cx="8" cy="11" r="1" fill="currentColor"/>
            </svg>
            <input type="password" id="password_konfirm" name="password_konfirm" placeholder="Ulangi password baru" autocomplete="new-password" required>
            <button class="pw-toggle" onclick="togglePw('password_konfirm','eye2')" type="button">
              <svg id="eye2" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z"/>
                <circle cx="8" cy="8" r="2"/>
              </svg>
            </button>
          </div>
        </div>

        <input type="hidden" name="step3" value="1">
        <button class="btn-submit" type="submit">
          <span class="btn-text">Simpan Password Baru</span>
        </button>
      </form>
    </div>

    <!-- ── STEP 4: Sukses ── -->
    <?php elseif ($step == 4): ?>
    <div class="step-panel">
      <div class="success-panel">
        <div class="success-icon-wrap">
          <svg class="success-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="success-title">Password Berhasil Diubah</div>
        <div class="success-desc">
          Password akun Anda telah diperbarui.<br>
          Silakan login dengan password baru Anda.
        </div>
        <a href="login.php">
          <button class="btn-submit" type="button">
            <span class="btn-text">Masuk Sekarang →</span>
          </button>
        </a>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Footer -->
  <div class="card-footer">
    <span class="footer-text">Ingat password?</span>
    <a href="login.php" class="back-link">Login →</a>
  </div>
</div>

<script>
  // ── Toggle show/hide password ──
  function togglePw(fieldId, iconId) {
    const pw   = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    if (pw.type === 'password') {
      pw.type = 'text';
      icon.innerHTML = `<path d="M2 2l12 12M6.5 6.6A3 3 0 0111.4 11M4.2 4.3C2.5 5.5 1 8 1 8s3 5 7 5a7 7 0 003.8-1.1M8 3C12 3 15 8 15 8s-.7 1.1-1.8 2.2"/>`;
    } else {
      pw.type = 'password';
      icon.innerHTML = `<path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/>`;
    }
  }

  // ── OTP auto-focus ──
  const otpInputs = document.querySelectorAll('.otp-wrap input');
  otpInputs.forEach((input, i) => {
    input.addEventListener('input', e => {
      const val = e.target.value.replace(/\D/g, '');
      e.target.value = val;
      if (val && i < otpInputs.length - 1) otpInputs[i + 1].focus();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && i > 0) otpInputs[i - 1].focus();
    });
    input.addEventListener('paste', e => {
      e.preventDefault();
      const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      paste.split('').slice(0, 6).forEach((ch, j) => {
        if (otpInputs[i + j]) otpInputs[i + j].value = ch;
      });
      const next = Math.min(i + paste.length, otpInputs.length - 1);
      otpInputs[next].focus();
    });
  });

  // ── Countdown resend OTP ──
  const countdownEl = document.getElementById('countdown');
  const timerText   = document.getElementById('timer-text');
  const resendBtn   = document.getElementById('resend-btn');

  if (countdownEl) {
    let secs = 60;
    const timer = setInterval(() => {
      secs--;
      countdownEl.textContent = secs;
      if (secs <= 0) {
        clearInterval(timer);
        timerText.style.display = 'none';
        if (resendBtn) resendBtn.disabled = false;
      }
    }, 1000);
  }

  // ── Password strength checker ──
  function checkStrength(val) {
    const segs  = [document.getElementById('seg1'), document.getElementById('seg2'),
                   document.getElementById('seg3'), document.getElementById('seg4')];
    const label = document.getElementById('strength-label');
    if (!label) return;

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

    const classes = ['', 'weak', 'medium', 'strong'];
    const labels  = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
    const colors  = ['', 'var(--red)', 'var(--gold-dim)', 'var(--green)', 'var(--green)'];

    segs.forEach((s, i) => {
      s.className = 'strength-seg';
      if (i < score) {
        if (score <= 1) s.classList.add('weak');
        else if (score <= 2) s.classList.add('medium');
        else s.classList.add('strong');
      }
    });

    label.textContent = val.length ? labels[score] : '';
    label.style.color = colors[score];
  }

  // ── Reset ke step 1 ──
  function resetStep() {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = '';
    const h = document.createElement('input');
    h.type = 'hidden'; h.name = 'reset_step'; h.value = '1';
    f.appendChild(h); document.body.appendChild(f); f.submit();
  }
</script>

<?php
// Handle reset step dari tombol "Ganti Email"
if (isset($_POST['reset_step'])) {
    unset($_SESSION['fp_step'], $_SESSION['fp_otp'], $_SESSION['fp_expired'],
          $_SESSION['fp_user_id'], $_SESSION['fp_email'], $_SESSION['fp_attempts'], $_SESSION['fp_verified']);
    header("Location: lupa_password.php");
    exit();
}
?>
</body>
</html>   