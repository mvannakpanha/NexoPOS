<?php

namespace App\Services;

use App\Classes\Hook;
use App\Events\TransactionAfterCreatedEvent;
use App\Events\TransactionAfterCreateEvent;
use App\Events\TransactionAfterUpdatedEvent;
use App\Events\TransactionAfterUpdateEvent;
use App\Exceptions\NotAllowedException;
use App\Exceptions\NotFoundException;
use App\Fields\DirectTransactionFields;
use App\Fields\EntityTransactionFields;
use App\Fields\ReccurringTransactionFields;
use App\Fields\RecurringTransactionFields;
use App\Fields\SalaryTransactionFields;
use App\Fields\ScheduledTransactionField;
use App\Fields\ScheduledTransactionFields;
use App\Models\TransactionAccount;
use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\CustomerAccountHistory;
use App\Models\TransactionCategory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductRefund;
use App\Models\Procurement;
use App\Models\RegisterHistory;
use App\Models\Role;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    /**
     * @var DateService
     */
    protected $dateService;

    protected $accountTypes = [
        CashFlow::ACCOUNT_SALES => [ 'operation' => CashFlow::OPERATION_CREDIT, 'option' => 'ns_sales_cashflow_account' ],
        CashFlow::ACCOUNT_REFUNDS => [ 'operation' => CashFlow::OPERATION_DEBIT, 'option' => 'ns_sales_refunds_account' ],
        CashFlow::ACCOUNT_SPOILED => [ 'operation' => CashFlow::OPERATION_DEBIT, 'option' => 'ns_stock_return_spoiled_account' ],
        CashFlow::ACCOUNT_PROCUREMENTS => [ 'operation' => CashFlow::OPERATION_DEBIT, 'option' => 'ns_procurement_cashflow_account' ],
        CashFlow::ACCOUNT_CUSTOMER_CREDIT => [ 'operation' => CashFlow::OPERATION_CREDIT, 'option' => 'ns_customer_crediting_cashflow_account' ],
        CashFlow::ACCOUNT_CUSTOMER_DEBIT => [ 'operation' => CashFlow::OPERATION_DEBIT, 'option' => 'ns_customer_debitting_cashflow_account' ],
    ];

    public function __construct( DateService $dateService )
    {
        $this->dateService = $dateService;
    }

    public function create( $fields )
    {
        $expense = new Transaction;

        foreach ( $fields as $field => $value ) {
            $expense->$field = $value;
        }

        $expense->author = Auth::id();
        $expense->save();

        event( new TransactionAfterCreatedEvent( $expense, request()->all() ) );

        return [
            'status' => 'success',
            'message' => __( 'The expense has been successfully saved.' ),
            'data' => compact( 'expense' ),
        ];
    }

    public function edit( $id, $fields )
    {
        $expense = $this->get( $id );

        if ( $expense instanceof Transaction ) {
            foreach ( $fields as $field => $value ) {
                $expense->$field = $value;
            }

            $expense->author = Auth::id();
            $expense->save();

            event( new TransactionAfterUpdatedEvent( $expense, request()->all() ) );

            return [
                'status' => 'success',
                'message' => __( 'The expense has been successfully updated.' ),
                'data' => compact( 'expense' ),
            ];
        }

        throw new NotFoundException( __( 'Unable to find the expense using the provided identifier.' ) );
    }

    /**
     * get a specific expense using
     * the provided id
     *
     * @param int expense id
     * @return Collection|Transaction|NotFoundException
     */
    public function get( $id = null )
    {
        if ( $id === null ) {
            return Transaction::get();
        }

        $expense = Transaction::find( $id );

        if ( ! $expense instanceof Transaction ) {
            throw new NotFoundException( __( 'Unable to find the requested expense using the provided id.' ) );
        }

        return $expense;
    }

    /**
     * Delete an expense using the
     * provided id
     *
     * @param int expense id
     * @return array
     */
    public function delete( $id )
    {
        $expense = $this->get( $id );
        $expense->delete();

        return [
            'status' => 'success',
            'message' => __( 'The expense has been correctly deleted.' ),
        ];
    }

    /**
     * Retreive a specific account type
     * or all accuont type
     */
    public function getTransactionAccountByID( $id )
    {
        if ( $id !== null ) {
            $category = TransactionAccount::find( $id );
            if ( ! $category instanceof TransactionAccount ) {
                throw new NotFoundException( __( 'Unable to find the requested account type using the provided id.' ) );
            }

            return $category;
        }

        return TransactionAccount::get();
    }

    /**
     * Delete specific account type
     *
     * @todo must be implemented
     */
    public function deleteTransactionAccount( $id, $force = true )
    {
        $accountType = $this->getTransactionAccountByID( $id );

        if ( $accountType->expenses->count() > 0 && $force === false ) {
            throw new NotAllowedException( __( 'You cannot delete an account type that has transaction bound.' ) );
        }

        /**
         * if there is not expense, it
         * won't be looped
         */
        $accountType->expenses->map( function( $expense ) {
            $expense->delete();
        });

        $accountType->delete();

        return [
            'status' => 'success',
            'message' => __( 'The account type has been deleted.' ),
        ];
    }

    /**
     * Delete a specific category
     * using the provided id, along with the expenses
     *
     * @param int id
     * @param bool force deleting
     * @return array|NotAllowedException
     */
    public function deleteCategory( $id, $force = false )
    {
        $accountType = $this->getTransactionAccountByID( $id );

        if ( $accountType->expenses->count() > 0 && $force === false ) {
            throw new NotAllowedException( __( 'You cannot delete a category which has expenses bound.' ) );
        }

        /**
         * if there is not expense, it
         * won't be looped
         */
        $accountType->expenses->map( function( $expense ) {
            $expense->delete();
        });

        $accountType->delete();

        return [
            'status' => 'success',
            'message' => __( 'The expense category has been deleted.' ),
        ];
    }

    /**
     * Get a specific expense
     * category using the provided ID
     */
    public function getTransaction( int $id )
    {
        $accountType = TransactionAccount::with( 'expenses' )->find( $id );

        if ( ! $accountType instanceof TransactionAccount ) {
            throw new NotFoundException( __( 'Unable to find the expense category using the provided ID.' ) );
        }

        return $accountType;
    }

    /**
     * Create a category using
     * the provided details
     *
     * @param array category detail
     * @return array status of the operation
     *
     * @deprecated
     */
    public function createCategory( $fields )
    {
        $category = new TransactionAccount;

        foreach ( $fields as $field => $value ) {
            $category->$field = $value;
        }

        $category->author = Auth::id();
        $category->save();

        return [
            'status' => 'success',
            'message' => __( 'The expense category has been saved' ),
            'data' => compact( 'category' ),
        ];
    }

    /**
     * Creates an accounting account
     *
     * @param array $fields
     * @return array status
     */
    public function createAccount( $fields )
    {
        $category = new TransactionAccount;

        foreach ( $fields as $field => $value ) {
            $category->$field = $value;
        }

        $category->author = ns()->getValidAuthor();
        $category->save();

        return [
            'status' => 'success',
            'message' => __( 'The account has been created.' ),
            'data' => compact( 'category' ),
        ];
    }

    /**
     * Update specified expense
     * category using a provided ID
     */
    public function editTransactionAccount( int $id, array $fields ): array
    {
        $category = $this->getTransaction( $id );

        foreach ( $fields as $field => $value ) {
            $category->$field = $value;
        }

        $category->author = Auth::id();
        $category->save();

        return [
            'status' => 'success',
            'message' => __( 'The expense category has been updated.' ),
            'data' => compact( 'category' ),
        ];
    }

    /**
     * Will trigger for not recurring expense
     *
     * @param Transaction $expense
     * @return void
     */
    public function triggerTransaction( $expense )
    {
        $histories = $this->recordCashFlowHistory( $expense );

        /**
         * a non recurring expenses
         * once triggered should be disabled to
         * prevent futher execution on modification.
         */
        $expense->active = false;
        $expense->save();

        return compact( 'expense', 'histories' );
    }

    public function getCategoryTransaction( $id )
    {
        $accountType = $this->getTransaction( $id );

        return $accountType->expenses;
    }

    public function recordCashFlowHistory( $expense )
    {
        if ( ! empty( $expense->group_id  ) ) {
            return Role::find( $expense->group_id )->users()->get()->map( function( $user ) use ( $expense ) {
                if ( $expense->category instanceof TransactionAccount ) {
                    $history = new CashFlow;
                    $history->value = $expense->value;
                    $history->expense_id = $expense->id;
                    $history->operation = 'debit';
                    $history->author = $expense->author;
                    $history->name = str_replace( '{user}', ucwords( $user->username ), $expense->name );
                    $history->expense_category_id = $expense->category->id;
                    $history->save();

                    return $history;
                }

                return false;
            })->filter(); // only return valid history created
        } else {
            $history = new CashFlow;
            $history->value = $expense->value;
            $history->expense_id = $expense->id;
            $history->operation = $expense->operation ?? 'debit'; // if the operation is not defined, by default is a "debit"
            $history->author = $expense->author;
            $history->name = $expense->name;
            $history->procurement_id = $expense->procurement_id ?? 0; // if the cash flow is created from a procurement
            $history->order_id = $expense->order_id ?? 0; // if the cash flow is created from a refund
            $history->order_refund_id = $expense->order_refund_id ?? 0; // if the cash flow is created from a refund
            $history->order_product_id = $expense->order_product_id ?? 0; // if the cash flow is created from a refund
            $history->order_refund_product_id = $expense->order_refund_product_id ?? 0; // if the cash flow is created from a refund
            $history->register_history_id = $expense->register_history_id ?? 0; // if the cash flow is created from a register transaction
            $history->customer_account_history_id = $expense->customer_account_history_id ?? 0; // if the cash flow is created from a customer payment.
            $history->expense_category_id = $expense->category->id;
            $history->save();

            return collect([ $history ]);
        }
    }

    /**
     * Process recorded expenses
     * and check whether they are supposed to be processed
     * on the current day.
     *
     * @return array of process results.
     */
    public function handleRecurringTransactions( Carbon $date = null )
    {
        if ( $date === null ) {
            $date = $this->dateService->copy();
        }

        $processStatus = Transaction::recurring()
            ->active()
            ->get()
            ->map( function( $expense ) use ( $date ) {
                switch ( $expense->occurrence ) {
                    case 'month_starts':
                        $expenseScheduledDate = $date->copy()->startOfMonth();
                        break;
                    case 'month_mid':
                        $expenseScheduledDate = $date->copy()->startOfMonth()->addDays(14);
                        break;
                    case 'month_ends':
                        $expenseScheduledDate = $date->copy()->endOfMonth();
                        break;
                    case 'x_before_month_ends':
                        $expenseScheduledDate = $date->copy()->endOfMonth()->subDays( $expense->occurrence_value );
                        break;
                    case 'x_after_month_starts':
                        $expenseScheduledDate = $date->copy()->startOfMonth()->addDays( $expense->occurrence_value );
                        break;
                    case 'on_specific_day':
                        $expenseScheduledDate = $date->copy();
                        $expenseScheduledDate->day = $expense->occurrence_value;
                        break;
                }

                if ( isset( $expenseScheduledDate ) && $expenseScheduledDate instanceof Carbon ) {
                    /**
                     * Checks if the recurring expenses about to be saved has been
                     * already issued on the occuring day.
                     */
                    if ( $date->isSameDay( $expenseScheduledDate ) ) {
                        if ( ! $this->hadCashFlowRecordedAlready( $expenseScheduledDate, $expense ) ) {
                            $histories = $this->recordCashFlowHistory( $expense );

                            return [
                                'status' => 'success',
                                'data' => compact( 'expense', 'histories' ),
                                'message' => sprintf( __( 'The expense "%s" has been processed on day "%s".' ), $expense->name, $date->toDateTimeString() ),
                            ];
                        }

                        return [
                            'status' => 'failed',
                            'message' => sprintf( __( 'The expense "%s" has already been processed.' ), $expense->name ),
                        ];
                    }
                }

                return [
                    'status' => 'failed',
                    'message' => sprintf( __( 'The expenses "%s" hasn\'t been proceesed, as it\'s out of date.' ), $expense->name ),
                ];
            });

        $successFulProcesses = collect( $processStatus )->filter( fn( $process ) => $process[ 'status' ] === 'success' );

        return [
            'status' => 'success',
            'data' => $processStatus->toArray(),
            'message' => $successFulProcesses->count() === $processStatus->count() ?
                __( 'The process has been correctly executed and all expenses has been processed.' ) :
                    sprintf( __( 'The process has been executed with some failures. %s/%s process(es) has successed.' ), $successFulProcesses->count(), $processStatus->count() ),
        ];
    }

    /**
     * Check if an expense has been executed during a day.
     * To prevent many recurring expenses to trigger multiple times
     * during a day.
     */
    public function hadCashFlowRecordedAlready( $date, Transaction $expense )
    {
        $history = CashFlow::where( 'expense_id', $expense->id )
            ->where( 'created_at', '>=', $date->startOfDay()->toDateTimeString() )
            ->where( 'created_at', '<=', $date->endOfDay()->toDateTimeString() )
            ->get();

        return $history instanceof CashFlow;
    }

    /**
     * Will record an expense resulting from a paid procurement
     *
     * @param Procurement $procurement
     * @return void
     */
    public function handleProcurementTransaction( Procurement $procurement )
    {
        if (
            $procurement->payment_status === Procurement::PAYMENT_PAID &&
            $procurement->delivery_status === Procurement::STOCKED
        ) {
            $accountTypeCode = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_PROCUREMENTS );

            /**
             * this behave as a flash expense
             * made only for recording an history.
             */
            $expense = new Transaction;
            $expense->value = $procurement->cost;
            $expense->active = true;
            $expense->author = $procurement->author;
            $expense->procurement_id = $procurement->id;
            $expense->name = sprintf( __( 'Procurement : %s' ), $procurement->name );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $accountTypeCode;
            $expense->created_at = $procurement->created_at;
            $expense->updated_at = $procurement->updated_at;

            $this->recordCashFlowHistory( $expense );
        }
    }

    /**
     * Will record an expense for every refund performed
     *
     * @param OrderProduct $orderProduct
     * @return void
     */
    public function createTransactionFromRefund( Order $order, OrderProductRefund $orderProductRefund, OrderProduct $orderProduct )
    {
        $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_REFUNDS );

        /**
         * Every product refund produce a debit
         * operation on the system.
         */
        $expense = new Transaction;
        $expense->value = $orderProductRefund->total_price;
        $expense->active = true;
        $expense->operation = CashFlow::OPERATION_DEBIT;
        $expense->author = $orderProductRefund->author;
        $expense->order_id = $order->id;
        $expense->order_product_id = $orderProduct->id;
        $expense->order_refund_id = $orderProductRefund->order_refund_id;
        $expense->order_refund_product_id = $orderProductRefund->id;
        $expense->name = sprintf( __( 'Refunding : %s' ), $orderProduct->name );
        $expense->id = 0; // this is not assigned to an existing expense
        $expense->category = $expenseCategory;

        $this->recordCashFlowHistory( $expense );

        if ( $orderProductRefund->condition === OrderProductRefund::CONDITION_DAMAGED ) {
            /**
             * Only if the product is damaged we should
             * consider saving that as a waste.
             */
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_SPOILED );

            $expense = new Transaction;
            $expense->value = $orderProductRefund->total_price;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_DEBIT;
            $expense->author = $orderProductRefund->author;
            $expense->order_id = $order->id;
            $expense->order_product_id = $orderProduct->id;
            $expense->order_refund_id = $orderProductRefund->order_refund_id;
            $expense->order_refund_product_id = $orderProductRefund->id;
            $expense->name = sprintf( __( 'Spoiled Good : %s' ), $orderProduct->name );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;

            $this->recordCashFlowHistory( $expense );
        }
    }

    /**
     * If the order has just been
     * created and the payment status is PAID
     * we'll store the total as a cash flow transaction.
     *
     * @param Order $order
     * @return void
     */
    public function handleCreatedOrder( Order $order )
    {
        if ( $order->payment_status === Order::PAYMENT_PAID ) {
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_SALES );

            $expense = new Transaction;
            $expense->value = $order->total;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_CREDIT;
            $expense->author = $order->author;
            $expense->order_id = $order->id;
            $expense->name = sprintf( __( 'Sale : %s' ), $order->code );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $order->created_at;
            $expense->updated_at = $order->updated_at;

            $this->recordCashFlowHistory( $expense );
        }
    }

    /**
     * Will pul the defined account
     * or will create a new one according to the settings
     *
     * @param string $accountSettingsName
     * @param array $defaults
     */
    public function getDefinedTransactionAccount( $accountSettingsName, $defaults ): TransactionAccount
    {
        $accountType = TransactionAccount::find( ns()->option->get( $accountSettingsName ) );

        if ( ! $accountType instanceof TransactionAccount ) {
            $result = $this->createAccount( $defaults );

            $accountType = (object) $result[ 'data' ][ 'category' ];

            /**
             * Will set the expense as the default category expense
             * category for subsequent expenses.
             */
            ns()->option->set( $accountSettingsName, $accountType->id );

            $accountType = TransactionAccount::find( ns()->option->get( $accountSettingsName ) );
        }

        return $accountType;
    }

    /**
     * Will compare order payment status
     * and if the previous and the new payment status are supported
     * the transaction will be record to the cash flow.
     */
    public function handlePaymentStatus( string $previous, string $new, Order $order )
    {
        if ( in_array( $previous, [
            Order::PAYMENT_HOLD,
            Order::PAYMENT_DUE,
            Order::PAYMENT_PARTIALLY,
            Order::PAYMENT_PARTIALLY_DUE,
            Order::PAYMENT_UNPAID,
        ]) && in_array(
            $new, [
                Order::PAYMENT_PAID,
            ]
        )) {
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_SALES );

            $expense = new Transaction;
            $expense->value = $order->total;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_CREDIT;
            $expense->author = $order->author;
            $expense->order_id = $order->id;
            $expense->name = sprintf( __( 'Sale : %s' ), $order->code );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;

            $this->recordCashFlowHistory( $expense );
        }
    }

    public function recomputeCashFlow( $rangeStarts = null, $rangeEnds = null )
    {
        /**
         * We'll register cash flow for complete orders
         */
        $this->processPaidOrders( $rangeStarts, $rangeEnds );
        $this->processCustomerAccountHistories( $rangeStarts, $rangeEnds );
        $this->processTransactions( $rangeStarts, $rangeEnds );
        $this->processProcurements( $rangeStarts, $rangeEnds );
        $this->processRecurringTransactions( $rangeStarts, $rangeEnds );
        $this->processRefundedOrders( $rangeStarts, $rangeEnds );
    }

    /**
     * Retreive the account configuration
     * using the account type
     *
     * @param string $type
     */
    public function getTransactionAccountByCode( $type ): TransactionAccount
    {
        $account = $this->accountTypes[ $type ] ?? false;

        if ( ! empty( $account ) ) {
            /**
             * This will define the label
             */
            switch ( $type ) {
                case CashFlow::ACCOUNT_CUSTOMER_CREDIT: $label = __( 'Customer Credit Account' );
                    break;
                case CashFlow::ACCOUNT_CUSTOMER_DEBIT: $label = __( 'Customer Debit Account' );
                    break;
                case CashFlow::ACCOUNT_PROCUREMENTS: $label = __( 'Procurements Account' );
                    break;
                case CashFlow::ACCOUNT_REFUNDS: $label = __( 'Sales Refunds Account' );
                    break;
                case CashFlow::ACCOUNT_REGISTER_CASHIN: $label = __( 'Register Cash-In Account' );
                    break;
                case CashFlow::ACCOUNT_REGISTER_CASHOUT: $label = __( 'Register Cash-Out Account' );
                    break;
                case CashFlow::ACCOUNT_SALES: $label = __( 'Sales Account' );
                    break;
                case CashFlow::ACCOUNT_SPOILED: $label = __( 'Spoiled Goods Account' );
                    break;
            }

            return $this->getDefinedTransactionAccount( $account[ 'option' ], [
                'name' => $label,
                'operation' => $account[ 'operation' ],
                'account' => $type,
            ]);
        }

        throw new NotFoundException( sprintf(
            __( 'Not found account type: %s' ),
            $type
        ) );
    }

    /**
     * Will process refunded orders
     *
     * @param string $rangeStart
     * @param string $rangeEnds
     * @return void
     */
    public function processRefundedOrders( $rangeStarts, $rangeEnds )
    {
        $orders = Order::where( 'created_at', '>=', $rangeStarts )
            ->where( 'created_at', '<=', $rangeEnds )
            ->paymentStatus( Order::PAYMENT_REFUNDED )
            ->get();

        $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_REFUNDS );

        $orders->each( function( $order ) use ( $expenseCategory ) {
            $expense = new Transaction;
            $expense->value = $order->total;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_DEBIT;
            $expense->author = $order->author;
            $expense->customer_account_history_id = $order->id;
            $expense->name = sprintf( __( 'Refund : %s' ), $order->code );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $order->created_at;
            $expense->updated_at = $order->updated_at;

            $this->recordCashFlowHistory( $expense );
        });
    }

    /**
     * Will process paid orders
     *
     * @param string $rangeStart
     * @param string $rangeEnds
     * @return void
     */
    public function processPaidOrders( $rangeStart, $rangeEnds )
    {
        $orders = Order::where( 'created_at', '>=', $rangeStart )
            ->with( 'customer' )
            ->where( 'created_at', '<=', $rangeEnds )
            ->paymentStatus( Order::PAYMENT_PAID )
            ->get();

        $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_SALES );

        Customer::where( 'id', '>', 0 )->update([ 'purchases_amount' => 0 ]);

        $orders->each( function( $order ) use ( $expenseCategory ) {
            $expense = new Transaction;
            $expense->value = $order->total;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_CREDIT;
            $expense->author = $order->author;
            $expense->customer_account_history_id = $order->id;
            $expense->name = sprintf( __( 'Sale : %s' ), $order->code );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $order->created_at;
            $expense->updated_at = $order->updated_at;

            $customer = Customer::find( $order->customer_id );

            if ( $customer instanceof Customer ) {
                $customer->purchases_amount += $order->total;
                $customer->save();
            }

            $this->recordCashFlowHistory( $expense );
        });
    }

    /**
     * Will process the customer histories
     *
     * @return void
     */
    public function processCustomerAccountHistories( $rangeStarts, $rangeEnds )
    {
        $histories = CustomerAccountHistory::where( 'created_at', '>=', $rangeStarts )
            ->where( 'created_at', '<=', $rangeEnds )
            ->get();

        $histories->each( function( $history ) {
            $this->handleCustomerCredit( $history );
        });
    }

    /**
     * Will create an expense for each created procurement
     *
     * @return void
     */
    public function processProcurements( $rangeStarts, $rangeEnds )
    {
        Procurement::where( 'created_at', '>=', $rangeStarts )
            ->where( 'created_at', '<=', $rangeEnds )
            ->get()->each( function( $procurement ) {
                $this->handleProcurementTransaction( $procurement );
            });
    }

    /**
     * Will trigger not recurring expenses
     *
     * @return void
     */
    public function processTransactions( $rangeStarts, $rangeEnds )
    {
        Transaction::where( 'created_at', '>=', $rangeStarts )
            ->where( 'created_at', '<=', $rangeEnds )
            ->notRecurring()
            ->get()
            ->each( function( $expense ) {
                $this->triggerTransaction( $expense );
            });
    }

    public function processRecurringTransactions( $rangeStart, $rangeEnds )
    {
        $startDate = Carbon::parse( $rangeStart );
        $endDate = Carbon::parse( $rangeEnds );

        if ( $startDate->lessThan( $endDate ) && $startDate->diffInDays( $endDate ) >= 1 ) {
            while ( $startDate->isSameDay() ) {
                ns()->date = $startDate;

                $this->handleRecurringTransactions( $startDate );

                $startDate->addDay();
            }
        }
    }

    /**
     * Will add customer credit operation
     * to the cash flow history
     *
     * @param CustomerAccountHistory $customerHistory
     * @return void
     */
    public function handleCustomerCredit( CustomerAccountHistory $customerHistory )
    {
        if ( in_array( $customerHistory->operation, [
            CustomerAccountHistory::OPERATION_ADD,
            CustomerAccountHistory::OPERATION_REFUND,
        ]) ) {
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_CUSTOMER_CREDIT );

            $expense = new Transaction;
            $expense->value = $customerHistory->amount;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_CREDIT;
            $expense->author = $customerHistory->author;
            $expense->customer_account_history_id = $customerHistory->id;
            $expense->name = sprintf( __( 'Customer Account Crediting : %s' ), $customerHistory->customer->name );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $customerHistory->created_at;
            $expense->updated_at = $customerHistory->updated_at;

            $this->recordCashFlowHistory( $expense );
        } elseif ( in_array(
            $customerHistory->operation, [
                CustomerAccountHistory::OPERATION_PAYMENT,
            ]
        ) ) {
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_CUSTOMER_DEBIT );

            $expense = new Transaction;
            $expense->value = $customerHistory->amount;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_DEBIT;
            $expense->author = $customerHistory->author;
            $expense->customer_account_history_id = $customerHistory->id;
            $expense->order_id = $customerHistory->order_id;
            $expense->name = sprintf( __( 'Customer Account Purchase : %s' ), $customerHistory->customer->name );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $customerHistory->created_at;
            $expense->updated_at = $customerHistory->updated_at;

            $this->recordCashFlowHistory( $expense );
        } elseif ( in_array(
            $customerHistory->operation, [
                CustomerAccountHistory::OPERATION_DEDUCT,
            ]
        ) ) {
            $expenseCategory = $this->getTransactionAccountByCode( CashFlow::ACCOUNT_CUSTOMER_DEBIT );

            $expense = new Transaction;
            $expense->value = $customerHistory->amount;
            $expense->active = true;
            $expense->operation = CashFlow::OPERATION_DEBIT;
            $expense->author = $customerHistory->author;
            $expense->customer_account_history_id = $customerHistory->id;
            $expense->name = sprintf( __( 'Customer Account Deducting : %s' ), $customerHistory->customer->name );
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $customerHistory->created_at;
            $expense->updated_at = $customerHistory->updated_at;

            $this->recordCashFlowHistory( $expense );
        }
    }

    public function handleCashOperation( RegisterHistory $registerHistory )
    {
        if ( in_array( $registerHistory->action, [ 
                RegisterHistory::ACTION_CASHING, 
                RegisterHistory::ACTION_CASHOUT,
                RegisterHistory::ACTION_OPENING,
                RegisterHistory::ACTION_CLOSING,
            ] ) ) {
            
            $registerHistory->load( 'register' );

            $code   =   match( $registerHistory->action ) {
                RegisterHistory::ACTION_CASHING =>  CashFlow::ACCOUNT_REGISTER_CASHIN,
                RegisterHistory::ACTION_OPENING =>  CashFlow::ACCOUNT_REGISTER_CASHIN,
                RegisterHistory::ACTION_CASHOUT =>  CashFlow::ACCOUNT_REGISTER_CASHOUT,
                RegisterHistory::ACTION_CLOSING =>  CashFlow::ACCOUNT_REGISTER_CASHOUT,
            };

            $expenseCategory = $this->getTransactionAccountByCode( $code );

            $expense = new Transaction;
            $expense->value = $registerHistory->value;
            $expense->active = true;
            $expense->operation = match( $registerHistory->action ) {
                RegisterHistory::ACTION_CASHING =>  CashFlow::OPERATION_CREDIT,
                RegisterHistory::ACTION_OPENING =>  CashFlow::OPERATION_CREDIT,
                RegisterHistory::ACTION_CASHOUT =>  CashFlow::OPERATION_DEBIT,
                RegisterHistory::ACTION_CLOSING =>  CashFlow::OPERATION_DEBIT,
            };
            $expense->author = $registerHistory->author;
            $expense->register_history_id = $registerHistory->id;
            $expense->name = match( $registerHistory->action ) {
                RegisterHistory::ACTION_CASHING =>  sprintf( __( 'Cash In : %s'), $registerHistory->register->name ),
                RegisterHistory::ACTION_OPENING =>  sprintf( __( 'Cash In : %s'), $registerHistory->register->name ),
                RegisterHistory::ACTION_CASHOUT =>  sprintf( __( 'Cash Out : %s'), $registerHistory->register->name ),
                RegisterHistory::ACTION_CLOSING =>  sprintf( __( 'Cash Out : %s'), $registerHistory->register->name ),
            };
            $expense->id = 0; // this is not assigned to an existing expense
            $expense->category = $expenseCategory;
            $expense->created_at = $registerHistory->created_at;
            $expense->updated_at = $registerHistory->updated_at;

            $this->recordCashFlowHistory( $expense );  
        }
    }

    public function getConfigurations( Transaction $expense )
    {
        $recurringFields = new ReccurringTransactionFields( $expense );
        $directFields = new DirectTransactionFields( $expense );
        $entityFields = new EntityTransactionFields( $expense );
        $scheduledFields = new ScheduledTransactionFields( $expense );

        $asyncTransactions = [];
        $warningMessage = false;

        /**
         * Those features can only be enabled
         * if the jobs are configured correctly.
         */
        if ( ns()->canPerformAsynchronousOperations() ) {
            $asyncTransactions = [
                [
                    'identifier' => ReccurringTransactionFields::getIdentifier(),
                    'label' => __( 'Recurring Transaction' ),
                    'icon' => asset( 'images/recurring.png' ),
                    'fields' => $recurringFields->get(),
                ], [
                    'identifier' => EntityTransactionFields::getIdentifier(),
                    'label' => __( 'Entity Transaction' ),
                    'icon' => asset( 'images/salary.png' ),
                    'fields' => $entityFields->get(),
                ], [
                    'identifier' => ScheduledTransactionFields::getIdentifier(),
                    'label' => __( 'Scheduled Transaction' ),
                    'icon' => asset( 'images/schedule.png' ),
                    'fields' => $scheduledFields->get(),
                ],
            ];
        } else {
            $warningMessage = sprintf(
                __( 'Some transactions are disabled as NexoPOS is not able to <a target="_blank" href="%s">perform asynchronous requests</a>.' ),
                'https://my.nexopos.com/en/documentation/troubleshooting/workers-or-async-requests-disabled'
            );
        }

        $configurations = Hook::filter( 'ns-transactions-configurations', [
            [
                'identifier' => DirectTransactionFields::getIdentifier(),
                'label' => __( 'Direct Transaction' ),
                'icon' => asset( 'images/budget.png' ),
                'fields' => $directFields->get(),
            ], ...$asyncTransactions,
        ]);

        $recurrence = Hook::filter( 'ns-transactions-recurrence', [
            [
                'type' => 'select',
                'label' => __( 'Condition' ),
                'name' => 'occurrence',
                'value' => $expense->occurrence ?? '',
                'options' => Helper::kvToJsOptions([
                    Transaction::OCCURRENCE_START_OF_MONTH => __( 'First Day Of Month' ),
                    Transaction::OCCURRENCE_END_OF_MONTH => __( 'Last Day Of Month' ),
                    Transaction::OCCURRENCE_MIDDLE_OF_MONTH => __( 'Month middle Of Month' ),
                    Transaction::OCCURRENCE_X_AFTER_MONTH_STARTS => __( '{day} after month starts' ),
                    Transaction::OCCURRENCE_X_BEFORE_MONTH_ENDS => __( '{day} before month ends' ),
                    Transaction::OCCURRENCE_SPECIFIC_DAY => __( 'Every {day} of the month' ),
                ]),
            ], [
                'type' => 'number',
                'label' => __( 'Days' ),
                'name' => 'occurrence_value',
                'value' => $expense->occurrence_value ?? 0,
                'shows' => [
                    'occurrence' => [
                        Transaction::OCCURRENCE_X_AFTER_MONTH_STARTS,
                        Transaction::OCCURRENCE_X_BEFORE_MONTH_ENDS,
                        Transaction::OCCURRENCE_SPECIFIC_DAY,
                    ],
                ],
                'description' => __( 'Make sure set a day that is likely to be executed' ),
            ],
        ]);

        return compact( 'recurrence', 'configurations', 'warningMessage' );
    }
}