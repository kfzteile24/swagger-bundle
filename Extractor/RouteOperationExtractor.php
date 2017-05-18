<?php

namespace Draw\SwaggerBundle\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\PathParameter;
use Symfony\Component\Routing\Route;

class RouteOperationExtractor implements ExtractorInterface
{

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
        if (!$source instanceof Route) {
            return false;
        }

        if (!$type instanceof Operation) {
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
     * @param Route $source
     * @param Operation $type
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $type, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach($source->compile()->getPathVariables() as $pathVariable) {
            foreach($type->parameters as $parameter) {
                if($parameter->name == $pathVariable) {
                    continue 2;
                }
            }
            $pathParameter = new PathParameter();
            $pathParameter->name = $pathVariable;
            $pathParameter->type = "string";
            $type->parameters[] = $pathParameter;
        }
    }
}
