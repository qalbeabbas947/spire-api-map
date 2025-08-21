<?php
/**
 * Plugin Name:       Coastalynk Map
 * Description:       Displays a map for the end user.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-block
 *
 * @package CreateBlock
 */
require 'vendor/autoload.php';

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\RawObject;

add_shortcode( 'show_vessels', 'wpdocs_show_vessels' );
function wpdocs_show_vessels( $atts ) {
	
    ob_start();
    echo '<div id="coastlynkmap" style="width: 1024px; height: 800px;"></div>';

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

add_action( 'wp_ajax_nopriv_coastlynk_get_vessels', 'coastlynk_get_vessels_for_map' );
add_action( 'wp_ajax_coastlynk_get_vessels', 'coastlynk_get_vessels_for_map' );

/**
 * Enqueue script with script.aculo.us as a dependency.
 */
function coastlynk_get_vessels_for_map() {
    $client = new Client(
        'https://api.spire.com/graphql',
        ['Authorization' => 'Bearer 3caca44c971d4DAC9C2b977c30C1512A']
    );

    try {
        $limit = 1000;
        $afterCursor = '';
        $args = ['first' => $limit];
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

        $results = $client->runQuery($gql, true);
        //print_r($results->getData());

        echo json_encode($results->getData());


    } catch (QueryError $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Enqueue script with script.aculo.us as a dependency.
 */
function my_scripts_method() {
    wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array( 'jquery' ) );
    wp_enqueue_script( 'markercluster', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js', array( 'jquery' ) );

	wp_enqueue_script( 'coastlynk-map-js', plugins_url( '/js/coastlynk.js' , __FILE__ ), array( 'jquery' ), time(), true );
    wp_localize_script( 'coastlynk-map-js', 'COSTALUNKVARS', [          
                'ajaxURL' => admin_url( 'admin-ajax.php' )
            ] );
    wp_enqueue_style( 'coastlynk-map-css', plugins_url( '/css/coastlynk.css' , __FILE__ ), array(), time() );
    wp_enqueue_style( 'coastlynk-map-leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), time() );
}
add_action( 'wp_enqueue_scripts', 'my_scripts_method' );