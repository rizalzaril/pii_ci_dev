<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>File Tidak Dapat Diakses</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #f0f2f5, #dfe9f3);
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      padding-top: 20px;
      color: #333;
    }

    .error-template {
      background: #fff;
      padding: 40px 30px;
      text-align: center;
      border-radius: 10px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      /* transition: all 0.3s ease-in-out; */
    }

    .error-template:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .error-header {
      font-size: 90px;
      color: #dc3545;
      margin-bottom: 20px;
      animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }

      100% {
        transform: scale(1);
      }
    }

    h2 {
      font-weight: bold;
      margin-bottom: 20px;
    }

    .error-details {
      font-size: 16px;
      margin-bottom: 20px;
      color: #555;
    }

    .error-actions .btn {
      margin: 8px;
      padding: 12px 25px;
      font-size: 16px;
      border-radius: 30px;
      transition: all 0.2s ease-in-out;
    }

    .btn-dark {
      background-color: #000000ff;
      border-color: #898080ff;
      color: #fff;
    }

    .btn-dark:hover {
      background-color: #525659ff;
      border-color: #004999;
    }

    .btn-default {
      background-color: #f1f1f1;
      border-color: #ddd;
    }

    .btn-default:hover {
      background-color: #e0e0e0;
      border-color: #ccc;
    }

    .logo-img {
      margin: 25px auto;
      max-width: 50%;
      display: block;
      /* pastikan center */
      opacity: 0.9;
      transition: opacity 0.3s ease-in-out;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="error-template">
          <div class="error-header">
            <span class="glyphicon glyphicon-ban-circle"></span>
          </div>
          <h2>File Tidak Dapat Diakses</h2>
          <div class="error-details">
            Maaf, file yang Anda cari tidak ditemukan atau akses dibatasi.
          </div>

          <img src="<?= base_url('/assets/images/logo_pii_new.png') ?>"
            alt="Logo" class="logo-img img-responsive center-block">

          <div class="error-actions">
            <a onclick="window.location.href='<?php echo base_url('admin/home'); ?>'"
              class="btn btn-dark btn-lg">
              <span class="glyphicon glyphicon-key"></span> Akses Login
            </a>

            <a href="javascript:history.back()" class="btn btn-default btn-lg">
              <span class="glyphicon glyphicon-arrow-left"></span> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>