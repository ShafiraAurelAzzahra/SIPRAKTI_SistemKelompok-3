<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Detail Barang</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$coverImageFilePath = ITEM_COVER_URI . $item['item_cover'];
?>
<style>
  #item-cover {
    background-image: url(<?= base_url((!empty($item['item_cover']) && file_exists($coverImageFilePath)) ? $coverImageFilePath : ITEM_COVER_URI . DEFAULT_ITEM_COVER); ?>);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    max-width: 400px;
    height: 380px;
  }
</style>

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
    <div class="d-flex justify-content-between mb-4">
      <div>
        <a href="<?= base_url('admin/items'); ?>" class="btn btn-outline-primary">
          <i class="ti ti-arrow-left"></i>
          Kembali
        </a>
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <div>
          <a href="<?= base_url("admin/items/{$item['slug']}/edit"); ?>" class="btn btn-primary w-100">
            <i class="ti ti-edit"></i>
            Edit
          </a>
        </div>
        <div>
          <form action="<?= base_url("admin/items/{$item['slug']}"); ?>" method="post">
            <?= csrf_field(); ?>
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure?');">
              <i class="ti ti-trash"></i>
              Delete
            </button>
          </form>
        </div>
      </div>
    </div>
    <h5 class="card-title fw-semibold mb-4">Detail Barang</h5>
    <div class="row">
      <div class="col-12 col-lg-4">
        <div id="item-cover" class="mb-4 bg-light">
        </div>
      </div>
      <div class="col-12 col-lg-8 d-flex flex-wrap">
        <div class="w-100 mb-2">
          <h2 class="mb-2"><?= $item['title']; ?></h2>
          <h5>Tahun: <?= $item['year']; ?></h5>
          <h5>Merk: <?= $item['author']; ?></h5>
          <h5>Produsen: <?= $item['publisher']; ?></h5>
          <h5>Kategori: <?= $item['category']; ?></h5>
          <h5>Rak: <?= $item['rack']; ?>, Lantai <?= $item['floor']; ?></h5>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h2>
          <i class="ti ti-database"></i>
        </h2>
        <h3>
          Total: <?= $item['quantity']; ?>
        </h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h2>
          <i class="ti ti-arrows-exchange"></i>
        </h2>
        <h3>
          Dipinjam: <?= $loanCount; ?>
        </h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h2>
          <i class="ti ti-item-2"></i>
        </h2>
        <h3>
          Tersedia: <?= $itemStock; ?>
        </h3>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>