<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Query;
use Plasticode\Models\Traits\Children;
use Plasticode\Util\Sort;
use Plasticode\Util\Strings;

/**
 * @property integer $id
 * @property integer|null $parentId
 * @property integer|null $cat
 * @property string $nameRu
 * @property string $nameEn
 * @property integer $hideeng
 * @property string|null $origin
 * @property string|null $text
 * @property string|null $cache
 * @property integer $announce
 * @property integer $gameId
 * @property integer $noBreadcrumb
 * @property string|null $aliases
 * @method ArticleCategory|null category()
 * @method self withCategory(ArticleCategory|callable|null $category)
 */
class Article extends NewsSource
{
    use CachedDescription;
    use Children;

    protected static function getDescriptionField() : string
    {
        return 'text';
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

    public function subArticles() : Collection
    {
        return $this
            ->children()
            ->where(
                function ($item) {
                    return $item->isPublished();
                }
            )
            ->ascStr('name_ru');
    }
    
    public function breadcrumbs() : Collection
    {
        $breadcrumbs = Collection::empty();
        
        $article = $this->parent();
        
        while (!is_null($article)) {
            if (!$article->noBreadcrumb) {
                $breadcrumbs = $breadcrumbs->add($article);
            }

            $article = $article->parent();
        }
        
        return $breadcrumbs
            ->reverse()
            ->map(
                function ($a) {
                    return [
                        'url' => $a->url(),
                        'text' => $a->nameRu,
                        'title' => $a->titleEn(),
                    ];
                }
            );
    }

    public static function search(string $searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
            ->all()
            ->multiSort(
                [
                    'name_ru' => ['type' => Sort::STRING],
                    'category' => ['type' => Sort::NULL],
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
        
        if ($cat !== null) {
            $parts[] = $cat->nameEn;
        }
        
        if ($cat !== null || $this->nameRu !== $this->nameEn) {
            $parts[] = $this->nameRu;
        }
        
        return Strings::doubleBracketsTag(null, ...$parts);
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        $cat = $this->category();
        
        return self::$container->linker->article(
            $this->nameEn,
            $cat ? $cat->nameEn : null
        );
    }
    
    public static function getNewsByTag(string $tag) : Query
    {
        $query = static::getByTag($tag);
        return self::announced($query);
    }

    public static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query
    {
        $query = static::getLatest($game, null, $exceptNewsId);
        return self::announced($query);
    }
    
    public static function getNewsBefore(Game $game, string $date) : Query
    {
        return self::getLatestNews($game)
            ->whereLt('published_at', $date)
            ->orderByDesc('published_at');
    }
    
    public static function getNewsAfter(Game $game, string $date) : Query
    {
        return self::getLatestNews($game)
            ->whereGt('published_at', $date)
            ->orderByAsc('published_at');
    }
    
    public static function getNewsByYear(int $year) : Query
    {
        $query = self::getPublished()
            ->whereRaw('(year(published_at) = ?)', [$year]);
        
        return self::announced($query);
    }

    public function displayTitle() : string
    {
        return $this->nameRu;
    }
}
