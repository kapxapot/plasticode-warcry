<?php

namespace App\Parsing\LinkMappers;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Parsing\LinkMappers\Basic\EntityLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\SlugChunk;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Arrays;
use Plasticode\Util\Strings;

class ArticleLinkMapper extends EntityLinkMapper
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    /** @var ArticleRepositoryInterface */
    private $articleRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var TagLinkMapper */
    private $tagLinkMapper;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        TagRepositoryInterface $tagRepository,
        RendererInterface $renderer,
        LinkerInterface $linker,
        TagLinkMapper $tagLinkMapper
    )
    {
        parent::__construct($renderer, $linker);

        $this->articleRepository = $articleRepository;
        $this->tagRepository = $tagRepository;
        $this->tagLinkMapper = $tagLinkMapper;
    }

    protected function entity() : string
    {
        return 'article';
    }

    protected function baseUrl() : string
    {
        return $this->linker->article();
    }

    /**
     * Maps article chunks to an article link.
     *
     * @param SlugChunk $slugChunk
     * @param string[] $otherChunks
     * @return string|null
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $slug = $slugChunk->slug();
        $name = Arrays::last($otherChunks) ?? $slug;

        $cat = count($otherChunks) > 1
            ? Arrays::first($otherChunks)
            : null;

        $escapedSlug = Strings::fromSpaces($slug);
        $escapedCat = Strings::fromSpaces($cat);

        $article = $this->articleRepository->getBySlugOrAlias($slug, $cat);

        if ($article && $article->isPublished()) {
            return $this->renderer->articleUrl(
                $name,
                $slug,
                $escapedSlug,
                $cat,
                $escapedCat
            );
        }

        // if such tag exists, render as tag
        if ($this->tagLinkMapper && $this->tagRepository->exists($slug)) {
            return $this->renderAsTag($slugChunk, $otherChunks);
        }

        return $this->renderer->noArticleUrl($name, $slug, $cat);
    }

    private function renderAsTag(SlugChunk $slugChunk, array $otherChunks) : string
    {
        $slugChunk = $this->tagLinkMapper->adaptSlugChunk($slugChunk);

        return $this->tagLinkMapper->mapSlug($slugChunk, $otherChunks);
    }

    public function renderLinks(ParsingContext $context): ParsingContext
    {
        $context = parent::renderLinks($context);

        $context = $this->tagLinkMapper->renderLinks($context);

        return $context;
    }
}
