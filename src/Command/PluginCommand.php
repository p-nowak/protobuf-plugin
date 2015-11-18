<?php

namespace Protobuf\Compiler\Command;

use Psr\Log\LogLevel;
use Protobuf\Stream;
use Protobuf\Compiler\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Plugin to generate PHP classes from stdin generated by protoc
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class PluginCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('protobuf:plugin')
            ->setDescription('Plugin to generate PHP classes from stdin generated by protoc');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // compile data from stdin
            // Create a compiler interface
            $stream   = $this->createStream();
            $compiler = $this->createCompiler($output);
            $response = $compiler->compile($stream);

            $output->write((string) $response);

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln($e->getTraceAsString());

            return 255;
        }
    }

    /**
     * @return \Protobuf\Stream
     */
    protected function createStream()
    {
        $handle = fopen('php://stdin', 'r');
        $stream = new Stream($handle);

        return $stream;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Protobuf\Compiler\Compiler
     */
    protected function createCompiler(OutputInterface $output)
    {
        $logger   = $this->createConsoleLogger($output);
        $compiler = new Compiler($logger);

        return $compiler;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Logger\ConsoleLogger
     */
    protected function createConsoleLogger(OutputInterface $output)
    {
        return new ConsoleLogger(
            $output,
            [
                // aways output notice, info and debug
                LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
            ],
            [
                // redirect messages to stderr
                LogLevel::WARNING => ConsoleLogger::INFO,
                LogLevel::NOTICE  => ConsoleLogger::ERROR,
                LogLevel::INFO    => ConsoleLogger::ERROR,
                LogLevel::DEBUG   => ConsoleLogger::ERROR
            ]
        );
    }
}