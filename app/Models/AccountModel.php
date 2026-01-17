<?php
namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $dataFile;
    protected $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
        if ($this->userId) {
            $this->dataFile = WRITEPATH . 'account_' . $this->userId . '.txt';
        } else {
            $this->dataFile = WRITEPATH . 'account.txt';
        }

        if (! is_file($this->dataFile)) {
            file_put_contents($this->dataFile, "0.00");
        }
    }

    public function getBalance()
    {
        $contents = @file_get_contents($this->dataFile);
        if ($contents === false) {
            return 0.00;
        }
        return (float) trim($contents);
    }

    public function deposit($amount)
    {
        $balance = $this->getBalance();
        $balance += (float) $amount;
        file_put_contents($this->dataFile, number_format($balance, 2, '.', ''), LOCK_EX);
        return $balance;
    }
}

