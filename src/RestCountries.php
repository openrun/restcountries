<?php

namespace Rest\RestCountries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class RestCountries
 * @package Rest\RestCountries
 */
class RestCountries
{
    protected $argv = [];
    protected $responseHandler;
    protected $return;

    protected $url = [
        1 => [
            'https://restcountries.eu/rest/v2/name/{code}?fullText=true',
            'https://restcountries.eu/rest/v2/lang/{code}'
        ],
    ];

    /**
     * RestCountries constructor.
     * @param $argv
     */
    public function __construct($argv)
    {
        $this->argv = $argv;
        $this->responseHandler = new responseHandler();

        $this->return = '';
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResult(): string
    {
        $this->cleanEntryString();

        $count = count($this->argv);
        $responseHandler = $this->responseHandler;

        if ($count < 2 or $count > 5) {
            $responseString = $responseHandler->help();
            $this->return .= $responseString;

        } else if ($count === 2) {
            $this->getCountry();

        } else if ($count === 5) {
            $firstArg = $this->argv[1] . ' ' . $this->argv[2];
            $secondArg = $this->argv[3] . ' ' . $this->argv[4];
            $this->compareCountries($firstArg, $secondArg);

        } else {
            $this->checkWhatIsThis();

        }

        return $this->return;
    }

    /**
     * @param null $countryName
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCountry($countryName = null)
    {
        $urlSet = $this->url[1];
        $responseHandler = $this->responseHandler;

        if (null === $countryName) {
            $countryName = $this->argv[1];
        }

        $urlSet[0] = str_replace('{code}', $countryName, $urlSet[0]);
        $response = $this->guzzleClient($urlSet[0]);

        $body = $response->getBody()->getContents();
        $status = $response->getStatusCode();

        try {
            if ($status == 200) {
                $languageCode = $this->getLanguageCode($body);
                $countryList = $this->sameLanguageCountry($languageCode, $urlSet[1], $countryName);

                $responseString = $responseHandler->countryList(implode(', ', $countryList), $countryName);
                $this->return .= $responseString;

            } else {
                $responseString = $responseHandler->countryCodeError();
                $this->return .= $responseString;
            }
        } catch (RequestException $exception) {
            $text = $exception->getMessage();
            $format = false;
            $responseString = $responseHandler->customText($text, $format);
            $this->return .= $responseString;
        }

        return $status;
    }

    /**
     * @param $body
     * @return array
     */
    private function getLanguageCode($body): array
    {
        $languageCodeArray = json_decode($body, true)[0]['languages'];
        $languageCode = [];
        foreach ($languageCodeArray as $lang) {
            $languageCode[] = str_replace('"', '', $lang['iso639_1']);
        }

        return $languageCode;
    }

    /**
     * @param null $firstArg
     * @param null $secondArg
     * @param bool $allMessages
     * @return bool|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function compareCountries($firstArg = null, $secondArg = null, $allMessages = true)
    {
        if (null === $firstArg or null === $secondArg) {
            return null;
        }

        $format = false;
        $ok = true;

        $urlSet = $this->url[1];
        $responseHandler = $this->responseHandler;

        $languageCode = [];

        foreach ([$firstArg, $secondArg] as $arg) {

            $urlSet[0] = str_replace('{code}', $arg, $this->url[1][0]);

            $response = $this->guzzleClient($urlSet[0]);
            $body = $response->getBody()->getContents();
            $status = $response->getStatusCode();

            if ($status === 200) {
                $languageCode[] = $this->getLanguageCode($body);
            } else {
                $ok = false;
                break;
            }
        }

        $operator = 'do not ';
        if ($ok) {
            foreach ($languageCode[0] as $lang) {
                if (in_array($lang, $languageCode[1])) {
                    $format = true;
                    $operator = '';
                    break;
                }
            }

            $text = $firstArg . ' and ' . $secondArg . ' ' . $operator . 'speak the same language';
            $responseString = $responseHandler->customText($text, $format);
            $this->return .= $responseString;

        } else if ($allMessages) {
            $responseString = $responseHandler->countryCodeError();
            $this->return .= $responseString;

        }

        return $ok;
    }

    /**
     * @param $languageCode
     * @param $urlSet
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sameLanguageCountry($languageCode, $url, $countryName): array
    {
        $responseHandler = $this->responseHandler;
        $languageString = implode(', ' ,$languageCode);

        $languageString = str_replace('"', '', $languageString);
        $responseString = $responseHandler->countryCodeOk($languageString);
        $this->return .= $responseString;

        $countryList = [];
        foreach ($languageCode as $lang) {
            $currentUrl = str_replace('{code}', $lang, $url);
            $response = $this->guzzleClient($currentUrl);

            $body = $response->getBody()->getContents();
            $countries = json_decode($body, true);

            foreach ($countries as $country) {
                if (isset($country['name']) and $country['name'] != $countryName) {
                    $countryList[] = $country['name'];
                }
            }
        }

        return $countryList;
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function checkWhatIsThis()
    {
        $argv = $this->argv;
        $count = count($this->argv);

        if ($count === 3) {
            $countryName3 = [
                [$argv[1], $argv[2]],
                [$argv[1] . ' ' . $argv[2]],
            ];
            $result = $this->compareCountries($countryName3[0][0], $countryName3[0][1], false);

            if (!$result) {
                $this->getCountry($countryName3[1][0]);
            }
        }
        if ($count === 4) {
            $countryName4 = [
                [$argv[1] . ' ' . $argv[2], $argv[3]],
                [$argv[1], $argv[2] . ' ' . $argv[3]]
            ];
            $result = $this->compareCountries($countryName4[0][0], $countryName4[0][1], false);

            if (!$result) {
                $this->compareCountries($countryName4[1][0], $countryName4[1][1]);
            }
        }
    }

    /**
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function guzzleClient($url)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', $url);

        } catch (ClientException $exception) {
            $response = $exception->getResponse();

        } catch (RequestException $exception) {
            $response = $exception->getResponse();

        }

        return $response;
    }

    /**
     * @return $this
     */
    private function cleanEntryString()
    {

        for ($i = 0; $i < count ($this->argv); $i++) {
            $this->argv[$i] = preg_replace('/[^A-Za-z .]/', '', $this->argv[$i]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getArgv(): array
    {
        return $this->argv;
    }
}