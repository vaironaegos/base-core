<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Repository;

use MongoDB\Driver\Cursor;
use MongoDB\Client as MongoDbClient;
use MongoDB\Collection as MongoDbCollection;
use Astrotech\Core\Base\Domain\Contracts\LogRepository;
use Astrotech\Core\Base\Domain\Contracts\Dto\SearchOptions;
use Astrotech\Core\Base\Infra\Doctrine\Paginatable\InputData;
use Astrotech\Core\Base\Infra\Doctrine\Sorteable\MongoDbSortable;
use Astrotech\Core\Base\Infra\Doctrine\Searcheable\MongoDbSearchable;
use Astrotech\Core\Base\Infra\Doctrine\Paginatable\MongoDbPaginatable;

final class MongoDbLogRepository implements LogRepository
{
    use MongoDbSearchable;
    use MongoDbSortable;
    use MongoDbPaginatable;

    private string $collectionName = 'logs';
    private string $dbName;
    private MongoDbCollection $collection;

    public function __construct(
        private readonly MongoDbClient $mongoDbClient
    ) {
        $this->dbName = config('queryDb.dbname');
        $this->collection = $this->mongoDbClient->{$this->dbName}->{$this->collectionName};
    }

    public function search(SearchOptions $options): array
    {
        $findFilters = $this->processSearch($options->filters);
        $findOptions = array_merge(
            $this->processSort($options->sort),
            $this->buildPagination(new InputData(
                currentPage: $options->page,
                perPage: $options->perPage,
                skipPagination: $options->skipPagination
            ))
        );

        $rows = $this->toArray($this->collection->find($findFilters, $findOptions));
        $this->paginationData['count'] = count($rows);

        return [
            'data' => $this->toArray($this->collection->find($findFilters, $findOptions)),
            'pagination' => $this->paginationData
        ];
    }

    public function insert(array $data): string|int
    {
        $insertId = $this->collection->insertOne($data);
        return (string)$insertId->getInsertedId();
    }

    private function toArray(Cursor $cursor): array
    {
        $rows = [];

        foreach ($cursor as $document) {
            $id = (string)$document['_id'];
            unset($document['_id']);

            $rows[] = [
                ...$document,
                'id' => $id,
                'message' => json_decode($document['message'], true),
            ];
        }

        return $rows;
    }
}
