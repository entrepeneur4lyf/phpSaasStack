<?php

namespace Src\Controllers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\MarkdownConverter;
use Src\Interfaces\CacheServiceInterface;

class MarkdownController extends BaseController
{
    private CacheServiceInterface $cacheService;

    public function __construct(CacheServiceInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function render()
    {
        $markdown = $this->request->getJSON()->markdown;
        $cacheKey = 'markdown_' . md5($markdown);

        $cachedHtml = $this->cacheService->get($cacheKey);
        if ($cachedHtml !== null) {
            return $this->response->setJSON(['html' => $cachedHtml]);
        }

        // Configure the Environment with all the CommonMark parsers/renderers
        $environment = new Environment([
            'allow_unsafe_links' => false,
            'embed' => [
                'adapter' => new \League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter(),
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com'],
                'fallback' => 'link',
            ],
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
            ],
        ]);

        // Add the extensions
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new EmbedExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());

        // Create the converter
        $converter = new MarkdownConverter($environment);

        // Convert the Markdown to HTML
        $html = $converter->convert($markdown)->getContent();

        // Cache the result
        $this->cacheService->set($cacheKey, $html, 3600); // Cache for 1 hour

        return $this->response->setJSON(['html' => $html]);
    }
}