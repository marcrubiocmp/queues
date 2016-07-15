<?php

namespace spec\Cmp\Task\Infrastructure\Consumer\RabbitMQ;

use Cmp\DomainEvent\Domain\Event\DomainEvent;
use Cmp\Task\Domain\Task\JSONTaskFactory;
use Cmp\Task\Domain\Task\Task;
use Cmp\Task\Infrastructure\Consumer\RabbitMQ\RabbitMQConsumer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RabbitMQMessageHandlerSpec extends ObjectBehavior
{

    private $deliveryTag = "test tag";

    public function let(JSONTaskFactory $jsonTaskFactory)
    {
        $this->beConstructedWith($jsonTaskFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Cmp\Task\Infrastructure\Consumer\RabbitMQ\RabbitMQMessageHandler');
    }

    public function it_should_call_the_eventCallback_with_a_domain_object(
        AMQPMessage $amqpMessage,
        JSONTaskFactory $jsonTaskFactory,
        Task $task,
        RabbitMQConsumer $rabbitMQConsumer,
        AMQPChannel $amqpChannel
    ) {
        $amqpMessage->delivery_info = ['channel' => $amqpChannel, 'delivery_tag' => $this->deliveryTag];
        $jsonTaskFactory->create($amqpMessage->body)->shouldBeCalled()->willReturn($task);
        $rabbitMQConsumer->notify($task)->shouldBeCalled();

        $this->setEventCallback(array($rabbitMQConsumer, 'notify'));
        $this->handleMessage($amqpMessage);
    }

    public function it_should_send_ACK_after_processing_the_message(
        AMQPMessage $amqpMessage,
        JSONTaskFactory $jsonTaskFactory,
        Task $task,
        RabbitMQConsumer $rabbitMQConsumer,
        AMQPChannel $amqpChannel
    ) {
        $amqpMessage->delivery_info = ['channel' => $amqpChannel, 'delivery_tag' => $this->deliveryTag];
        $jsonTaskFactory->create($amqpMessage->body)->shouldBeCalled()->willReturn($task);
        $amqpChannel->basic_ack($this->deliveryTag)->shouldBeCalled();

        $this->setEventCallback(array($rabbitMQConsumer, 'notify'));
        $this->handleMessage($amqpMessage);
    }

}