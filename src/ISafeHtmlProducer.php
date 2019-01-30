<?php
namespace Packaged\SafeHtml;

interface ISafeHtmlProducer
{
  /**
   * @return SafeHtml
   */
  public function produceSafeHTML(): SafeHtml;
}
