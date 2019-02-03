<?php

namespace Rest\RestCountries;

use Colors\Color;

/**
 * Class ResponseHandler
 * @package Rest\RestCountries
 */
class ResponseHandler
{
    public      $msg;

    protected   $nl2br;
    protected   $callback;
    protected   $message = [

        'noCli' => '
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | Please use php command line to run script.                               {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | For help run:                                                            {PHP_EOL}
 |                                                                          {PHP_EOL}
 | $ php index.php --help                                                   {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',

        'help' => '
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | This script gets data from restcountries.eu                              {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 |                                                                          {PHP_EOL}
 | index.php [--help]      |   Show help                                    {PHP_EOL}
 |           [1st country] |   To get country code ISO639_1 like (es, en)   {PHP_EOL}
 |                             use country name eg. Spain, UK, Switzerland. {PHP_EOL}
 |                             Script prints also countries speaking        {PHP_EOL}
 |                             the same language(s).                        {PHP_EOL}
 |           [2nd country] |   Checks if both countries speak the same      {PHP_EOL}
 |                             language.                                    {PHP_EOL}
 |                                                                          {PHP_EOL}
 | !!! Demo version !!!    |   Max 2-word countries (eg. Sierra Leone)      {PHP_EOL}
 |                                                                          {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',

        'countryCodeError' => '
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | Improper country name or wrong parameters count.                         {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 |                                                                          {PHP_EOL}
 | Use country name like: Spain, Uk, Sierra Leone. Max 2 countries.         {PHP_EOL}
 |                                                                          {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | For help run:  $ php index.php --help                                    {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',
        'countryCodeOk' => '
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
 | Country language code: {code}                                              {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',
        'countryList' => '
                                                                            {PHP_EOL}
  {country} speaks same language with these countries:                      {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
{code}                                                                      {PHP_EOL}
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',
        'customText' => '
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
  {code}                                                                    {PHP_EOL}
                                                                            {PHP_EOL}
 |------------------------------------------------------------------------  {PHP_EOL}
                                                                            {PHP_EOL}
',
    ];

    /**
     * ResponseHandler constructor.
     */
    public function __construct()
    {
        $this->msg = '';
        $this->nl2br = false;
    }

    /**
     * @param null $nl2br
     * @return string
     */
    public function noCli($nl2br = null): string
    {
        $this->nl2br = $nl2br;
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback, null);

        return $this->msg;
    }

    /**
     * @return string
     */
    public function help(): string
    {
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback);

        return $this->msg;
    }

    /**
     * @return string
     */
    public function countryCodeError(): string
    {
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback);

        return $this->msg;
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function countryCodeOk($text): string
    {
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback, true);

        $this->msg = str_replace('{code}', $text, $this->msg);

        return $this->msg;
    }

    /**
     * @param $text
     * @param $country
     * @return mixed|string
     */
    public function countryList($text, $country): string
    {
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback, true);

        $this->msg = str_replace('{code}', $text, $this->msg);
        $this->msg = str_replace('{country}', $country, $this->msg);

        return $this->msg;
    }

    /**
     * @param $text
     * @param $result
     * @return mixed|string
     */
    public function customText($text, $format): string
    {
        $callback = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $this->getMessage($callback, true);

        $this->msg = str_replace('{code}', $text, $this->msg);
        $this->formatMessage($format);

        return $this->msg;
    }

    /**
     * @param $callback
     * @param bool $format
     * @return $this
     */
    private function getMessage($callback, $format = false)
    {
        $this->callback = $callback[0]['function'];
        $msg = $this->message[$this->callback];
        $this->msg = $msg;
        $this->formatMessage($format);

        return $this;
    }

    /**
     * @return $this
     */
    private function wrapHtml()
    {
        $this->msg = '<div style="font-family: \'Courier New\'; font-size: 12px;">'
                . $this->msg .
            '</div>';

        return $this;
    }

    /**
     * @return $this
     */
    private function addBreak()
    {
        $this->msg = '{PHP_EOL}' . $this->msg . '{PHP_EOL}';

        return $this;
    }

    /**
     * @return $this
     */
    private function convertNl2br()
    {
        $replacer = '';
        if ($this->nl2br) {
            $replacer = '<br>';
        }
        $this->msg = str_replace('{PHP_EOL}', $replacer, $this->msg);

        return $this;
    }

    /**
     * @param bool $format
     * @return $this
     */
    private function formatMessage($format = false)
    {
        $c = new Color();

        $this->addBreak()->convertNl2br();

        if (null === $format) {
            $this->wrapHtml();
        } else {
            if ($format === false) {
                $this->msg = $c($this->msg)->white->bold->bg_red;
            } else if ($format === true) {
                $this->msg = $c($this->msg)->green->bold;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->msg;
    }
}