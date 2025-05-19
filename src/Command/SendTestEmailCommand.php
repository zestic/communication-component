<?php

declare(strict_types=1);

namespace Communication\Command;

use Communication\Communication;
use Communication\Context\CommunicationContext;
use Communication\Context\EmailContext;
use Communication\Interactor\SendCommunication;
use Communication\Recipient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SendTestEmailCommand extends Command
{
    protected static $defaultName = 'communication:send-test-email';

    public function __construct(
        private SendCommunication $sender,
        private EmailContext $emailContext,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Where to send the test email',
            )
            ->addOption(
                'from',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The email address the test email comes from',
            )
            ->addOption(
                'subject',
                's',
                InputOption::VALUE_OPTIONAL,
                'The subject of the test email',
                'Test Email'
            )
            ->addOption(
                'body',
                'b',
                InputOption::VALUE_OPTIONAL,
                'The body of the test email',
                '<p>This is a test email sent from the communication component.</p>'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $subject = $input->getOption('subject');
        $body = $input->getOption('body');

        // Create a recipient
        $recipient = (new Recipient())
            ->setEmail($email);

        // Set the context data for the email channel
        $this->emailContext->setSubject($subject);
        $this->emailContext->setBodyContext([
            'body' => $body,
            'additionalData' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'sender' => 'System'
            ]
        ]);

        // Set from address if provided
        if ($from = $input->getOption('from')) {
            $this->emailContext->setFrom($from);
        }

        // Create a communication context with the email context
        $context = new CommunicationContext(['email' => $this->emailContext]);

        // Create a new Communication with the generic.email definition ID and context
        $communication = new Communication('generic.email', $context);

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Send the communication
        $this->sender->send($communication);

        $output->writeln(sprintf(
            '<info>Test email sent to %s with subject "%s"</info>',
            $email,
            $subject
        ));

        return Command::SUCCESS;
    }
}
