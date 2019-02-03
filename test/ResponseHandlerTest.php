<?php
declare(strict_types=1);

namespace Rest\RestCountries;

use PHPUnit\Framework\TestCase;

final class ResponseHandlerTest extends TestCase
{

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }


    /**
     * @return void
     * @throws \Exception
     */
    public function testIsClass()
    {
        $this->assertInstanceOf(ResponseHandler::class, new ResponseHandler());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testNoCliReturnsString()
    {
        $this->assertInternalType('string',(new ResponseHandler())->noCli());

        $noCli = (new ResponseHandler())->noCli();
        $this->assertRegexp('/Please use php command line to run script/', $noCli);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testHelpReturnsString()
    {
        $this->assertInternalType('string', (new ResponseHandler())->help());

        $rh = new ResponseHandler();
        $rh->help();
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertRegexp('/Show help/', $getMsg);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCountryCodeErrorReturnsString()
    {
        $this->assertInternalType('string', (new ResponseHandler())->countryCodeError());

        $rh = new ResponseHandler();
        $rh->countryCodeError();
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertRegexp('/Improper country name or wrong/', $getMsg);
    }


    /**
     * @param $text
     * @dataProvider providerOfTestStrings
     * @return void
     * @throws \Exception
     **/
    public function testCountryCodeOkReturnsString($text)
    {
        $this->assertInternalType('string', (new ResponseHandler())->countryCodeOk($text));

        $rh = new ResponseHandler();
        $rh->countryCodeOk($text);
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertRegExp('/Country language code/', $getMsg);
    }

    /**
     * @param $text
     * @param $country
     * @dataProvider providerOfTextAndCountry
     * @return void
     * @throws \Exception
     **/
    public function testCountryListReturnsString($text, $country)
    {
        $this->assertInternalType('string', (new ResponseHandler())->countryList($text, $country));

        $rh = new ResponseHandler();
        $rh->countryList($text, $country);
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertRegExp('/speaks same language with these countries/', $getMsg);
    }


    /**
     * @dataProvider providerOfTestStrings
     * @param $text
     * @param $result
     * @throws \Exception
     */
    public function testCustomTextReturnsString($text, $result)
    {
        $this->assertInternalType('string', (new ResponseHandler())->customText($text, $result));

        $rh = new ResponseHandler();
        $rh->customText($text,$result);
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertRegExp('/' . $text . '/', $getMsg);
    }

    /**
     * @dataProvider providerOfListOfCallbacks
     * @param $callback
     * @param $format
     * @throws \Exception
     */
    public function testGetMessageReturnsString($callback, $format)
    {
        $rh = new ResponseHandler();
        $this->invokeMethod($rh, 'getMessage', [
            'callback'=>$callback,
            'format'=>$format
            ]
        );
        $getMsg = $this->invokeMethod($rh, 'getMsg', []);

        $this->assertInternalType('string', $getMsg);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testWrapHtmlReturnsClass()
    {
        $rh = new ResponseHandler();
        $wrapHtml = $this->invokeMethod($rh, 'wrapHtml', []);

        $this->assertInstanceOf(ResponseHandler::class, $wrapHtml);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAddBreakReturnsClass()
    {
        $rh = new ResponseHandler();
        $addBreak = $this->invokeMethod($rh, 'addBreak', []);

        $this->assertInstanceOf(ResponseHandler::class,$addBreak);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testConvertNl2brReturnsClass()
    {
        $rh = new ResponseHandler();
        $convert = $this->invokeMethod($rh, 'convertNl2br', []);

        $this->assertInstanceOf(ResponseHandler::class, $convert);
    }

    /**
     * @dataProvider providerOfBool
     * @return void
     * @param $format
     * @throws \Exception
     */
    public function testFormatMessageReturnsClass($format)
    {
        $rh = new ResponseHandler();
        $formatMessage = $this->invokeMethod($rh, 'formatMessage', ['format' => $format]);

        $this->assertInstanceOf(ResponseHandler::class, $formatMessage);
    }

    /**
     * @return void
     * @dataProvider providerOfListOfCallbacks
     * @param $callback
     * @param $format
     * @throws \Exception
     */

    public function test__ToStringReturnsString($callback, $format)
    {
        $rh = new ResponseHandler();
        $getMessage = $this->invokeMethod($rh, 'getMessage', [
                'callback' => $callback,
                'format' => $format
            ]
        );
        $msg = $getMessage->__toString();

        $this->assertInternalType('string', $msg);

    }

    /**
     * @return array
     */
    public function providerOfTestStrings()
    {
        return [
            ['String 1 <>', false],
            ['String 3 ', true],
            ['String 2', null],
            [null, null],
            ['String 1 <script>', false]
        ];
    }

    /**
     * @return array
     */
    public function providerOfBool()
    {
        return [
            [false],
            [true],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function providerOfListOfCallbacks()
    {
        return [
            [[0 => ['function' => 'noCli']], false],
            [[0 => ['function' => 'help']], true],
            [[0 => ['function' => 'countryCodeError']], null],
            [[0 => ['function' => 'countryCodeOk']], false],
        ];
    }

    /**
     * @return array
     */
    public function providerOfTextAndCountry()
    {
        return [
            ['list of countries', 'Switzerland'],
            ['list of countries', 'US'],
        ];
    }
}
