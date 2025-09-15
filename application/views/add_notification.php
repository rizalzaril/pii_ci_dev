<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>

  <!DOCTYPE html>
  <html>

  <head>
    <title>Tambah Notifikasi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  </head>

  <body class="container" style="margin-top:50px;">

    <h3>Tambah Notifikasi</h3>
    <form action="<?= base_url('notification/insert_notification') ?>" method="POST">
      <div class="form-group">
        <label for="user_id">User ID (optional)</label>
        <input type="number" name="user_id" id="user_id" class="form-control" placeholder="Kosongkan untuk broadcast">
      </div>

      <div class="form-group">
        <label for="title">Judul</label>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="message">Pesan</label>
        <textarea name="message" id="message" rows="4" class="form-control" required></textarea>
      </div>

      <button type="submit" class="btn btn-success">Simpan</button>
      <a href="<?= base_url('notification') ?>" class="btn btn-default">Kembali</a>
    </form>

  </body>

  </html>


</body>

</html>