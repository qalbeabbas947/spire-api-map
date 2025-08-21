<?php

///https://github.com/mghoneimy/php-graphql-client/tree/master/src
require 'vendor/autoload.php';

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\Variable;

// Initialize client
$client = new Client(
    'https://api.spire.com/graphql',
    ['Authorization' => 'Bearer 3caca44c971d4DAC9C2b977c30C1512A']
);

try {
    

    // Build dimensions query
    $dimensions = (new Query('dimensions'))
        ->setSelectionSet(['length', 'width']);

    // Build validated query
    $validated = (new Query('validated'))
        ->setSelectionSet([
            'name',
            'imo',
            'callsign',
            'shipType',
            $dimensions
        ]);

    // Build staticData query
    $staticData = (new Query('staticData'))
        ->setSelectionSet([
            'name',
            'imo',
            'mmsi',
            'aisClass',
            'callsign',
            'flag',
            'shipType',
            'updateTimestamp',
            $dimensions,
            $validated
        ]);

    // Build the main query with proper TimeRange input
    $query = (new Query('vessels'))
        ->setSelectionSet([
            (new Query('nodes'))
                ->setSelectionSet([
                    'id',
                    $staticData
                ])
        ]);

    // For debugging: view the generated query
    // echo $query->__toString();

    $results = $client->runQuery($query);
    $data = $results->getData();
    echo '<pre>';
    print_r($data->vessels);
    echo '</pre>';

} catch (QueryError $e) {
    echo "GraphQL Error: ";
    print_r($e->getErrorDetails());
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}