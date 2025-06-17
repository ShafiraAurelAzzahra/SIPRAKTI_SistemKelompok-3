<?php

namespace App\Controllers;

use App\Models\ItemModel;

class Home extends BaseController
{
    protected ItemModel $itemModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel;
    }

    public function index(): string
    {
        return view('home/home');
    }

    public function item(): string
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

        return view('home/item', $data);
    }
}
