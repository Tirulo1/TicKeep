<?php // partials/header_auth.php ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'TicKeep' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box}
    body{
      font-family:'Poppins',sans-serif;
      background:linear-gradient(135deg,#0f1f6e 0%,#1D4ED8 55%,#38bdf8 100%);
      min-height:100vh;
      margin:0;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:2rem 1rem;
      color:#111827;
    }
    .tk-auth-card{
      background:#fff;
      border-radius:20px;
      box-shadow:0 8px 40px rgba(0,0,0,.25);
      padding:2.75rem 2.5rem;
      width:100%;
      max-width:460px;
    }
    .tk-auth-logo{
      text-align:center;
      font-size:2.4rem;
      font-weight:700;
      color:#1D4ED8;
      letter-spacing:-0.5px;
      margin-bottom:.15rem;
    }
    .tk-auth-page-title{
      text-align:center;
      font-size:1.2rem;
      font-weight:600;
      color:#111827;
      margin-bottom:1.75rem;
    }
    .form-label{
      font-size:.88rem;
      font-weight:600;
      color:#374151;
      margin-bottom:.4rem;
      display:block;
    }
    .form-control{
      width:100%;
      border:1.5px solid #E2E6EA;
      border-radius:10px!important;
      font-family:'Poppins',sans-serif;
      font-size:.95rem;
      color:#111827;
      background:#fff;
      padding:.75rem 1rem;
      transition:border-color .2s,box-shadow .2s;
      outline:none;
    }
    .form-control::placeholder{color:#B0B8C1}
    .form-control:focus{
      border-color:#1D4ED8;
      box-shadow:0 0 0 3px rgba(29,78,216,.10);
    }
    .form-control-lg{font-size:.95rem!important;padding:.75rem 1rem!important}
    .input-pw{position:relative}
    .input-pw .form-control{padding-right:3rem}
    .toggle-pw{
      position:absolute;right:.85rem;top:50%;
      transform:translateY(-50%);
      background:none;border:none;cursor:pointer;
      color:#6B7280;padding:.2rem;
      display:flex;align-items:center;
      transition:color .2s;line-height:1;
    }
    .toggle-pw:hover{color:#1D4ED8}
    .btn-auth{
      display:block;width:100%;
      background:#1D4ED8;border:none;
      border-radius:10px;
      font-family:'Poppins',sans-serif;
      font-size:1rem;font-weight:600;
      color:#fff;padding:.85rem 1.5rem;
      cursor:pointer;
      transition:background .2s,transform .15s,box-shadow .2s;
      box-shadow:0 2px 10px rgba(29,78,216,.30);
      margin-top:.5rem;text-align:center;text-decoration:none;
    }
    .btn-auth:hover{
      background:#1e40af;
      transform:translateY(-1px);
      box-shadow:0 4px 14px rgba(29,78,216,.38);
      color:#fff;
    }
    .btn-auth:active{transform:translateY(0)}
    .mb-f{margin-bottom:1.1rem}
    .tk-alert{
      display:flex;align-items:center;gap:.5rem;
      border-radius:10px;font-size:.87rem;
      padding:.7rem 1rem;margin-bottom:1rem;
    }
    .tk-alert-danger {background:#FEE2E2;color:#991B1B;border:1px solid #FECACA}
    .tk-alert-success{background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0}
    .tk-alert-warning{background:#FEF3C7;color:#92400E;border:1px solid #FDE68A}
    .tk-alert-info   {background:#EFF6FF;color:#1e40af;border:1px solid #BFDBFE}
    .tk-switch{
      text-align:center;margin-top:1.25rem;
      font-size:.88rem;color:#6B7280;
    }
    .tk-switch a{color:#1D4ED8;font-weight:600;text-decoration:none}
    .tk-switch a:hover{text-decoration:underline}
    .tk-forgot{text-align:right;margin-top:-.4rem;margin-bottom:1rem}
    .tk-forgot a{font-size:.82rem;color:#6B7280;text-decoration:none}
    .tk-forgot a:hover{color:#1D4ED8;text-decoration:underline}
    @media(max-width:480px){
      .tk-auth-card{padding:2rem 1.5rem}
      .tk-auth-logo{font-size:2rem}
    }
  </style>
</head>
<body>
<div class="tk-auth-card">
  <div class="tk-auth-logo">TicKeep</div>
  <h2 class="tk-auth-page-title"><?= isset($authTitle) ? htmlspecialchars($authTitle) : '' ?></h2>