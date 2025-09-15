<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Font Awesome (ikon lonceng) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
    }

    .notification {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }

    .notification .badge {
      position: absolute;
      top: -8px;
      right: -8px;
      padding: 5px 8px;
      border-radius: 50%;
      background: red;
      color: white;
      font-size: 12px;
    }

    #notif-list {
      display: none;
      position: absolute;
      right: 0;
      margin-top: 10px;
      background: #fff;
      width: 300px;
      border: 1px solid #ddd;
      border-radius: 5px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      list-style: none;
      padding: 0;
      z-index: 1000;
    }

    #notif-list li {
      padding: 10px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    #notif-list li:last-child {
      border-bottom: none;
    }

    #notif-list li:hover {
      background: #f9f9f9;
    }

    /* notif belum dibaca (bold + overlay background) */
    .notif-item.unread {
      font-weight: bold;
      background-color: #f0f8ff;
      /* biru muda */
      position: relative;
    }

    /* kasih titik indikator di kiri */
    .notif-item.unread::before {
      content: "";
      width: 8px;
      height: 8px;
      background: red;
      border-radius: 50%;
      position: absolute;
      left: 8px;
      top: 16px;
    }

    /* notif sudah dibaca (pudar) */
    .notif-item.read {
      color: #888;
      background-color: #fafafa;
    }
  </style>
</head>

<body>
  <h1><?= $title ?></h1>

  <!-- Ikon Lonceng -->
  <div class="notification" id="notif-bell">
    <i class="fa-solid fa-bell fa-2x"></i>
    <span class="badge" id="notif-count">0</span>
  </div>

  <!-- List Notifikasi -->
  <ul id="notif-list"></ul>

  <script>
    function loadNotifications() {
      var url = '<?= base_url('notification/get_notification') ?>';

      $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
          let html = '';
          let unreadCount = 0; // hanya hitung notif unread

          if (data.length === 0) {
            html = '<li>Tidak ada notifikasi</li>';
          } else {
            data.forEach(function(notif) {
              let statusClass = notif.is_read == 0 ? 'unread' : 'read';

              if (notif.is_read == 0) unreadCount++; // hitung hanya unread

              html += `
              <li class="notif-item ${statusClass}" data-id="${notif.id}">
                <strong>${notif.message}</strong><br>
                <small>${notif.created_at}</small>
              </li>`;
            });
          }

          $('#notif-list').html(html);

          // update badge
          if (unreadCount > 0) {
            $('#notif-count').text(unreadCount).show();
          } else {
            $('#notif-count').hide(); // sembunyikan kalau sudah 0
          }
        },
        error: function(xhr, status, error) {
          console.error('❌ Error fetching notifications:', error);
          console.log("Response Text:", xhr.responseText);
        }
      });
    }

    // Toggle tampil/hidden list notifikasi saat klik lonceng
    $('#notif-bell').on('click', function() {
      $('#notif-list').toggle();
    });

    // Refresh notifikasi tiap 5 detik
    setInterval(loadNotifications, 5000);
    loadNotifications();

    // Script untuk menandai notifikasi sebagai sudah dibaca
    $(document).on('click', '.notif-item', function() {
      var notifId = $(this).data('id'); // ambil ID notifikasi
      var urlMarkRead = '<?= base_url('notification/mark_as_read') ?>';

      $.ajax({
        url: urlMarkRead,
        method: 'POST',
        data: {
          id: notifId
        }, // kirim ID ke server
        success: function(response) {
          console.log("✅ Mark as read response:", response);
          loadNotifications(); // refresh notifikasi agar badge update
        },
        error: function(xhr, status, error) {
          console.error('❌ Error marking notification as read:', error);
          console.log("Response Text:", xhr.responseText);
        }
      });
    });
  </script>


</body>

</html>