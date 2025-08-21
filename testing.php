<?php

require 'vendor/autoload.php';

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\RawObject;

// Initialize client
$client = new Client(
    'https://api.spire.com/graphql',
    ['Authorization' => 'Bearer 3caca44c971d4DAC9C2b977c30C1512A']
);

try {
    $afterCursor = null;
    $args = ['first' => 100];
    if ($afterCursor) {
        $args['after'] = $afterCursor;
    }

    $gql = (new Query('vessels'))
        ->setArguments($args)
        ->setSelectionSet([
            (new Query('pageInfo'))
                ->setSelectionSet([
                    'hasNextPage',
                    'endCursor'
                ]),
            (new Query('totalCount'))
                ->setSelectionSet([
                    'value',
                    'relation'
                ]),
            (new Query('nodes'))
                ->setSelectionSet([
                    'id',
                    (new Query('staticData'))
                        ->setSelectionSet([
                            'imo',
                            'mmsi',
                            'flag',
                            'name',
                            'callsign',
                            'timestamp',
                            'shipType',
                            (new Query('dimensions'))
                                ->setSelectionSet([
                                    'a',
                                    'b',
                                    'c',
                                    'd',
                                    'width',
                                    'length'
                                ])
                        ]),
                    (new Query('lastPositionUpdate'))
                        ->setSelectionSet([
                            'collectionType',
                            'course',
                            'heading',
                            'latitude',
                            'longitude',
                            'navigationalStatus',
                            'rot',
                            'speed',
                            'timestamp',
                            'updateTimestamp'
                        ]),
                    (new Query('currentVoyage'))
                        ->setSelectionSet([
                            'destination',
                            'draught',
                            'eta',
                            'timestamp'
                        ]),
                    (new Query('characteristics'))
                        ->setSelectionSet([
                            (new Query('basic'))
                                ->setSelectionSet([
                                    (new Query('capacity'))
                                        ->setSelectionSet([
                                            'deadweight',
                                            'grossTonnage'
                                        ]),
                                    (new Query('history'))
                                        ->setSelectionSet([
                                            'builtYear'
                                        ]),
                                    (new Query('vesselTypeAndTrading'))
                                        ->setSelectionSet([
                                            'vesselSubtype'
                                        ])
                                ])
                        ])
                ])
        ]);



    $results = $client->runQuery($gql);
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
