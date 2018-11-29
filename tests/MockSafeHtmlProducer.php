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

  /**
   * @return SafeHtml|SafeHtml[]
   */
  public function produceSafeHTML()
  {
    return $this->_content;
  }
}
