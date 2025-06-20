<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Daftar Barang</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
    <div class="row mb-2">
      <div class="col-12 col-lg-5">
        <h5 class="card-title fw-semibold mb-4">
          <?php if (isset($category)) : ?>
            <?= 'Data Barang Kategori ' . $category; ?>
          <?php elseif (isset($rack)) : ?>
            <?= 'Data Barang Rak ' . $rack; ?>
          <?php else : ?>
            Data Barang
          <?php endif; ?>
        </h5>
      </div>
      <div class="col-12 col-lg-7">
        <div class="d-flex gap-2 justify-content-md-end">
          <div>
            <form action="" method="get">
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="search" value="<?= $search ?? ''; ?>" placeholder="Cari barang" aria-label="Cari barang" aria-describedby="searchButton">
                <button class="btn btn-outline-secondary" type="submit" id="searchButton">Cari</button>
              </div>
            </form>
          </div>
          <div>
            <a href="<?= base_url('admin/items/new'); ?>" class="btn btn-primary py-2">
              <i class="ti ti-plus"></i>
              Tambah Data Barang
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="overflow-x-scroll">
      <table class="table table-hover table-striped">
        <thead class="table-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Gambar barang</th>
            <th scope="col">Nama barang</th>
            <th scope="col">Kategori</th>
            <th scope="col">Rak</th>
            <th scope="col">Jumlah</th>
            <th scope="col" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-group-divider">
          <?php $i = 1 + ($itemPerPage * ($currentPage - 1)) ?>
          <?php if (empty($items)) : ?>
            <tr>
              <td class="text-center" colspan="7"><b>Tidak ada data</b></td>
            </tr>
          <?php endif; ?>
          <?php foreach ($items as $item) : ?>
            <tr>
              <th scope="row"><?= $i++; ?></th>
              <td>
                <a href="<?= base_url("admin/items/{$item['slug']}"); ?>">
                  <div class="d-flex justify-content-center" style="max-width: 150px; height: 120px;">
                    <?php
                    $coverImageFilePath = ITEM_COVER_URI . $item['item_cover'];
                    ?>
                    <img class="mx-auto mh-100" src="<?= base_url((!empty($item['item_cover']) && file_exists($coverImageFilePath)) ? $coverImageFilePath : ITEM_COVER_URI . DEFAULT_ITEM_COVER); ?>" alt="<?= $item['title']; ?>">
                  </div>
                </a>
              </td>
              <td>
                <a href="<?= base_url("admin/items/{$item['slug']}"); ?>">
                  <p class="text-primary-emphasis text-decoration-underline"><b><?= "{$item['title']} ({$item['year']})"; ?></b></p>
                  <p class="text-body"><?= "Author: {$item['author']}"; ?></p>
                </a>
              </td>
              <td><?= $item['category']; ?></td>
              <td><?= $item['rack']; ?></td>
              <td><?= $item['quantity']; ?></td>
              <td>
                <a href="<?= base_url("admin/items/{$item['slug']}/edit"); ?>" class="d-block btn btn-primary w-100 mb-2">
                  <i class="ti ti-edit"></i>
                  Edit
                </a>
                <form action="<?= base_url("admin/items/{$item['slug']}"); ?>" method="post">
                  <?= csrf_field(); ?>
                  <input type="hidden" name="_method" value="DELETE">
                  <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure?');">
                    <i class="ti ti-trash"></i>
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= $pager->links('items', 'my_pager'); ?>
  </div>
</div>
<?= $this->endSection() ?>