<?php
namespace Packaged\SafeHtml;

use Packaged\Helpers\Strings;

class SafeHtml
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
  public function append($html /* , ... */)
  {
    foreach(func_get_args() as $html)
    {
      $this->content .= self::escape($html);
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

    if(is_array($input))
    {
      return new static(
        implode(
          $arrayGlue,
          array_map(function ($input) use ($arrayGlue) { return SafeHtml::escape($input, $arrayGlue); }, $input)
        )
      );
    }

    if($input instanceof ISafeHtmlProducer)
    {
      return $input->produceSafeHTML();
    }

    try
    {
      Strings::stringable($input);
      return new static(\htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    catch(\Exception $ex)
    {
      $class = get_class($input);
      throw new \Exception(
        "Object (of class '{$class}') implements " .
        "ISafeHTMLProducer but did not return anything " .
        "renderable from produceSafeHTML()."
      );
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
   * @param   string $string Some string.
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
   * @param   string $string Some string.
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
}
