<?php if (empty($items)) : ?>
  <h5 class="card-title fw-semibold my-4 text-danger">Barang tidak ditemukan</h5>
  <p class="text-danger"><?= $msg ?? ''; ?></p>
<?php else : ?>
  <h5 class="card-title fw-semibold my-4">Hasil pencarian barang</h5>
  <div class="overflow-x-scroll">
    <table class="table table-hover table-striped">
      <thead class="table-light">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Gambar barang</th>
          <th scope="col">Nama barang</th>
          <th scope="col">Produsen</th>
          <th scope="col">Kategori</th>
          <th scope="col">Rak</th>
          <th scope="col">Stok tersisa</th>
          <th scope="col" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="table-group-divider">
        <?php $i = 1; ?>
        <?php foreach ($items as $item) : ?>
          <?php if (!$item['deleted_at']) : ?>
            <tr>
              <th scope="row"><?= $i++; ?></th>
              <td>
                <div class="d-flex justify-content-center" style="max-width: 150px; height: 120px;">
                  <?php
                  $coverImageFilePath = ITEM_COVER_URI . $item['item_cover'];

                  $coverImageUrl = base_url((!empty($item['item_cover']) && file_exists($coverImageFilePath))
                    ? $coverImageFilePath
                    : ITEM_COVER_URI . DEFAULT_ITEM_COVER);
                  ?>
                  <img class="mx-auto mh-100" src="<?= $coverImageUrl; ?>" alt="<?= $item['title']; ?>">
                </div>
              </td>
              <td>
                <p><b><?= "{$item['title']} ({$item['year']})"; ?></b></p>
                <p class="text-body"><?= "Author: {$item['author']}"; ?></p>
              </td>
              <td><?= $item['publisher']; ?></td>
              <td><?= $item['category']; ?></td>
              <td><?= $item['rack']; ?></td>
              <td><?= $item['stock']; ?></td>
              <td style="width: 120px;" class="text-center">
                <?php if (intval($item['stock'] ?? 0) > 0) :
                  $rndm = md5(rand(0, 10000));
                ?>
                  <script>
                    let item<?= $item['id'] . $rndm; ?>Element = document.getElementById('item<?= $item['slug']; ?>');

                    const item<?= $item['id'] . $rndm; ?> = {
                      slug: "<?= $item['slug']; ?>",
                      title: "<?= "{$item['title']} ({$item['year']})"; ?>",
                      cover: "<?= $item['item_cover']; ?>",
                      stock: "<?= $item['stock']; ?>"
                    };

                    function onChange<?= $item['id'] . $rndm; ?>() {
                      check(item<?= $item['id'] . $rndm; ?>Element);
                      select(item<?= $item['id'] . $rndm; ?>Element.checked, item<?= $item['id'] . $rndm; ?>);
                    }

                    item<?= $item['id'] . $rndm; ?>Element.checked = itemSelection.has('<?= $item['slug']; ?>');
                  </script>
                  <button type="button" class="btn btn-secondary" onclick="onChange<?= $item['id'] . $rndm; ?>()">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="item<?= $item['slug']; ?>" onchange="onChange<?= $item['id'] . $rndm; ?>()">
                      <label class="form-check-label">
                        Pilih
                      </label>
                    </div>
                  </button>
                <?php else : ?>
                  <button class="d-block btn btn-dark w-100 mb-2" disabled>
                    Stok habis
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <script>
    function check(element) {
      element.checked = !element.checked;
    }

    function select(checked, item) {
      if (checked) {
        selectItem(item);
      } else {
        unselectItem(item.slug);
      }
    }
  </script>
<?php endif; ?>