<?php

namespace Codeception\Module;

use JsonSchema\Uri\UriRetriever;

/**
* Json schema module for codeception
*/
class JsonSchema extends \Codeception\Module
{
    /**
    *  Validate response by json schema
    *
    *  @param string $schema path to json schema file
    */
    public function canSeeResponseIsValidOnSchemaFile($schema)
    {
        $schemaPath = realpath($schema);
       
        $retriever = new \JsonSchema\Uri\UriRetriever();
        $schema = $retriever->retrieve('file://' .$schemaPath);

        $refResolver = new \JsonSchema\RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . $schemaPath);

        $response = $this->getModule('REST')->response;

        $validator = new \JsonSchema\Validator();
        $validator->setUriRetriever(new UriRetriever());
        $validator->check(json_decode($response), $schema);

        $message = '';
        $isValid = $validator->isValid(); 
        if (! $isValid) {
            $message = "JSON does not validate. Violations:\n";
            foreach ($validator->getErrors() as $error) {
                $message .= $error['property']." ".$error['message'].PHP_EOL;
            }
        }

        $this->assertTrue($isValid, $message);
    }
}
