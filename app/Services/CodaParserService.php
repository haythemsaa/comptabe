<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

/**
 * CODA Parser Service
 *
 * Parses Belgian CODA bank statement files according to the CODA 2.6 standard.
 * CODA = COdified DAta - Belgian standard for electronic bank statements.
 */
class CodaParserService
{
    protected array $result = [];
    protected array $currentTransaction = [];
    protected int $currentSequence = 0;

    /**
     * Parse a CODA file content
     */
    public function parse(string $content): array
    {
        $this->result = [
            'account_iban' => null,
            'account_bic' => null,
            'currency' => 'EUR',
            'statement_number' => null,
            'statement_date' => null,
            'old_balance' => 0,
            'new_balance' => 0,
            'total_credit' => 0,
            'total_debit' => 0,
            'transactions' => [],
        ];

        $lines = explode("\n", str_replace("\r", "", $content));

        foreach ($lines as $line) {
            if (strlen($line) < 1) {
                continue;
            }

            $recordType = substr($line, 0, 1);

            switch ($recordType) {
                case '0':
                    $this->parseHeaderRecord($line);
                    break;
                case '1':
                    $this->parseOldBalanceRecord($line);
                    break;
                case '2':
                    $this->parseMovementRecord($line);
                    break;
                case '3':
                    $this->parseInformationRecord($line);
                    break;
                case '8':
                    $this->parseNewBalanceRecord($line);
                    break;
                case '9':
                    $this->parseTrailerRecord($line);
                    break;
            }
        }

        // Finalize last transaction if any
        $this->finalizeTransaction();

        return $this->result;
    }

    /**
     * Parse record type 0 - Header
     */
    protected function parseHeaderRecord(string $line): void
    {
        // Position 6-11: Creation date (DDMMYY)
        $dateStr = substr($line, 5, 6);
        if ($dateStr && $dateStr !== '000000') {
            $this->result['statement_date'] = $this->parseDate($dateStr);
        }

        // Position 60-71: BIC
        $bic = trim(substr($line, 59, 11));
        if ($bic) {
            $this->result['account_bic'] = $bic;
        }
    }

    /**
     * Parse record type 1 - Old Balance
     */
    protected function parseOldBalanceRecord(string $line): void
    {
        // Position 2: Article code (detail level)
        $articleCode = substr($line, 1, 1);

        if ($articleCode === '0') {
            // Position 6-42: Account number (Belgian format or IBAN)
            $accountNumber = trim(substr($line, 5, 37));

            // Try to extract IBAN
            if (preg_match('/([A-Z]{2}\d{2}[A-Z0-9]{1,30})/', $accountNumber, $matches)) {
                $this->result['account_iban'] = $matches[1];
            } elseif (strlen($accountNumber) >= 12) {
                // Belgian format: convert to IBAN
                $this->result['account_iban'] = $this->belgianToIban($accountNumber);
            }

            // Position 43-57: Old balance (sign at position 42)
            $sign = substr($line, 42, 1);
            $balance = (float)substr($line, 43, 15) / 1000;
            $this->result['old_balance'] = $sign === '1' ? -$balance : $balance;

            // Position 58-63: Statement date
            $dateStr = substr($line, 57, 6);
            if ($dateStr && $dateStr !== '000000') {
                $this->result['statement_date'] = $this->parseDate($dateStr);
            }

            // Position 126-128: Statement sequence number
            $this->result['statement_number'] = trim(substr($line, 125, 3));

            // Position 64-66: Currency
            $currency = trim(substr($line, 63, 3));
            if ($currency) {
                $this->result['currency'] = $currency;
            }
        }
    }

    /**
     * Parse record type 2 - Movement Record
     */
    protected function parseMovementRecord(string $line): void
    {
        $articleCode = substr($line, 1, 1);

        switch ($articleCode) {
            case '1':
                // First part of transaction
                $this->finalizeTransaction();
                $this->startNewTransaction($line);
                break;
            case '2':
                // Continuation of transaction
                $this->continueTransaction($line);
                break;
            case '3':
                // Final part of transaction
                $this->finalizeTransactionPart($line);
                break;
        }
    }

    /**
     * Start a new transaction from record 21
     */
    protected function startNewTransaction(string $line): void
    {
        $this->currentSequence = (int)substr($line, 2, 4);

        // Position 32-46: Amount (sign at position 31)
        $sign = substr($line, 31, 1);
        $amount = (float)substr($line, 32, 15) / 1000;
        $amount = $sign === '1' ? -$amount : $amount;

        // Position 48-53: Value date
        $valueDate = $this->parseDate(substr($line, 47, 6));

        // Position 63-85: Transaction code
        $transactionCode = substr($line, 53, 8);

        // Position 62-114: Communication (free format)
        $communication = trim(substr($line, 61, 53));

        // Position 115-127: Entry date
        $entryDate = $this->parseDate(substr($line, 114, 6));

        // Position 128: Statement sequence
        $statementSeq = substr($line, 124, 3);

        $this->currentTransaction = [
            'sequence' => $this->currentSequence,
            'amount' => $amount,
            'value_date' => $valueDate,
            'transaction_date' => $entryDate ?? $valueDate,
            'transaction_code' => $transactionCode,
            'description' => $communication,
            'reference' => null,
            'counterparty_name' => null,
            'counterparty_account' => null,
            'structured_communication' => null,
            'currency' => $this->result['currency'],
        ];

        // Track totals
        if ($amount > 0) {
            $this->result['total_credit'] += $amount;
        } else {
            $this->result['total_debit'] += abs($amount);
        }
    }

    /**
     * Continue transaction from record 22
     */
    protected function continueTransaction(string $line): void
    {
        if (empty($this->currentTransaction)) {
            return;
        }

        // Position 11-63: Communication continuation
        $communication = trim(substr($line, 10, 53));
        $this->currentTransaction['description'] .= ' ' . $communication;

        // Position 64-98: Client reference
        $clientRef = trim(substr($line, 63, 35));
        if ($clientRef) {
            $this->currentTransaction['reference'] = $clientRef;
        }

        // Position 99-125: BIC of counterparty
        $bic = trim(substr($line, 98, 11));
    }

    /**
     * Finalize transaction from record 23
     */
    protected function finalizeTransactionPart(string $line): void
    {
        if (empty($this->currentTransaction)) {
            return;
        }

        // Position 11-47: Counterparty account (IBAN or Belgian format)
        $counterpartyAccount = trim(substr($line, 10, 37));
        if ($counterpartyAccount) {
            if (preg_match('/([A-Z]{2}\d{2}[A-Z0-9]{1,30})/', $counterpartyAccount, $matches)) {
                $this->currentTransaction['counterparty_account'] = $matches[1];
            } else {
                $this->currentTransaction['counterparty_account'] = $counterpartyAccount;
            }
        }

        // Position 48-82: Counterparty name
        $counterpartyName = trim(substr($line, 47, 35));
        if ($counterpartyName) {
            $this->currentTransaction['counterparty_name'] = $counterpartyName;
        }

        // Position 83-117: Communication
        $communication = trim(substr($line, 82, 35));
        if ($communication) {
            $this->currentTransaction['description'] .= ' ' . $communication;
        }
    }

    /**
     * Parse record type 3 - Information Record
     */
    protected function parseInformationRecord(string $line): void
    {
        $articleCode = substr($line, 1, 1);

        if ($articleCode === '1' || $articleCode === '2' || $articleCode === '3') {
            // Additional information for current transaction
            $info = trim(substr($line, 40, 73));

            if (!empty($this->currentTransaction)) {
                // Check for structured communication
                if (preg_match('/\+{3}(\d{3}\/\d{4}\/\d{5})\+{3}/', $info, $matches)) {
                    $this->currentTransaction['structured_communication'] = '+++' . $matches[1] . '+++';
                }

                $this->currentTransaction['description'] .= ' ' . $info;
            }
        }
    }

    /**
     * Parse record type 8 - New Balance
     */
    protected function parseNewBalanceRecord(string $line): void
    {
        // Position 42-56: New balance (sign at position 41)
        $sign = substr($line, 41, 1);
        $balance = (float)substr($line, 42, 15) / 1000;
        $this->result['new_balance'] = $sign === '1' ? -$balance : $balance;

        // Position 57-62: Balance date
        $dateStr = substr($line, 56, 6);
        if ($dateStr && $dateStr !== '000000' && !$this->result['statement_date']) {
            $this->result['statement_date'] = $this->parseDate($dateStr);
        }
    }

    /**
     * Parse record type 9 - Trailer
     */
    protected function parseTrailerRecord(string $line): void
    {
        // Finalize any pending transaction
        $this->finalizeTransaction();

        // Position 17-31: Total debit
        // Position 32-46: Total credit
        // These can be used for validation
    }

    /**
     * Finalize current transaction and add to results
     */
    protected function finalizeTransaction(): void
    {
        if (!empty($this->currentTransaction)) {
            // Clean up description
            $this->currentTransaction['description'] = preg_replace('/\s+/', ' ', trim($this->currentTransaction['description']));

            // Extract structured communication if not already found
            if (!$this->currentTransaction['structured_communication']) {
                if (preg_match('/\+{3}(\d{3}\/\d{4}\/\d{5})\+{3}/', $this->currentTransaction['description'], $matches)) {
                    $this->currentTransaction['structured_communication'] = '+++' . $matches[1] . '+++';
                }
            }

            $this->result['transactions'][] = $this->currentTransaction;
            $this->currentTransaction = [];
        }
    }

    /**
     * Parse CODA date format (DDMMYY) to Carbon
     */
    protected function parseDate(string $dateStr): ?Carbon
    {
        if (!$dateStr || $dateStr === '000000' || strlen($dateStr) < 6) {
            return null;
        }

        try {
            $day = substr($dateStr, 0, 2);
            $month = substr($dateStr, 2, 2);
            $year = substr($dateStr, 4, 2);

            // Convert 2-digit year to 4-digit
            $fullYear = $year > 50 ? '19' . $year : '20' . $year;

            return Carbon::createFromFormat('Y-m-d', "{$fullYear}-{$month}-{$day}");
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Convert Belgian account number to IBAN
     */
    protected function belgianToIban(string $belgian): string
    {
        // Remove any formatting
        $clean = preg_replace('/[^0-9]/', '', $belgian);

        if (strlen($clean) === 12) {
            // Belgian format: XXX-XXXXXXX-XX
            // IBAN format: BExx XXXX XXXX XXXX
            $checkDigits = 98 - (int)bcmod($clean . '1114' . '00', '97');
            return 'BE' . str_pad($checkDigits, 2, '0', STR_PAD_LEFT) . $clean;
        }

        return $belgian;
    }
}
