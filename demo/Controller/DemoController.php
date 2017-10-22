<?php

namespace QueryMule\Demo\Controller;

use QueryMule\Builder\Connection\Database;
use QueryMule\Builder\Connection\DatabaseHandler;
use QueryMule\Demo\Table\Author;
use QueryMule\Demo\Table\Book;
use QueryMule\Query\Sql\Statement\SelectInterface;

/**
 * Class Demo
 * @package QueryMule\Demo
 */
class DemoController
{
    /**
     * @var \QueryMule\Query\Connection\Driver\DriverInterface
     */
    private $driver;

    /**
     * Demo constructor.
     */
    public function __construct()
    {
        $database = new Database([
            'sqlite' => [
                DatabaseHandler::DATABASE_DRIVER => 'sqlite',
                DatabaseHandler::DATABASE_DATABASE => 'sqlite.db',
                DatabaseHandler::DATABASE_PATH_TO_FILE => __DIR__ . '/../Database/sqlite.db',
                DatabaseHandler::DATABASE_ADAPTER => DatabaseHandler::ADAPTER_PDO,
            ]
        ]);

        $this->driver = $database->dbh('sqlite')->driver();
    }

    /**
     * @param $book_id
     * @param bool $with_author
     * @return string
     */
    public function getBook($book_id, $with_author = false)
    {
        $book = new Book($this->driver);
        $author = new Author($this->driver);

        if(empty($book_id)){
            return json_encode(['error'=>'book_id is required!'], JSON_PRETTY_PRINT);
        }

        $query = $book->select([SelectInterface::SQL_STAR],'b');

        if($with_author){
            $book->joinAuthor($author);

            //$author->filterByAuthorId(1);
        }

        $book->filterByBookId($book_id);

        $query->where('a.author_id','=?',"1");

        $stm = $query->build();

        var_dump($stm);

        $result = $this->driver->fetchAll($stm);

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    public function postBook(){}

    public function putBook(){}

    public function deleteBook(){}
}