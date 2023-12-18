<?php

namespace App\Http;


use App\Models\Transaction;
use App\Models\FixedTerm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserBalanceDTO
{
    private $user;
    private $accounts;
    private $fixedTermDeposits;
    private $history;
    private $balance;

    public function __construct(array $balance)
    {
        $userId = Auth::id();
        $this->user = User::with('account')->find($userId);

        if ($this->user) {
            $this->accounts = $this->user->account;
            $this->history = Transaction::whereIn('account_id', $this->accounts->pluck('id'))->get();
            $this->fixedTermDeposits = FixedTerm::whereIn('account_id', $this->accounts->pluck('id'))->get();
            $this->balance = $balance;
        } else {
            $this->accounts = collect();
            $this->history = collect();
            $this->fixedTermDeposits = collect();
            $this->balance = collect();
        }
    }

    private function calculateBalance()
    {
        $arsBalance = 0;
        $usdBalance = 0;

        if ($this->accounts) {
            foreach ($this->accounts as $account) {
                if ($account->currency === 'ARS') {
                    $arsBalance += $account->balance;
                } elseif ($account->currency === 'USD') {
                    $usdBalance += $account->balance;
                }
            }
        }

        return [
            'ARS accounts balance' => $arsBalance,
            'USD accounts balance' => $usdBalance,
        ];
    }

    public function toArray()
    {
        return [
            'accounts' => $this->accounts,
            'balance' => $this->calculateBalance(),
            'history' => $this->history,
            'fixed_term_deposits' => $this->fixedTermDeposits,
        ];
    }
}