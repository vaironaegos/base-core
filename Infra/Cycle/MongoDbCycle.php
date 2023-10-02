<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Cycle;

use MongoDB\Client as MongoDbClient;
use MongoDB\Collection as MongoDbCollection;
use MongoDB\Database as MongoDbDatabase;
use MongoDB\Model\BSONDocument;

final class MongoDbCycle
{
    protected MongoDbCollection $collection;
    protected MongoDbDatabase $dbManager;
    protected string $dbName;

    public function __construct(
        protected MongoDbClient $mongoDbClient
    ) {
        $this->dbName = config('queryDb.dbname');
        $this->dbManager = $this->mongoDbClient->{$this->dbName};
    }

    public function findSoft(string $className, array $filters, array $options, $isArray = true): array
    {
        $modelClass = new $className([]);
        $fields = $modelClass->toViewArray();

        $cursor = $this->collection->find($filters, $options)->toArray();

        if ($isArray) {
            $rows = [];
            foreach ($cursor as $document) {
                $document['id'] = (string)$document['_id'];
                unset($document['_id']);

                foreach ($fields as $key => $field) {
                    $isRelation = is_string($key) && str_contains($key, '_');
                    if ($isRelation) {
                        [$a, $relation] = explode('_', $key);

                        if (str_contains($relation, '.')) {
                            [$relation1, $relation2] = explode('.', $relation);

                            foreach ($field as $relField) {
                                $newDocument[$relation1][$relField] = $document->{$relation1}?->
                                {$relation2}?->{$relField};
                            }
                            continue;
                        }

                        foreach ($field as $relField) {
                            $newDocument[$relation][$relField] = $document->{$relation}?->{$relField};
                        }
                        continue;
                    }

                    if (is_string($key)) {
                        $newDocument[$key] = $document->{$field};
                        continue;
                    }

                    $newDocument[$field] = $document->{$field};
                }

                $rows[] = $newDocument;
            }

            return $rows;
        }

        return $cursor;
    }

    public function find(array $filters, array $options, $isArray = true): array
    {
        $cursor = $this->collection->find($filters, $options)->toArray();

        if ($isArray) {
            $rows = [];
            foreach ($cursor as $document) {
                $document['id'] = (string)$document['_id'];
                unset($document['_id']);

                $newDocument = [];

                foreach ($document as $field => $value) {
                    if ($value instanceof BSONDocument) {
                        $newDocument[$field] = $value->getArrayCopy();
                        continue;
                    }

                    $newDocument[$field] = $value;
                }

                $rows[] = $newDocument;
            }

            return $rows;
        }

        return $cursor;
    }

    public function setDbName(string $dbName): void
    {
        $this->dbName = $dbName;
    }

    public function setCollection(string $collectionName): MongoDbCollection
    {
        $this->collection = $this->dbManager->{$collectionName};
        return $this->collection;
    }
}
