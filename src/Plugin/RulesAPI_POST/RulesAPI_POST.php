<?php

namespace Drupal\RulesAPI_POST\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides "POST" rules action.
 *
 * @RulesAction(
 *   id = "RulesAPI_post",
 *   label = @Translation("Rules Action for API POST"),
 *   category = @Translation("Data"),
 *   context = {
 *     "url" = @ContextDefinition("string",
 *       label = @Translation("URL"),
 *       description = @Translation("The Url address where to post, get and delete request send. <br><b>Example:</b> https://example.com/node?_format=hal_json "),
 *       required = TRUE,
 *       multiple = TRUE,
 *     ),
 *     "linkurl" = @ContextDefinition("string",
 *       label = @Translation("Link URL"),
 *       description = @Translation("The service URL.<br> <b>Example:</b> https://example.com/rest/type/node/article "),
 *       required = TRUE,
 *       multiple = TRUE,
 *     ),
 *     "apiuser" = @ContextDefinition("string",
 *       label = @Translation("API User Name"),
 *       description = @Translation("Username for API Access"),
 *       required = FALSE,
 *      ),
 *     "apipass" = @ContextDefinition("string",
 *       label = @Translation("API User Password"),
 *       description = @Translation("Password for API Access"),
 *       required = FALSE,
 *      ),
 *     "apitoken" = @ContextDefinition("string",
 *       label = @Translation("API Session Token"),
 *       description = @Translation("Session Token for API Access"),
 *       required = FALSE,
 *      ),
 *     "user_id" = @ContextDefinition("string",
 *       label = @Translation("User ID"),
 *       description = @Translation("This custom field needs to be added to your Content Type"),
 *       required = FALSE,
 *      ),
 *     "headers" = @ContextDefinition("string",
 *       label = @Translation("Headers"),
 *       description = @Translation("Request headers to send as 'name: value' pairs, one per line (e.g., Accept: text/plain). See <a href='https://www.wikipedia.org/wiki/List_of_HTTP_header_fields'>wikipedia.org/wiki/List_of_HTTP_header_fields</a> for more information."),
 *       required = FALSE,
 *      ),
 *     "method" = @ContextDefinition("string",
 *       label = @Translation("Method"),
 *       description = @Translation("The HTTP request methods like'HEAD','POST','PUT','DELETE','TRACE','OPTIONS','CONNECT','PATCH' etc."),
 *       required = FALSE,
 *     ),
 *     "data" = @ContextDefinition("string",
 *       label = @Translation("Data"),
 *       description = @Translation("The request body, formatter as 'param=value&param=value&...' or one 'param=value' per line.."),
 *       required = FALSE,
 *       multiple = TRUE,
 *       assignment_restriction = "data",
 *     ),
 *     "max_redirects" = @ContextDefinition("integer",
 *       label = @Translation("Max Redirect"),
 *       description = @Translation("How many times a redirect may be followed."),
 *       default_value = 3,
 *       required = FALSE,
 *       assignment_restriction = "input",
 *     ),
 *     "timeout" = @ContextDefinition("float",
 *       label = @Translation("Timeout"),
 *       description = @Translation("The maximum number of seconds the request may take.."),
 *       default_value = 30,
 *       required = FALSE,
 *     ),
 *   },
 *   provides = {
 *     "http_response" = @ContextDefinition("string",
 *       label = @Translation("HTTP data")
 *     )
 *   }
 * )
 *
 * @todo: Define that message Context should be textarea comparing with textfield Subject
 * @todo: Add access callback information from Drupal 7.
 */
class RulesAPI_POST extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger for the rules channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a httpClient object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param GuzzleHttp\ClientInterface $http_client
   *   The guzzle http client instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('rest_post');
    $this->http_client = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('http_client')
    );
  }

  /**
   * Set up form variables
   *
   * @param string[] $url
   *   Url addresses HTTP request.
   * @param string[] $linkurl
   *   Link Url addresse for service
   * @param string[] $apiuser
   *   (optional) The User Name for API call
   * @param string[] $apipass
   *   (optional) The User Passord for API call
   * @param string[] $apiuser
   *   (optional) The Session Token for API call
   * @param string[] $user_id
   *   (optional) A temp value
   * @param string|null $headers
   *   (optional) Header information of HTTP Request.
   * @param string $method
   *   (optional) Method of HTTP request.
   * @param string|null $data
   *   (optional) Raw data of HTTP request.
   * @param int|null $maxRedirect
   *   (optional) Max redirect for HTTP request.
   * @param int|null $timeOut
   *   (optional) Time Out for HTTP request.
   */

//protected function doExecute () {
//  protected function doExecute(array $url, $headers, $method, $data = NULL, $maxRedirect = 3, $timeOut = 30) {
 protected function doExecute(array $url, $linkurl, $apiuser, $apipass, $apitoken, $user_id) {

  // Debug message
 drupal_set_message(t("API Content Created. V.2"), 'warning');

$serialized_entity = json_encode([
  'title' => [['value' => "API Created Content: Ticket"]],
// 'title' => [['value' => $tempvalue]],
  'type' => [['target_id' => 'article']],
// Set the value of a custom field
// For this next line to work, Article needs a custom user_id field.
// This is an example for filling custom fields
  'field_user_id' => [['value' => $user_id ]],
  '_links' => ['type' => [
        'href' => $linkurl[0]
  ]],
]) ;

$client = \Drupal::httpClient();
$url =$url[0];
$method = 'POST';
$options = [
  'auth' => [
    $apiuser,
    $apipass
  ],
'timeout' => '3',
'body' => $serialized_entity,
'headers' => [
'Content-Type' => 'application/hal+json',
'Accept' => 'application/hal+json',
'X-CSRF-Token' => $apitoken
    ],
];
try {
  $response = $client->request($method, $url, $options);
  $code = $response->getStatusCode();
  if ($code == 200) {
    $body = $response->getBody()->getContents();
    return $body;
  }
}
catch (RequestException $e) {
  watchdog_exception('rest_post', $e);
  }
 }
}
