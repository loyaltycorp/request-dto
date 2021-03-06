<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\TestHelper;

use LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder;
use LoyaltyCorp\RequestHandlers\Builder\ObjectValidator;
use LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException;
use LoyaltyCorp\RequestHandlers\TestHelper\RequestObjectTestHelper;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestBooleanRequest;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\SerializerStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\TestHelper\RequestObjectTestHelper
 */
class RequestObjectTestHelperTest extends TestCase
{
    /**
     * Tests buildFailedRequest
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException
     */
    public function testBuildFailedRequest(): void
    {
        $object = new TestRequest();
        $expected = [
            'property' => ['Message']
        ];

        $helper = $this->getHelper($object, [[
            new ConstraintViolation('Message', '', [], '', 'property', '')
        ]]);

        $result = $helper->buildFailingRequest(TestRequest::class, '');

        static::assertSame($expected, $result);
    }

    /**
     * Tests buildFailedRequest
     *
     * @return void
     */
    public function testBuildFailedRequestNotFailing(): void
    {
        $object = new TestRequest();

        $helper = $this->getHelper($object, [[]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There were no validation errors.');

        $helper->buildFailingRequest(TestRequest::class, '');
    }

    /**
     * Test retrieving boolean properties from a request object.
     *
     * @return void
     */
    public function testGetBooleanProperties(): void
    {
        $object = new TestBooleanRequest(true);
        $expected = [
            'boolProperty' => true
        ];

        $helper = $this->getHelper($object);

        $properties = $helper->getRequestProperties($object);

        static::assertSame($expected, $properties);
    }

    /**
     * Tests unvalidated request creation
     *
     * @return void
     */
    public function testGetRequestProperties(): void
    {
        $object = new TestRequest('test');
        $expected = [
            'property' => 'test'
        ];

        $helper = $this->getHelper($object);

        $properties = $helper->getRequestProperties($object);

        static::assertSame($expected, $properties);
    }

    /**
     * Tests unvalidated request creation
     *
     * @return void
     */
    public function testUnvalidatedRequest(): void
    {
        $object = new TestRequest();

        $helper = $this->getHelper($object);

        $thing = $helper->buildUnvalidatedRequest(TestRequest::class, '');

        static::assertSame($object, $thing);
    }

    /**
     * Tests validated request creation
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException
     */
    public function testValidatedRequest(): void
    {
        $object = new TestRequest();

        $helper = $this->getHelper($object, [[]]);

        $thing = $helper->buildValidatedRequest(TestRequest::class, '');

        static::assertSame($object, $thing);
    }

    /**
     * Tests validated request creation when theres a validation failure
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException
     */
    public function testValidatedRequestWhenUnvalidated(): void
    {
        $object = new TestRequest();

        $helper = $this->getHelper($object, [[
            new ConstraintViolation('', '', [], '', '', '')
        ]]);

        $this->expectException(ValidationFailedException::class);

        $helper->buildValidatedRequest(TestRequest::class, '');
    }

    /**
     * Gets helper under test.
     *
     * @param \Throwable|object $object
     * @param \Symfony\Component\Validator\ConstraintViolation[][]|null $violations
     *
     * @return \LoyaltyCorp\RequestHandlers\TestHelper\RequestObjectTestHelper
     */
    private function getHelper($object = null, ?array $violations = null): RequestObjectTestHelper
    {
        $serializer = new SerializerStub($object);
        $innerValidator = new ValidatorStub($violations);
        $validator = new ObjectValidator($innerValidator);

        return new RequestObjectTestHelper(
            new ObjectBuilder($serializer, $validator),
            $serializer
        );
    }
}
