<?php

namespace Jycamier\MessengerProcessBundle\Task;

use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageBusDispatcherTask extends AbstractConfigurableTask
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('stamps', []);
        $resolver->setAllowedTypes('stamps', 'array');
    }

    /**
     * @inheritDoc
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function execute(ProcessState $state)
    {
        $envelop = $this->messageBus->dispatch($this->prepareEnvelope($state));

        $state->setOutput($envelop);
    }

    /**
     * @param ProcessState $state
     * @return Envelope
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    protected function prepareEnvelope(ProcessState $state): Envelope
    {
        return (new Envelope($state->getInput()))
            ->with(
                ...array_map(
                    static function ($stamp) {
                        return new $stamp();
                    },
                    $this->getOption($state, 'stamps')
                )
            );
    }
}
