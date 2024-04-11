<?php

namespace Vundb\FirestoreBundle\Tests\Repository;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\QuerySnapshot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Vundb\FirestoreBundle\Repository\AbstractRepository;

class AbstractRepositoryTest extends TestCase
{
    private MockObject $client;
    private MockObject $collection;
    private MockObject|TestRepository $repository;

    protected function setUp(): void
    {
        $this->collection = $this->getMockBuilder(CollectionReference::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'limit', 'documents'])
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
}

class TestRepository extends AbstractRepository
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
