<?php

namespace App\Models;

use App\Collections\ArticleCollection;
use Plasticode\Models\Traits\Parented;
use Plasticode\Util\Strings;

/**
 * @property integer|null $parentId
 * @property integer|null $cat
 * @property string $nameRu
 * @property string $nameEn
 * @property integer $hideeng
 * @property string|null $origin
 * @property string|null $text
 * @property string|null $cache
 * @property integer $announce
 * @property integer $noBreadcrumb
 * @property string|null $aliases
 * @method ArticleCategory|null category()
 * @method ArticleCollection children()
 * @method self withCategory(ArticleCategory|callable|null $category)
 * @method self withChildren(ArticleCollection|callable $children)
 */
class Article extends NewsSource
{
    use Parented;

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            'children',
            'parent',
            'category',
        ];
    }

    public function title() : string
    {
        $cat = $this->category();

        return $this->nameRu . ($cat ? ' (' . $cat->nameRu . ')' : '');
    }

    public function titleEn() : ?string
    {
        return $this->hideeng ? null : $this->nameEn;
    }

    public function titleFull() : string
    {
        $en = $this->titleEn();

        return $this->nameRu . ($en ? ' (' . $en . ')' : ''); 
    }

    /**
     * Returns published sub-articles.
     */
    public function subArticles() : ArticleCollection
    {
        return $this
            ->children()
            ->where(
                fn (self $a) => $a->isPublished()
            )
            ->ascStr('name_ru');
    }

    public function breadcrumbs() : ArticleCollection
    {
        $breadcrumbs = [];

        $article = $this->parent();

        while ($article) {
            if (!$article->noBreadcrumb) {
                $breadcrumbs[] = $article;
            }

            $article = $article->parent();
        }

        return ArticleCollection::make($breadcrumbs)
            ->reverse()
            ->map(
                fn (Article $a) =>
                [
                    'url' => $a->url(),
                    'text' => $a->nameRu,
                    'title' => $a->titleEn(),
                ]
            );
    }

    public function serialize() : array
    {
        $cat = $this->category();

        return [
            'id' => $this->getId(),
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
            'category' => $cat ? $cat->serialize() : null,
            'tags' => Strings::toTags($this->tags),
        ];
    }

    public function code() : string
    {
        $parts[] = $this->nameEn;

        $cat = $this->category();

        if ($cat) {
            $parts[] = $cat->nameEn;
        }

        if ($cat || $this->nameRu !== $this->nameEn) {
            $parts[] = $this->nameRu;
        }

        return Strings::doubleBracketsTag(null, ...$parts);
    }

    // NewsSourceInterface

    public function displayTitle() : string
    {
        return $this->nameRu;
    }

    public function rawText() : ?string
    {
        return $this->text;
    }
}
