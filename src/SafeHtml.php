<?php
namespace Packaged\SafeHtml;

use Exception;
use Packaged\Helpers\Strings;
use function get_class;
use function htmlspecialchars;
use function is_array;
use const ENT_QUOTES;

class SafeHtml implements ISafeHtmlProducer
{
  private $content;

  public function __construct($content)
  {
    $this->content = (string)$content;
  }

  public static function __callStatic($name, $arguments)
  {
    switch($name)
    {
      case 'escapeUri':
        return SafeHtmlEscape::uri($arguments[0]);
      case 'escapeUriPathComponent':
        return SafeHtmlEscape::uriPathComponent($arguments[0]);
      case 'unescapeUriPathComponent':
        return SafeHtmlEscape::unescapeUriPathComponent($arguments[0]);
    }
    return null;
  }

  public function __toString()
  {
    return $this->content;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function produceSafeHTML(): SafeHtml
  {
    return $this;
  }

  /**
   * @param $html
   *
   * @return $this
   * @throws Exception
   */
  public function append(...$html)
  {
    foreach($html as $toAppend)
    {
      $this->content .= self::escape($toAppend)->getContent();
    }
    return $this;
  }

  /**
   * @param        $input
   * @param string $arrayGlue
   *
   * @return SafeHtml
   * @throws Exception
   */
  public static function escape($input, $arrayGlue = ' ')
  {
    if($input === null)
    {
      return new static('');
    }

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
        if($iv === null)
        {
          continue;
        }
        $return .= $arrayGlue . static::escape($iv, $arrayGlue)->getContent();
      }
      return new static(substr($return, strlen($arrayGlue)));
    }

    try
    {
      Strings::stringable($input);
      return new static(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    catch(Exception $ex)
    {
      throw new Exception(
        "Object (of class '" . ($input ? get_class($input) : 'null') . "') cannot be converted to SafeHtml."
      );
    }
  }
}
