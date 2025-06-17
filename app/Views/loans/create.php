<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Peminjaman Baru</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= base_url('admin/loans/new/items/search?member-uid=' . $member['uid']); ?>" class="btn btn-outline-primary mb-3">
  <i class="ti ti-arrow-left"></i>
  Kembali
</a>
<form action="<?= base_url('admin/loans'); ?>" method="post">
  <?= csrf_field(); ?>
  <input type="hidden" name="member_uid" value="<?= $member['uid']; ?>">
  <!-- Member -->
  <div class="card">
    <div class="card-body">
      <h5 class="card-title fw-semibold mb-3">Data Anggota</h5>
      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <label for="member_name" class="form-label">Nama anggota</label>
          <input type="text" class="form-control" id="member_name" name="member_name" value="<?= "{$member['first_name']} {$member['last_name']}"; ?>" disabled>
        </div>
        <div class="col-12 col-md-6 mb-3">
          <label for="member_email" class="form-label">Email</label>
          <input type="text" class="form-control" id="member_email" name="member_email" value="<?= $member['email']; ?>" disabled>
        </div>
        <div class="col-12 col-md-6 mb-3">
          <label for="member_phone" class="form-label">Nomor telepon</label>
          <input type="text" class="form-control" id="member_phone" name="member_phone" value="<?= $member['phone']; ?>" disabled>
        </div>
        <div class="col-12 col-md-6 mb-3">
          <label for="member_address" class="form-label">Alamat</label>
          <input type="text" class="form-control" id="member_address" name="member_address" value="<?= $member['address']; ?>" disabled>
        </div>
      </div>
    </div>
  </div>
  <!-- Loan -->
  <div class="card">
    <div class="card-body">
      <h5 class="card-title fw-semibold mb-4">Form Peminjaman Barang</h5>
      <div class="row">
        <?php foreach ($items as $item) : ?>
          <input type="hidden" name="slugs[]" value="<?= $item['slug']; ?>">
          <div class="col-12">
            <div class="card border border-2 border-primary overflow-hidden position-relative">
              <div class="card-body">
                <div class="position-absolute top-50 start-0 translate-middle-y border border-black me-4" style="background-image: url(<?= base_url(ITEM_COVER_URI) . $item['item_cover']; ?>); height: 160px; width: 120px; background-position: center; background-size: cover;">
                </div>
                <div class="row">
                  <div class="col-5">
                    <div class="d-flex align-items-start" style="margin-left: 100px;">
                      <div>
                        <p style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"><b><?= "{$item['title']} ({$item['year']})"; ?></b></p>
                        <p>Merk: <?= $item['author']; ?></p>
                        <p>Produsen: <?= $item['publisher']; ?></p>
                      </div>
                    </div>
                  </div>
                  <div class="col-2">
                    <label for="quantity-<?= $item['slug']; ?>" class="form-label">Jumlah</label>
                    <input type="number" class="form-control <?php if ($validation->hasError("quantity-{$item['slug']}")) : ?>is-invalid<?php endif ?>" id="quantity-<?= $item['slug']; ?>" name="quantity-<?= $item['slug']; ?>" value="1" placeholder="max=10" max="<?= $item['stock'] < 10 ? $item['stock'] : 10; ?>" min="1" aria-describedby="itemStock" required>
                    <div class="invalid-feedback">
                      <?= $validation->getError("quantity-{$item['slug']}"); ?>
                    </div>
                    <div id="itemStock" class="form-text">Stok: <?= $item['stock']; ?></div>
                  </div>
                  <div class="col-5">
                    <label class="form-label">Lama meminjam</label>
                    <div class="my-2 <?php if ($validation->hasError("duration-{$item['slug']}")) : ?>is-invalid<?php endif ?>">
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="7days-<?= $item['slug']; ?>" name="duration-<?= $item['slug']; ?>" value="7" <?= ($oldInput['duration-' . $item['slug']] ?? '') == '7' ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="7days-<?= $item['slug']; ?>">
                          7 hari
                        </label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="14days-<?= $item['slug']; ?>" name="duration-<?= $item['slug']; ?>" value="14" <?= ($oldInput['duration-' . $item['slug']] ?? '') == '14' ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="14days-<?= $item['slug']; ?>">
                          14 hari
                        </label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="30days-<?= $item['slug']; ?>" name="duration-<?= $item['slug']; ?>" value="30" <?= ($oldInput['duration-' . $item['slug']] ?? '') == '30' ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="30days-<?= $item['slug']; ?>">
                          30 hari
                        </label>
                      </div>
                    </div>
                    <div class="invalid-feedback">
                      <?= $validation->getError("duration-{$item['slug']}"); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>

</script>
<?= $this->endSection() ?>