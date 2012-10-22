<?php
namespace Pandaco;

use \Wrench\Connection;

/**
 * @author John Mouloud <john.mouloud@pandaco.net>
 */
abstract class Application extends \Wrench\Application\Application
{
    protected $clients           = array();
    protected $serverClients     = array();
    protected $serverInfo        = array();
    protected $serverClientCount = 0;


    /**
     * @param Connection $client
     */
    public function onConnect(Connection $client)
    {
        $id = $client->getId();
        $this->clients[$id] = $client;
    }

    /**
     * @param Connection $client
     */
    public function onDisconnect(Connection $client)
    {
        $id = $client->getId();
        unset($this->clients[$id]);
    }

    /**
     * @param  mixed $serverInfo
     * @return boolean
     */
    public function setServerInfo($serverInfo)
    {
        if (is_array($serverInfo)) {
            $this->serverInfo = $serverInfo;
            return true;
        }
        return false;
    }

    /**
     * @param string $ip
     * @param int    $port
     */
    public function clientConnected($ip, $port)
    {
        $this->serverClients[$port] = $ip;
        $this->serverClientCount++;
        $this->statusMsg('Client connected: ' .$ip.':'.$port);
        $data = array(
            'ip'          => $ip,
            'port'        => $port,
            'clientCount' => $this->serverClientCount,
        );
        $encodedData = $this->encodeData('clientConnected', $data);
        $this->sendAll($encodedData);
    }

    /**
     * @param  string  $ip
     * @param  int     $port
     * @return boolean
     */
    public function clientDisconnected($ip, $port)
    {
        if (!isset($this->serverClients[$port])) {
            return false;
        }
        unset($this->serverClients[$port]);
        $this->serverClientCount--;
        $this->statusMsg('Client disconnected: ' .$ip.':'.$port);
        $data = array(
            'port'        => $port,
            'clientCount' => $this->serverClientCount,
        );
        $encodedData = $this->encodeData('clientDisconnected', $data);
        $this->sendAll($encodedData);
    }

    /**
     * @param int $port
     */
    public function clientActivity($port)
    {
        $encodedData = $this->encodeData('clientActivity', $port);
        $this->sendAll($encodedData);
    }

    /**
     * @param string $text
     * @param string $type
     */
    public function statusMsg($text, $type = 'info')
    {
        $data = array(
            'type' => $type,
            'text' => '['. strftime('%Y-%m-%d %h-%i-%s', time()) . '] ' . $text,
        );
        $encodedData = $this->encodeData('statusMsg', $data);
        $this->sendAll($encodedData);
    }

    /**
     * @param Connection $client
     */
    protected function sendServerinfo(Connection $client)
    {
        if (count($this->clients) < 1) {
            return false;
        }

        $currentServerInfo = $this->serverInfo;
        $currentServerInfo['clientCount'] = count($this->serverClients);
        $currentServerInfo['clients'] = $this->serverClients;


        $encodedData = $this->encodeData('serverInfo', $currentServerInfo);
        $client->send($encodedData);
    }

    /**
     * Sends a message to everyone, even for the socket that starts it.
     *
     * @param string $encodedData
     */
    protected function sendAll($encodedData)
    {
        if (count($this->clients) < 1) {
            return false;
        }

        /**
         * @var $sendTo \Wrench\Connection
         */
        foreach ($this->clients as $sendTo) {
            $sendTo->send($encodedData);
        }
    }

    /**
     * Sends a message to everyone else except for the socket that starts it.
     *
     * @param string     $encodedData
     * @param Connection $client
     */
    protected function broadcast($encodedData, Connection $client)
    {
        /**
         * @var $sendTo \Wrench\Connection
         */
        foreach ($this->clients as $sendTo) {
            if ($sendTo->getId() !== $client->getId()) {
                $sendTo->send($encodedData);
            }
        }
    }

    /**
     * @param  string $data
     * @return string
     */
    protected function decodeData($data)
    {
        $decodedData = json_decode($data, true);

        if ($decodedData === null) {
            return false;
        }

        if (isset($decodedData['action'], $decodedData['data']) === false) {
            return false;
        }

        return $decodedData;
    }

    /**
     * @param  string $action
     * @param  string $data
     * @return string
     */
    protected function encodeData($action, $data)
    {
        if (empty($action)) {
            return false;
        }

        $payload = array(
            'action' => $action,
            'data'   => $data
        );

        return json_encode($payload);
    }
}