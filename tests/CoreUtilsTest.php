<?php

use App\CoreUtils;
use PHPUnit\Framework\TestCase;

class CoreUtilsTest extends TestCase {
	function testQueryStringAssoc(){
		$result = CoreUtils::queryStringAssoc('?a=b&c=1');
		self::assertEquals([
			'a' => 'b',
			'c' => 1,
		], $result);
	}

	function testAposEncode(){
		$result = CoreUtils::aposEncode("No Man's Lie");
		self::assertEquals("No Man&apos;s Lie", $result);
		$result = CoreUtils::aposEncode('"implying"');
		self::assertEquals("&quot;implying&quot;", $result);
	}

	function testEscapeHTML(){
		$result = CoreUtils::escapeHTML("<script>alert('XSS')</script>");
		self::assertEquals("&lt;script&gt;alert('XSS')&lt;/script&gt;", $result);
		$result = CoreUtils::escapeHTML('<');
		self::assertEquals("&lt;", $result);
		$result = CoreUtils::escapeHTML('>');
		self::assertEquals("&gt;", $result);
	}

	function testNotice(){
		$exception = false;
		try {
			CoreUtils::notice('invalid type','asd');
		}
		catch(Exception $e){ $exception = true; }
		self::assertTrue($exception, 'Invalid notice type must throw exception');

		$result = CoreUtils::notice('info','text');
		self::assertEquals("<div class='notice info'><p>text</p></div>", $result);

		$result = CoreUtils::notice('info','title','text');
		self::assertEquals("<div class='notice info'><label>title</label><p>text</p></div>", $result);

		$result = CoreUtils::notice('info','title',"mutliline\n\nnotice");
		self::assertEquals("<div class='notice info'><label>title</label><p>mutliline</p><p>notice</p></div>", $result);
	}

	function testPad(){
		$result = CoreUtils::pad(1);
		self::assertEquals('01',$result);
		$result = CoreUtils::pad(10);
		self::assertEquals('10',$result);
	}

	function testCapitalize(){
		$result = CoreUtils::capitalize('apple pie');
		self::assertEquals('Apple pie', $result);
		$result = CoreUtils::capitalize('apple pie', true);
		self::assertEquals('Apple Pie', $result);
		$result = CoreUtils::capitalize('APPLE PIE', true);
		self::assertEquals('Apple Pie', $result);
		$result = CoreUtils::capitalize('aPpLe pIe', true);
		self::assertEquals('Apple Pie', $result);
	}

	function testGetMaxUploadSize(){
		$result = CoreUtils::getMaxUploadSize(['4M','10M']);
		self::assertEquals('4 MB', $result);
		$result = CoreUtils::getMaxUploadSize(['4k','4k']);
		self::assertEquals('4 KB', $result);
		$result = CoreUtils::getMaxUploadSize(['5G','5M']);
		self::assertEquals('5 MB', $result);
	}

	function testExportVars(){
		$result = CoreUtils::exportVars([
			'a' => 1,
			'reg' => new \App\RegExp('^ab?c$','gui'),
			'b' => true,
			's' => 'string',
		]);
		/** @noinspection all */
		self::assertEquals('<script>var a=1,reg=/^ab?c$/gi,b=true,s="string"</script>', $result);
	}

	function testSanitizeHtml(){
		$result = CoreUtils::sanitizeHtml('<script>alert("XSS")</script><a href="/#hax">Click me</a>');
		self::assertEquals('&lt;script&gt;alert("XSS")&lt;/script&gt;&lt;a href="/#hax"&gt;Click me&lt;/a&gt;',$result);
		$result = CoreUtils::sanitizeHtml('Text<b>Bold</b><i>Italic</i><strong>Strong</strong><em>Emphasis</em>Text');
		self::assertEquals('Text<b>Bold</b><i>Italic</i><strong>Strong</strong><em>Emphasis</em>Text',$result);
	}

	function testArrayToNaturalString(){
		$result = CoreUtils::arrayToNaturalString([1]);
		self::assertEquals('1', $result);
		$result = CoreUtils::arrayToNaturalString([1,2]);
		self::assertEquals('1 and 2', $result);
		$result = CoreUtils::arrayToNaturalString([1,2,3]);
		self::assertEquals('1, 2 and 3', $result);
		$result = CoreUtils::arrayToNaturalString([1,2,3,4]);
		self::assertEquals('1, 2, 3 and 4', $result);
	}

	function testCheckStringValidity(){
		$result = CoreUtils::checkStringValidity('Oh my~!', 'Exclamation', '[^A-Za-z!\s]', true);
		self::assertEquals("Exclamation (Oh my~!) contains an invalid character: ~", $result);
		$result = CoreUtils::checkStringValidity('A_*cbe>#', 'String', '[^A-Za-z]', true);
		self::assertEquals("String (A_*cbe&gt;#) contains the following invalid characters: _, *, &gt; and #", $result);
	}

	function testPosess(){
		$result = CoreUtils::posess('David');
		self::assertEquals("David's", $result);
		$result = CoreUtils::posess('applications');
		self::assertEquals("applications'", $result);
	}

	function testMakePlural(){
		$result = CoreUtils::makePlural('apple',2);
		self::assertEquals('apples', $result);
		$result = CoreUtils::makePlural('apple',1);
		self::assertEquals('apple', $result);
		$result = CoreUtils::makePlural('apple',2,true);
		self::assertEquals('2 apples', $result);
		$result = CoreUtils::makePlural('apple',1,true);
		self::assertEquals('1 apple', $result);
		$result = CoreUtils::makePlural('staff member',2,true);
		self::assertEquals('2 staff members', $result);
	}

	function testBrowserNameToClass(){
		$result = CoreUtils::browserNameToClass('Chrome');
		self::assertEquals('chrome', $result);
		$result = CoreUtils::browserNameToClass('Edge');
		self::assertEquals('edge', $result);
		$result = CoreUtils::browserNameToClass('Firefox');
		self::assertEquals('firefox', $result);
		$result = CoreUtils::browserNameToClass('Internet Explorer');
		self::assertEquals('internetexplorer', $result);
		$result = CoreUtils::browserNameToClass('IE Mobile');
		self::assertEquals('iemobile', $result);
		$result = CoreUtils::browserNameToClass('Opera');
		self::assertEquals('opera', $result);
		$result = CoreUtils::browserNameToClass('Opera Mini');
		self::assertEquals('operamini', $result);
		$result = CoreUtils::browserNameToClass('Safari');
		self::assertEquals('safari', $result);
		$result = CoreUtils::browserNameToClass('Vivaldi');
		self::assertEquals('vivaldi', $result);
	}

	function testTrim(){
		$result = CoreUtils::trim('I    like    spaces');
		self::assertEquals('I like spaces', $result);
	}

	function testAverage(){
		$result = CoreUtils::average(1);
		self::assertEquals(1, $result);
		$result = CoreUtils::average(1,2);
		self::assertEquals(1.5, $result);
		$result = CoreUtils::average(1,2,3);
		self::assertEquals(2, $result);
	}

	function testHex2Rgb(){
		$result = CoreUtils::hex2Rgb("#AFDC34");
		self::assertEquals([175,220,52],$result);
		$result = CoreUtils::hex2Rgb("#000000");
		self::assertEquals([0,0,0],$result);
		$result = CoreUtils::hex2Rgb("#ffffff");
		self::assertEquals([255,255,255],$result);
	}

	function testNormalizeStashID(){
		$result = CoreUtils::nomralizeStashID('76dfg312kla');
		self::assertEquals('076dfg312kla', $result);
		$result = CoreUtils::nomralizeStashID('76dfg312kla4');
		self::assertEquals('76dfg312kla4', $result);
		$result = CoreUtils::nomralizeStashID('000adfg312kla4');
		self::assertEquals('0adfg312kla4', $result);
	}

	function testCutoff(){
		$result = CoreUtils::cutoff('This is a long string', 10);
		self::assertEquals(10, CoreUtils::length($result));
		self::assertEquals('This is a…', $result);
	}

	function testYiq(){
		$result = CoreUtils::yiq("#ffffff");
		self::assertEquals(255, $result);
		$result = CoreUtils::yiq("#808080");
		self::assertEquals(128, $result);
		$result = CoreUtils::yiq("#000000");
		self::assertEquals(0, $result);
	}

	function testSet(){
		$array = [];
		CoreUtils::set($array, 'key', 'value');
		self::assertArrayHasKey('key', $array);
		self::assertEquals('value', $array['key']);

		$object = new stdClass();
		CoreUtils::set($object, 'key', 'value');
		self::assertObjectHasAttribute('key', $object);
		self::assertEquals('value', $object->key);
	}
}
