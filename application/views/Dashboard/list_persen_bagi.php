<div class="container mt-5">

  <?php if ($this->session->flashdata('success_update') || $this->session->flashdata('success_delete')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Sukses!</strong> <?= $this->session->flashdata('success_save'); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <a href="<?= base_url('/dashboard/add_data') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add data </a>

  <table class="table mt-3">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Kode</th>
        <th scope="col">Keterangan</th>
        <th scope="col">Angka awal (%)</th>
        <th scope="col">Hasil akhir (%)</th>
        <th scope="col">Aksi</th>
      </tr>
    </thead>

    <tbody>
      <?php
      $no = 1;
      foreach ($list_data as $list) : ?>
        <tr>
          <th scope="row"><?= $no++ ?></th>
          <td><?= $list->kode ?></td>
          <td><?= htmlspecialchars_decode($list->keter)  ?></td>
          <td><?= $list->nilai_awal . '%' ?></td>
          <td><?= $list->persen . '%' ?></td>
          <td>
            <a class="btn btn-primary btn-sm" href="<?= base_url('/dashboard/edit_data/' . $list->id) ?>"><i class="fas fa-edit"></i></a>
            <a class="btn btn-danger btn-sm btn-delete" data-id="<?= $list->id ?>" data-kode="<?= $list->kode ?>"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

<?php if ($this->session->flashdata('success_delete') || $this->session->flashdata('success_update')): ?>
  <script>
    //alert delete

    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: '<?= $this->session->flashdata("success_delete") ?>',
      showConfirmButton: false,
      timer: 2000
    });


    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: '<?= $this->session->flashdata("success_update") ?>',
      showConfirmButton: false,
      timer: 2000
    });
  </script>
<?php endif; ?>


<!-- Confirm delete -->
<script>
  $(document).ready(function() {
    $('.btn-delete').on('click', function(e) {
      e.preventDefault();
      const id = $(this).data('id');
      const kode = $(this).data('kode');

      Swal.fire({
        title: 'Yakin hapus data ini?',
        text: "Data dengan kode " + '' +
          kode + " yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '<?= base_url("/dashboard/delete_data/") ?>' + id;
        }
      });
    });
  });
</script>