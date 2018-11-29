<?php
namespace Packaged\SafeHtml;

interface ISafeHtmlProducer
{
  /**
   * @return SafeHtml|SafeHtml[]
   */
  public function produceSafeHTML();
}
