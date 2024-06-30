<?php

namespace Vundb\FirestoreBundle\Tests\Repository;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\QuerySnapshot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Uid\Uuid;
use Vundb\FirestoreBundle\Entity\Entity;
use Vundb\FirestoreBundle\Repository\Repository;

class AbstractRepositoryTest extends TestCase
{
    private MockObject $client;
    private MockObject $collection;
    private MockObject|TestRepository $repository;

    protected function setUp(): void
    {
        $this->collection = $this->getMockBuilder(CollectionReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'limit', 'documents', 'document'])
            ->getMock();
        $this->repository = $this->getMockBuilder(TestRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['client'])
            ->getMock();
        $this->client = $this->getMockBuilder(FirestoreClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['collection'])
            ->getMock();

        $this->client->expects($this->any())
            ->method('collection')
            ->with(TestRepository::COLLECTION)
            ->willReturn($this->collection);
        $this->repository->expects($this->any())
            ->method('client')
            ->willReturn($this->client);
    }

    public function testFindBy_withEmptyResult()
    {
        $filters = [];
        $documents = [];

        $this->setUpClientQuery($filters, $documents);

        $this->assertSame($documents, $this->repository->findBy($filters));
    }

    public function testFindBy_withResult()
    {
        $filters = [];
        $documents = [
            $this->getMockBuilder(DocumentSnapshot::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DocumentSnapshot::class)->disableOriginalConstructor()->getMock()
        ];

        $this->setUpClientQuery($filters, $documents);

        $this->assertCount(count($documents), $this->repository->findBy($filters));
    }

    public function testFindOneBy_withEmptyResult()
    {
        $filters = [];
        $documents = $this->getMockBuilder(QuerySnapshot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEmpty'])
            ->getMock();
        $documents->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);

        $this->setUpClientQueryWithLimit($filters, 1, $documents);

        $this->assertNull($this->repository->findOneBy($filters));
    }

    public function testFindOneBy_withResult()
    {
        $filters = [];
        $documents = $this->getMockBuilder(QuerySnapshot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEmpty', 'rows'])
            ->getMock();
        $documents->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);
        $documents->expects($this->any())
            ->method('rows')
            ->willReturn([
                $this->getMockBuilder(DocumentSnapshot::class)->disableOriginalConstructor()->getMock()
            ]);

        $this->setUpClientQueryWithLimit($filters, 1, $documents);

        $this->assertNotNull($this->repository->findOneBy($filters));
    }

    public function testFindOneById_withEmptyResult()
    {
        $id = random_bytes(8);
        $documents = $this->getMockBuilder(QuerySnapshot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEmpty'])
            ->getMock();
        $documents->expects($this->any())
            ->method('isEmpty')
            ->willReturn(true);

        $this->collection->expects($this->once())
            ->method('where')
            ->with('id', '=', $id)
            ->willReturn($this->collection);
        $this->collection->expects($this->once())
            ->method('documents')
            ->willReturn($documents);

        $this->assertNull($this->repository->findOneById($id));
    }

    public function testFindOneById_withResult()
    {
        $id = random_bytes(8);
        $snapshots = [
            $this->getMockBuilder(DocumentSnapshot::class)->disableOriginalConstructor()->getMock()
        ];

        $documents = $this->getMockBuilder(QuerySnapshot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEmpty', 'rows'])
            ->getMock();
        $documents->expects($this->any())
            ->method('isEmpty')
            ->willReturn(false);
        $documents->expects($this->any())
            ->method('rows')
            ->willReturn($snapshots);

        $this->collection->expects($this->once())
            ->method('where')
            ->with('id', '=', $id)
            ->willReturn($this->collection);
        $this->collection->expects($this->once())
            ->method('documents')
            ->willReturn($documents);

        $this->assertNotNull($this->repository->findOneById($id));
    }

    public function testPersist()
    {
        $entity = new TestEntity();

        $document = $this->getMockBuilder(DocumentReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $document->expects($this->once())
            ->method('set')
            ->withAnyParameters()
            ->willReturn($entity);
        $this->setUpCollectionReturningDocument($document);

        $this->assertSame($this->repository->persist($entity), $entity);
        $this->assertEquals(36, strlen($entity->getId()));
    }

    public function testPersist_withEntityHavingId()
    {
        $entity = (new TestEntity())
            ->setId(Uuid::v4())
            ->setName('Hello Wolrd');

        $document = $this->getMockBuilder(DocumentReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $document->expects($this->once())
            ->method('set')
            ->with($this->callback(function ($arg) use ($entity) {
                return is_array($arg) && array_key_exists('id', $arg) && $arg['id'] === $entity->getId();
            }))
            ->willReturn($entity);
        $this->setUpCollectionReturningDocument($document);

        $this->assertSame($this->repository->persist($entity), $entity);
        $this->assertEquals(36, strlen($entity->getId()));
    }

    ### PRIVATE ###

    private function setUpClientQuery(array $filters, mixed $documents)
    {
        $this->setUpClientQueryWithLimit($filters, 0, $documents);
    }

    private function setUpClientQueryWithLimit(array $filters, int $limit, mixed $documents)
    {
        $this->collection->expects($this->once())
            ->method('where')
            ->with($filters)
            ->willReturn($this->collection);
        if ($limit > 0) {
            $this->collection->expects($this->once())
                ->method('limit')
                ->with($limit)
                ->willReturn($this->collection);
        }
        $this->collection->expects($this->any())
            ->method('documents')
            ->willReturn($documents);
    }

    private function setUpCollectionReturningDocument(mixed $document)
    {
        $this->collection->expects($this->once())
            ->method('document')
            ->with($this->callback(function ($arg) {
                return strlen($arg) === 36;
            }))
            ->willReturn($document);
    }
}

class TestRepository extends Repository
{
    public const COLLECTION = 'A7AF6CEC47CF';

    protected function collection(): string
    {
        return self::COLLECTION;
    }

    protected function hydrate(DocumentSnapshot $document): mixed
    {
        return new stdClass();
    }
}

class TestEntity extends Entity
{
    protected string $name = '';
}
