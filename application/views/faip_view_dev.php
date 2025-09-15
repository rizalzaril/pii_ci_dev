re
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <!-- _ci_view: <?php echo $_ci_view; ?> -->
  <!-- _version: 20240721 -->
  <!-- <title><?php echo $title; ?></title> -->
  <?php $this->load->view('admin/common/meta_tags'); ?>
  <?php $this->load->view('admin/common/before_head_close'); ?>

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <?php

  function generate_table_score($w1, $w2, $w3, $w4, $p, $total = '')
  {
    return '<table><tr><td colspan="3" style="text-align: left;"><b>' . $total . '</b></td></tr>'
      . '<tr><td>W1</td> <td>:</td><td style="text-align: right;">' . $w1 . '</td></tr>'
      . '<tr><td>W2</td><td>:</td><td style="text-align: right;">' . $w2 . '</td></tr>'
      . '<tr><td>W3</td><td>:</td><td style="text-align: right;">' . $w3 . '</td></tr>'
      . '<tr><td>W4</td><td>:</td><td style="text-align: right;">' . $w4 . '</td></tr>'
      . '<tr><td>P</td><td>:</td><td style="text-align: right;">' . $p . '</td></tr></table>';
  }

  function generate_table_score_with_faipid($faip_id, $w1, $w2, $w3, $w4, $p, $total = '')
  {
    return   '<table><tr><td colspan="3" style="text-align: left;"><b>' . $total . '</b></td></tr>'
      . '<tr><td>W1</td><td>:</td><td id="w1_' . $faip_id . '" style="text-align: right;">' . $w1 . '</td></tr>'
      . '<tr><td>W2</td><td>:</td><td id="w2_' . $faip_id . '" style="text-align: right;">' . $w2 . '</td></tr>'
      . '<tr><td>W3</td><td>:</td><td id="w3_' . $faip_id . '" style="text-align: right;">' . $w3 . '</td></tr>'
      . '<tr><td>W4</td><td>:</td><td id="w4_' . $faip_id . '" style="text-align: right;">' . $w4 . '</td></tr>'
      . '<tr><td>P</td><td>:</td><td id="p_' . $faip_id . '" style="text-align: right;">' . $p . '</td></tr></table>';
  }

  function generate_asesor_value($asesor_num, $user_type, $admin_id, $id, $asesor, $majelis, $status_faip, $row_score, $score_id, $score, $w1, $w2, $w3, $w4, $p, $total)
  {
    // Admin BK saja yg bisa set asesor
    if (isAdminBK()) {
      if ($status_faip < 9 && $status_faip > 5) {

        if (empty($majelis)) {
          echo '<a href="javascript:;" class="btn btn-warning btn-xs" onClick="load_quick_majelis(\'' . $id . '\',\'' . $asesor_num . '\');">Not Set</a>';
        } else {
          echo '<a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_majelis(\'' . $id . '\',\'' . $asesor_num . '\');">' . $asesor . '</a>';
        }
      } else {
        echo isset($majelis) ? $asesor : "";
      }

      if ($total != 0) {
        echo generate_table_score(
          $w1,
          $w2,
          $w3,
          $w4,
          $p,
          '<a class="btn btn-primary btn-xs" href="' . base_url() . 'admin/members/faipview2/' . $id . '/' . $majelis . '" target="_blank">' . $total . '</a>'
        );
      }
    }
    // Bukan Admin BK/Wilayah/Kolektif
    else {
      echo isset($majelis) ? $asesor : "";
      echo '<br/><br/>';
      $cek_ = false;
      if (isset($row_score)) {
        foreach ($score as $key => $v) {
          if ($majelis == $score_id[$key] && $majelis == $admin_id) {
            $vtotal = $v;
            if ($status_faip >= 6 && $status_faip <= 8) {
              $vtotal = $vtotal . ' <a class="btn btn-success btn-xs" href="' . base_url() . 'admin/members/faipview/' . $id . '" target="_blank">Edit</a>';
            }
            echo generate_table_score($w1, $w2, $w3, $w4, $p, $vtotal);

            $cek_ = true;
          }
        }
        if (!$cek_ && $majelis == $admin_id && ($status_faip >= 6 && $status_faip <= 8)) {
          echo '<a href="' . base_url('admin/members/faipview/' . $id) . '" class="btn btn-success btn-xs">Score</a>';
        }
      } else {
        if ($user_type == "7" && ($status_faip >= 6 && $status_faip <= 8) && $majelis == $admin_id) {
          echo '<a href="' . base_url('admin/members/faipview/' . $id) . '" class="btn btn-success btn-xs">Score</a>';
        }
      }

      // User login bukan Admin BK dan bukan Asesor1
      if ($total != 0 && $majelis !== $admin_id) {

        $btn_copy = null;
        if ($status_faip >= 6 && $status_faip <= 8) {
          $btn_copy = '<a class="btn btn-primary btn-xs" onclick="copy(' . $id . ',' . $majelis . ')" target="_blank">Copy</a>';
        }

        echo generate_table_score($w1, $w2, $w3, $w4, $p, $total . ' ' . $btn_copy);
      }
    }
  }

  function hari_ini($hari)
  {
    //$hari = date ("D");

    switch ($hari) {
      case 'Sun':
        $hari_ini = "Minggu";
        break;

      case 'Mon':
        $hari_ini = "Senin";
        break;

      case 'Tue':
        $hari_ini = "Selasa";
        break;

      case 'Wed':
        $hari_ini = "Rabu";
        break;

      case 'Thu':
        $hari_ini = "Kamis";
        break;

      case 'Fri':
        $hari_ini = "Jumat";
        break;

      case 'Sat':
        $hari_ini = "Sabtu";
        break;

      default:
        $hari_ini = "";
        break;
    }

    return $hari_ini;
  }

  function tgl_indo($tanggal)
  {
    $bulan = array(
      1 =>   'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
    );
    $pecahkan = explode('-', $tanggal);

    // variabel pecahkan 0 = tanggal
    // variabel pecahkan 1 = bulan
    // variabel pecahkan 2 = tahun

    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
  }

  ?>

  <script>
    $(function() {
      $("#from,#until").datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        yearRange: "<?php echo date('Y'); ?>:2050"
      });

      $("#interview_date").datepicker({
        dateFormat: 'dd-mm-yy',
        minDate: 0,
      });

      $("#from,#until").change(function() {
        var from = $("#from").val();
        var to = $("#until").val();

        var parts = from.split("-");
        var st = new Date(parseInt(parts[2], 10),
          parseInt(parts[1], 10) - 1,
          parseInt(parts[0], 10));
        var parts2 = to.split("-");
        var et = new Date(parseInt(parts2[2], 10),
          parseInt(parts2[1], 10) - 1,
          parseInt(parts2[0], 10));

        //alert(st +' '+ et);
        if (from != '' && to != '') {
          if (st > et) {
            alert("Invalid Date Range");
            $(this).val('');
          }
        }
      });

      $("#from_ip").datepicker({
        dateFormat: 'dd-mm-yy',
        setDate: new Date(),
        changeMonth: true,
        changeYear: true,
        yearRange: "<?php echo date('Y'); ?>:2050"
      });


    });

    //---------------------------------------------- Tambahan function untuk tombol Catatan BK Request P' Rully by Ipur ------------------------------------------------

    function viewCatatanBK(id) {
      var windowx = window.open('<?php echo base_url(); ?>admin/members/catatan_bk/' + id, '_blank', 'toolbar=0,location=0,menubar=0,width=400,height=300');
      ang
    }

    /*
    function viewRemarks(id){
    	var windowx = window.open('<?php echo base_url(); ?>admin/members/remarksnya/'+id, '_blank', 'toolbar=0,location=0,menubar=0,width=400,height=300');
    }
    */
    //----------------------------------------------------------------------------------------------------------------------------------------------------

    function viewFAIP(id) {
      var windowx = window.open('<?php echo base_url(); ?>admin/members/download_faip_2/' + id, '_blank', 'toolbar=0,location=0,menubar=0,width=900,height=500');
    }

    function viewFAIP2(id) {
      var windowx = window.open('<?php echo base_url(); ?>admin/members/download_faip/' + id, '_blank', 'toolbar=0,location=0,menubar=0,width=900,height=500');
    }

    function pad(str, max) {
      return str.length < max ? pad("0" + str, max) : str;
    }

    function load_quick_ip_view(id, cert) {

      var id_ip = id;
      $.ajax({
        url: '<?php echo site_url('admin/members/get_faip_by_id') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_ip
        },
        success: function(jsonObject, status) {
          var x = JSON.parse(jsonObject);
          console.log(x);

          /*$('#kta').val(x.kta);
          $('#no_ip').val(x.noip);
          $('#ip_type').val(x.sertid);
          $('#from_ip').val(x.sk_from);
          */

          if (cert == 1) {
            keter = "IPP";
          }
          if (cert == 2) {
            keter = "IPM";
          }
          if (cert == 3) {
            keter = "IPU";
          }
          $('#ip_typee').val(keter);

          $('#ip_type').val(cert);
          $('#ip_bk').val(pad(x.bidang, 2));
          if (x.hkk != '' && x.hkk != null)
            $('#ip_hkk').val(pad(x.hkk, 2));

          $('#ip_typee').attr('disabled', "true");
          $('#ip_type').attr('disabled', "true");
          $('#ip_bk').attr('disabled', "true");
          $('#ip_hkk').attr('disabled', "true");

          $('#from_ip').val('<?php echo date("d-m-Y"); ?>');

          $('#nama_ip').val(x.nama);

          $('#quick_ip').modal('show');
          $("#id_ip").val(id);
        }
      });


    }

    function load_upload_skip_view(no_kta) {
      var id_kta = no_kta;

      $('#id_f').val(id_kta);
      $('#quick_upload_skip').modal('show');

    }

    function load_quick_history_view(id) {
      //$('#quick_history').modal('show');
      var id_ip = id;
      $.ajax({
        url: '<?php echo site_url('admin/members/get_history_by_faipid') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_ip
        },
        success: function(jsonObject, status) {
          var x = JSON.parse(jsonObject);
          console.log(x);
          var tmp = '';
          $(".timeline").html('');
          $.each(x, function(index, value) {

            if (tmp != value.tgl) {
              if (value.tgl == '') {
                $(".timeline").append('<li class="time-label"><span class="bg-yellow">Next Step</span></li>' + ((!value.status.includes("FAIP Verified") && !value.status.includes("RETURNED TO APL")) ? '<li><i class="fa fa-circle"></i><div class="timeline-item"><span class="time"><i class="fa fa-clock-o"></i> </span><h3 class="timeline-header no-border" style="color:#B3A6C4">' + value.status + '</h3></div></li>' : ''));
              } else {
                $(".timeline").append('<li class="time-label"><span class="bg-green">' + value.tgl + '</span></li><li><i class="fa fa-circle bg-black"></i><div class="timeline-item"><span class="time"><i class="fa fa-clock-o"></i> ' + value.jam + '</span><h3 class="timeline-header no-border">' + value.status + '</h3></div></li>');
              }
            } else {
              if (value.tgl == '') {
                if (!value.status.includes("FAIP Verified") && !value.status.includes("RETURNED TO APL")) {
                  $(".timeline").append('<li><i class="fa fa-circle"></i><div class="timeline-item"><span class="time"><i class="fa fa-clock-o"></i> </span><h3 class="timeline-header no-border" style="color:#B3A6C4">' + value.status + '</h3></div></li>');
                }
              } else {
                $(".timeline").append('<li><i class="fa fa-circle bg-black"></i><div class="timeline-item"><span class="time"><i class="fa fa-clock-o"></i> ' + value.jam + '</span><h3 class="timeline-header no-border">' + value.status + '</h3></div></li>');
              }
            }

            tmp = value.tgl;
          });

          $('#quick_history').modal('show');
        }
      });


    }

    function edit_score(id, w1, w2, w3, w4, p) {
      $('#bk_id_faip').val(id);
      $('#bk_w1_score').val(w1);
      $('#bk_w2_score').val(w2);
      $('#bk_w3_score').val(w3);
      $('#bk_w4_score').val(w4);
      $('#bk_p_score').val(p);
      $('#edit_score').modal('show');
      bk_calc_score();
    }

    function bk_calc_score() {
      var w1 = $('#bk_w1_score').val();
      var w2 = $('#bk_w2_score').val();
      var w3 = $('#bk_w3_score').val();
      var w4 = $('#bk_w4_score').val();
      var p = $('#bk_p_score').val();

      var total = parseFloat(w1) + parseFloat(w2) + parseFloat(w3) + parseFloat(w4) + parseFloat(p);


      var kep = '';
      if (total >= 600 && total < 3000) {
        if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "Memenuhi persyaratan untuk sertifikasi IPP";
        else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
      } else if (total >= 3000 && total < 6000) {
        if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
          kep = "Memenuhi persyaratan untuk sertifikasi IPM";
        else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "Belum memenuhi persyaratan untuk sertifikasi IPM. Memenuhi persyaratan untuk sertifikasi IPP";
        else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
      } else if (total >= 6000) {
        if (w1 >= 600 && w2 >= 1800 && w3 >= 1200 && w4 >= 600 && p >= 1800)
          kep = "Memenuhi persyaratan untuk IPU";
        else if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
          kep = "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM";
        else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPP";
        else kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
      } else if (total < 600) {
        kep = "Belum memenuhi persyaratan untuk sertifikasi IPP";
      }

      $('#bk_all_score').val(total);
      $('#bk_keputusan').val(kep);
    }

    function bk_calc_score2() {
      var w1 = $('#bk_w1_score_faip').val();
      var w2 = $('#bk_w2_score_faip').val();
      var w3 = $('#bk_w3_score_faip').val();
      var w4 = $('#bk_w4_score_faip').val();
      var p = $('#bk_p_score_faip').val();

      var total = parseFloat(w1) + parseFloat(w2) + parseFloat(w3) + parseFloat(w4) + parseFloat(p);


      var kep = '';
      if (total >= 600 && total < 3000) {
        if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "1";
        else kep = "0";
      } else if (total >= 3000 && total < 6000) {
        if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
          kep = "2";
        else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "1";
        else kep = "0";
      } else if (total >= 6000) {
        if (w1 >= 600 && w2 >= 1800 && w3 >= 1200 && w4 >= 600 && p >= 1800)
          kep = "3";
        else if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
          kep = "2";
        else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
          kep = "1";
        else kep = "0";
      } else if (total < 600) {
        kep = "0";
      }

      if (!Number.isNaN(total)) {
        $('#bk_all_score_faip').val(total);
        $('#bk_keputusan_faip').val(kep);
        if (kep == '3') $('#total_va').val(2200000);
        else if (kep == '2') $('#total_va').val(1650000);
        else $('#total_va').val(1100000);
      }
    }

    function calc_total_va() {
      var kep = $('#bk_keputusan_faip').val();
      if (kep == '3') $('#total_va').val(2200000);
      else if (kep == '2') $('#total_va').val(1650000);
      else $('#total_va').val(1100000);
    }


    function load_quick_status(id, status) {
      if (status != '') {

        var url = "<?php echo site_url('admin/members/ajax_show_status_faip'); ?>?status=" + status;
        $('#status').load(url, function() {
          $('#quick_status').modal('show');
          $("#id_status").val(id);

          $("#status").val(status);
          check_status();
        });




      }
    }

    function load_quick_status2(id, status) {
      if (status != '') {

        var url = "<?php echo site_url('admin/members/ajax_show_status_faip_2'); ?>?status=" + status;
        $('#status').load(url, function() {
          $('#quick_status').modal('show');
          $("#id_status").val(id);

          $("#status").val(status);
          check_status();
        });




      }
    }

    function load_quick_revisi(id) {
      $('#quick_revisi').modal('show');
      $("#id_revisi").val(id);
    }

    function savesetbk() {
      var bk = $('#bk').val();
      var user_id = $('#id_bk').val();
      $.ajax({
        url: '<?php echo site_url('admin/members/ajax_update_faip_bk') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: user_id,
          bk: bk
        },
        success: function(response, status) {
          console.log(response);
          response = JSON.parse(response);
          if (response.status) {
            $('#errboxUpdateBK').text(response.message);
            $('#errboxUpdateBK').show();

            //On success refresh page
            location.reload();
          } else {
            $('#errboxUpdateBK').text('Error:' + response.message);
            $('#errboxUpdateBK').show();
          }
        },
        error: function(jqXHR, exception) {
          console.log(jqXHR);
          var error_msg = '';
          if (jqXHR.status === 0) {
            error_msg = 'Not connect.\n Verify Network.';
          } else if (jqXHR.status == 403) {
            error_msg = 'Not authorized. [403]';
          } else if (jqXHR.status == 500) {
            error_msg = 'Internal Server Error [500].';
          } else if (exception === 'parsererror') {
            error_msg = 'Requested JSON parse failed.';
          } else if (exception === 'timeout') {
            error_msg = 'Time out error.';
          } else if (exception === 'abort') {
            error_msg = 'Ajax request aborted.';
          } else {
            var json = JSON.parse(jqXHR.responseText)
            if (json.message) {
              error_msg = json.message;
            } else {
              error_msg = '<br/>\n' + jqXHR.responseText;
            }

          }
          $('#errboxUpdateBK').html('<strong>Failed: </strong>' + error_msg);
          $('#errboxUpdateBK').show();
        }
      });

      //$('#quick_bk').modal('toggle');
      //window.location.href = "<?php echo base_url(); ?>admin/members";
    }

    function savesetstatus() {
      var status = $('#status').val();
      var remarks = $('#remarks').val();
      var id_status = $('#id_status').val();

      var score = 0;
      var keputusan = "";

      var tgl = "";
      var jam_awal = "";
      var jam_akhir = "";
      var lokasi = "";

      var valid = true;

      if (status == "8") {
        tgl = $('#interview_date').val();
        jam_awal = $('#interview_start_hour').val();
        jam_akhir = $('#interview_end_hour').val();
        lokasi = $('#interview_loc').val();

        if (tgl == '' || jam_awal == '' || jam_akhir == '' || lokasi == '') {
          valid = false;
          alert('Silahkan isi Tanggal, Waktu dan Lokasi / Link Interview');
        }
      } else if (status == "9") {
        score = $('#score_final').val();
        keputusan = $('#keputusan').val();
      }

      if (valid) {

        var dataHTML = 'not valid';
        $.ajax({
          url: '<?php echo site_url('admin/members/setfaipstatus') ?>',
          dataType: "html",
          type: "POST",
          async: true, //false
          data: {
            id: id_status,
            status: status,
            remarks: remarks,
            score: score,
            keputusan: keputusan,
            tgl: tgl,
            jam_awal: jam_awal,
            jam_akhir: jam_akhir,
            lokasi: lokasi
          },
          success: function(jsonObject, status) {
            console.log(jsonObject);
            if ((jsonObject != 'not valid')) {
              dataHTML = jsonObject;
            }

            if (dataHTML == 'not valid')
              alert('not valid');
            location.reload();
          }
        });

        //$('#quick_profile').modal('hide');
        $('#quick_status').modal('toggle');

        //window.location.href = "<?php echo base_url(); ?>admin/members";
      }
    }

    function saveeditscore() {
      var bk_id_faip = $('#bk_id_faip').val();
      var bk_keputusan = $('#bk_keputusan').val();
      var bk_w1_score = $('#bk_w1_score').val();
      var bk_w2_score = $('#bk_w2_score').val();
      var bk_w3_score = $('#bk_w3_score').val();
      var bk_w4_score = $('#bk_w4_score').val();
      var bk_p_score = $('#bk_p_score').val();
      var bk_all_score = $('#bk_all_score').val();

      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/edit_score') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: bk_id_faip,
          hkeputusan: bk_keputusan,
          hjml: bk_all_score,
          hwb1: bk_w1_score,
          hwb2: bk_w2_score,
          hwb3: bk_w3_score,
          hwb4: bk_w4_score,
          hpil: bk_p_score
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');

          //$('#edit_score').modal('toggle');
          location.reload();
          //$('#quick_status').modal('toggle');
        }
      });


    }

    function savesetmember() {
      var cabang = $('#cabang').val();
      var bk = $('#bk').val();
      var from = $('#from').val();
      var until = $('#until').val();
      var id_c = $('#id_c').val();
      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/setmember') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_c,
          cabang: cabang,
          bk: bk,
          from: from,
          until: until
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
        }
      });

      //$('#quick_profile').modal('hide');
      $('#quick_profile').modal('toggle');
      location.reload();
    }

    function savesetmajelis() {
      var tipe_faip = $('#tipe_faip').val();
      var majelis = $('#majelis').val();
      var id_faip = $('#id_faip').val();
      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/savesetmajelis') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id_faip: id_faip,
          tipe_faip: tipe_faip,
          majelis: majelis
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          location.reload();
          //$('#quick_majelis').modal('toggle');
        }
      });

      //$('#quick_profile').modal('hide');


      //window.location.href = "<?php echo base_url(); ?>admin/members";
    }

    function savesetip() {
      //var tgl_sk   	=  $('#tgl_sk_ip').val();
      //var ip_cabang   =  $('#ip_cabang').val();

      var ip_bk = $('#ip_bk').val();
      var ip_hkk = $('#ip_hkk').val();
      var no_ip = $('#no_ip').val();
      var ip_type = $('#ip_type').val();
      //var ip_kp   	=  $('#ip_kp').val();	
      var from = $('#from_ip').val();
      var id_ip = $('#id_ip').val();
      var no_seri = $('#no_seri').val();
      var nama_ip = $('#nama_ip').val();
      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/ajax_faip_setip') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_ip,
          ip_bk: ip_bk,
          ip_hkk: ip_hkk,
          from: from,
          ip_type: ip_type,
          no_ip: no_ip,
          no_seri: no_seri,
          nama_ip: nama_ip
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
        }
      });

      //$('#quick_profile').modal('hide');
      $('#quick_ip').modal('toggle');
      location.reload();
    }

    function savesetrevisi() {
      var remarks = $('#remarks_revisi').val();
      var id_status = $('#id_revisi').val();

      var dataHTML = 'not valid';
      $.ajax({
        url: '<?php echo site_url('admin/members/ajax_setfaiprevisi') ?>',
        dataType: "html",
        type: "POST",
        async: true, //false
        data: {
          id: id_status,
          remarks: remarks
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          if ((jsonObject != 'not valid')) {
            dataHTML = jsonObject;
          }

          if (dataHTML == 'not valid')
            alert('not valid');
          location.reload();
        }
      });

      //$('#quick_profile').modal('hide');
      $('#quick_revisi').modal('toggle');

      //window.location.href = "<?php echo base_url(); ?>admin/members";
    }

    function upload_bukti() {
      var formData = new FormData();
      formData.append('bukti', $('#bukti')[0].files[0]);
      $('#msg_upload').html('Uploading...');
      $.ajax({
        url: "<?php echo site_url('admin/faip/ajax_upload_bap_faip_manual'); ?>",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data, textStatus, jqXHR) {
          var msg = '';
          if (data.trim().substring(0, 9) == 'filename:') {
            filename = data.trim().substring(9, data.length);
            msg = "<input type='hidden' id='bukti_image_url' value='" + filename + "'>" +
              'File has been uploaded to server: <a href=\'<?php echo base_url("/assets/uploads/faip_manual/"); ?>' + filename + '\' target=\'_blank\' class=\'ava_discus\'>' + filename + '</a>';

            $('#msg_upload').html(msg);
            uploadFlag = 1;
            $('#errUpload').html('');
          } else {
            var msg = 'Unknown error';
            data = data.trim();
            if (data == '400101') {
              msg = 'Please select an image (jpg, gif, png, bmp file) or PDF file.';
            } else if (data == '400102') {
              msg = 'Allowed file format are (gif|jpg|png|jpeg|pdf|bmp).';
            } else if (data == '400103') {
              msg = 'Sorry, maximum file size should be 700 KB';
            } else if (data == '400104') {
              msg = data;
            }

            $('#errUpload').html('<span style:\'color:red\'>' + msg + '</span>');
            $('#msg_upload').html('');
            $('#bukti').val('');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#errUpload').html(textStatus);
          $('#bukti').val('');
        }
      });
    }

    function add_faip() {
      //$("#no_kta").val('').trigger('change');	
      //$('#no_kta').empty().trigger("change");
      //$("#ktaform")[0].reset();
      $('#faipManualAction').html('Tambah');
      $('#errboxFaipManual').html('');
      $('#errboxFaipManual').hide();
      $('#quick_faip').modal('show');
      $('#faip_id').val('');
      $('#no_kta_faip').val('').change();
      $('#no_kta_faip').removeAttr("disabled");
      $('#ip_bk_faip').val('').change();
      $('#ip_bk_faip').removeAttr("disabled");
      $('#bk_w1_score_faip').val('');
      $('#bk_w2_score_faip').val('');
      $('#bk_w3_score_faip').val('');
      $('#bk_w4_score_faip').val('');
      $('#bk_p_score_faip').val('');
      $('#bk_all_score_faip').val('');
      $('#bk_keputusan_faip').val('').change();;
      $('#total_va').val('0');
      $('#msg_upload').html('');


      $('#quick_faip').modal('show');

    }

    function loadQuickEditFaipManual(faip_id, no_kta, ip, bk, w1, w2, w3, w4, p, keputusan, bapFilename) {
      //$("#no_kta").val('').trigger('change');	
      //$('#no_kta').empty().trigger("change");
      //$("#ktaform")[0].reset();
      no_kta = String(no_kta).padStart(5, '0');
      $('#faipManualAction').html('Edit');
      $('#errboxFaipManual').html('');
      $('#errboxFaipManual').hide();
      $('#quick_faip').modal('show');
      $('#faip_id').val(faip_id);
      $('#no_kta_faip').append('<option value="' + no_kta + '" data-select2-id="select2-data-2-wgz5">' + no_kta + '</option>');
      $('#no_kta_faip').val(no_kta).change();
      $('#no_kta_faip').attr('disabled', 'disabled');
      $('#no_kta_faip').click(function() {
        alert('Cannot edit Nomor KTA');
      });
      $('#ip_bk_faip').val(bk).change();
      $('#ip_bk_faip').attr('disabled', 'disabled');
      $('#bk_w1_score_faip').val(w1);
      $('#bk_w2_score_faip').val(w2);
      $('#bk_w3_score_faip').val(w3);
      $('#bk_w4_score_faip').val(w4);
      $('#bk_p_score_faip').val(p);
      $('#bk_all_score_faip').val(w1 + w2 + w3 + w4 + p);
      $('#bk_all_score_faip').focus();
      $('#bk_keputusan_faip').val(keputusan).change();
      $('#total_va').val('0');
      $('#msg_upload').html('Uploaded file: <a href="<?php echo base_url() . 'assets/uploads/faip_manual/'; ?>' + bapFilename + '" target="_new">' + bapFilename + '</a>');


    }

    function saveaddfaip() {
      var faip_id = $('#faip_id').val();
      var no_kta = $('#no_kta_faip').val();
      var bk = $('#ip_bk_faip').val();
      var bk_keputusan = $('#bk_keputusan_faip').val();
      var bk_w1_score = $('#bk_w1_score_faip').val();
      var bk_w2_score = $('#bk_w2_score_faip').val();
      var bk_w3_score = $('#bk_w3_score_faip').val();
      var bk_w4_score = $('#bk_w4_score_faip').val();
      var bk_p_score = $('#bk_p_score_faip').val();
      var bk_all_score = $('#bk_all_score_faip').val();
      var total = $('#total_va').val();
      var bukti = $('#bukti_image_url');

      var dataHTML = 'not valid';
      //alert('faipManualAction: ' + $('#faipManualAction').html() + ', faip_id: ' + faip_id);
      // Create new FAIP
      if ($('#faipManualAction').html() == 'Tambah' && faip_id == '') {
        if (no_kta == '' || bk == '' || bk_w1_score == '' || bk_w2_score == '' || bk_w3_score == '' || bk_w4_score == '' || bk_p_score == '' || bk_keputusan == '' || bukti.val() == undefined)
          alert('Please filled all required fields');
        else {
          $.ajax({
            url: '<?php echo site_url('admin/faip/ajax_faip_manual_upsert') ?>',
            dataType: "json",
            type: "POST",
            async: true, //false
            data: {
              no_kta: no_kta,
              bk: bk,
              hkeputusan: bk_keputusan,
              hjml: bk_all_score,
              hwb1: bk_w1_score,
              hwb2: bk_w2_score,
              hwb3: bk_w3_score,
              hwb4: bk_w4_score,
              hpil: bk_p_score,
              total: total,
              bukti: bukti.val()
            },
            success: function(response, status) {
              console.log(response);
              //response = JSON.parse(response);
              if (response.status) {
                $('#errboxFaipManual').text(response.message);
                $('#errboxFaipManual').show();
                location.reload();
              } else {
                $('#errboxFaipManual').text('Error:' + response.message);
                $('#errboxFaipManual').show();
              }
            },
            error: function(jqXHR, exception) {
              console.log(jqXHR);
              var error_msg = '';
              if (jqXHR.status === 0) {
                error_msg = 'Not connect.\n Verify Network.';
              } else if (jqXHR.status == 403) {
                error_msg = 'Not authorized. [403]';
              } else if (jqXHR.status == 404) {
                error_msg = 'Requested page not found. [404]';
              } else if (jqXHR.status == 500) {
                error_msg = 'Internal Server Error [500].';
              } else if (exception === 'parsererror') {
                error_msg = 'Requested JSON parse failed.';
              } else if (exception === 'timeout') {
                error_msg = 'Time out error.';
              } else if (exception === 'abort') {
                error_msg = 'Ajax request aborted.';
              } else {
                var json = JSON.parse(jqXHR.responseText)
                if (json.message) {
                  error_msg = json.message;
                } else {
                  error_msg = '<br/>\n' + jqXHR.responseText;
                }

              }
              $('#errboxFaipManual').html('<strong>Failed: </strong>' + error_msg);
              $('#errboxFaipManual').show();
            }
          });
        }
      }

      // Update/Edit FAIP Manual
      else if ($('#faipManualAction').html() == 'Edit' && faip_id != '') {
        if (bk_w1_score == '' || bk_w2_score == '' || bk_w3_score == '' || bk_w4_score == '' || bk_p_score == '' || bk_keputusan == '' || bukti.val() == undefined)
          alert('Please filled all required fields');
        else {
          $.ajax({
            url: '<?php echo site_url('admin/faip/ajax_faip_manual_upsert') ?>',
            dataType: "html",
            type: "PUT",
            async: true, //false
            data: {
              faip_id: faip_id,
              hkeputusan: bk_keputusan,
              hjml: bk_all_score,
              hwb1: bk_w1_score,
              hwb2: bk_w2_score,
              hwb3: bk_w3_score,
              hwb4: bk_w4_score,
              hpil: bk_p_score,
              total: total,
              bukti: bukti.val()
            },
            success: function(response, status) {
              console.log(response);
              response = JSON.parse(response);
              if (response.status) {
                $('#errboxFaipManual').text(response.message);
                $('#errboxFaipManual').show();
                location.reload();
              } else {
                $('#errboxFaipManual').text('Error:' + response.message);
                $('#errboxFaipManual').show();
              }
            },
            error: function(jqXHR, exception) {
              console.log(jqXHR);
              var error_msg = '';
              if (jqXHR.status === 0) {
                error_msg = 'Not connect.\n Verify Network.';
              } else if (jqXHR.status == 403) {
                error_msg = 'Not authorized. [403]';
                //} else if (jqXHR.status == 404) {
                //	error_msg = 'Requested page not found. [404]'; //Faip ID is not found
              } else if (jqXHR.status == 500) {
                error_msg = 'Internal Server Error [500].';
              } else if (exception === 'parsererror') {
                error_msg = 'Requested JSON parse failed.';
              } else if (exception === 'timeout') {
                error_msg = 'Time out error.';
              } else if (exception === 'abort') {
                error_msg = 'Ajax request aborted.';
              } else {
                var json = JSON.parse(jqXHR.responseText)
                if (json.message) {
                  error_msg = json.message;
                } else {
                  error_msg = '<br/>\n' + jqXHR.responseText;
                }
              }
              $('#errboxFaipManual').html('<strong>Failed: </strong>' + error_msg);
              $('#errboxFaipManual').show();
            }
          });

        }

      } else {
        alert('Unknown operation: Edit or Add?');
      }

    }

    function load_quick_PaymentDetail(id) {
      $('#quick_PaymentDetail').modal('show');
      $('#errboxPaymentDetail').hide();
      $('#paymentDetailText').html('');

      var id_ip = id;
      $.ajax({
        url: '<?php echo site_url('admin/payment/ajax_detail_by_faipid') ?>/' + id,
        dataType: "json",
        type: "GET",
        async: true, //false
        data: {
          id: id_ip
        },
        success: function(jsonObject, status) {
          console.log(jsonObject);
          keyval = '';
          idx = 0;
          keyval += '<ul class="nav nav-tabs">';

          $.each(jsonObject.data, function(key, value) {
            keyval += '<li ' + ((idx == 0) ? ' class="active"' : '') + '><a data-toggle="tab" href="#payment' + (idx + 1) + '">Payment ' + (idx + 1) + '</a></li>';
            idx++;
          });
          keyval += '</ul>';

          idx = 0;
          keyval += '<div class="tab-content">';
          $.each(jsonObject.data, function(key, value) {
            keyval += '<div id="payment' + (idx + 1) + '" class="tab-pane fade' + ((idx == 0) ? ' in active' : '') + '">';
            $.each(jsonObject.data[idx], function(key, value) {
              keyval += '<div class="row"><div class="col-md-4"><b>' + key + '</b>:</div><div class="col-md-4" >' + value + '</div></div>';
            });
            keyval += '</div>';
            idx++;
          });
          keyval += '</div>';
          $('#paymentDetailText').html(keyval);
        },
        error: function(jqXHR, exception) {
          console.log(jqXHR);
          var error_msg = '';
          if (jqXHR.status === 0) {
            error_msg = 'Error: Not connect.\n Verify Network.';
          } else if (jqXHR.status == 403) {
            error_msg = 'Not authorized. [403]';
          } else if (jqXHR.status == 404) {
            error_msg = 'No payment related to the FAIP record found. [404]';
          } else if (jqXHR.status == 500) {
            error_msg = 'Internal Server Error [500].';
          } else if (exception === 'parsererror') {
            error_msg = 'Error: Requested JSON parse failed.';
          } else if (exception === 'timeout') {
            error_msg = 'Time out error.';
          } else if (exception === 'abort') {
            error_msg = 'Error: Ajax request aborted.';
          } else {
            var json = JSON.parse(jqXHR.responseText)
            if (json.message) {
              error_msg = json.message;
            } else {
              error_msg = '<br/>\n' + jqXHR.responseText;
            }
          }
          $('#errboxPaymentDetail').html(error_msg);
          $('#errboxPaymentDetail').show();
        }
      });


    }
  </script>

  <style>
    .table td {
      vertical-align: top !important;
    }
  </style>

</head>

<body class="skin-blue">
  <?php $this->load->view('admin/common/after_body_open'); ?>
  <?php $this->load->view('admin/common/header'); ?>
  <div class="wrapper row-offcanvas row-offcanvas-left">
    <?php $this->load->view('admin/common/left_side'); ?>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1> Members Management
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">FAIP</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header">
                <h3 class="box-title">FAIP</h3>
              </div>
              <!-- /.box-header -->
              <div class="box-body table-responsive" style="overflow-x:scroll;">

                <?php if ($showAddFaipManual) { ?>
                  <div style="margin: 4px;">
                    <a class="btn btn-primary" href="#" onClick="add_faip();"><i class="glyphicon glyphicon-file"></i>&nbsp;Add Faip Manual</a>
                  </div>
                <?php } ?>

                <!--Pagination-->
                <div class="paginationWrap"> <?php echo ($result) ? $links : ''; ?></div>

                <?php $this->load->view('admin/common/member_quick_search_bar_faip'); ?>
                <div class="clearfix text-right" style="padding:10px;">



                  <?php
                  //if(!isset($_GET['industry_ID'])):
                  ?>
                  Total Records: <strong><?php echo $total_rows; ?>

                    <?php //endif;
                    // Hanya sebagai catatan: Sebelumnya table headers seperti ini
                    /*
				<th style="min-width:200px;">Asesor 1</th>
				<th style="min-width:200px;">Asesor 2</th>
				<th style="min-width:200px;">Asesor 3</th>
			*/  ?>
                  </strong> </div>
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                    <tr>

                      <th align="center">No. KTA</th>
                      <th>Name</th>
                      <th style="width:0.1%;">FAIP ID / Jenis Permohonan</th>
                      <th>Pre Score</th>
                      <th>Asesor 1</th>
                      <th>Asesor 2</th>
                      <th>Asesor 3</th>
                      <th>Final Score</th>
                      <th>Status</th>
                      <?php if ($showTglSIPPrint) { ?>
                        <th align="center">Tgl SIP to Print</th>
                      <?php } ?>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if ($result):
                      foreach ($result as $row):
                        //print_r($row);						
                        $score_id = explode(",", $row->score_id);
                        $score = explode(",", $row->score);
                        $wajib1_score = explode(",", $row->wajib1_score_);
                        $wajib2_score = explode(",", $row->wajib2_score_);
                        $wajib3_score = explode(",", $row->wajib3_score_);
                        $wajib4_score = explode(",", $row->wajib4_score_);
                        $pilihan_score = explode(",", $row->pilihan_score_);
                        $keputusan = explode(",", $row->keputusan);
                        $w1_total = 0;
                        $w2_total = 0;
                        $w3_total = 0;
                        $w4_total = 0;
                        $p_total = 0;

                        $score_akhir = 0;
                        $count_score = 0;

                        $w1_total_m1 = 0;
                        $w2_total_m1 = 0;
                        $w3_total_m1 = 0;
                        $w4_total_m1 = 0;
                        $p_total_m1 = 0;
                        $all_total_m1 = 0;

                        $w1_total_m2 = 0;
                        $w2_total_m2 = 0;
                        $w3_total_m2 = 0;
                        $w4_total_m2 = 0;
                        $p_total_m2 = 0;
                        $all_total_m2 = 0;

                        $w1_total_m3 = 0;
                        $w2_total_m3 = 0;
                        $w3_total_m3 = 0;
                        $w4_total_m3 = 0;
                        $p_total_m3 = 0;
                        $all_total_m3 = 0;

                        $w1_total_m4 = 0;
                        $w2_total_m4 = 0;
                        $w3_total_m4 = 0;
                        $w4_total_m4 = 0;
                        $p_total_m4 = 0;
                        $all_total_m4 = 0;

                        $score_akhir_bk = 0;
                        $w1_total_bk = 0;
                        $w2_total_bk = 0;
                        $w3_total_bk = 0;
                        $w4_total_bk = 0;
                        $p_total_bk = 0;


                        if (isset($row->score)) {
                          $flag_is_bk_score = false;
                          foreach ($score as $key => $v) {
                            $v = (is_numeric($v) ? $v : 0);
                            $score_akhir = $score_akhir + $v;
                            $count_score++;

                            if ($row->majelis1 == $score_id[$key]) {
                              $w1_total = $w1_total + $wajib1_score[$key];
                              $w2_total = $w2_total + $wajib2_score[$key];
                              $w3_total = $w3_total + $wajib3_score[$key];
                              $w4_total = $w4_total + $wajib4_score[$key];
                              $p_total = $p_total + $pilihan_score[$key];

                              $all_total_m1 = $v;
                              $w1_total_m1 = $wajib1_score[$key];
                              $w2_total_m1 = $wajib2_score[$key];
                              $w3_total_m1 = $wajib3_score[$key];
                              $w4_total_m1 = $wajib4_score[$key];
                              $p_total_m1 = $pilihan_score[$key];
                            } else if ($row->majelis2 == $score_id[$key]) {
                              $w1_total = $w1_total + $wajib1_score[$key];
                              $w2_total = $w2_total + $wajib2_score[$key];
                              $w3_total = $w3_total + $wajib3_score[$key];
                              $w4_total = $w4_total + $wajib4_score[$key];
                              $p_total = $p_total + $pilihan_score[$key];

                              $all_total_m2 = $v;
                              $w1_total_m2 = $wajib1_score[$key];
                              $w2_total_m2 = $wajib2_score[$key];
                              $w3_total_m2 = $wajib3_score[$key];
                              $w4_total_m2 = $wajib4_score[$key];
                              $p_total_m2 = $pilihan_score[$key];
                            } else if ($row->majelis3 == $score_id[$key]) {
                              $w1_total = $w1_total + $wajib1_score[$key];
                              $w2_total = $w2_total + $wajib2_score[$key];
                              $w3_total = $w3_total + $wajib3_score[$key];
                              $w4_total = $w4_total + $wajib4_score[$key];
                              $p_total = $p_total + $pilihan_score[$key];

                              $all_total_m3 = $v;
                              $w1_total_m3 = $wajib1_score[$key];
                              $w2_total_m3 = $wajib2_score[$key];
                              $w3_total_m3 = $wajib3_score[$key];
                              $w4_total_m3 = $wajib4_score[$key];
                              $p_total_m3 = $pilihan_score[$key];
                            } else if ($row->majelis1 != $score_id[$key] && $row->majelis2 != $score_id[$key] && $row->majelis3 != $score_id[$key]) {

                              $score_akhir_bk = $v;
                              $w1_total_bk = $wajib1_score[$key];
                              $w2_total_bk = $wajib2_score[$key];
                              $w3_total_bk = $wajib3_score[$key];
                              $w4_total_bk = $wajib4_score[$key];
                              $p_total_bk = $pilihan_score[$key];
                              $flag_is_bk_score = true;
                            }
                          }

                          if (!$flag_is_bk_score) {

                            $score_akhir = round($score_akhir / $count_score, 2);
                            $w1_total = round(($w1_total / $count_score), 2);
                            $w2_total = round(($w2_total / $count_score), 2);
                            $w3_total = round(($w3_total / $count_score), 2);
                            $w4_total = round(($w4_total / $count_score), 2);
                            $p_total = round(($p_total / $count_score), 2);
                          } else {
                            $score_akhir = $score_akhir_bk;
                            $w1_total = $w1_total_bk;
                            $w2_total = $w2_total_bk;
                            $w3_total = $w3_total_bk;
                            $w4_total = $w4_total_bk;
                            $p_total = $p_total_bk;
                          }
                          //print_r($score_akhir);
                        }

                        $temp_ = $this->main_mod->msrquery('select id, lic_num from user_cert where status=2 and user_id=' . $row->user_id . ' and cert_url=' . $row->id . ' order by id desc limit 1')->row();
                        $lic_num = isset($temp_->lic_num) ? $temp_->lic_num : "";
                        $lic_id = isset($temp_->id) ? $temp_->id : "";
                    ?>
                        <tr id="row_<?php echo $row->id; ?>">

                          <td valign="middle" data-colname="NoKTA">
                            <?php echo str_pad($row->wil, 4, '0', STR_PAD_LEFT) . ' - ' . str_pad($row->bk, 2, '0', STR_PAD_LEFT) . ' - ' . str_pad($row->no_kta, 6, '0', STR_PAD_LEFT);
                            echo '<br />' . $row->wil_name;
                            echo '<br/><br/><b>' . $row->bk_name . '</b>'; ?>
                          </td>
                          <td valign="middle" data-colname="Nama"><a href="<?php echo base_url('admin/members/details/' . $row->user_id); ?>"><?php echo $row->nama; ?></a></td>
                          <td valign="middle" data-colname="JenisPermohonan"><?php echo $row->id; ?><br /><?php echo ($row->faip_type == "00") ? "Perdana" : "Pemutakhiran"; ?> <?php echo $row->certificate_type; ?></td>
                          <td valign="middle" data-colname="PreScore">
                            <br />
                            <?php
                            if ($row->is_manual == TRUE) {
                              //No Pre-Score 
                            } else if (isAdminLSKI() || isAdminMajelisAll() || isAdminBKWilayahKolektif()) {

                              echo generate_table_score(
                                $row->wajib1_score,
                                $row->wajib2_score,
                                $row->wajib3_score,
                                $row->wajib4_score,
                                $row->pilihan_score,
                                $row->total_score
                              );
                            }
                            ?>

                          </td>


                          <td valign="middle" data-colname="Asesor1">
                            <?php generate_asesor_value(
                              1,
                              $this->session->userdata('type'),
                              $this->session->userdata('admin_id'),
                              $row->id,
                              $row->asesor1,
                              $row->majelis1,
                              $row->status_faip,
                              $row->score,
                              $score_id,
                              $score,
                              $w1_total_m1,
                              $w2_total_m1,
                              $w3_total_m1,
                              $w4_total_m1,
                              $p_total_m1,
                              $all_total_m1
                            );
                            ?>
                          </td>
                          <td valign="middle" data-colname="Asesor2">
                            <?php generate_asesor_value(
                              2,
                              $this->session->userdata('type'),
                              $this->session->userdata('admin_id'),
                              $row->id,
                              $row->asesor2,
                              $row->majelis2,
                              $row->status_faip,
                              $row->score,
                              $score_id,
                              $score,
                              $w1_total_m2,
                              $w2_total_m2,
                              $w3_total_m2,
                              $w4_total_m2,
                              $p_total_m2,
                              $all_total_m2
                            );
                            ?>
                          </td>
                          <td valign="middle" data-colname="Asesor3">
                            <?php generate_asesor_value(
                              3,
                              $this->session->userdata('type'),
                              $this->session->userdata('admin_id'),
                              $row->id,
                              $row->asesor3,
                              $row->majelis3,
                              $row->status_faip,
                              $row->score,
                              $score_id,
                              $score,
                              $w1_total_m3,
                              $w2_total_m3,
                              $w3_total_m3,
                              $w4_total_m3,
                              $p_total_m3,
                              $all_total_m3
                            );
                            ?>
                          </td>

                          <td valign="middle" data-colname="FinalScore">
                            <?php
                            if (isAdminBK() && ($row->status_faip >= 6 && $row->status_faip <= 8)) {
                            ?>
                              <a href="#" onclick="edit_score('<?= $row->id ?>','<?= $w1_total ?>','<?= $w2_total ?>','<?= $w3_total ?>','<?= $w4_total ?>','<?= $p_total ?>');" class="btn btn-primary btn-xs">Score</a><br />
                            <?php
                            }
                            /*else if($this->session->userdata('type')=="7" && $row->status_faip!='12'){
					?>
					<a href="<?php echo base_url('admin/members/faipview/'.$row->id);?>" class="btn btn-primary btn-xs">Score</a>
					<?php	
					}*/
                            ?>

                            <?php
                            if (isAdminBK()) {
                              if ($score_akhir != 0) {
                                if ($row->status_faip >= 9) {
                                  echo '<b>' . $row->keputusan_bk . '</b> <br />';
                                }

                                echo generate_table_score_with_faipid(
                                  $row->id,
                                  number_format((float)$w1_total, 2, '.', ''),
                                  number_format((float)$w2_total, 2, '.', ''),
                                  number_format((float)$w3_total, 2, '.', ''),
                                  number_format((float)$w4_total, 2, '.', ''),
                                  number_format((float)$p_total, 2, '.', ''),
                                  number_format((float)$score_akhir, 2, '.', '')
                                );
                              }
                            } else if (isAdminMajelisAll() || isAdminLSKI() || isAdminKolektif()) {
                              if ($row->status_faip >= 9) {
                                $arr = [];
                                $arr1 = [];

                                if ($row->is_manual == '1') {
                                  $kep = '';
                                  if ($row->keputusan_manual == '1') $kep = 'IPP';
                                  else if ($row->keputusan_manual == '2') $kep = 'IPM';
                                  else if ($row->keputusan_manual == '3') $kep = 'IPU';

                                  echo '<b>' . $kep . '</b><br />'; //- '.$keputusan[$key].'
                                  echo generate_table_score(
                                    $row->wajib1_score,
                                    $row->wajib2_score,
                                    $row->wajib3_score,
                                    $row->wajib4_score,
                                    $row->pilihan_score,
                                    $row->total_score
                                  );
                                } else if ($row->keputusan_bk != '') {
                                  $arr = [];
                                  $arr1 = [];
                                  $keputusan = $row->keputusan_bk;
                                  $score = $row->score_bk;

                                  $arr[0] = $score;
                                  $arr1[0] = $keputusan;

                                  echo '<b>' . $arr1[0] . '</b><br />';
                                  echo generate_table_score(
                                    number_format((float)$w1_total, 2, '.', ''),
                                    number_format((float)$w2_total, 2, '.', ''),
                                    number_format((float)$w3_total, 2, '.', ''),
                                    number_format((float)$w4_total, 2, '.', ''),
                                    number_format((float)$p_total, 2, '.', ''),
                                    $arr[0]
                                  );
                                } else {
                                  $keputusan = explode(",", $row->keputusan);
                                  $score = explode(",", $row->score);

                                  foreach ($score as $key => $v) {
                                    $arr[0] = $score[$key];
                                    $arr1[0] = $keputusan[$key];
                                  }
                                  echo '<b>' . ($arr1[0] != '' ? $arr1[0] : 'Belum memenuhi IPP') . '</b><br />';
                                  echo generate_table_score(
                                    number_format((float)$w1_total, 2, '.', ''),
                                    number_format((float)$w2_total, 2, '.', ''),
                                    number_format((float)$w3_total, 2, '.', ''),
                                    number_format((float)$w4_total, 2, '.', ''),
                                    number_format((float)$p_total, 2, '.', ''),
                                    $arr[0]
                                  );
                                }
                              }
                            }
                            ?>
                          </td>
                          <td valign="middle" data-colname="Status">
                            <?php
                            $special_admin_lski_admin = array(
                              '672', //Ruli, rulyahmadj@yahoo.com
                              '731',
                              '659'  //Direktur LSKI, dir.lski@pii.or.id
                            );

                            if (isAdminLSKI()) { ?>
                              <?php echo $row->status_name; ?><br />
                              <?php
                              if ($row->status_faip == '11') {
                                // Status: SIP TO PRINT (LSKI)
                                //echo $row->status_name.' ';
                                $arr1 = "";
                                if (isset($row->score)) {
                                  if ($row->keputusan_bk != "") {
                                    $id_cert = 0;
                                    if (
                                      $row->keputusan_bk == "IPP" ||
                                      $row->keputusan_bk == "Memenuhi persyaratan untuk sertifikasi IPP" ||
                                      $row->keputusan_bk == "Belum memenuhi persyaratan untuk sertifikasi IPM. Memenuhi persyaratan untuk sertifikasi IPP" ||
                                      $row->keputusan_bk == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPP"
                                    ) {

                                      $id_cert = 1;
                                    } else if (
                                      $row->keputusan_bk == "IPM" || $row->keputusan_bk == "Memenuhi persyaratan untuk sertifikasi IPM" ||
                                      $row->keputusan_bk == "Belum memenuhi persyaratan untuk sertifikasi IPU. Memenuhi persyaratan untuk sertifikasi IPM"
                                    ) {
                                      $id_cert = 2;
                                    } else if (
                                      $row->keputusan_bk == "IPU" ||
                                      $row->keputusan_bk == "Memenuhi persyaratan untuk IPU"
                                    ) {
                                      $id_cert = 3;
                                    }
                                    $arr1 = $id_cert;
                                  } else {
                                    $keputusan = explode(",", $row->keputusan);
                                    foreach ($score as $key => $v) {
                                      $id_cert = 0;
                                      if ($keputusan[$key] == "IPP") $id_cert = 1;
                                      else if ($keputusan[$key] == "IPM") $id_cert = 2;
                                      else if ($keputusan[$key] == "IPU") $id_cert = 3;
                                      $arr1 = $id_cert;
                                    }
                                  }
                                  //echo '<br />'.$arr1.' - '.$row->lic_num .'<br />';


                                  if ($arr1 != "" && $lic_num == "" && $showSetSKIP) { ?>
                                    <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_ip_view('<?php echo $row->id; ?>','<?= $arr1 ?>');">
                                      Set SKIP</a><?php
                                                }
                                              } else if ($row->is_manual == "1" && $showSetSKIP) { ?>
                                  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_ip_view('<?php echo $row->id; ?>','<?= $row->keputusan_manual ?>');">
                                    Set SKIP</a><?php
                                              }
                                            }
                                            //else //ER: "else if" diganti karena button Change Status perlu muncul juga saat status_faip == 11

                                            if (
                                              $row->is_manual !== '1' &&
                                              (in_array($row->status_faip, array('1', '2', '3', '4', '5', '6', ' 7', '8', '9'))
                                                || (
                                                  in_array($row->status_faip, array('10', '11')) &&
                                                  in_array($this->session->userdata('admin_id'), $special_admin_lski_admin)
                                                )
                                              )
                                            ) {
                                              // ER: Perubahan tanggal 20240625 - sebelumnya Change status hanya untuk array('1', '2', '5', '11') 
                                              //     Perubahan juga ada di backend: ajax_show_status_faip()

                                              if ($this->session->userdata('admin_id') == 672 || $this->session->userdata('admin_id') == 731) { ?>
                                  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_status('<?php echo $row->id; ?>','<?php echo $row->status_faip; ?>');">
                                    Change Status</a>


                                <?php }

                                              //------------------------------------------------------------------------------------------------ Tambahan by IP ----------------------------------------
                                              if ($row->status_faip == '6' || $row->status_faip == '3' || $row->status_faip == '9' || $row->status_faip == '11') {
                                                if ($row->need_revisi == '1') {
                                                  echo '<br><br>' . trim($row->revisi_note) . '<br>';
                                                }
                                              }
                                              //---------------------------------------------------------------------------------------------------------------------------------------------------------------							   
                                            }
                                            /*else if($row->status_faip=='6'){
							?>
							<a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_status2('<?php echo $row->id;?>','<?php echo $row->status_faip;?>');">
							<?php echo $row->status_name;?></a>
							<?php
							}*/ else if ($row->status_faip == '3') {
                                ?>
                                <b><?php echo $row->status_name; ?></b>
                                <br /><br />
                                <b><?php echo $row->remarks; ?></b>
                              <?php
                                            } else if ($row->status_faip == '13') {
                                              $temp_ = $this->main_mod->msrquery('select * from user_approval where faip_id=' . $row->id . ' order by seq asc')->result();
                              ?>
                                <b><?php echo $row->status_name; ?></b>
                                <br /><br />
                                <?php
                                              foreach ($temp_ as $va) {
                                                if ($va->status != 'Waiting for Approval')
                                                  echo ' - <span style="color:blue">' . $va->app_title . '</span><br /><b style="font-size:10px;">(' . $va->status . '_' . $va->status_date . ')</b> ' . $va->remark . '<br />';
                                              }
                                            }
                                          } else if (isAdminBK()) {
                                            if ($row->status_faip == '6' || $row->status_faip == '8') { // || $row->status_faip=='7'
                                ?>
                                <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_status('<?php echo $row->id; ?>','<?php echo $row->status_faip; ?>');">
                                  <?php echo $row->status_name; ?></a><?php
                                                                    } else {
                                                                      ?>
                                <b><?php echo $row->status_name; ?></b>
                              <?php
                                                                    }
                              ?>
                              <br /><br />
                              <b><?php echo $row->remarks; ?></b>
                            <?php
                                          } else if (isAdminMajelisAll() || isAdminKolektif()) { ?>
                              <b><?php echo $row->status_name; ?></b><?php
                                                                    }

                                                                    if ($row->status_faip == '8') {
                                                                      // Status: TO INTERVIEW (MUK)
                                                                      $interview_date = $row->interview_date;
                                                                      $interview_start_hour = $row->interview_start_hour;
                                                                      $interview_end_hour = $row->interview_end_hour;
                                                                      $interview_loc = $row->interview_loc;
                                                                      if ($interview_date != '') {
                                                                        echo '<br />Wawancara : <br />';
                                                                        echo hari_ini(date('D', strtotime($interview_date))) . ', ' . tgl_indo(date('Y-m-d', strtotime($interview_date))) . '<br />';
                                                                        echo $interview_start_hour . '-' . $interview_end_hour . ' WIB<br />';
                                                                        echo $interview_loc . '<br />';
                                                                      }
                                                                    }

                                                                    if ($lic_num != "") {
                                                                      ?>
                              <br /><b>
                                <?php
                                                                      //TODO: Move query to controller
                                                                      echo $lic_num;
                                                                      if (in_array($this->session->userdata('type'), array("0", "1"))) {
                                                                        $temp_ = $this->main_mod->msrquery('select id from user_cert_temp where faip_id=' . $row->id . ' and status=1 order by id asc')->row();
                                                                        if (isset($temp_->id)) {
                                ?>
                                    <a href="<?php echo base_url() . 'admin/members/download_skip?id=' . $lic_id; ?>" style="color:blue;" target="_blank">DOWNLOAD</a>
                                <?php }
                                                                      } ?>
                              </b>
                            <?php
                                                                    } ?>
                          </td>
                          <?php
                          if ($showTglSIPPrint) { ?>
                            <td valign="middle"><?php echo $row->tgl_sip_to_print; ?></td><?php
                                                                                        } ?>
                          <td valign="middle" data-colname="Action">
                            <a onclick="viewFAIP('<?php echo $row->id; ?>')" href="#" class="btn btn-primary btn-xs">View</a>
                            <?php
                            echo '<input type="hidden" id="total_' . $row->id . '" value="' . ($count_score != 0 ? ($score_akhir) : 0) . '" />';
                            echo '<input type="hidden" id="total_asesor_' . $row->id . '" value="' . ($count_score != 0 ? ($count_score) : 0) . '" />';
                            ?>

                            <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_history_view('<?php echo $row->id; ?>');">History</a>
                            <?php /*?>
				  <a href="<?php echo base_url('admin/members/faipview/'.$row->id);?>" class="btn btn-primary btn-xs">View</a><br />
				  <?php */ ?>
                            <!- --------------------- Tambahan Request dari P' Rully ----------------------------------------------- ->
                              <?php
                              if (isAdminLSKI()) { ?>
                                <a onclick="viewCatatanBK('<?php echo $row->id; ?>')" href="#" class="btn btn-primary btn-xs">Catatan BK</a>
                                | <a onclick="load_upload_skip_view('<?php echo $row->no_kta; ?>')" href="#" class="btn btn-primary btn-xs">Upload E-SKIP</a>

                                <?php
                                $golek = $this->faip_model->get_user_skip_kta($row->no_kta);
                                if ($golek != null) {
                                  $skipnee = $golek->skip; // $skipnee ='KTA-'.$row->no_kta.'-'.$skipne ; 
                                } else {
                                  $skipnee = 'Belum_punya_SKIP.png';
                                }

                                /*
$filter =  $skipnee ; // "namafile";
$folder = 'assets/SKIP/'.$filter ; // './';
// $proses = new RecursiveDirectoryIterator("$folder");
$proses = new RecursiveDirectoryIterator("$folder");
$tampil[] = $proses ;


foreach(new RecursiveIteratorIterator($proses) as $file)
{
  if (!((strpos(strtolower($file), $filter)) === false) || empty($filter))
  {
   // $tampil[] = preg_replace("#/#", "/", $file);
   $tampil = $file ;
  }
  $tampil[] = $file ;
}

 sort($tampil);
print_r($tampil);	exit() ;
*/

                                $urlSkip = base_url() . 'admin/members/skip_file' . $skipnee;
                                ?>
                                &nbsp;<a class="btn btn-primary btn-xs" href="<?= $urlSkip  ?>" target="_blank">Download SKIP</a>

                              <?php  } ?>
                              <!- ------------------------------------------------------------------------------------------------------------------------------------------------- ->
                                <?php
                                if ($showPaymentInfo && ($row->status_faip >= 1)) {
                                ?><a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_PaymentDetail('<?php echo $row->id; ?>');">Payment Info</a><?php
                                                                                                                                                                      }
                                                                                                                                                                      if ($showRevisi && $row->status_faip <= 6) {
                                                                                                                                                                        if ($row->need_revisi == '0') {
                                                                                                                                                                        ?><a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_revisi('<?php echo $row->id; ?>');">Revisi</a> <?php
                                                                                                                                                                                                                                                                                                  } else if ($row->need_revisi == '1') {
                                                                                                                                                                                                                                                                                                    echo '<br />' . $row->revisi_note;
                                                                                                                                                                                                                                                                                                  }
                                                                                                                                                                                                                                                                                                } else if ($showRevisi && ($row->status_faip >= 6 && $row->status_faip <= 8)) {
                                                                                                                                                                                                                                                                                                  if ($row->need_revisi == '0') {
                                                                                                                                                                                                                                                                                                    ?><a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_revisi('<?php echo $row->id; ?>');">Revisi</a><?php
                                                                                                                                                                                                                                                                                                                                                                                                                            } else if ($row->need_revisi == '1') {
                                                                                                                                                                                                                                                                                                                                                                                                                              echo '<br />' . $row->revisi_note;
                                                                                                                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                                                                                                                          }

                                                                                                                                                                                                                                                                                                                                                                                                                              ?>
                                <?php
                                // Change/Set BK
                                if ($showChangeBK) {
                                  // Old code, need to be removed
                                  // $this->session->userdata('type')=="0" || $this->session->userdata('admin_id')=="670" || $this->session->userdata('admin_id')=="659"
                                  // //($this->session->userdata('type')=="0" || $this->session->userdata('type')=="1") && $row->status_faip!='12'  
                                ?>
                                  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_profile_view8('<?php echo $row->id; ?>','<?php echo str_pad($row->bidang, 3, '0', STR_PAD_LEFT); ?>');">
                                    Change BK</a><?php
                                                }  ?>
                                <?php
                                if ($row->is_manual == "1") {
                                ?>
                                  <br /><br />
                                  FAIP Manual<br />
                                  <?php
                                  if ($showEditFaipManual) { ?>
                                    &nbsp;<a href="javascript:;" class="btn btn-primary btn-xs" onClick="loadQuickEditFaipManual(<?php echo $row->id . ',\'' . $row->no_kta . '\',\'' . $row->wil . '\',\'' . $row->bk . '\',' . intval($row->wajib1_score) . ',' . intval($row->wajib2_score) . ',' . intval($row->wajib3_score) . ',' . intval($row->wajib4_score) . ',' . intval($row->pilihan_score) . ',\'' . $row->keputusan_manual . '\',\'' . $row->bap . '\''; ?>)">Edit</a><?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    if ($showDownloadBAP) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      ?>
                                    &nbsp;<a class="btn btn-primary btn-xs" href="<?php echo base_url() . 'assets/uploads/faip_manual/' . $row->bap; ?>" target="_blank">BAP</a>
                                <?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  }
                                ?>
                          </td>

                        </tr>
                      <?php endforeach;
                    else: ?>
                      <tr>
                        <td colspan="10" align="center" class="text-red">No Record found!</td>
                      </tr>
                    <?php
                    endif;
                    ?>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>

              <!--Pagination-->
              <div class="paginationWrap"> <?php echo ($result) ? $links : ''; ?> </div>

              <!-- /.box-body -->
            </div>
            <!-- /.box -->

            <!-- /.box -->
          </div>
        </div>
      </section>
      <!-- /.content -->
    </aside>

    <div class="modal fade" id="quick_upload_skip">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Upload E-SKIP <span id="j_comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxUloadSkip" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box">
              <table width="95%" style="border: 0;">
                <table width="95%" style="border: 0;">

                  <tr>
                    <td><strong><span class="form-group">File E-SKIP</span></strong></td>
                    <td id="">
                      <form action="<?= base_url('/admin/members/fungsiUploadGambar') ?>" method="post" enctype="multipart/form-data">

                        <input type="file" name="gambar" id="gambar" accept="png, jpeg, jpg, gif, pdf" value='Pilih Filenya' />
                        <input type="text" name='id_f' id='id_f' />
                        <button type="submit">Upload File</button>

                      </form>

                  </tr>


                </table>


              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_bk">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set BK <span id="j_comp_name" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxUpdateBK" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box">
              <table width="95%" style="border: 0;">

                <tr>
                  <td><strong><span class="form-group">BK</span></strong></td>
                  <td id="">
                    <select class="form-control" name="bk" id="bk" required="">
                      <?php
                      if (isset($m_bk[0])) {
                        foreach ($m_bk as $val) {
                          echo '<option value="' . $val->value . '" > ' . $val->value . ' ' . $val->name . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetbk()">Save</button><input type="hidden" name="id_bk" id="id_bk" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_status">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set Status <span id="j_comp_name_" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_">
              <table width="95%" border="0">

                <tr>
                  <td><strong><span class="form-group">Status</span></strong></td>
                  <td id="">
                    <select class="form-control" name="status" id="status" required="" onchange="check_status()">
                      <?php
                      /*if(isset($m_faip_status[0])){
							foreach($m_faip_status as $val){
								//if($row->status_faip=="1" && ($val->value=="2" || $val->value=="3"))
								//	echo '<option value="'.$val->value.'" > '.$val->name.'</option>';
								///if(($row->status_faip>="5" && $row->status_faip<="10") && ($val->value>="6" && $val->value<="10"))
								//	echo '<option value="'.$val->value.'" > '.$val->name.'</option>';
								//if(($row->status_faip=="12") && ($val->value=="13"))
									echo '<option value="'.$val->value.'" > '.$val->name.'</option>';
							}
						
					}*/
                      ?>
                    </select>
                  </td>
                </tr>

                <tr style="display:none;" id="tdinterview_date">
                  <td>

                    <strong><span class="form-group">Tanggal Interview</span></strong>
                  </td>
                  <td>
                    <input type="text" name="interview_date" id="interview_date" class="form-control datepicker" />

                  </td>
                </tr>

                <tr style="display:none;" id="tdinterview_hour">
                  <td><strong><span class="form-group">Waktu Interview</span></strong></td>
                  <td>
                    <input type="time" name="interview_start_hour" id="interview_start_hour" class="timepicker" /> - <input type="time" name="interview_end_hour" id="interview_end_hour" class="timepicker" />
                  </td>
                </tr>

                <tr style="display:none;" id="tdinterview_loc">
                  <td><strong><span class="form-group">Lokasi / Link Interview</span></strong></td>
                  <td>
                    <textarea name="interview_loc" id="interview_loc"></textarea>
                  </td>
                </tr>

                <tr style="display:none;" id="tdinterview_note">
                  <td><span style="color:red">Note : Informasi Tanggal, Waktu dan Lokasi / Link akan ke email ke aplikan</span></td>
                </tr>



                <tr style="display:none;" id="tdscore">
                  <td><strong><span class="form-group">Score Final</span></strong></td>
                  <td>
                    <input type="text" name="score_final" id="score_final" />
                  </td>
                </tr>

                <tr style="display:none;" id="tdkeputusan">
                  <td><strong><span class="form-group">Hasil</span></strong></td>
                  <td>
                    <select class="form-control" name="keputusan" id="keputusan" required="">
                      <option value="Belum Memenuhi IPP"> Belum Memenuhi IPP</option>
                      <option value="IPP"> IPP</option>
                      <option value="IPM"> IPM</option>
                      <option value="IPU"> IPU</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Note</span></strong></td>
                  <td>
                    <textarea name="remarks" id="remarks"></textarea>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetstatus()" data-dismiss="modal">Save</button><input type="hidden" name="id_status" id="id_status" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_ip">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set SKIP <span id="j_comp_name_2" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox2" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box2">
              <table width="95%" border="0">

                <tr>
                  <td width="35%"><strong><span class="form-group">Kualifikasi</span></strong></td>
                  <td id="">

                    <select id="ip_type" name="ip_type" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <option value="1">IPP</option>
                      <option value="2">IPM</option>
                      <option value="3">IPU</option>
                    </select>

                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">BK FAIP</span></strong></td>
                  <td id="">

                    <select id="ip_bk" name="ip_bk" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_bk)) {
                        foreach ($m_bk as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>


                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">HKK</span></strong></td>
                  <td id="">

                    <select id="ip_hkk" name="ip_hkk" class="form-control input-md" required="">
                      <option value="00">00</option>
                      <?php
                      if (isset($m_hkk)) {
                        foreach ($m_hkk as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>

                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">No. IP (6 Digit Nomor urut IP)</span></strong></td>
                  <td id="">
                    <input type="text" size="10" name="no_ip" id="no_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                                ?>" class="form-control" placeholder="No. IP" required="required" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nama yang tercetak pada SKIP</span></strong></td>
                  <td id="">
                    <input type="text" name="nama_ip" id="nama_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                          ?>" class="form-control" placeholder="Nama" required="required" />
                  </td>
                </tr>

                <?php /* ?>
			
			<tr>
              <td width="25%"><strong><span class="form-group">Masukan Cabang</span></strong></td>
              <td id="">
			  <select id="ip_cabang" name="ip_cabang" class="form-control input-md" required="">
				<option value="">--Choose--</option>
				<?php
				if(isset($m_cab)){
					foreach($m_cab as $val){
						?>
						<option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
						<?php
					}
				}
				?>
				</select>
			  </td>
            </tr>
            <tr>
              <td><strong><span class="form-group">Masukan BK / HKK</span></strong></td>
              <td id="">
			  <select id="ip_bk" name="ip_bk" class="form-control input-md" required="">
				<option value="">--Choose--</option>
				<?php
				if(isset($m_bk)){
					foreach($m_bk as $val){
						?>
						<option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
						<?php
					}
				}
				?>
				</select>
			  </td>
            </tr>
			
			<tr>
              <td><strong><span class="form-group">Masukan Sub Bidang Kejuruan</span></strong></td>
              <td id="">
			  <select id="ip_subbk" name="ip_subbk" class="form-control input-md" required="">
				<option value="">--Choose--</option>
				</select>
			  </td>
            </tr>
            
			<tr>
              <td><strong><span class="form-group">Kode Pemutakhiran Sertifikat</span></strong></td>
              <td id="">
				<input type="text" name="ip_kp" id="ip_kp" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control" placeholder="Kode Pemutakhiran Sertifikat" required="required" />
			  </td>
            </tr>
			
			<?php */ ?>

                <tr>
                  <td><strong><span class="form-group">From Date</span></strong></td>
                  <td id="">
                    <input type="text" size="10" name="from_ip" id="from_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                                    ?>" class="form-control datepicker" placeholder="From Date" required="required" />
                  </td>
                </tr>



                <tr>
                  <td><strong><span class="form-group">No. Sertifikat (Nomor yg tertera diatas kanan pada SIP)</span></strong></td>
                  <td id="">
                    <input type="text" name="no_seri" id="no_seri" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));
                                                                          ?>" class="form-control" placeholder="No. Sertifikat" required="" />
                  </td>
                </tr>

                <?php /* ?>
            <tr>
              <td><strong><span class="form-group">To Date</span></strong></td>
              <td id="">
				<input type="text" name="until_ip" id="until_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control datepicker" placeholder="To Date" required="required" />
			  </td>
            </tr>
			
			<tr>
              <td><strong><span class="form-group">Tanggal SK</span></strong></td>
              <td id="">
				<input type="text" name="tgl_sk_ip" id="tgl_sk_ip" value="<?php //echo set_value('dob',(isset($row->dob)?date('d-m-Y',strtotime($row->dob)):$row->dob));?>" class="form-control datepicker" placeholder="SK Date" required="required" />
			  </td>
            </tr>
			<?php */ ?>
                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetip()" data-dismiss="modal">Save</button><input type="hidden" name="id_ip" id="id_ip" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_majelis">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set Asesor <span id="j_majelis" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox_majelis" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_majelis">
              <table width="95%" border="0">

                <tr>
                  <td><strong><span class="form-group">Asesor</span></strong></td>
                  <td>
                    <select class="form-control" name="majelis" id="majelis" required="">
                      <?php
                      /*if(isset($m_majelis[0])){
						foreach($m_majelis as $val){
							echo '<option value="'.$val->id.'" > '.$val->name.'</option>';
						}
					}*/
                      if (isset($m_user_majelis[0])) {
                        foreach ($m_user_majelis as $val) {
                          echo '<option value="' . $val->id . '" > ' . $val->firstname . ' ' . $val->lastname . ' (' . str_pad($val->no_kta, 6, '0', STR_PAD_LEFT) . ')</option>';
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td><button type="button" class="btn btn-default" onclick="savesetmajelis()" data-dismiss="modal">Save</button>
                    <input type="hidden" name="id_faip" id="id_faip" value="" />
                    <input type="hidden" name="tipe_faip" id="tipe_faip" value="" />
                  </td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="edit_score">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Edit Score <span id="j_comp_name_2" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox2" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_2">
              <table width="95%" border="0">

                <tr>
                  <td>Unit</td>
                  <td>Nilai Rata-rata Asesor / Konsensus</td>
                  <td>Batas Nilai Minimum (IPP)</td>
                  <td>Batas Nilai Minimum (IPM)</td>
                  <td>Batas Nilai Minimum (IPU)</td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">W1</span></strong></td>
                  <td>
                    <input type="text" name="bk_w1_score" id="bk_w1_score" onchange="bk_calc_score();" />
                  </td>
                  <td><strong><span class="form-group">60</span></strong></td>
                  <td><strong><span class="form-group">300</span></strong></td>
                  <td><strong><span class="form-group">600</span></strong></td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">W2</span></strong></td>
                  <td>
                    <input type="text" name="bk_w2_score" id="bk_w2_score" onchange="bk_calc_score();" />
                  </td>
                  <td><strong><span class="form-group">180</span></strong></td>
                  <td><strong><span class="form-group">900</span></strong></td>
                  <td><strong><span class="form-group">1800</span></strong></td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">W3</span></strong></td>
                  <td>
                    <input type="text" name="bk_w3_score" id="bk_w3_score" onchange="bk_calc_score();" />
                  </td>
                  <td><strong><span class="form-group">120</span></strong></td>
                  <td><strong><span class="form-group">600</span></strong></td>
                  <td><strong><span class="form-group">1200</span></strong></td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">W4</span></strong></td>
                  <td>
                    <input type="text" name="bk_w4_score" id="bk_w4_score" onchange="bk_calc_score();" />
                  </td>
                  <td><strong><span class="form-group">60</span></strong></td>
                  <td><strong><span class="form-group">300</span></strong></td>
                  <td><strong><span class="form-group">600</span></strong></td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">P</span></strong></td>
                  <td>
                    <input type="text" name="bk_p_score" id="bk_p_score" onchange="bk_calc_score();" />
                  </td>
                  <td><strong><span class="form-group">180</span></strong></td>
                  <td><strong><span class="form-group">900</span></strong></td>
                  <td><strong><span class="form-group">1800</span></strong></td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Score</span></strong></td>
                  <td>
                    <input type="text" name="bk_all_score" id="bk_all_score" disabled="disabled" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Keputusan</span></strong></td>
                  <td colspan="4">
                    <textarea name="bk_keputusan" id="bk_keputusan" disabled="disabled" style="width:100%"></textarea>
                  </td>
                </tr>


                <!--<tr>
              <td><strong><span class="form-group">Note</span></strong></td>
              <td>
				<textarea name="remarks" id="remarks"></textarea>
			  </td>
            </tr>-->

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="saveeditscore()" data-dismiss="modal">Save</button><input type="hidden" name="bk_id_faip" id="bk_id_faip" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>


    <div class="modal fade" id="quick_history">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">History <span id="j_history" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <ul class="timeline">




            </ul>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_revisi">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Set Revisi<span id="j_comp_name_revisi" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errbox_revisi" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_revisi">
              <table width="95%" border="0">

                <tr>
                  <td><strong><span class="form-group">Note</span></strong></td>
                  <td>
                    <textarea name="remarks_revisi" id="remarks_revisi"></textarea>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="savesetrevisi()" data-dismiss="modal">Save</button><input type="hidden" name="id_revisi" id="id_revisi" value="" /></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="quick_faip">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><span id="faipManualAction">Tambah</span> FAIP Manual <span id="j_comp_name_2_faip" style="font-weight:bold;"></span></h4>
          </div>
          <div class="modal-body">
            <!-- /.box-header -->
            <!-- form start -->
            <div id="errboxFaipManual" style="display:none;" class="alert alert-warning" role="alert"></div>
            <div class="box-body" id="j_box_2_faip">
              <input type="text" name="faip_id" id="faip_id" hidden="hidden" value="" />
              <table width="95%" border="0">

                <tr>
                  <td><strong><span class="form-group">Nomor KTA</span></strong> <span class="red">*</span></td>
                  <td id="">
                    <select id="no_kta_faip" name="no_kta_faip" class="form-control input-md" required="">
                      <option value="">--Choose--</option>

                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">BK FAIP</span></strong><span class="red">*</span></td>
                  <td id="">
                    <select id="ip_bk_faip" name="ip_bk_faip" class="form-control input-md" required="">
                      <option value="">--Choose--</option>
                      <?php
                      if (isset($m_bk)) {
                        foreach ($m_bk as $val) {
                      ?>
                          <option value="<?php echo $val->value; ?>" <?php //echo (isset($user_address[0]->addresstype)?(($user_address[0]->addresstype==$val->id)?'selected="true"':""):""); 
                                                                      ?>><?php echo $val->value; ?> - <?php echo $val->name; ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                </tr>


                <tr>
                  <td><strong><span class="form-group">Nilai W1</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_w1_score_faip" id="bk_w1_score_faip" onchange="bk_calc_score2();" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nilai W2</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_w2_score_faip" id="bk_w2_score_faip" onchange="bk_calc_score2();" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nilai W3</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_w3_score_faip" id="bk_w3_score_faip" onchange="bk_calc_score2();" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nilai W4</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_w4_score_faip" id="bk_w4_score_faip" onchange="bk_calc_score2();" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Nilai P</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_p_score_faip" id="bk_p_score_faip" onchange="bk_calc_score2();" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Total Score</span></strong><span class="red">*</span></td>
                  <td>
                    <input type="text" name="bk_all_score_faip" id="bk_all_score_faip" disabled="disabled" />
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Keputusan</span></strong><span class="red">*</span></td>
                  <td colspan="4">
                    <select id="bk_keputusan_faip" name="bk_keputusan_faip" class="form-control input-md" required="" onchange="calc_total_va();">
                      <option value="">--Choose--</option>
                      <option value="0">Belum memenuhi persyaratan untuk sertifikasi IPP</option>
                      <option value="1">IPP</option>
                      <option value="2">IPM</option>
                      <option value="3">IPU</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group">Total Pembayaran VA</span></strong></td>
                  <td>
                    <input type="text" name="total_va" id="total_va" disabled="disabled" />
                  </td>
                </tr>

                <tr>
                  <td><strong>Attachment <br /> <span class="red">(Max. 700KB, image atau PDF)</span></strong></td>
                  <td>
                    <div>
                      <div class="form-group">
                        <div>
                          <input type="file" name="bukti" id="bukti" onchange="upload_bukti()">
                          <div id="msg_upload"></div>
                          <div id="errUpload" class="red"></div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td><strong><span class="form-group"></span></strong></td>
                  <td id=""><button type="button" class="btn btn-default" onclick="saveaddfaip()">Save</button></td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>

        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>


    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
  </div>

  <div class="modal fade" id="quick_PaymentDetail">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Payment Detail<span id="j_comp_name_p" style="font-weight:bold;"></span></h4>
        </div>
        <div class="modal-body">
          <!-- /.box-header -->
          <!-- form start -->
          <div id="errboxPaymentDetail" style="display:none;" class="alert alert-warning" role="alert"></div>
          <div class="box-body" id="j_box_p">
            <div id="paymentDetailText"></div>
          </div>
          <!-- /.box-body -->

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>



  <?php $this->load->view('admin/common/footer'); ?>



  <script>
    function load_quick_majelis(id, type) {
      $('#quick_majelis').modal('show');
      $("#id_faip").val(id);
      $("#tipe_faip").val(type);

    }

    function check_status() {
      var status = $("#status").val();
      if (status == "9") {
        $('#tdscore').css('display', 'table-row');
        $('#tdkeputusan').css('display', 'table-row');
        $('#tdinterview_date').css('display', 'none');
        $('#tdinterview_hour').css('display', 'none');
        $('#tdinterview_loc').css('display', 'none');
        $('#tdinterview_note').css('display', 'none');


        var id = $("#id_status").val();


        $('#score_final').val($('#total_' + id).val());
        $('#score_final').attr('readonly', 'true');


        var w1 = $('#w1_' + id).text();
        var w2 = $('#w2_' + id).text();
        var w3 = $('#w3_' + id).text();
        var w4 = $('#w4_' + id).text();
        var p = $('#p_' + id).text();

        var total = parseFloat(w1) + parseFloat(w2) + parseFloat(w3) + parseFloat(w4) + parseFloat(p);


        var kep = '';
        if (total >= 600 && total < 3000) {
          if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "IPP";
          else kep = "Belum Memenuhi IPP";
        } else if (total >= 3000 && total < 6000) {
          if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
            kep = "IPM";
          else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "IPP";
          else kep = "Belum Memenuhi IPP";
        } else if (total >= 6000) {
          if (w1 >= 600 && w2 >= 1800 && w3 >= 1200 && w4 >= 600 && p >= 1800)
            kep = "IPU";
          else if (w1 >= 300 && w2 >= 900 && w3 >= 600 && w4 >= 300 && p >= 900)
            kep = "IPM";
          else if (w1 >= 60 && w2 >= 180 && w3 >= 120 && w4 >= 60 && p >= 180)
            kep = "IPP";
          else kep = "Belum Memenuhi IPP";
        } else if (total < 600) {
          kep = "Belum Memenuhi IPP";
        }
        console.log('Total: ' + total + ', Keputusan:' + kep);
        $('#keputusan').val(kep);
        $('#keputusan').attr('disabled', 'disabled');

      } else if (status == "8") {
        $('#tdscore').css('display', 'none');
        $('#tdkeputusan').css('display', 'none');
        $('#tdinterview_date').css('display', 'table-row');
        $('#tdinterview_hour').css('display', 'table-row');
        $('#tdinterview_loc').css('display', 'table-row');
        $('#tdinterview_note').css('display', 'table-row');
      } else {
        $('#tdscore').css('display', 'none');
        $('#tdkeputusan').css('display', 'none');
        $('#tdinterview_date').css('display', 'none');
        $('#tdinterview_hour').css('display', 'none');
        $('#tdinterview_loc').css('display', 'none');
        $('#tdinterview_note').css('display', 'none');
      }
    }

    function copy(faip_id, majelis) {
      var changeConfirmation = confirm("Data penilaian Anda sebelumnya untuk aplikan ini sebelumnya akan terhapus. Apakah anda yakin akan meng-copy score? ");
      if (changeConfirmation) {
        $.ajax({
          url: '<?php echo site_url('admin/faip/ajax_copyfaip') ?>',
          dataType: "json",
          type: "POST",
          async: true,
          data: {
            faip_id: faip_id,
            majelis: majelis
          },
          success: function(response, status) {
            console.log(response);
            if (response.status) {
              alert("Score has been copied successfully");
              location.reload();
            } else {
              alert("Failed to copy score: " + response.message);
            }
          },
          error: function(jqXHR, exception) {
            console.log(jqXHR);
            var error_msg = '';
            if (jqXHR.status === 0) {
              error_msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 403) {
              error_msg = 'Not authorized. [403]';
              //} else if (jqXHR.status == 404) {
              //	error_msg = 'Requested page not found. [404]'; //Faip ID is not found
            } else if (jqXHR.status == 500) {
              error_msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
              error_msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
              error_msg = 'Time out error.';
            } else if (exception === 'abort') {
              error_msg = 'Ajax request aborted.';
            } else {
              var json = JSON.parse(jqXHR.responseText)
              if (json.message) {
                error_msg = json.message;
              } else {
                error_msg = '<br/>\n' + jqXHR.responseText;
              }
            }
            alert("Failed to copy score: " + error_msg);
          }
        });


      } else {
        return false;
      }
    }
  </script>

  <style>
    .timeline>li>.timeline-item {
      margin-top: 0px;
    }
  </style>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#no_kta_faip').select2({
        width: '100%',
        ajax: {
          url: function(params) {
            return '<?php echo base_url(); ?>admin/members/ajax_member_search?id=' + params.term + '&page=' + params.page || 1;
          },
          processResults: function(data) {
            return {
              results: $.map(JSON.parse(data), function(item) {
                return {
                  text: item.no_kta,
                  id: item.no_kta
                }
              })
            };
          }
        }
      });


    });
  </script>

  <style>
    .red {
      color: red;
    }
  </style>