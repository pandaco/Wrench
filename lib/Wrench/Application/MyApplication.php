<?php
namespace Wrench\Application;

use \Wrench\Connection;

/**
 * My Application for testing purposes
 *
 * @author John Mouloud <john.mouloud@pandaco.net>
 */
class MyApplication extends Application
{
    private $_clients           = array();
    private $_serverClients     = array();
    private $_serverInfo        = array();
    private $_serverClientCount = 0;


    public function onConnect(Connection $client)
    {
        $id = $client->getId();
        $this->_clients[$id] = $client;


        $this->clientConnected($client->getIp(), $client->getPort());

        $this->_sendServerinfo($client);
    }

    public function onDisconnect(Connection $client)
    {
        $id = $client->getId();
        unset($this->_clients[$id]);
    }

    public function onData($data, Connection $client)
    {
        var_dump($data);
        $this->_sendAll($this->_encodeData('hello', $data));
    }

    public function setServerInfo($serverInfo)
    {
        if (is_array($serverInfo)) {
            $this->_serverInfo = $serverInfo;
            return true;
        }
        return false;
    }


    public function clientConnected($ip, $port)
    {
        $this->_serverClients[$port] = $ip;
        $this->_serverClientCount++;
        $this->statusMsg('Client connected: ' .$ip.':'.$port);
        $data = array(
            'ip'          => $ip,
            'port'        => $port,
            'clientCount' => $this->_serverClientCount,
        );
        $encodedData = $this->_encodeData('clientConnected', $data);
        $this->_sendAll($encodedData);
    }

    public function clientDisconnected($ip, $port)
    {
        if(!isset($this->_serverClients[$port]))
        {
            return false;
        }
        unset($this->_serverClients[$port]);
        $this->_serverClientCount--;
        $this->statusMsg('Client disconnected: ' .$ip.':'.$port);
        $data = array(
            'port' => $port,
            'clientCount' => $this->_serverClientCount,
        );
        $encodedData = $this->_encodeData('clientDisconnected', $data);
        $this->_sendAll($encodedData);
    }

    public function clientActivity($port)
    {
        $encodedData = $this->_encodeData('clientActivity', $port);
        $this->_sendAll($encodedData);
    }

    public function statusMsg($text, $type = 'info')
    {
        $data = array(
            'type' => $type,
            'text' => '['. strftime('%m-%d %H:%M', time()) . '] ' . $text,
        );
        $encodedData = $this->_encodeData('statusMsg', $data);
        $this->_sendAll($encodedData);
    }

    private function _sendServerinfo(Connection $client)
    {
        if (count($this->_clients) < 1) {
            return false;
        }
        $currentServerInfo = $this->_serverInfo;
        $currentServerInfo['clientCount'] = count($this->_serverClients);
        $currentServerInfo['clients'] = $this->_serverClients;


        $encodedData = $this->_encodeData('serverInfo', $currentServerInfo);
        $client->send($encodedData);
    }

    private function _sendAll($encodedData)
    {
        if (count($this->_clients) < 1) {
            return false;
        }
        foreach($this->_clients as $sendto) {
            $sendto->send($encodedData);
        }
    }

    protected function _decodeData($data)
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

    protected function _encodeData($action, $data)
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