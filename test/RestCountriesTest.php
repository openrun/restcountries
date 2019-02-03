<?php
declare(strict_types=1);

namespace Rest\RestCountries;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;

class RestCountriesTest extends TestCase
{
    private $return;
    private $response200 = '[{"name":"Spain","topLevelDomain":[".es"],"alpha2Code":"ES","alpha3Code":"ESP","callingCodes":["34"],"capital":"Madrid","altSpellings":["ES","Kingdom of Spain","Reino de España"],"region":"Europe","subregion":"Southern Europe","population":46438422,"latlng":[40.0,-4.0],"demonym":"Spanish","area":505992.0,"gini":34.7,"timezones":["UTC","UTC+01:00"],"borders":["AND","FRA","GIB","PRT","MAR"],"nativeName":"España","numericCode":"724","currencies":[{"code":"EUR","name":"Euro","symbol":"€"}],"languages":[{"iso639_1":"es","iso639_2":"spa","name":"Spanish","nativeName":"Español"}],"translations":{"de":"Spanien","es":"España","fr":"Espagne","ja":"スペイン","it":"Spagna","br":"Espanha","pt":"Espanha","nl":"Spanje","hr":"Španjolska","fa":"اسپانیا"},"flag":"https://restcountries.eu/data/esp.svg","regionalBlocs":[{"acronym":"EU","name":"European Union","otherAcronyms":[],"otherNames":[]}],"cioc":"ESP"}]';

    private $responseArgentina = '[{"name":"Argentina","topLevelDomain":[".ar"],"alpha2Code":"AR","alpha3Code":"ARG","callingCodes":["54"],"capital":"Buenos Aires","altSpellings":["AR","Argentine Republic","República Argentina"],"region":"Americas","subregion":"South America","population":43590400,"latlng":[-34.0,-64.0],"demonym":"Argentinean","area":2780400.0,"gini":44.5,"timezones":["UTC-03:00"],"borders":["BOL","BRA","CHL","PRY","URY"],"nativeName":"Argentina","numericCode":"032","currencies":[{"code":"ARS","name":"Argentine peso","symbol":"$"}],"languages":[{"iso639_1":"es","iso639_2":"spa","name":"Spanish","nativeName":"Español"},{"iso639_1":"gn","iso639_2":"grn","name":"Guaraní","nativeName":"Avañe\'ẽ"}],"translations":{"de":"Argentinien","es":"Argentina","fr":"Argentine","ja":"アルゼンチン","it":"Argentina","br":"Argentina","pt":"Argentina","nl":"Argentinië","hr":"Argentina","fa":"آرژانتین"},"flag":"https://restcountries.eu/data/arg.svg","regionalBlocs":[{"acronym":"USAN","name":"Union of South American Nations","otherAcronyms":["UNASUR","UNASUL","UZAN"],"otherNames":["Unión de Naciones Suramericanas","União de Nações Sul-Americanas","Unie van Zuid-Amerikaanse Naties","South American Union"]}],"cioc":"ARG"}]';

    private $mockHandler;
    private $restCountries;

    public function setUp()
    {
        $this->return = '';

        $this->mockHandler = new MockHandler();

        $httpClient = new Client([
            'handler' => $this->mockHandler,
        ]);

        $this->restCountries = new RestCountries($httpClient);
    }

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
     * @dataProvider providerOfArgv
     * @param $argv
     * @throws \Exception
     */
    public function testRestCountriesIsClass($argv)
    {
        $rc = new restCountries($argv);
        $this->assertInstanceOf(RestCountries::class, $rc);
    }

    /**
     * @param $argv
     * @dataProvider providerOfArgv
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetResultDataDivert($argv)
    {
        $rc = new RestCountries($argv);
        $this->invokeMethod($rc, 'cleanEntryString', []);
        $argvArray = $this->invokeMethod($rc, 'getArgv', []);

        for ($i = 0; $i < count($argvArray); $i++) {
            $this->assertRegExp('/\b[a-zA-Z.]+\b/', $argvArray[$i]);
        }

        $count = count($argvArray);
        $this->assertInternalType('int', $count);

        $responseHandler = new responseHandler();
        $this->assertInstanceOf(ResponseHandler::class, $responseHandler);

//        return $this->return;
    }

    /**
     *
     */
    public function testGetCountryResponse200ReturnsCountryCode()
    {
        try {
            $this->mockHandler->append(new Response(200, []));

            $languageCode = $this->invokeMethod($this->restCountries, 'getLanguageCode', ['body' => $this->response200]);
            $this->assertInternalType('string', $languageCode[0]);
            $this->assertEquals(2, strlen($languageCode[0]));
        } catch (\Exception $exception) {
            $this->fail('testGetCountryResponse200ReturnsCountryCode returns following Exception ' . $exception->getMessage());
        }

        // all other assertions made @ResponseHandlerTest.php
    }

    public function _testGetLanguageCode()
    {
        // tested above
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCompareCountries()
    {
        $rc = $this->restCountries;
        $languageCode = [];
        foreach ([$this->response200, $this->responseArgentina] as $body) {
            $languageCode[] = $this->invokeMethod($rc, 'getLanguageCode', ['body' => $body]);
        }

        foreach ($languageCode[0] as $lang) {
            if (in_array($lang, $languageCode[1])) {
                $this->assertTrue(in_array($lang, $languageCode[1]));

            } else {
                $this->assertFalse(in_array($lang, $languageCode[1]));

            }
        }
    }

    /**
     * @dataProvider providerOfSameLanguageData
     * @param $responseItLang
     * @param $countryName
     * @throws \Exception
     */
    public function testSameLanguageCountryReturnsArray($responseItLang, $countryName)
    {
        $countries = json_decode($responseItLang, true);

        $this->assertInternalType('array', $countries);

        $countryList = [];
        foreach ($countries as $country) {
            if (isset($country['name']) and $country['name'] != $countryName) {
                $countryList[] = $country['name'];
                $this->assertInternalType('string', $country['name']);
            }
        }

        $this->assertEquals(count($countries), count($countryList) + 1);
    }

    public function _testGuzzleClient()
    {
        // tested with Mock above
    }

    public function _testCleanEntryString()
    {
        // tested @testGetResultDataDivert
    }



    /**
     *
     * @return array
     */
    public function providerOfArgv()
    {
        return [
            [['index.php']],
            [['US2222']],
            [['USA']],
            [['Sierra', 'Leone']],
            [['index.php', 'Spain']],
            [['index.php', 'Argentina', 'US']],
            [['index.php', 'Argentinasss', 'USSSS']],
            [['index.php', 'Argentina', 'Switzerland']],
            [['index.php', 'Poland', 'poland', 'Switzerland']],
            [['index.php', 'Poland', 'poland', 'poland', 'Switzerland']],
            [['index.php', 'Sierra', 'Leone', 'Switzerland']],
            [['index.php', 'Switzerland', 'Sierra', 'Leone']],
        ];
    }

    /**
     *
     * @return array
     */
    public function providerOfCountry()
    {
        return [
            ['index.php'],
            ['US'],
            ['USA'],
            ['Sierra Leone'],
            ['Germany'],
        ];
    }

    /**
     *
     * @return array
     */
    public function providerOfArgvAndCountry()
    {
        $argv = $this->providerOfArgv();
        $country = $this->providerOfCountry();

        $ret = [];

        foreach ($argv as $arg) {
            foreach ($country as $ctry) {
                $item = [$arg[0], $ctry[0]];
                $ret[] = $item;
            }
        }

        print_r($ret);

        return $ret;
    }

    /**
     *
     * @return array
     */
    public function providerOfGuzzleBody()
    {
        // $argv, $country, $response200, $response202

        return [
            [['index.php', 'Spain'],
                'Spain',
                '[{"name":"Spain","topLevelDomain":[".es"],"alpha2Code":"ES","alpha3Code":"ESP","callingCodes":["34"],"capital":"Madrid","altSpellings":["ES","Kingdom of Spain","Reino de España"],"region":"Europe","subregion":"Southern Europe","population":46438422,"latlng":[40.0,-4.0],"demonym":"Spanish","area":505992.0,"gini":34.7,"timezones":["UTC","UTC+01:00"],"borders":["AND","FRA","GIB","PRT","MAR"],"nativeName":"España","numericCode":"724","currencies":[{"code":"EUR","name":"Euro","symbol":"€"}],"languages":[{"iso639_1":"es","iso639_2":"spa","name":"Spanish","nativeName":"Español"}],"translations":{"de":"Spanien","es":"España","fr":"Espagne","ja":"スペイン","it":"Spagna","br":"Espanha","pt":"Espanha","nl":"Spanje","hr":"Španjolska","fa":"اسپانیا"},"flag":"https://restcountries.eu/data/esp.svg","regionalBlocs":[{"acronym":"EU","name":"European Union","otherAcronyms":[],"otherNames":[]}],"cioc":"ESP"}]'],
            '',
        ];
    }

    public function providerOfSameLanguageData()
    {
        return [
            ['[{"name":"Holy See","topLevelDomain":[".va"],"alpha2Code":"VA","alpha3Code":"VAT","callingCodes":["379"],"capital":"Rome","altSpellings":["Sancta Sedes","Vatican","The Vatican"],"region":"Europe","subregion":"Southern Europe","population":451,"latlng":[41.9,12.45],"demonym":"","area":0.44,"gini":null,"timezones":["UTC+01:00"],"borders":["ITA"],"nativeName":"Sancta Sedes","numericCode":"336","currencies":[{"code":"EUR","name":"Euro","symbol":"€"}],"languages":[{"iso639_1":"la","iso639_2":"lat","name":"Latin","nativeName":"latine"},{"iso639_1":"it","iso639_2":"ita","name":"Italian","nativeName":"Italiano"},{"iso639_1":"fr","iso639_2":"fra","name":"French","nativeName":"français"},{"iso639_1":"de","iso639_2":"deu","name":"German","nativeName":"Deutsch"}],"translations":{"de":"Heiliger Stuhl","es":"Santa Sede","fr":"voir Saint","ja":"聖座","it":"Santa Sede","br":"Vaticano","pt":"Vaticano","nl":"Heilige Stoel","hr":"Sveta Stolica","fa":"سریر مقدس"},"flag":"https://restcountries.eu/data/vat.svg","regionalBlocs":[],"cioc":""},{"name":"Italy","topLevelDomain":[".it"],"alpha2Code":"IT","alpha3Code":"ITA","callingCodes":["39"],"capital":"Rome","altSpellings":["IT","Italian Republic","Repubblica italiana"],"region":"Europe","subregion":"Southern Europe","population":60665551,"latlng":[42.83333333,12.83333333],"demonym":"Italian","area":301336.0,"gini":36.0,"timezones":["UTC+01:00"],"borders":["AUT","FRA","SMR","SVN","CHE","VAT"],"nativeName":"Italia","numericCode":"380","currencies":[{"code":"EUR","name":"Euro","symbol":"€"}],"languages":[{"iso639_1":"it","iso639_2":"ita","name":"Italian","nativeName":"Italiano"}],"translations":{"de":"Italien","es":"Italia","fr":"Italie","ja":"イタリア","it":"Italia","br":"Itália","pt":"Itália","nl":"Italië","hr":"Italija","fa":"ایتالیا"},"flag":"https://restcountries.eu/data/ita.svg","regionalBlocs":[{"acronym":"EU","name":"European Union","otherAcronyms":[],"otherNames":[]}],"cioc":"ITA"},{"name":"San Marino","topLevelDomain":[".sm"],"alpha2Code":"SM","alpha3Code":"SMR","callingCodes":["378"],"capital":"City of San Marino","altSpellings":["SM","Republic of San Marino","Repubblica di San Marino"],"region":"Europe","subregion":"Southern Europe","population":33005,"latlng":[43.76666666,12.41666666],"demonym":"Sammarinese","area":61.0,"gini":null,"timezones":["UTC+01:00"],"borders":["ITA"],"nativeName":"San Marino","numericCode":"674","currencies":[{"code":"EUR","name":"Euro","symbol":"€"}],"languages":[{"iso639_1":"it","iso639_2":"ita","name":"Italian","nativeName":"Italiano"}],"translations":{"de":"San Marino","es":"San Marino","fr":"Saint-Marin","ja":"サンマリノ","it":"San Marino","br":"San Marino","pt":"São Marinho","nl":"San Marino","hr":"San Marino","fa":"سان مارینو"},"flag":"https://restcountries.eu/data/smr.svg","regionalBlocs":[],"cioc":"SMR"},{"name":"Switzerland","topLevelDomain":[".ch"],"alpha2Code":"CH","alpha3Code":"CHE","callingCodes":["41"],"capital":"Bern","altSpellings":["CH","Swiss Confederation","Schweiz","Suisse","Svizzera","Svizra"],"region":"Europe","subregion":"Western Europe","population":8341600,"latlng":[47.0,8.0],"demonym":"Swiss","area":41284.0,"gini":33.7,"timezones":["UTC+01:00"],"borders":["AUT","FRA","ITA","LIE","DEU"],"nativeName":"Schweiz","numericCode":"756","currencies":[{"code":"CHF","name":"Swiss franc","symbol":"Fr"}],"languages":[{"iso639_1":"de","iso639_2":"deu","name":"German","nativeName":"Deutsch"},{"iso639_1":"fr","iso639_2":"fra","name":"French","nativeName":"français"},{"iso639_1":"it","iso639_2":"ita","name":"Italian","nativeName":"Italiano"}],"translations":{"de":"Schweiz","es":"Suiza","fr":"Suisse","ja":"スイス","it":"Svizzera","br":"Suíça","pt":"Suíça","nl":"Zwitserland","hr":"Švicarska","fa":"سوئیس"},"flag":"https://restcountries.eu/data/che.svg","regionalBlocs":[{"acronym":"EFTA","name":"European Free Trade Association","otherAcronyms":[],"otherNames":[]}],"cioc":"SUI"}]',
                'Italy']
        ];
    }



}
