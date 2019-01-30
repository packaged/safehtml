<?php
namespace Packaged\Tests\SafeHtml;

use Packaged\SafeHtml\SafeHtml;
use PHPUnit\Framework\TestCase;

class SafeHtmlTest extends TestCase
{
  public function testAppend()
  {
    $html = new SafeHtml('cat');
    $this->assertEquals('cat', $html->getContent());
    $html->append('dog');
    $this->assertEquals('catdog', $html->getContent());
    $this->assertEquals('catdog', (string)$html);
  }

  public function testEscape()
  {
    $this->assertEquals('test', SafeHtml::escape('test'));
    $this->assertEquals(
      '&lt;a href=&quot;&quot;&gt;',
      SafeHtml::escape('<a href="">')
    );

    $escaped = new SafeHtml('escapedContent');
    $this->assertSame($escaped, SafeHtml::escape($escaped));

    $mock = new MockSafeHtmlProducer();
    $mock->setContent($escaped);
    $this->assertSame($escaped, SafeHtml::escape($mock));

    $theGreateEscape = new SafeHtml('escapedGreatness');
    $mock->setContent([$escaped, $theGreateEscape]);
    $this->assertEquals(
      'escapedContent escapedGreatness',
      SafeHtml::escape($mock->getContent())
    );
    $this->assertEquals(
      'escapedContent,escapedGreatness',
      SafeHtml::escape($mock->getContent(), ',')
    );

    $mockBird = new MockSafeHtmlProducer();
    $mockBird->setContent('second coming');
    $mock->setContent($mockBird);
    $this->assertEquals('second coming', SafeHtml::escape($mock));

    $this->expectException(\Exception::class);
    SafeHtml::escape(new \stdClass());
  }

  public function testEscapeGlue()
  {
    $this->assertEquals('cat dog', SafeHtml::escape(['cat', 'dog']));
    $this->assertEquals('cat,dog', SafeHtml::escape(['cat', 'dog'], ','));
  }

  public function testURIEscape()
  {
    $this->assertEquals(
      '%2B/%20%3F%23%26%3A%21xyz%25',
      SafeHtml::escapeUri('+/ ?#&:!xyz%')
    );
  }

  public function testURIPathComponentEscape()
  {
    $this->assertEquals(
      'a%252Fb',
      SafeHtml::escapeUriPathComponent('a/b')
    );

    $str = '';
    for($ii = 0; $ii <= 255; $ii++)
    {
      $str .= chr($ii);
    }

    $this->assertEquals(
      $str,
      SafeHtml::unescapeUriPathComponent(
        rawurldecode( // Simulates webserver.
          SafeHtml::escapeUriPathComponent($str)
        )
      )
    );
  }
}
