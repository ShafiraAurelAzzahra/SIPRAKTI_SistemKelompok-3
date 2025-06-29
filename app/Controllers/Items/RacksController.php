<?php

namespace App\Controllers\Items;

use App\Models\ItemModel;
use App\Models\RackModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\RESTful\ResourceController;

class RacksController extends ResourceController
{
    protected RackModel $rackModel;
    protected ItemModel $itemModel;

    public function __construct()
    {
        $this->rackModel = new RackModel;
        $this->itemModel = new ItemModel;
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $itemPerPage = 20;

        $racks = $this->rackModel->paginate($itemPerPage, 'racks');

        $itemCountInRacks = [];

        foreach ($racks as $rack) {
            array_push($itemCountInRacks, $this->itemModel
                ->where('rack_id', $rack['id'])
                ->countAllResults());
        }

        $data = [
            'racks'             => $racks,
            'itemCountInRacks'  => $itemCountInRacks,
            'pager'             => $this->rackModel->pager,
            'currentPage'       => $this->request->getVar('page_racks') ?? 1,
            'itemPerPage'       => $itemPerPage,
        ];

        return view('racks/index', $data);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $rack = $this->rackModel->where('id', $id)->first();

        if (empty($rack)) {
            throw new PageNotFoundException('Rack not found');
        }

        $itemPerPage = 20;

        $items = $this->itemModel
            ->select('items.*, item_stock.quantity, categories.name as category, racks.name as rack, racks.floor')
            ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
            ->join('categories', 'items.category_id = categories.id', 'LEFT')
            ->join('racks', 'items.rack_id = racks.id', 'LEFT')
            ->where('rack_id', $id)
            ->paginate($itemPerPage, 'items');

        $data = [
            'items'         => $items,
            'pager'         => $this->itemModel->pager,
            'currentPage'   => $this->request->getVar('page_items') ?? 1,
            'itemPerPage'   => $itemPerPage,
            'rack'          => $this->rackModel
                ->select('racks.name')
                ->where('id', $id)->first()['name']
        ];

        return view('items/index', $data);
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        return view('racks/create', [
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        if (!$this->validate([
            'rack'  => 'required|alpha_numeric_punct|max_length[8]',
            'floor' => 'permit_empty|if_exist|alpha_numeric_punct|max_length[16]',
        ])) {
            $data = [
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            return view('racks/create', $data);
        }

        if (!$this->rackModel->save([
            'name' => $this->request->getVar('rack'),
            'floor' => !empty($this->request->getVar('floor')) ? $this->request->getVar('floor') : 1,
        ])) {
            $data = [
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            session()->setFlashdata(['msg' => 'Insert failed']);
            return view('racks/create', $data);
        }

        session()->setFlashdata(['msg' => 'Insert new rack successful']);
        return redirect()->to('admin/racks');
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        $rack = $this->rackModel->where('id', $id)->first();

        if (empty($rack)) {
            throw new PageNotFoundException('Rack not found');
        }

        $data = [
            'rack'          => $rack,
            'validation'    => \Config\Services::validation(),
        ];

        return view('racks/edit', $data);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $rack = $this->rackModel->where('id', $id)->first();

        if (empty($rack)) {
            throw new PageNotFoundException('Category not found');
        }

        if (!$this->validate([
            'rack'  => 'required|alpha_numeric_punct|max_length[8]',
            'floor' => 'permit_empty|if_exist|alpha_numeric_punct|max_length[16]',
        ])) {
            $data = [
                'rack'       => $rack,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            return view('racks/edit', $data);
        }

        if (!$this->rackModel->save([
            'id'   => $id,
            'name' => $this->request->getVar('rack'),
            'floor' => !empty($this->request->getVar('floor')) ? $this->request->getVar('floor') : 1,
        ])) {
            $data = [
                'rack'   => $rack,
                'validation' => \Config\Services::validation(),
                'oldInput'   => $this->request->getVar(),
            ];

            session()->setFlashdata(['msg' => 'Insert failed']);
            return view('racks/create', $data);
        }

        session()->setFlashdata(['msg' => 'Update rack successful']);
        return redirect()->to('admin/racks');
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $rack = $this->rackModel->where('id', $id)->first();

        if (empty($rack)) {
            throw new PageNotFoundException('Rack not found');
        }

        if (!$this->rackModel->delete($id)) {
            session()->setFlashdata(['msg' => 'Failed to delete rack', 'error' => true]);
            return redirect()->back();
        }

        session()->setFlashdata(['msg' => 'Rack deleted successfully']);
        return redirect()->to('admin/racks');
    }
}
