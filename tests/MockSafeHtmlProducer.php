<?php
namespace Packaged\Tests\SafeHtml;

use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;

class MockSafeHtmlProducer implements ISafeHtmlProducer
{
  protected $_content;

  public function setContent($content)
  {
    $this->_content = $content;
  }

  public function getContent()
  {
    return $this->_content;
  }

  /**
   * @return SafeHtml
   * @throws \Exception
   */
  public function produceSafeHTML(): SafeHtml
  {
    return SafeHtml::escape($this->_content);
  }
}
