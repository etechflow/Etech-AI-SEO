<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Etechflow\AiSeo\Service\MetaGenerator;

class GenerateCommand extends Command
{
    public function __construct(
        private MetaGenerator $generator,
        private State $state,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('etechflow:aiseo:generate')
            ->setDescription('Generate AI SEO meta for a product (CLI test).')
            ->addOption('product-id', null, InputOption::VALUE_REQUIRED, 'Product entity ID')
            ->addOption('store-id', null, InputOption::VALUE_OPTIONAL, 'Store ID', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Throwable $e) {
        }
        $pid = (int)$input->getOption('product-id');
        if (!$pid) {
            $output->writeln('<error>--product-id is required</error>');
            return Command::FAILURE;
        }
        try {
            $res = $this->generator->generateForProduct($pid, (int)$input->getOption('store-id'));
            $output->writeln('<info>Meta Title:</info>       ' . $res['title']);
            $output->writeln('<info>Meta Description:</info> ' . $res['description']);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
