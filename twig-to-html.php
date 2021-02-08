<?php

$possibleAutoloadPaths = [
    // local dev repository
    __DIR__ . '/vendor/autoload.php',
    // dependency
    __DIR__ . '/../../../vendor/autoload.php',
];

foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (file_exists($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        break;
    }
}

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

(new SingleCommandApplication())
    ->addArgument('input', InputArgument::REQUIRED, 'Input directory')
    ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output directory', 'build')
    ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'Pattern filter', '')
    ->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Filename filter', '*.twig')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $finder = Finder::create()
            ->name($input->getOption('filter'))
            ->in($input->getArgument('input'))
            ->path($input->getOption('pattern'))
            ->files();

        $twig = new Environment(new FilesystemLoader($input->getArgument('input')));

        foreach ($finder as $file) {
            $output->writeln("Rendering $file");

            $absolutePath = $input->getOption('output') . '/' . $file->getRelativePath();
            @mkdir($absolutePath, 0755, true);

            file_put_contents($absolutePath . '/' . $file->getFilenameWithoutExtension(), $twig->render($file->getRelativePathname()));
        }
    })
    ->run();
