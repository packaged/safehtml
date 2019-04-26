<?php
namespace Packaged\SafeHtml;

use Packaged\Helpers\Strings;

class SafeHtml implements ISafeHtmlProducer
{
  private $content;

  public function __construct($content)
  {
    $this->content = (string)$content;
  }

  public function __toString()
  {
    return $this->content;
  }

  public function getContent()
  {
    return $this->content;
  }

  /**
   * @param $html
   *
   * @return $this
   * @throws \Exception
   */
  public function append(...$html)
  {
    foreach($html as $toAppend)
    {
      $this->content .= self::escape($toAppend);
    }
    return $this;
  }

  /**
   * @param        $input
   * @param string $arrayGlue
   *
   * @return SafeHtml
   * @throws \Exception
   */
  public static function escape($input, $arrayGlue = ' ')
  {
    if($input instanceof SafeHtml)
    {
      return $input;
    }

    if($input instanceof ISafeHtmlProducer)
    {
      return $input->produceSafeHTML();
    }

    if(is_array($input))
    {
      $return = '';
      foreach($input as $iv)
      {
        $return .= $arrayGlue . static::escape($iv, $arrayGlue)->getContent();
      }
      return new static(ltrim($return, $arrayGlue));
    }

    try
    {
      Strings::stringable($input);
      return new static(\htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    catch(\Exception $ex)
    {
      throw new \Exception("Object (of class '" . get_class($input) . "') cannot be converted to SafeHtml.");
    }
  }

  /**
   * Escape text for inclusion in a URI or a query parameter. Note that this
   * method does NOT escape '/', because "%2F" is invalid in paths and Apache
   * will automatically 404 the page if it's present. This will produce correct
   * (the URIs will work) and desirable (the URIs will be readable) behavior in
   * these cases:
   *
   *    '/path/?param='.phutil_escape_uri($string);         # OK: Query
   *    Parameter
   *    '/path/to/'.phutil_escape_uri($string);             # OK: URI Suffix
   *
   * It will potentially produce the WRONG behavior in this special case:
   *
   *    COUNTEREXAMPLE
   *    '/path/to/'.phutil_escape_uri($string).'/thing/';   # BAD: URI Infix
   *
   * In this case, any '/' characters in the string will not be escaped, so you
   * will not be able to distinguish between the string and the suffix (unless
   * you have more information, like you know the format of the suffix). For
   * infix URI components, use @{function:phutil_escape_uri_path_component}
   * instead.
   *
   * @param string $string Some string.
   *
   * @return  string  URI encoded string, except for '/'.
   */
  public static function escapeUri($string)
  {
    return str_replace('%2F', '/', rawurlencode($string));
  }

  /**
   * Escape text for inclusion as an infix URI substring. See discussion at
   * @{function:phutil_escape_uri}. This function covers an unusual special
   *                                case;
   * @{function:phutil_escape_uri} is usually the correct function to use.
   *
   * This function will escape a string into a format which is safe to put into
   * a URI path and which does not contain '/' so it can be correctly parsed
   * when embedded as a URI infix component.
   *
   * However, you MUST decode the string with
   * @{function:phutil_unescape_uri_path_component} before it can be used in
   *                                                the
   * application.
   *
   * @param string $string Some string.
   *
   * @return  string  URI encoded string that is safe for infix composition.
   */
  public static function escapeUriPathComponent($string)
  {
    return rawurlencode(rawurlencode($string));
  }

  /**
   * Unescape text that was escaped by
   * @{function:phutil_escape_uri_path_component}. See
   * @{function:phutil_escape_uri} for discussion.
   *
   * Note that this function is NOT the inverse of
   * @{function:phutil_escape_uri_path_component}! It undoes additional escaping
   * which is added to survive the implied unescaping performed by the webserver
   * when interpreting the request.
   *
   * @param string $string Some string emitted
   *                       from @{function:phutil_escape_uri_path_component} and
   *                       then accessed via a web server.
   *
   * @return string Original string.
   */
  public static function unescapeUriPathComponent($string)
  {
    return rawurldecode($string);
  }

  public function produceSafeHTML(): SafeHtml
  {
    return $this;
  }
}
