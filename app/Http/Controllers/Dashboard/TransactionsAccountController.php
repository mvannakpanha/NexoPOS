<?php

/**
 * NexoPOS Controller
 *
 * @since  1.0
 **/

namespace App\Http\Controllers\Dashboard;

use App\Crud\ExpenseCategoryCrud;
use App\Crud\TransactionAccountCrud;
use App\Http\Controllers\DashboardController;
use App\Models\ExpenseCategory;
use App\Models\TransactionAccount;
use App\Services\Options;
use Illuminate\Support\Facades\View;

class TransactionsAccountController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index Controller Page
     *
     * @return  view
     *
     * @since  1.0
     **/
    public function index()
    {
        return View::make( 'NexoPOS::index' );
    }

    /**
     * Show expenses
     * categories
     *
     * @return view
     */
    public function listExpensesCategories()
    {
        return TransactionAccountCrud::table();
    }

    /**
     * Show expenses
     * categories
     *
     * @return view
     */
    public function createExpenseCategory()
    {
        return TransactionAccountCrud::form();
    }

    public function editExpenseCategory( TransactionAccount $account )
    {
        return TransactionAccountCrud::form( $account );
    }
}