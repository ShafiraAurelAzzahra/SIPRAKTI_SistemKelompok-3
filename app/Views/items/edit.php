<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Edit Barang</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= previous_url() ?>" class="btn btn-outline-primary mb-3">
  <i class="ti ti-arrow-left"></i>
  Kembali
</a>

<?php if (session()->getFlashdata('msg')) : ?>
  <div class="pb-2">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= session()->getFlashdata('msg') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold">Edit Barang</h5>
    <form action="<?= base_url('admin/items/' . $item['slug']); ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field(); ?>
      <input type="hidden" name="_method" value="PUT">
      <div class="row">
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3 p-3">
          <label for="cover" class="d-block" style="cursor: pointer;">
            <div class="d-flex justify-content-center bg-light overflow-hidden h-100 position-relative">
              <?php
              $coverImageFilePath = ITEM_COVER_URI . $item['item_cover'];
              ?>
              <img id="itemCoverPreview" src="<?= base_url((!empty($item['item_cover']) && file_exists($coverImageFilePath)) ? $coverImageFilePath : ITEM_COVER_URI . DEFAULT_ITEM_COVER); ?>" alt="" height="300" class="z-1">
            </div>
          </label>
        </div>
        <div class="col-12 col-md-6 col-lg-8 col-xl-9">
          <div class="mb-3">
            <label for="cover" class="form-label">Gambar barang</label>
            <input class="form-control <?php if ($validation->hasError('cover')) : ?>is-invalid<?php endif ?>" type="file" id="cover" name="cover" onchange="previewImage()">
            <div class="invalid-feedback">
              <?= $validation->getError('cover'); ?>
            </div>
          </div>
          <div class="mb-3">
            <label for="title" class="form-label">Nama barang</label>
            <input type="text" class="form-control <?php if ($validation->hasError('title')) : ?>is-invalid<?php endif ?>" id="title" name="title" value="<?= $oldInput['title'] ?? $item['title']; ?>" required>
            <div class="invalid-feedback">
              <?= $validation->getError('title'); ?>
            </div>
          </div>
          <div class="mb-3">
            <label for="author" class="form-label">Merk</label>
            <input type="text" class="form-control <?php if ($validation->hasError('author')) : ?>is-invalid<?php endif ?>" id="author" name="author" value="<?= $oldInput['author'] ?? $item['author']; ?>" required>
            <div class="invalid-feedback">
              <?= $validation->getError('author'); ?>
            </div>
          </div>
          <div class="mb-3">
            <label for="publisher" class="form-label">Produsen</label>
            <input type="text" class="form-control <?php if ($validation->hasError('publisher')) : ?>is-invalid<?php endif ?>" id="publisher" name="publisher" value="<?= $oldInput['publisher'] ?? $item['publisher']; ?>" required>
            <div class="invalid-feedback">
              <?= $validation->getError('publisher'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <label for="isbn" class="form-label">Serial Number</label>
          <input type="number" class="form-control <?php if ($validation->hasError('isbn')) : ?>is-invalid<?php endif ?>" id="isbn" name="isbn" minlength="10" maxlength="13" aria-describedby="isbnHelp" value="<?= $oldInput['isbn'] ?? $item['isbn']; ?>" required>
          <div id="isbnHelp" class="form-text">
            Contain only numbers.
          </div>
          <div class="invalid-feedback">
            <?= $validation->getError('isbn'); ?>
          </div>
        </div>
        <div class="col-12 col-md-6 mb-3">
          <label for="year" class="form-label">Tahun pembelian</label>
          <input type="number" class="form-control <?php if ($validation->hasError('year')) : ?>is-invalid<?php endif ?>" id="year" name="year" minlength="4" maxlength="4" value="<?= $oldInput['year'] ?? $item['year']; ?>" required>
          <div class="invalid-feedback">
            <?= $validation->getError('year'); ?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 col-lg-4 mb-3">
          <label for="rack" class="form-label">Rak</label>
          <select class="form-select <?php if ($validation->hasError('rack')) : ?>is-invalid<?php endif ?>" aria-label="Select rack" id="rack" name="rack" value="<?= $oldInput['rack'] ?? $item['rack_id']; ?>" required>
            <option>--Pilih rak--</option>
            <?php foreach ($racks as $rack) : ?>
              <option value="<?= $rack['id']; ?>" <?= ($oldInput['rack'] ?? $item['rack_id']) == $rack['id'] ? 'selected' : ''; ?>><?= $rack['name']; ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">
            <?= $validation->getError('rack'); ?>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 mb-3">
          <label for="category" class="form-label">Kategori</label>
          <select class="form-select <?php if ($validation->hasError('category')) : ?>is-invalid<?php endif ?>" aria-label="Select category" id="category" name="category" value="<?= $oldInput['category'] ?? $item['category_id']; ?>" required>
            <option>--Pilih category--</option>
            <?php foreach ($categories as $category) : ?>
              <option value="<?= $category['id']; ?>" <?= ($oldInput['category'] ?? $item['category_id']) == $category['id'] ? 'selected' : ''; ?>><?= $category['name']; ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">
            <?= $validation->getError('category'); ?>
          </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
          <label for="stock" class="form-label">Jumlah stok barang</label>
          <input type="number" class="form-control <?php if ($validation->hasError('stock')) : ?>is-invalid<?php endif ?>" id="stock" name="stock" value="<?= $oldInput['stock'] ?? $item['quantity']; ?>" required>
          <div class="invalid-feedback">
            <?= $validation->getError('stock'); ?>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function previewImage() {
    const fileInput = document.querySelector('#cover');
    const imagePreview = document.querySelector('#itemCoverPreview');

    const reader = new FileReader();
    reader.readAsDataURL(fileInput.files[0]);

    reader.onload = function(e) {
      imagePreview.src = e.target.result;
    };
  }
</script>
<?= $this->endSection() ?>