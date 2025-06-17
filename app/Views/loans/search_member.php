<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Peminjaman Baru</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= base_url('admin/loans'); ?>" class="btn btn-outline-primary mb-3">
  <i class="ti ti-arrow-left"></i>
  Kembali
</a>

<?php if (session()->getFlashdata('msg')) : ?>
  <div class="pb-2">
    <div class="alert <?= (session()->getFlashdata('error') ?? false) ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
      <?= session()->getFlashdata('msg') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <h5 class="card-title fw-semibold mb-4">Cari anggota</h5>
        <div class="mb-3">
          <label for="search" class="form-label">Cari UID, nama atau email</label>
          <input type="text" class="form-control" id="search" name="search" placeholder="'Ikhsan', 'xibox@gmail.com'">
          <div class="invalid-feedback">
          </div>
        </div>
        <button class="btn btn-primary" onclick="getMemberData(document.querySelector('#search').value)">Cari</button>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div id="memberResult">
          <p class="text-center mt-4">Data anggota muncul disini</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function getMemberData(param) {
    jQuery.ajax({
      url: "<?= base_url('admin/loans/new/members/search'); ?>",
      type: 'get',
      data: {
        'param': param
      },
      success: function(response, status, xhr) {
        $('#memberResult').html(response);

        $('html, body').animate({
          scrollTop: $("#memberResult").offset().top
        }, 500);
      },
      error: function(xhr, status, thrown) {
        console.log(thrown);
        $('#memberResult').html(thrown);
      }
    });
  }
</script>
<?= $this->endSection() ?>