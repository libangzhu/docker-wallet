<?php
namespace common\service\es;

use Elasticsearch\ClientBuilder;

class Elastic
{
    protected $host;
    protected $client;

    public function __construct($host = null)
    {
        $this->host = $host;
    }

    public function connect()
    {
        $this->client = ClientBuilder::create()->setHosts([$this->host])
            ->build();
    }

    public function search($params)
    {
        $response = $this->client->search($params);

        return $response;
    }
}