<?php
/**
 * Created by PhpStorm.
 * User: quimmanrique
 * Date: 13/02/17
 * Time: 18:55
 */

namespace Infrastructure\AmqpLib\v26\RabbitMQ\Task;

use \Domain\Task\Consumer as DomainConsumer;
use Domain\Task\JSONTaskFactory;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\Config\BindConfig;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\Config\ConnectionConfig;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\Config\ConsumeConfig;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\Config\ExchangeConfig;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\Config\QueueConfig;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\MessageHandler;
use Infrastructure\AmqpLib\v26\RabbitMQ\Queue\QueueReader;
use Psr\Log\LoggerInterface;

class Consumer extends DomainConsumer
{
    /**
     * Consumer constructor.
     * @param string $host
     * @param $port
     * @param $user
     * @param $password
     * @param $vHost
     * @param $exchangeName
     * @param $queueName
     * @param LoggerInterface $logger
     * @param callable $callback
     */
    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $vHost,
        $exchangeName,
        $queueName,
        LoggerInterface $logger,
        callable $callback
    )
    {
        $queueReader = new QueueReader(
            new ConnectionConfig($host, $port, $user, $password, $vHost),
            new QueueConfig($queueName, false, true, false, false),
            new ExchangeConfig($exchangeName, 'fanout', false, true, false),
            new BindConfig(),
            new ConsumeConfig(false, false, false, false),
            $logger,
            new MessageHandler(new JSONTaskFactory(), $callback)
        );
        parent::__construct($queueReader);
    }
}