<?php
class TransactionsApiController
{
    private Transaction $transaction;
    private Book        $book;
    private Borrower    $borrower;
    private Settings    $settings;
    private ActivityLog $log;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->book        = new Book();
        $this->borrower    = new Borrower();
        $this->settings    = new Settings();
        $this->log         = new ActivityLog();
    }

    public function handle(string $action, array $input): never
    {
        match ($action) {
            'get_transactions'   => $this->getTransactions(),
            'get_returnable'     => $this->getReturnable(),
            'get_lost_books'     => $this->getLostBooks(),
            'borrow_book'        => $this->borrowBook($input),
            'return_book'        => $this->returnBook($input),
            'report_lost'        => $this->reportLost($input),
            'remove_lost_book'   => $this->removeLostBook($input),
            default              => Response::error("Unknown transactions action: {$action}"),
        };
    }

    private function getTransactions(): never
    {
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = (int)($_GET['limit'] ?? 10);
        Response::json($this->transaction->list($status, $page, $limit, $search));
    }

    private function getReturnable(): never
    {
        $rows = $this->transaction->listReturnable();
        Response::json(['transactions' => $rows, 'total' => count($rows)]);
    }

    private function getLostBooks(): never
    {
        $rows = $this->transaction->listLost();
        Response::json(['lost_books' => $rows, 'total' => count($rows)]);
    }

    private function borrowBook(array $input): never
    {
        $bookId      = (int)($input['book_id']     ?? 0);
        $borrowerId  = (int)($input['borrower_id'] ?? 0);
        $defaultDays = (int)($this->settings->get('max_borrow_days') ?? 14);
        $borrowDays  = (int)($input['borrow_days'] ?? $defaultDays);
        $notes       = trim($input['notes']        ?? '');

        if (!$bookId || !$borrowerId) {
            Response::error('Book and Member are required.');
        }

        $book = $this->book->find($bookId);
        if (!$book) {
            Response::error('Book not found.', 404);
        }
        if ((int)$book['available_stock'] <= 0) {
            Response::error('No available copies of this book.');
        }
        if ($book['status'] === 'lost') {
            Response::error('This book is marked as lost and cannot be borrowed.');
        }

        $borrowerRow = $this->borrower->find($borrowerId);
        if (!$borrowerRow) {
            Response::error('Member not found.', 404);
        }

        $maxBooks = (int)($this->settings->get('max_books_per_member') ?? 3);
        if ($this->transaction->activeLoanCount($borrowerId) >= $maxBooks) {
            Response::error("Member has reached the maximum borrow limit ({$maxBooks} books).");
        }

        $borrowDate = date('Y-m-d');
        $dueDate    = date('Y-m-d', strtotime("+{$borrowDays} days"));

        $this->transaction->create($bookId, $borrowerId, $borrowDate, $dueDate, $notes);
        $this->book->decrementStock($bookId);

        $this->log->log(
            'Book Borrowed',
            "\"{$book['title']}\" borrowed by {$borrowerRow['name']} (due {$dueDate})"
        );

        Response::success("Book borrowed successfully! Due: {$dueDate}");
    }

    private function returnBook(array $input): never
    {
        $transId   = (int)($input['transaction_id'] ?? 0);
        $condition = trim($input['condition']        ?? 'Good');

        if (!$transId) {
            Response::error('Transaction ID required.');
        }

        $trans = $this->transaction->find($transId);
        if (!$trans) {
            Response::error('Transaction not found.', 404);
        }
        if ($trans['status'] === 'returned') {
            Response::error('Book already returned.');
        }
        if ($trans['status'] === 'lost') {
            Response::error('Book is marked as lost — cannot be returned.');
        }

        $returnDate = date('Y-m-d');
        $this->transaction->markReturned($transId, $returnDate, $condition, 0);
        $this->book->incrementStock($trans['book_id'], $condition);

        $this->log->log('Book Returned', "\"{$trans['title']}\" returned");

        Response::success('Book returned successfully!');
    }

    private function reportLost(array $input): never
    {
        $transId   = (int)($input['transaction_id'] ?? 0);
        $lostNotes = trim($input['lost_notes']       ?? '');

        if (!$transId) {
            Response::error('Transaction ID required.');
        }

        $trans = $this->transaction->find($transId);
        if (!$trans) {
            Response::error('Transaction not found.', 404);
        }
        if ($trans['status'] === 'returned') {
            Response::error('Book already returned.');
        }
        if ($trans['status'] === 'lost') {
            Response::error('Book is already marked as lost.');
        }

        $db = Database::getInstance();
        $db->prepare("
            UPDATE lbr_transactions
            SET status     = 'lost',
                is_lost    = 1,
                lost_date  = CURDATE(),
                lost_notes = ?
            WHERE id = ?
        ")->execute([$lostNotes, $transId]);

        $this->book->decrementStockOnLoss($trans['book_id']);

        $this->log->log('Book Lost', "\"{$trans['title']}\" reported as lost");
        Response::success('Book reported as lost successfully.');
    }

    private function removeLostBook(array $input): never
    {
        $transId = (int)($input['transaction_id'] ?? 0);

        if (!$transId) {
            Response::error('Transaction ID required.');
        }

        $trans = $this->transaction->find($transId);
        if (!$trans) {
            Response::error('Transaction not found.', 404);
        }
        if ($trans['status'] !== 'lost') {
            Response::error('Only lost book records can be removed.');
        }

        $db = Database::getInstance();

        $bookId = (int)$trans['book_id'];
        $db->prepare("
            UPDATE lbr_books
            SET available_stock = LEAST(total_stock, available_stock + 1),
                total_stock     = total_stock + 1,
                status          = 'available'
            WHERE id = ?
        ")->execute([$bookId]);

        $db->prepare("DELETE FROM lbr_transactions WHERE id = ?")->execute([$transId]);

        $this->log->log(
            'Lost Book Removed',
            "Removed lost record for \"{$trans['title']}\" — book stock restored (Transaction #{$transId})"
        );

        Response::success('Lost book record removed and book stock restored.');
    }
}