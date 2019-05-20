<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Bridge\Laravel\Providers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use EoneoPay\Utils\AnnotationReader;
use LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers\ParamConverterProvider;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ManagerRegistryStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class ParamConverterProviderTest extends TestCase
{
    /**
     * Tests register
     *
     * @return void
     */
    public function testRegister(): void
    {
        $application = new ApplicationStub();
        $application->bind(ManagerRegistry::class, ManagerRegistryStub::class);

        // Register services
        (new ParamConverterProvider($application))->register();

        $services = [
            Reader::class => AnnotationReader::class,
            ClassMetadataFactoryInterface::class => ClassMetadataFactory::class,
            ControllerListener::class => ControllerListener::class,
            DoctrineParamConverter::class => DoctrineParamConverter::class,
            RequestBodyParamConverter::class => RequestBodyParamConverter::class,
            ParamConverterListener::class => ParamConverterListener::class,
            ParamConverterManager::class => ParamConverterManager::class,
            ValidatorInterface::class => ValidatorInterface::class
        ];

        foreach ($services as $abstract => $concrete) {
            // Ensure services are bound
            self::assertInstanceOf($concrete, $application->get($abstract));
        }
    }
}
