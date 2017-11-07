<?php

namespace Algolia\SearchBundle\Engine;

use Algolia\SearchBundle\Searchable\SearchableEntityInterface;
use AlgoliaSearch\Client;

class AlgoliaEngine implements EngineInterface
{
    /** @var Client Client */
    protected $algolia;

    public function __construct(Client $algolia)
    {
        $this->algolia = $algolia;
    }

    public function add(SearchableEntityInterface $searchableEntity)
    {
        $this->update($searchableEntity);
    }

    public function update(SearchableEntityInterface $searchableEntity)
    {
        $record = $searchableEntity->getSearchableArray();

        $this->algolia->initIndex($searchableEntity->getIndexName())
            ->addObject($record, $searchableEntity->getId());
    }

    public function delete(SearchableEntityInterface $searchableEntity)
    {
        $this->algolia->initIndex($searchableEntity->getIndexName())
            ->deleteObject($searchableEntity->getId());
    }

    public function clear($indexName)
    {
        $this->algolia->initIndex($indexName)->clearIndex();
    }

    public function search($query, $indexName, $nbResults = 20, $page = 0, array $parameters = [])
    {
        $params = array_merge($parameters, [
            'hitsPerPage' => $nbResults,
            'page' => $page
        ]);

        return $this->algolia->initIndex($indexName)->search($query, $params);
    }

    public function searchIds($query, $indexName, $nbResults = 20, $page = 0, array $parameters = [])
    {
        $result = $this->search($query, $indexName, $nbResults, $page, $parameters);

        return array_column($result['hits'], 'objectID');
    }

    public function count($query, $indexName)
    {
        $results = $this->algolia->initIndex($indexName)->search($query);

        return (int) $results['nbHits'];
    }
}
