<?php

namespace Vundb\FirestoreBundle\Repository;

use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;

// use App\Entity\AbstractEntity;
// use Google\Cloud\Firestore\DocumentSnapshot;
// use Symfony\Component\Uid\Uuid;

/**
 * @template TEntity
 */
abstract class AbstractRepository
{
    protected FirestoreClient $client;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $databaseName)
    {
        $this->client = new FirestoreClient([
            'database' => $databaseName
        ]);
    }

    /**
     * @param array $filters
     * @return array<TEntity>
     */
    public function findBy(array $filters): array
    {
        $documents = $this->client()
            ->collection($this->collection())
            ->where($filters)
            ->documents();

        $entities = [];
        foreach ($documents as $document) {
            $entities[] = $this->hydrate($document);
        }

        return $entities;
    }

    /**
     * @param array $filters
     * @return ?TEntity
     */
    public function findOneBy(array $filters)
    {
        $documents = $this->client()
            ->collection($this->collection())
            ->where($filters)
            ->limit(1)
            ->documents();

        if ($documents->isEmpty()) {
            return null;
        }

        return $this->hydrate($documents->rows()[0]);
    }

    /**
     * @param string $id
     * @return ?TEntity
     */
    public function findOneById(string $id)
    {
        $documents = $this->client()->collection($this->collection())
            ->where('id', '=', $id)
            ->documents();

        if ($documents->isEmpty()) {
            return null;
        }

        return $this->hydrate($documents->rows()[0]);
    }

    //     /**
    //      * @return array<TEntity>
    //      */
    //     public function find(): array
    //     {
    //         $entities = [];

    //         $documents = $this->client->collection($this->collection())->documents();

    //         foreach ($documents as $document) {
    //             $entities[] = $this->hydrateDocumentSnapshot($document);
    //         }

    //         return $entities;
    //     }

    //     /**
    //      * @param TEntity $entity
    //      * @return TEntity
    //      */
    //     public function persist($entity)
    //     {
    //         /** @var AbstractEntity $entity */
    //         if (0 === strlen($entity->getId())) {
    //             $entity->setId(Uuid::v4());
    //         }

    //         $docRef = $this->client->collection($this->collection())->document($entity->getId());
    //         $docRef->set($entity->toArray());

    //         return $entity;
    //     }

    //     /**
    //      * @param string $id
    //      */
    //     public function delete(string $id)
    //     {
    //         $docRef = $this->client->collection($this->collection())->document($id);
    //         $docRef->delete();
    //     }

    /**
     * Returns the current collection path.
     *
     * @return string
     */
    abstract protected function collection(): string;

    /**
     * Hydrates given document snapshot to the desired Entity class.
     *
     * @return TEntity
     */
    abstract protected function hydrate(DocumentSnapshot $document): mixed;

    ### PRIVATE ###

    /**
     * @codeCoverageIgnore
     */
    protected function client(): FirestoreClient
    {
        return $this->client;
    }
}
