<?php

namespace App\Controllers\Dashboard;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\FineModel;
use App\Models\LoanModel;
use App\Models\MemberModel;
use App\Models\RackModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;

class DashboardController extends ResourceController
{
    protected ItemModel $itemModel;
    protected RackModel $rackModel;
    protected CategoryModel $categoryModel;
    protected MemberModel $memberModel;
    protected LoanModel $loanModel;
    protected FineModel $fineModel;

    public function __construct()
    {
        $this->itemModel = new ItemModel;
        $this->rackModel = new RackModel;
        $this->categoryModel = new CategoryModel;
        $this->memberModel = new MemberModel;
        $this->loanModel = new LoanModel;
        $this->fineModel = new FineModel;
    }

    public function index()
    {
        return redirect('admin/dashboard');
    }

    public function dashboard()
    {
        $data = array_merge(
            $this->getDataSummaries(),
            $this->getReports(),
            $this->getWeeklyOverview(),
            $this->getMonthlyFines(),
            $this->getTotalArrears(),
        );

        return view('dashboard/index', $data);
    }

    protected function getDataSummaries(): array
    {
        $items = $this->itemModel
            ->join('item_stock', 'items.id = item_stock.item_id', 'LEFT')
            ->findAll();

        $totalItemStocks = array_reduce(
            array_map(function ($item) {
                return $item['quantity'];
            }, $items),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );

        return [
            'items'                 => $items,
            'totalItemStock'        => $totalItemStocks,
            'racks'                 => $this->rackModel->findAll(),
            'categories'            => $this->categoryModel->findAll(),
            'members'               => $this->memberModel->findAll(),
            'loans'                 => $this->loanModel->findAll(),
        ];
    }

    protected function getReports(): array
    {
        $now = Time::now(locale: 'id');

        $todayMidnight = $now->today()->toDateTimeString();
        $tomorrowMidnight = $now->tomorrow()->toDateTimeString();

        $newMembersToday = $this->memberModel
            ->where("created_at BETWEEN '{$todayMidnight}' AND '{$tomorrowMidnight}'")
            ->findAll();

        $newLoansToday = $this->loanModel
            ->where("created_at BETWEEN '{$todayMidnight}' AND '{$tomorrowMidnight}'")
            ->findAll();

        $newItemReturnsToday = $this->loanModel
            ->where("return_date BETWEEN '{$todayMidnight}' AND '{$tomorrowMidnight}'")
            ->findAll();

        $returnDueToday = $this->loanModel
            ->where("due_date BETWEEN '{$todayMidnight}' AND '{$tomorrowMidnight}'")
            ->findAll();

        return [
            'newMembersToday'       => $newMembersToday,
            'newLoansToday'         => $newLoansToday,
            'newItemReturnsToday'   => $newItemReturnsToday,
            'returnDueToday'        => $returnDueToday,
        ];
    }

    protected function getWeeklyOverview(): array
    {
        $now = Time::now(locale: 'id');
        $lastWeekDateStringRange = [];

        $newMembersOverview = [];
        $loansOverview = [];
        $returnsOverview = [];

        for ($i = 6; $i >= 0; $i--) {
            $t = $now->today()->subDays($i);

            $todayDateTimeString = $now->today()->subDays($i)->toDateTimeString();
            $tomorrowDateTimeString = $now->tomorrow()->subDays($i)->toDateTimeString();

            array_push($lastWeekDateStringRange, "{$t->getDay()}/" . ($t->getMonth() <= 9 ? '0' : '') . $t->getMonth());

            array_push(
                $newMembersOverview,
                count(
                    $this->memberModel
                        ->where("created_at BETWEEN '{$todayDateTimeString}' AND '{$tomorrowDateTimeString}'")
                        ->findAll()
                )
            );
            array_push(
                $loansOverview,
                count(
                    $this->loanModel
                        ->where("created_at BETWEEN '{$todayDateTimeString}' AND '{$tomorrowDateTimeString}'")
                        ->findAll()
                )
            );
            array_push(
                $returnsOverview,
                count(
                    $this->loanModel
                        ->where("return_date BETWEEN '{$todayDateTimeString}' AND '{$tomorrowDateTimeString}'")
                        ->findAll()
                )
            );
        }

        return [
            'dateNow'                   => $now,
            'lastWeekDateStringRange'   => $lastWeekDateStringRange,
            'newMembersOverview'        => $newMembersOverview,
            'loansOverview'             => $loansOverview,
            'returnsOverview'           => $returnsOverview,
        ];
    }

    protected function getMonthlyFines(): array
    {
        $now = Time::now(locale: 'id');

        $firstDayLastMonth = $now->today()->subMonths(1)->setDay(1)->toDateTimeString();
        $lastDayLastMonth = $now->today()->setDay(1)->subSeconds(1)->toDateTimeString();
        $firstDayThisMonth = $now->today()->setDay(1)->toDateTimeString();
        $now;

        $finesDataLastMonth = $this->fineModel
            ->where("created_at BETWEEN '{$firstDayLastMonth}' AND '{$lastDayLastMonth}'")
            ->findAll();
        $finesDataThisMonth = $this->fineModel
            ->where("created_at BETWEEN '{$firstDayThisMonth}' AND '{$now->toDateTimeString()}'")
            ->findAll();

        $fineIncomeLastMonth['value'] = array_reduce(
            array_map(function ($fine) {
                return $fine['amount_paid'] ?? 0;
            }, $finesDataLastMonth),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );
        $fineIncomeLastMonth['month'] = $now->subMonths(1)->toLocalizedString('MMMM Y');

        $fineIncomeThisMonth['value'] = array_reduce(
            array_map(function ($fine) {
                return $fine['amount_paid'] ?? 0;
            }, $finesDataThisMonth),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );
        $fineIncomeThisMonth['month'] = $now->toLocalizedString('MMMM Y');

        return [
            'fineIncomeLastMonth' => $fineIncomeLastMonth,
            'fineIncomeThisMonth' => $fineIncomeThisMonth
        ];
    }

    protected function getTotalArrears(): array
    {
        $fines = $this->fineModel->findAll();

        $totalFines = array_reduce(
            array_map(function ($fine) {
                return $fine['fine_amount'];
            }, $fines),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );

        $totalFinesPaid = array_reduce(
            array_map(function ($fine) {
                if (($fine['amount_paid'] ?? 0) > $fine['fine_amount']) {
                    return $fine['fine_amount'];
                }
                return $fine['amount_paid'];
            }, $fines),
            function ($carry, $item) {
                return ($carry + $item);
            }
        );

        $fines = $this->fineModel->limit(100)->orderBy('created_at')->findAll();

        $carry = 0;
        $arrears = [];

        foreach ($fines as $fine) {
            $arrear = $carry;

            if (($fine['amount_paid'] ?? 0) <= $fine['fine_amount']) {
                $arrear = $carry + ($fine['fine_amount'] - $fine['amount_paid']);
            }

            array_push($arrears, [
                'arrear' => $arrear,
                'date' => Time::parse($fine['created_at'], locale: 'id')->toLocalizedString('d MMMM Y')
            ]);
            $carry = $arrear;
        }

        $totalArrears = $totalFines - $totalFinesPaid;

        $oldestFineDate = Time::parse($this->fineModel->selectMin('created_at')->first()['created_at'] ?? 'now', locale: 'id');

        return [
            'arrears' => $arrears,
            'totalArrears' => $totalArrears,
            'oldestFineDate' => $oldestFineDate,
        ];
    }
}
