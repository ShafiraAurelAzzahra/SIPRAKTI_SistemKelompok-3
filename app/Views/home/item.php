<?= $this->extend('layouts/home_layout') ?>

<?= $this->section('head') ?>
<title>Barang</title>
<?= $this->endSection() ?>

<?= $this->section('back'); ?>
<a href="<?= base_url(); ?>" class="btn btn-outline-primary m-3 position-absolute">
  <i class="ti ti-home"></i>
  Home
</a>
<?= $this->endSection(); ?>

<?= $this->section('content') ?>
<div class="card">
  <div class="card-body">
    <div class="row mb-4">
      <div class="col-12 col-lg-5">
        <h5 class="card-title fw-semibold">Daftar Barang</h5>
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
        </div>
      </div>
    </div>
    <div class="row">
      <?php if (empty($items)) : ?>
        <h4 class="text-center">Barang tidak ditemukan</h4>
      <?php endif; ?>
      <?php foreach ($items as $item) : ?>
        <?php
        $coverImageFilePath = ITEM_COVER_URI . $item['item_cover'];
        ?>
        <style>
          #coverItem<?= $item['id']; ?> {
            background-image: url('<?= base_url((!empty($item['item_cover']) && file_exists($coverImageFilePath)) ? $coverImageFilePath : ITEM_COVER_URI . DEFAULT_ITEM_COVER); ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: top;
            height: 250px;
          }
        </style>
        <div class="col-sm-6 col-xl-3">
          <div class="card overflow-hidden rounded-2" style="height: 375px;">
            <div class="position-relative">
              <a href="<?= base_url("admin/items/{$item['slug']}"); ?>">
                <div class="card-img-top rounded-0" id="coverItem<?= $item['id']; ?>">
                </div>
              </a>
            </div>
            <div class="card-body pt-3 p-4">
              <h6 class="fw-semibold fs-4">
                <?= substr($item['title'], 0, 64) . ((strlen($item['title']) > 64) ? '...'  : '') . " ({$item['year']})"; ?>
              </h6>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?= $pager->links('items', 'my_pager'); ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>