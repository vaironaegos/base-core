<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Repository;

use Astrotech\ApiBase\Domain\Contracts\Dto\SearchOptions;
use Astrotech\ApiBase\Domain\Contracts\LogRepository;
use Astrotech\ApiBase\Infra\Doctrine\Paginatable\DoctrineOdmPaginatable;
use Astrotech\ApiBase\Infra\Doctrine\Paginatable\InputData as PaginatableInputData;
use Astrotech\ApiBase\Infra\Doctrine\Searcheable\DoctrineOdmSearcheable;
use Astrotech\ApiBase\Infra\Doctrine\Searcheable\InputData;
use Astrotech\ApiBase\Infra\Doctrine\Sorteable\DoctrineOdmSorteable;
use Astrotech\ApiBase\Infra\Doctrine\Sorteable\InputData as SorteableInputData;
use MongoDB\Client as MongoDbClient;
use MongoDB\Collection as MongoDbCollection;

final class MongoDbLogRepository implements LogRepository
{
    use DoctrineOdmSearcheable;
    use DoctrineOdmPaginatable;
    use DoctrineOdmSorteable;

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
        $this->processSearch(new InputData(mongoDbClient: $this->mongoDbClient, filters: $options->filters));
        $this->processSort(new SorteableInputData(mongoDbClient: $this->mongoDbClient, params: $options->sort));
        $this->buildPagination(new PaginatableInputData(
            currentPage: $options->page,
            mongoDbClient: $this->mongoDbClient,
            skipPagination: $options->skipPagination
        ));

        return ['data' => $this->data, 'pagination' => $this->paginationData];
    }

    public function insert(array $data): string|int
    {
        $insertId = $this->collection->insertOne($data);
        return $insertId->getInsertedId();
    }
}
