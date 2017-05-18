<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\DrawBundle\Serializer\GroupHierarchy;
use Draw\Swagger\Schema\Schema;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\BodyParameter;
use Draw\Swagger\Schema\Operation;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ParamConverterExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var GroupHierarchy
     */
    private $groupHierarchy;

    public function __construct(Reader $reader, GroupHierarchy $groupHierarchy)
    {
        $this->reader = $reader;
        $this->groupHierarchy = $groupHierarchy;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        if (!$this->getParamConverter($source)) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     * @throws ExtractionImpossibleException
     * @return void
     */
    public function extract($method, $operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $paramConverter = $this->getParamConverter($method);
        if (is_null($type = $paramConverter->getClass())) {
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->getName() != $paramConverter->getName()) {
                    continue;
                }
                $type = $parameter->getClass()->getName();
            }
        }

        $operation->parameters[] = $parameter = new BodyParameter();

        $subContext = $extractionContext->createSubContext();

        $subContext->setParameter('direction', 'in');

        if ($serializationGroups = $this->getDeserializationGroups($paramConverter)) {
            $serializationGroups = $this->groupHierarchy->getReachableGroups($serializationGroups);
        }

        $subContext->setParameter(
            'validation-groups',
            $validationGroups = $this->getValidationGroups($paramConverter)
        );

        $modelContext = $subContext->getParameter('in-model-context', array());

        if ($serializationGroups) {

            $modelContext['serializer-groups'] = $serializationGroups;
        }

        if ($validationGroups) {
            $modelContext['validation-groups'] = $validationGroups;
        }

        $subContext->setParameter('in-model-context', $modelContext);

        $subContext->getSwagger()->extract(
            $type,
            $parameter->schema = new Schema(),
            $subContext
        );

        $parameter->schema->type = "object";
    }

    private function getDeserializationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();

        if (isset($options['deserializationContext']['groups'])) {
            return $options['deserializationContext']['groups'];
        }

        return  array(GroupsExclusionStrategy::DEFAULT_GROUP);
    }

    private function getValidationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if (isset($options['validator']['groups'])) {
            return $options['validator']['groups'];
        }

        return null;
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter
     */
    private function getParamConverter(ReflectionMethod $reflectionMethod)
    {
        $converters = array_filter(
            $this->reader->getMethodAnnotations($reflectionMethod),
            function ($converter) {
                if (!$converter instanceof ParamConverter) {
                    return false;
                }

                return $converter->getConverter() == "fos_rest.request_body";
            }
        );

        return reset($converters);
    }
}
