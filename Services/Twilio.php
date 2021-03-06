<?php

require_once 'Services/Twilio/Http.php';
require_once 'Services/Twilio/Page.php';
require_once 'Services/Twilio/DataProxy.php';
require_once 'Services/Twilio/CachingDataProxy.php';
require_once 'Services/Twilio/ArrayDataProxy.php';
require_once 'Services/Twilio/Resource.php';
require_once 'Services/Twilio/ListResource.php';
require_once 'Services/Twilio/InstanceResource.php';
require_once 'Services/Twilio/Resources.php';
require_once 'Services/Twilio/PartialApplicationHelper.php';

/**
 * Twilio API client interface.
 *
 * @category Services
 * @package  Services_Twilio
 * @author   Neuman Vong <neuman@twilio.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://pear.php.net/package/Services_Twilio
 */
class Services_Twilio extends Services_Twilio_Resource
{
  protected $http;
  protected $version;

  /**
   * Constructor.
   *
   * @param string               $sid      Account SID
   * @param string               $token    Account auth token
   * @param string               $version  API version
   * @param Services_Twilio_Http $_http    A HTTP client
   */
  public function __construct(
    $sid,
    $token,
    $version = '2010-04-01',
    $_http = null
  ) {
    $this->version = $version;
    $this->http = (null === $_http)
      ? new Services_Twilio_Http("https://$sid:$token@api.twilio.com")
      : $_http;
    $this->accounts = new Services_Twilio_Accounts($this);
    $this->account = $this->accounts->get($sid);
  }

  /**
   * GET the resource at the specified path.
   *
   * @param string $path   Path to the resource
   * @param array  $params Query string parameters
   *
   * @return object The object representation of the resource
   */
  public function retrieveData($path, array $params = array())
  {
    $path = "/$this->version/$path.json";
    return empty($params)
      ? $this->_processResponse($this->http->get($path))
      : $this->_processResponse(
        $this->http->get("$path?" . http_build_query($params, '', '&'))
      );
  }

  /**
   * POST to the resource at the specified path.
   *
   * @param string $path   Path to the resource
   * @param array  $params Query string parameters
   *
   * @return object The object representation of the resource
   */
  public function createData($path, array $params = array())
  {
    $path = "/$this->version/$path.json";
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
    return empty($params)
      ? $this->_processResponse($this->http->post($path, $headers))
      : $this->_processResponse(
        $this->http->post(
          $path,
          $headers,
          http_build_query($params, '', '&')
        )
      );
  }

  /**
   * Convert the JSON encoded resource into a PHP object.
   *
   * @param array $response 3-tuple containing status, headers, and body
   *
   * @return object PHP object decoded from JSON
   */
  private function _processResponse($response)
  {
    list($status, $headers, $body) = $response;
    if (200 <= $status && $status < 300) {
      if ($headers['content-type'] == 'application/json') {
        $object = json_decode($body);
        return $object;
      }
      throw new ErrorException('not json');
    }
    throw new ErrorException("$status: $body");
  }
}
// vim: ai ts=2 sw=2 noet sta
