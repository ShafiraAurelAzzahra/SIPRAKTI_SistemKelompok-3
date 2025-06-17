<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Cari Data Peminjaman</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= base_url('admin/fines'); ?>" class="btn btn-outline-primary mb-3">
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
        <h5 class="card-title fw-semibold mb-4">Cari anggota / barang</h5>
        <div class="mb-3">
          <label for="search" class="form-label">Cari UID, nama, email, judul barang</label>
          <input type="text" class="form-control" id="search" name="search" placeholder="'Ikhsan', 'xibox@gmail.com', 'Lorem ipsum'">
          <div class="invalid-feedback">
          </div>
        </div>
        <button class="btn btn-primary" onclick="getReturns(document.querySelector('#search').value)">Cari</button>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div id="returnsResult">
          <p class="text-center mt-4">Data peminjaman muncul disini</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function getReturns(param) {
    jQuery.ajax({
      url: "<?= base_url('admin/fines/returns/search'); ?>",
      type: 'get',
      data: {
        'param': param
      },
      success: function(response, status, xhr) {
        $('#returnsResult').html(response);

        $('html, body').animate({
          scrollTop: $("#returnsResult").offset().top
        }, 500);
      },
      error: function(xhr, status, thrown) {
        console.log(thrown);
        $('#returnsResult').html(thrown);
      }
    });
  }
</script>
<?= $this->endSection() ?>