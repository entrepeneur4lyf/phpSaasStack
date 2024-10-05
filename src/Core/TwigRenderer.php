<?php

declare(strict_types=1);

namespace Src\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Swoole\Http\Response;
use League\CommonMark\Environment\Environment as MarkdownEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TwigRenderer
{
    private Environment $twig;
    private MarkdownConverter $markdownConverter;
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
        $loader = new FilesystemLoader($templatePath);
        $this->twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../cache/twig',
            'auto_reload' => true,
        ]);

        $this->initializeMarkdownConverter();
        $this->addCustomExtensions();
    }

    private function initializeMarkdownConverter(): void
    {
        $environment = new MarkdownEnvironment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->markdownConverter = new MarkdownConverter($environment);
    }

    private function addCustomExtensions(): void
    {
        $this->twig->addFilter(new \Twig\TwigFilter('markdown', function ($content) {
            return $this->markdownConverter->convert($content)->getContent();
        }, ['is_safe' => ['html']]));
    }

    public function precompileViews(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->templatePath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'twig') {
                $templateName = str_replace($this->templatePath . '/', '', $file->getPathname());
                $this->twig->load($templateName);
            }
        }
    }

    public function render(Response $response, string $template, array $data = []): void
    {
        $content = $this->twig->render($template, $data);
        $response->header('Content-Type', 'text/html');
        $response->end($content);
    }
}