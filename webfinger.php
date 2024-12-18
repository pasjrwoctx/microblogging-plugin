<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all request parameters
$log_file = '/path/to/php-error.log'; // Ensure this path is writable
file_put_contents($log_file, 'Request received: ' . json_encode($_GET) . PHP_EOL, FILE_APPEND);

if (isset($_GET['resource'])) {
    $resource = $_GET['resource'];
    file_put_contents($log_file, 'Resource parameter: ' . $resource . PHP_EOL, FILE_APPEND);

    $response = array(
        "subject" => $resource,
        "aliases" => array(
            "acct:Your_Account",
            "https://Your_Site/author/YOU/",
            "https://Your_Site/?author=1",
            "https://Your_Site/@You"
        ),
        "links" => array(
            array(
                "rel" => "http://webfinger.net/rel/profile-page",
                "href" => "https://Your_Site/author/You/",
                "type" => "text/html"
            ),
            array(
                "rel" => "http://webfinger.net/rel/avatar",
                "href" => "https://www.gravatar.com/avatar/64afefca3ab61ada27ee12b2d2bdcd61?s=96&#038;r=g&#038;d=mm"
            ),
            array(
                "rel" => "self",
                "type" => "application/activity+json",
                "href" => "https://Your_Site/?author=1"
            ),
            array(
                "rel" => "http://ostatus.org/schema/1.0/subscribe",
                "template" => "https://Your_Site/wp-json/activitypub/1.0/interactions?uri={uri}"
            ),
            array(
                "rel" => "http://nodeinfo.diaspora.software/ns/schema/2.1",
                "href" => "https://Your_Site/wp-json/nodeinfo/2.1"
            ),
            array(
                "rel" => "http://nodeinfo.diaspora.software/ns/schema/2.0",
                "href" => "https://Your_Site/wp-json/nodeinfo/2.0"
            ),
            array(
                "rel" => "http://nodeinfo.diaspora.software/ns/schema/1.1",
                "href" => "https://Your_Site/wp-json/nodeinfo/1.1"
            ),
            array(
                "rel" => "http://nodeinfo.diaspora.software/ns/schema/1.0",
                "href" => "https://Your_Site/wp-json/nodeinfo/1.0"
            )
        )
            ),
            array(
                 "rel": "http://activitypub.rocks/terms#publicKey",
                 "href": "https://Your_Site/@You#public-key"
            )
        )
            ),
            array(
                 "rel": "http://activitypub.rocks/terms#actor",
                 "type": "application/activity+json",
                 "href": "https://Your_Site/@You"
            )
        )
    );
    

    header('Content-Type: application/jrd+json');
    echo json_encode($response);
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(array("error" => "Missing parameter: resource"));
    file_put_contents($log_file, 'Resource parameter is missing.' . PHP_EOL, FILE_APPEND);
}
?>
