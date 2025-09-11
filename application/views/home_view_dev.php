<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body {
      background-color: #ffffff;
      /* putih */
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      width: 80%;
      max-width: 900px;
      background: #fff;
      color: #333;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      border-radius: 6px;
      position: relative;
      z-index: 2;
    }

    .login-left {
      text-align: center;
      border-right: 1px solid #e0e0e0;
      padding: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-left img {
      max-width: 350px;
      /* ukuran logo lebih besar */
      width: 100%;
      height: auto;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-right {
      padding: 40px;
    }

    .login-right h2 {
      margin-bottom: 10px;
      font-weight: bold;
      color: #222;
    }

    .login-right p {
      margin-bottom: 30px;
      font-size: 13px;
      color: #777;
    }

    .form-control {
      margin-bottom: 15px;
      border-radius: 0;
      box-shadow: none;
    }

    .btn-login {
      background-color: #f26b3a;
      border: none;
      color: #fff;
      width: 100%;
      padding: 10px;
      border-radius: 3px;
      font-weight: bold;
    }

    .forgot-link {
      display: block;
      margin-top: 15px;
      text-align: center;
      color: #555;
    }

    .err {
      color: #d9534f;
      margin-bottom: 10px;
    }

    /* overlay putih full screen */
    #loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.95);
      /* putih semi transparan */
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      font-family: Arial, sans-serif;
    }

    /* animasi spinner */
    .spinner {
      border: 5px solid #f3f3f3;
      border-top: 5px solid #333;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin-bottom: 15px;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* teks loading */
    .loading-text {
      font-size: 16px;
      color: #333;
    }
  </style>
</head>

<body>

  <!-- UI modification by Rizal -->

  <!-- Overlay -->
  <div id="loading-overlay">
    <div class="spinner"></div>
    <div class="loading-text">Loading data, please wait...</div>
  </div>

  <div class="container login-container">
    <div class="row">
      <!-- Left Side -->
      <div class="col-sm-6 login-left">
        <img src="<?= base_url('/assets/images/logo_pii_new.png') ?>" alt="Logo">
      </div>
      <!-- Right Side -->
      <div class="col-sm-6 login-right">
        <h2>Welcome</h2>
        <p>Please login to Admin Dashboard.</p>

        <div class="err"><?php echo $msg; ?></div>

        <form method="post" action="" id="loginForm">
          <input name="username" class="form-control" id="username" type="text" placeholder="Username">
          <?php echo form_error('username', '<div class="err"><span>', '</span></div>'); ?>

          <input name="password" class="form-control" id="password" type="password" placeholder="Password">
          <?php echo form_error('password', '<div class="err"><span>', '</span></div>'); ?>

          <button type="submit" class="btn btn-login">Login</button>
        </form>
      </div>


      <p class="text text-info">UI Updated Test By: Rizal</p>
    </div>
  </div>


  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    $(document).ready(function() {
      // Sembunyikan overlay di awal
      $("#loading-overlay").hide();

      // Saat form login disubmit
      $("#loginForm").on("submit", function() {
        $("#loading-overlay").show(); // tampilkan overlay
      });
    });
  </script>


</body>

</html>