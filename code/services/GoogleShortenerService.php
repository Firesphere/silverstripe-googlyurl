<?php

class GoogleShortenerService extends Object
{
    /**
     * @var resource
     */
    protected $ch;

    protected $apiKey = '';

    /**
     * Hello!
     */
    public function __construct()
    {
        parent::__construct();

        $this->apiKey = GoogleShortenerService::config()->get('googlapi');

        $this->ch = curl_init();

        curl_setopt_array(
            $this->ch,
            [
                CURLOPT_URL            => 'https://www.googleapis.com/urlshortener/v1/url?key=' . $this->apiKey,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER         => 0,
                CURLOPT_HTTPHEADER     => ['Content-type:application/json'],
            ]
        );

    }

    /**
     * @param array $arrayData Array with longURL, e.g. ['longUrl' => 'http://my-long-link/here-is/this-page']
     * @return stdClass
     */
    public function getShortLink($arrayData)
    {
        $jsonData = Convert::array2json($arrayData);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($this->ch, CURLOPT_POST, count($arrayData));
        $response = curl_exec($this->ch);

        return Convert::json2obj($response);

    }

    /**
     * @todo find usage for this?
     * @param string $url
     * @return stdClass
     */
    public function expandShortLink($url)
    {
        // Set cURL options
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        curl_setopt(
            $this->ch, CURLOPT_URL,
            'https://www.googleapis.com/urlshortener/v1/url?key=' . $this->apiKey . '&shortUrl=' . $url
        );

        $response = curl_exec($this->ch);

        return Convert::json2obj($response);
    }

    /**
     * Bye bye!
     */
    public function __destruct()
    {
        curl_close($this->ch);
        $this->ch = null;
    }
}
