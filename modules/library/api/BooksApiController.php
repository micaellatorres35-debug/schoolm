<?php
class BooksApiController
{
    private Book        $book;
    private ActivityLog $log;

    public function __construct()
    {
        $this->book = new Book();
        $this->log  = new ActivityLog();
    }

    public function handle(string $action, array $input): never
    {
        match ($action) {
            'get_books'    => $this->getBooks(),
            'get_book'     => $this->getBook(),
            'add_book'     => $this->addBook($input),
            'update_book'  => $this->updateBook($input),
            'delete_book'  => $this->deleteBook($input),
            'upload_cover' => $this->uploadCover(),
            'get_genres'   => $this->getGenres(),
            default        => Response::error("Unknown books action: {$action}"),
        };
    }

    private function getBooks(): never
    {
        $search = $_GET['search'] ?? '';
        $genre  = $_GET['genre']  ?? '';
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = (int)($_GET['limit'] ?? 10);
        Response::json($this->book->list($search, $genre, $status, $page, $limit));
    }

    private function getBook(): never
    {
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->book->find($id);
        if (!$book) Response::error('Book not found.', 404);
        $transaction     = new Transaction();
        $book['history'] = $transaction->historyForBook($id);
        Response::json($book);
    }

    private function addBook(array $input): never
    {
        $title  = trim($input['title']  ?? '');
        $author = trim($input['author'] ?? '');
        if (!$title || !$author) Response::error('Title and Author are required.');

        $totalStock = max(1, (int)($input['total_stock'] ?? 1));

        $id = $this->book->create(
            title:       $title,
            author:      $author,
            genre:       trim($input['genre']       ?? 'General Reference'),
            year:        !empty($input['year']) ? (int)$input['year'] : null,
            isbn:        trim($input['isbn']        ?? '') ?: null,
            description: trim($input['description'] ?? ''),
            coverUrl:    trim($input['cover_url']   ?? '') ?: null,
            totalStock:  $totalStock,
        );

        $this->log->log('Book Added', "Added \"{$title}\" by {$author} (Stock: {$totalStock})");
        Response::success("Book \"{$title}\" added successfully!", ['id' => $id]);
    }

    private function updateBook(array $input): never
    {
        $id     = (int)($input['id']    ?? 0);
        $title  = trim($input['title']  ?? '');
        $author = trim($input['author'] ?? '');
        if (!$id || !$title || !$author) Response::error('ID, Title and Author are required.');

        $totalStock = max(1, (int)($input['total_stock'] ?? 1));

        $this->book->update(
            id:          $id,
            title:       $title,
            author:      $author,
            genre:       trim($input['genre']       ?? 'General Reference'),
            year:        !empty($input['year']) ? (int)$input['year'] : null,
            isbn:        trim($input['isbn']        ?? '') ?: null,
            description: trim($input['description'] ?? ''),
            condition:   trim($input['condition']   ?? 'Good'),
            coverUrl:    trim($input['cover_url']   ?? '') ?: null,
            totalStock:  $totalStock,
        );

        $this->log->log('Book Updated', "Updated \"{$title}\" (Stock: {$totalStock})");
        Response::success("Book \"{$title}\" updated successfully!");
    }

    private function deleteBook(array $input): never
    {
        $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
        if (!$id) Response::error('Book ID required.');

        $book = $this->book->find($id);
        if (!$book) Response::error('Book not found.', 404);

        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM lbr_transactions
            WHERE book_id = ?
              AND status IN ('active', 'overdue')
              AND return_date IS NULL
        ");
        $stmt->execute([$id]);
        $activeLoans = (int)$stmt->fetchColumn();

        if ($activeLoans > 0) {
            Response::error("Cannot delete this book — it has {$activeLoans} active/overdue loan(s).");
        }

        $db->prepare("DELETE FROM lbr_borrow_history WHERE book_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM lbr_transactions WHERE book_id = ? AND status NOT IN ('active', 'overdue')")->execute([$id]);

        $this->book->delete($id);

        $this->log->log('Book Deleted', "Deleted \"{$book['title']}\"");
        Response::success('Book deleted successfully!');
    }

    private function uploadCover(): never
    {
        if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
            Response::error('File upload failed.');
        }
        $file    = $_FILES['cover'];
        $bookId  = (int)($_POST['book_id'] ?? 0);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed, true)) Response::error('Invalid file type. Use JPG, PNG, WebP, or GIF.');
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'cover_' . ($bookId ?: uniqid()) . '_' . time() . '.' . $ext;
        $dest     = UPLOAD_DIR . $filename;
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dest)) Response::error('Failed to save file.', 500);
        if ($bookId) {
            $this->book->setCoverLocal($bookId, $filename);
            $this->log->log('Cover Uploaded', "Uploaded cover for book ID {$bookId}");
        }
        Response::success('Cover uploaded!', ['filename' => $filename, 'url' => UPLOAD_URL . $filename]);
    }

    private function getGenres(): never
    {
        Response::json(['genres' => $this->book->genres()]);
    }
}