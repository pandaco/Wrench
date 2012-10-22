<?php
namespace Pandaco\Application;

use Pandaco\Application;
use \Wrench\Connection;
use \Wrench\Payload\Payload;

/**
 * @author John Mouloud <john.mouloud@pandaco.net>
 */
class Demo extends Application
{
    public function onConnect(Connection $client)
    {
        parent::onConnect($client);
    }

    public function onDisconnect(Connection $client)
    {
        parent::onDisconnect($client);
    }

    public function onData(Payload $data, Connection $client)
    {

    }
}