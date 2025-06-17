<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Peminjaman Baru</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<a href="<?= base_url('admin/loans/new/members/search'); ?>" class="btn btn-outline-primary mb-3">
  <i class="ti ti-arrow-left"></i>
  Kembali
</a>
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-lg-6">
        <h5 class="card-title fw-semibold mb-4">Pilih Barang</h5>
        <div class="mb-4">
          <label for="search" class="form-label">Cari Nama barang, merk atau produsen barang</label>
          <input type="text" class="form-control" id="search" name="search" placeholder="Cari barang">
          <div class="invalid-feedback">
          </div>
        </div>
        <button class="btn btn-primary" onclick="getItemData(document.querySelector('#search').value)">Cari</button>
      </div>
      <div class="col-1 mb-3"></div>
      <div class="col-12 col-lg-5 d-flex flex-wrap">
        <h5 class="card-title fw-semibold mb-4">Data Anggota</h5>
        <div class="w-100 mb-4">
          <?php

          use CodeIgniter\I18n\Time;

          $tableData = [
            'Nama Lengkap'  => [$member['first_name'] . ' ' . $member['last_name']],
            'Email'         => $member['email'],
            'Nomor telepon' => $member['phone'],
            'Alamat'        => $member['address'],
            'Tanggal lahir' => Time::parse($member['date_of_birth'], locale: 'id')->toLocalizedString('d MMMM Y'),
            'Jenis kelamin' => $member['gender'] == 'Male' ? 'Laki-laki' : 'Perempuan',
          ];
          ?>
          <table>
            <?php foreach ($tableData as $key => $value) : ?>
              <?php if (is_array($value)) : ?>
                <tr>
                  <td>
                    <h6 class="text-black-50"><b><?= $key; ?></b></h6>
                  </td>
                  <td style="width:15px" class="text-center">
                    <h6 class="text-black-50"><b>:</b></h6>
                  </td>
                  <td>
                    <h6 class="text-black-50"><b><?= $value[0]; ?></b></h6>
                  </td>
                </tr>
              <?php else : ?>
                <tr>
                  <td>
                    <h6 class="text-black-50"><?= $key; ?></h6>
                  </td>
                  <td class="text-center">
                    <h6 class="text-black-50">:</h6>
                  </td>
                  <td>
                    <h6 class="text-black-50"><?= $value; ?></h6>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
    <div class="my-4">
      <h5 class="card-title fw-semibold mb-4">Barang dipilih</h5>
      <ul id="itemList" class="d-flex d-flex flex-wrap gap-2">
        <li id="none">--Silahkan cari dan pilih barang terlebih dahulu--</li>
      </ul>
      <form id="itemForm" action="<?= base_url('admin/loans/new'); ?>" method="post">
        <?= csrf_field(); ?>
        <input type="hidden" name="member_uid" value="<?= $member['uid']; ?>">
      </form>
    </div>
    <div class="row">
      <div class="col-12">
        <div id="itemResult">
          <p class="text-center mt-4">Data barang muncul disini</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function getItemData(param) {
    jQuery.ajax({
      url: "<?= base_url('admin/loans/new/items/search'); ?>",
      type: 'get',
      data: {
        'param': param,
        'memberUid': '<?= $member['uid']; ?>'
      },
      success: function(response, status, xhr) {
        $('#itemResult').html(response);

        $('html, body').animate({
          scrollTop: $("#itemResult").offset().top
        }, 500);
      },
      error: function(xhr, status, thrown) {
        console.log(thrown);
        $('#itemResult').html(thrown);
      }
    });
  }

  let itemSelection = new Map();

  const itemListElement = document.getElementById('itemList');
  const itemFormElement = document.getElementById('itemForm');

  function selectItem({
    slug,
    title,
    cover,
    stock
  }) {
    if (!itemSelection.has(slug) && itemListElement.querySelector(`#${slug}`) === null) {
      const item = {
        slug,
        title,
        cover,
        stock
      };

      itemSelection.set(slug, item);
      addItem(item);
    }
  }

  function unselectItem(slug) {
    itemSelection.delete(slug);
    removeItem(slug);
    document.getElementById(`item${slug}`).checked = false;
  }

  function addItem(item) {
    const itemCard = `<li id="${item.slug}">
          <div class="card border border-2 border-primary overflow-hidden position-relative" style="height: 160px; max-width: 355px;">
            <div class="card-body">
              <div class="position-absolute top-50 start-0 translate-middle-y border border-black me-4"  style="background-image: url(<?= base_url(ITEM_COVER_URI); ?>${item.cover}); height: 160px; width: 120px; background-position: center; background-size: cover;">
              </div>
              <div class="d-flex align-items-start" style="margin-left: 100px;">
                <div>
                  <p style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; width: 150px;"><b>${item.title}</b></p>
                  <b>Sisa stock: ${item.stock}</b>
                </div>
                <div class="ps-2">
                  <button type="button" onclick="unselectItem('${item.slug}')" class="btn">
                    <i class="ti ti-x fs-8"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </li>`;

    if (itemSelection.size === 1) {
      itemListElement.querySelector('#none').remove();
      itemFormElement.innerHTML += `<button id="confirmItem" class="btn btn-primary" type="submit">Konfirmasi</button>`;
    }

    itemListElement.innerHTML += itemCard;
    itemFormElement.innerHTML += `<input type="hidden" name="slugs[]" value="${item.slug}" id="input-${item.slug}">`;
  }

  function removeItem(slug) {
    const itemElement = itemListElement.querySelector(`#${slug}`);
    const itemInputElement = itemFormElement.querySelector(`#input-${slug}`);

    if (itemElement !== null && itemInputElement !== null) {
      itemElement.parentNode.removeChild(itemElement);
      itemInputElement.parentNode.removeChild(itemInputElement);

      if (itemSelection.size <= 0) {
        itemListElement.innerHTML += `<li id="none">--Silahkan cari dan pilih barang terlebih dahulu--</li>`;
        itemFormElement.querySelector('#confirmItem').remove();
      }
    }
  }
</script>
<?= $this->endSection() ?>