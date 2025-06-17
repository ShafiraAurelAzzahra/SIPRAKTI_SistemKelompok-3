<?php

namespace App\Controllers\Items;

use App\Models\ItemModel;
use App\Models\ItemStockModel;
use App\Models\CategoryModel;
use App\Models\LoanModel;
use App\Models\RackModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\RESTful\ResourceController;

class ItemsController extends ResourceController
{
    protected ItemModel $itemModel;
    protected CategoryModel $categoryModel;
    protected RackModel $rackModel;
    protected ItemStockModel $itemStockModel;
    protected LoanModel $loanModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel;
        $this->categoryModel = new CategoryModel;
        $this->rackModel = new RackModel;
        $this->itemStockModel = new ItemStockModel;
        $this->loanModel = new LoanModel;

        helper('upload');
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $itemPerPage = 20;

        if ($this->request->getGet('search')) {
            $keyword = $this->request->getGet('search');
            $items = $this->itemModel
                ->select('items.*, item_stock.quantity, categories.name as category, racks.name as rack, racks.floor')
                ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
                ->join('categories', 'items.category_id = categories.id', 'LEFT')
                ->join('racks', 'items.rack_id = racks.id', 'LEFT')
                ->like('title', $keyword, insensitiveSearch: true)
                ->orLike('slug', $keyword, insensitiveSearch: true)
                ->orLike('author', $keyword, insensitiveSearch: true)
                ->orLike('publisher', $keyword, insensitiveSearch: true)
                ->paginate($itemPerPage, 'items');

            $items = array_filter($items, function ($item) {
                return $item['deleted_at'] == null;
            });
        } else {
            $items = $this->itemModel
                ->select('items.*, item_stock.quantity, categories.name as category, racks.name as rack, racks.floor')
                ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
                ->join('categories', 'items.category_id = categories.id', 'LEFT')
                ->join('racks', 'items.rack_id = racks.id', 'LEFT')
                ->paginate($itemPerPage, 'items');
        }

        $data = [
            'items'         => $items,
            'pager'         => $this->itemModel->pager,
            'currentPage'   => $this->request->getVar('page_items') ?? 1,
            'itemPerPage'   => $itemPerPage,
            'search'        => $this->request->getGet('search')
        ];

        return view('items/index', $data);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($slug = null)
    {
        $item = $this->itemModel
            ->select('items.*, item_stock.quantity, categories.name as category, racks.name as rack, racks.floor')
            ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
            ->join('categories', 'items.category_id = categories.id', 'LEFT')
            ->join('racks', 'items.rack_id = racks.id', 'LEFT')
            ->where('slug', $slug)->first();

        if (empty($item)) {
            throw new PageNotFoundException('Item with slug \'' . $slug . '\' not found');
        }

        $loans = $this->loanModel->where([
            'item_id' => $item['id'],
            'return_date' => null
        ])->findAll();

        $loanCount = array_reduce(
            array_map(function ($loan) {
                return $loan['quantity'];
            }, $loans),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );

        $itemStock = $item['quantity'] - $loanCount;

        $data = [
            'item'      => $item,
            'loanCount' => $loanCount ?? 0,
            'itemStock' => $itemStock
        ];

        return view('items/show', $data);
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        $categories = $this->categoryModel->findAll();
        $racks = $this->rackModel->findAll();

        $data = [
            'categories' => $categories,
            'racks'      => $racks,
            'validation' => \Config\Services::validation(),
        ];

        return view('items/create', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        if (!$this->validate([
            'cover'     => 'is_image[cover]|mime_in[cover,image/jpg,image/jpeg,image/gif,image/png,image/webp]|max_size[cover,5120]',
            'title'     => 'required|string|max_length[127]',
            'author'    => 'required|alpha_numeric_punct|max_length[64]',
            'publisher' => 'required|string|max_length[64]',
            'isbn'      => 'required|numeric|min_length[10]|max_length[13]',
            'year'      => 'required|numeric|min_length[4]|max_length[4]|less_than_equal_to[2100]',
            'rack'      => 'required|numeric',
            'category'  => 'required|numeric',
            'stock'     => 'required|numeric|greater_than_equal_to[1]',
        ])) {
            $categories = $this->categoryModel->findAll();
            $racks = $this->rackModel->findAll();

            $data = [
                'categories' => $categories,
                'racks'      => $racks,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            return view('items/create', $data);
        }

        $coverImage = $this->request->getFile('cover');

        if ($coverImage->getError() != 4) {
            $coverImageFileName = uploadItemCover($coverImage);
        }

        $slug = url_title($this->request->getVar('title') . ' ' . rand(0, 1000), '-', true);

        if (!$this->itemModel->save([
            'slug' => $slug,
            'title' => $this->request->getVar('title'),
            'author' => $this->request->getVar('author'),
            'publisher' => $this->request->getVar('publisher'),
            'isbn' => $this->request->getVar('isbn'),
            'year' => $this->request->getVar('year'),
            'rack_id' => $this->request->getVar('rack'),
            'category_id' => $this->request->getVar('category'),
            'item_cover' => $coverImageFileName ?? null,
        ]) || !$this->itemStockModel->save([
            'item_id' => $this->itemModel->getInsertID(),
            'quantity' => $this->request->getVar('stock')
        ])) {
            $categories = $this->categoryModel->findAll();
            $racks = $this->rackModel->findAll();

            $data = [
                'categories' => $categories,
                'racks'      => $racks,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            session()->setFlashdata(['msg' => 'Insert failed']);
            return view('items/create', $data);
        }

        session()->setFlashdata(['msg' => 'Insert new item successful']);
        return redirect()->to('admin/items');
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($slug = null)
    {
        $item = $this->itemModel
            ->select('items.*, item_stock.quantity')
            ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
            ->where('slug', $slug)->first();

        if (empty($item)) {
            throw new PageNotFoundException('Item with slug \'' . $slug . '\' not found');
        }

        $categories = $this->categoryModel->findAll();
        $racks = $this->rackModel->findAll();

        $data = [
            'item'       => $item,
            'categories' => $categories,
            'racks'      => $racks,
            'validation' => \Config\Services::validation(),
        ];

        return view('items/edit', $data);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($slug = null)
    {
        $item = $this->itemModel->where('slug', $slug)->first();

        if (empty($item)) {
            throw new PageNotFoundException('Item with slug \'' . $slug . '\' not found');
        }

        if (!$this->validate([
            'cover'     => 'is_image[cover]|mime_in[cover,image/jpg,image/jpeg,image/gif,image/png,image/webp]|max_size[cover,5120]',
            'title'     => 'required|string|max_length[127]',
            'author'    => 'required|alpha_numeric_punct|max_length[64]',
            'publisher' => 'required|string|max_length[64]',
            'isbn'      => 'required|numeric|min_length[10]|max_length[13]',
            'year'      => 'required|numeric|min_length[4]|max_length[4]|less_than_equal_to[2100]',
            'rack'      => 'required|numeric',
            'category'  => 'required|numeric',
            'stock'     => 'required|numeric|greater_than_equal_to[1]',
        ])) {
            $categories = $this->categoryModel->findAll();
            $racks = $this->rackModel->findAll();

            $data = [
                'item'       => $item,
                'categories' => $categories,
                'racks'      => $racks,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            return view('items/edit', $data);
        }

        $itemStock = $this->itemStockModel->where('item_id', $item['id'])->first();

        $coverImage = $this->request->getFile('cover');

        if ($coverImage->getError() == 4) {
            $coverImageFileName = $item['item_cover'];
        } else {
            $coverImageFileName = updateItemCover(
                newCoverImage: $coverImage,
                formerCoverImageFileName: $item['item_cover']
            );
        }

        $slug = $this->request->getVar('title') != $item['title']
            ? url_title($this->request->getVar('title') . ' ' . rand(0, 1000), '-', true)
            : $item['slug'];

        if (!$this->itemModel->save([
            'id'  => $item['id'],
            'slug' => $slug,
            'title' => $this->request->getVar('title'),
            'author' => $this->request->getVar('author'),
            'publisher' => $this->request->getVar('publisher'),
            'isbn' => $this->request->getVar('isbn'),
            'year' => $this->request->getVar('year'),
            'rack_id' => $this->request->getVar('rack'),
            'category_id' => $this->request->getVar('category'),
            'item_cover' => $coverImageFileName ?? null,
        ]) || !$this->itemStockModel->save([
            'id' => $itemStock['id'],
            'item_id' => $item['id'],
            'quantity' => $this->request->getVar('stock')
        ])) {
            $categories = $this->categoryModel->findAll();
            $racks = $this->rackModel->findAll();

            $data = [
                'item'       => $item,
                'categories' => $categories,
                'racks'      => $racks,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            session()->setFlashdata(['msg' => 'Update failed']);
            return view('items/edit', $data);
        }

        session()->setFlashdata(['msg' => 'Update item successful']);
        return redirect()->to('admin/items');
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($slug = null)
    {
        $item = $this->itemModel->where('slug', $slug)->first();

        if (empty($item)) {
            throw new PageNotFoundException('Item with slug \'' . $slug . '\' not found');
        }

        $itemStock = $this->itemStockModel->where('item_id', $item['id'])->first();

        if (!$this->itemModel->delete($item['id']) || !$this->itemStockModel->delete($itemStock['id'])) {
            session()->setFlashdata(['msg' => 'Failed to delete item', 'error' => true]);
            return redirect()->back();
        }

        // delete former image file
        deleteItemCover($item['item_cover']);

        session()->setFlashdata(['msg' => 'Item deleted successfully']);
        return redirect()->to('admin/items');
    }
}
