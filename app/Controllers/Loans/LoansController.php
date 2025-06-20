<?php

namespace App\Controllers\Loans;

use App\Libraries\QRGenerator;
use App\Models\ItemModel;
use App\Models\LoanModel;
use App\Models\MemberModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;

class LoansController extends ResourceController
{
    protected LoanModel $loanModel;
    protected MemberModel $memberModel;
    protected ItemModel $itemModel;

    public function __construct()
    {
        $this->loanModel = new LoanModel;
        $this->memberModel = new MemberModel;
        $this->itemModel = new ItemModel;

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
            $loans = $this->loanModel
                ->select('members.*, members.uid as member_uid, items.*, loans.*')
                ->join('members', 'loans.member_id = members.id', 'LEFT')
                ->join('items', 'loans.item_id = items.id', 'LEFT')
                ->like('first_name', $keyword, insensitiveSearch: true)
                ->orLike('last_name', $keyword, insensitiveSearch: true)
                ->orLike('email', $keyword, insensitiveSearch: true)
                ->orLike('title', $keyword, insensitiveSearch: true)
                ->orLike('slug', $keyword, insensitiveSearch: true)
                ->paginate($itemPerPage, 'loans');
        } else {
            $loans = $this->loanModel
                ->select('members.*, members.uid as member_uid, items.*, loans.*')
                ->join('members', 'loans.member_id = members.id', 'LEFT')
                ->join('items', 'loans.item_id = items.id', 'LEFT')
                ->paginate($itemPerPage, 'loans');
        }

        $loans = array_filter($loans, function ($loan) {
            return $loan['deleted_at'] == null && $loan['return_date'] == null;
        });

        $data = [
            'loans'         => $loans,
            'pager'         => $this->loanModel->pager,
            'currentPage'   => $this->request->getVar('page_loans') ?? 1,
            'itemPerPage'   => $itemPerPage,
            'search'        => $this->request->getGet('search')
        ];

        return view('loans/index', $data);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($uid = null)
    {
        $loan = $this->loanModel
            ->select('members.*, members.uid as member_uid, items.*, loans.*, loans.qr_code as loan_qr_code, item_stock.quantity as item_stock, racks.name as rack, categories.name as category')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('items', 'loans.item_id = items.id', 'LEFT')
            ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
            ->join('racks', 'items.rack_id = racks.id', 'LEFT')
            ->join('categories', 'items.category_id = categories.id', 'LEFT')
            ->where('loans.uid', $uid)
            ->first();

        if (empty($loan)) {
            throw new PageNotFoundException('Loan not found');
        }

        if ($this->request->getGet('update-qr-code') && $loan['return_date'] == null) {
            $qrGenerator = new QRGenerator();
            $qrCodeLabel = substr($loan['first_name'] . ($loan['last_name'] ? " {$loan['last_name']}" : ''), 0, 12) . '_' . substr($loan['title'], 0, 12);
            $qrCode = $qrGenerator->generateQRCode(
                $loan['uid'],
                labelText: $qrCodeLabel,
                dir: LOANS_QR_CODE_PATH,
                filename: $qrCodeLabel
            );

            // delete former qr code
            deleteLoansQRCode($loan['qr_code']);

            $this->loanModel->update($loan['id'], ['qr_code' => $qrCode]);

            $loan = $this->loanModel
                ->select('members.*, members.uid as member_uid, items.*, loans.*, loans.qr_code as loan_qr_code, item_stock.quantity as item_stock, racks.name as rack, categories.name as category')
                ->join('members', 'loans.member_id = members.id', 'LEFT')
                ->join('items', 'loans.item_id = items.id', 'LEFT')
                ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
                ->join('racks', 'items.rack_id = racks.id', 'LEFT')
                ->join('categories', 'items.category_id = categories.id', 'LEFT')
                ->where('loans.uid', $uid)
                ->first();

            return redirect()->to("admin/loans/{$loan['uid']}");
        }

        $data = [
            'loan'         => $loan,
        ];

        return view('loans/show', $data);
    }

    public function searchMember()
    {
        if ($this->request->isAJAX()) {
            $param = $this->request->getVar('param');

            if (empty($param)) return;

            $members = $this->memberModel
                ->like('first_name', $param, insensitiveSearch: true)
                ->orLike('last_name', $param, insensitiveSearch: true)
                ->orLike('email', $param, insensitiveSearch: true)
                ->orWhere('uid', $param)
                ->findAll();

            $members = array_filter($members, function ($member) {
                return $member['deleted_at'] == null;
            });

            if (empty($members)) {
                return view('loans/member', ['msg' => 'Member not found']);
            }

            return view('loans/member', ['members' => $members]);
        }

        return view('loans/search_member');
    }

    public function searchItem()
    {
        if ($this->request->isAJAX()) {
            $param = $this->request->getVar('param');
            $memberUid = $this->request->getVar('memberUid');

            if (empty($param)) return;

            if (empty($memberUid)) {
                return view('loans/item', ['msg' => 'Member UID is empty']);
            }

            $items = $this->itemModel
                ->select('items.*, item_stock.quantity, categories.name as category, racks.name as rack, racks.floor')
                ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
                ->join('categories', 'items.category_id = categories.id', 'LEFT')
                ->join('racks', 'items.rack_id = racks.id', 'LEFT')
                ->like('title', $param, insensitiveSearch: true)
                ->orLike('slug', $param, insensitiveSearch: true)
                ->orLike('author', $param, insensitiveSearch: true)
                ->orLike('publisher', $param, insensitiveSearch: true)
                ->orWhere('isbn', $param)
                ->findAll();

            $items = array_filter($items, function ($item) {
                return $item['deleted_at'] == null;
            });

            if (empty($items)) {
                return view('loans/item', ['msg' => 'Item not found']);
            }

            $items = array_map(function ($item) {
                $newItem = $item;
                $newItem['stock'] = $this->getRemainingItemStocks($item);
                return $newItem;
            }, $items);

            return view('loans/item', ['items' => $items, 'memberUid' => $memberUid]);
        }

        $memberUid = $this->request->getVar('member-uid');

        if (empty($memberUid)) {
            session()->setFlashdata(['msg' => 'Select member first', 'error' => true]);
            return redirect()->to('admin/loans/new/members/search');
        }

        $member = $this->memberModel->where('uid', $memberUid)->first();

        if (empty($member)) {
            session()->setFlashdata(['msg' => 'Member not found', 'error' => true]);
            return redirect()->to('admin/loans/new/members/search');
        }

        return view('loans/search_item', ['member' => $member]);
    }

    protected function getRemainingItemStocks($item)
    {
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

        return $item['quantity'] - $loanCount;
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new($validation = null, $oldInput = null)
    {
        if ($this->request->getMethod() !== Method::POST) {
            return redirect()->to('admin/loans/new/members/search');
        }

        $member = $this->memberModel
            ->where('uid', $this->request->getVar('member_uid'))
            ->first();

        $items = [];

        $itemSlugs = $this->request->getVar('slugs');

        if (empty($itemSlugs)) {
            return redirect()->back();
        }

        foreach ($itemSlugs as $slug) {
            $item = $this->itemModel
                ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
                ->where('items.slug', $slug)->first();

            if (!empty($item)) {
                $item['stock'] = $this->getRemainingItemStocks($item);
                array_push($items, $item);
            }
        }

        $data = [
            'items'      => $items,
            'member'     => $member,
            'validation' => $validation ?? \Config\Services::validation(),
            'oldInput'   => $oldInput,
        ];

        return view('loans/create', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $validation = [
            'member_uid' => 'required|string|max_length[64]',
        ];

        $itemSlugs = $this->request->getVar('slugs') or die();

        foreach ($itemSlugs as $slug) {
            $validation['quantity-' . $slug] = 'required|numeric|integer|greater_than[0]|less_than_equal_to[10]';
            $validation['duration-' . $slug] = 'required|numeric|integer|greater_than[0]|less_than_equal_to[30]';
        }

        if (!$this->validate($validation)) {
            return $this->new(\Config\Services::validation(), $this->request->getVar());
        }

        $memberUid = $this->request->getVar('member_uid') or die();

        $member = $this->memberModel->where('uid', $memberUid)->first();

        if (empty($member)) {
            session()->setFlashdata(['msg' => 'Member not found']);
            return redirect()->to('admin/loans/new/members/search');
        }

        $newLoanIds = [];

        foreach ($itemSlugs as $slug) {
            $duration = $this->request->getVar('duration-' . $slug);
            $quantity = $this->request->getVar('quantity-' . $slug);

            $item = $this->itemModel->where('slug', $slug)->first();

            if (empty($duration) || empty($quantity) || empty($item)) {
                continue;
            }

            $loanUid = sha1($item['slug'] . $member['uid'] . time());

            $qrGenerator = new QRGenerator();

            $qrCodeLabel = substr($member['first_name'] . ($member['last_name'] ? " {$member['last_name']}" : ''), 0, 12) . '_' . substr($item['title'], 0, 12);

            $qrCode = $qrGenerator->generateQRCode(
                data: $loanUid,
                labelText: $qrCodeLabel,
                dir: LOANS_QR_CODE_PATH,
                filename: $qrCodeLabel
            );

            $newLoan = [
                'item_id' => $item['id'],
                'quantity' => $quantity,
                'member_id' => $member['id'],
                'uid' => $loanUid,
                'loan_date' => Time::now()->toDateTimeString(),
                'due_date' => Time::now()->addDays(intval($duration))->toDateTimeString(),
                'qr_code' => $qrCode,
            ];

            $this->loanModel->insert($newLoan);

            array_push($newLoanIds, $this->loanModel->getInsertID());
        }

        $newLoans = array_map(function ($id) {
            return $this->loanModel->select('members.*, members.uid as member_uid, items.*, loans.*')
                ->join('members', 'loans.member_id = members.id', 'LEFT')
                ->join('items', 'loans.item_id = items.id', 'LEFT')
                ->where('loans.id', $id)->first();
        }, $newLoanIds);

        return view('loans/result', [
            'newLoans'  => $newLoans
        ]);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    // public function edit($uid = null)
    // {
    //! Not implemented
    // }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    // public function update($uid = null)
    // {
    //! Not implemented
    // }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($uid = null)
    {
        $loan = $this->loanModel->where('uid', $uid)->first();

        if (empty($loan)) {
            throw new PageNotFoundException('Loan not found');
        };

        if (!$this->loanModel->delete($loan['id'])) {
            session()->setFlashdata(['msg' => 'Failed to delete loan', 'error' => true]);
            return redirect()->back();
        }

        deleteLoansQRCode($loan['qr_code']);

        session()->setFlashdata(['msg' => 'Loan deleted successfully']);
        return redirect()->to('admin/loans');
    }
}
