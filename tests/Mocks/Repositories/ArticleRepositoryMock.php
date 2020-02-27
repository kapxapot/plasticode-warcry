<?php

namespace App\Tests\Mocks\Repositories;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Util\Date;

class ArticleRepositoryMock implements ArticleRepositoryInterface
{
    /** @var Collection */
    private $articles;

    public function __construct()
    {
        $this->articles = Collection::make(
            [
                new Article(
                    [
                        'id' => 1,
                        'name_ru' => 'О сайте',
                        'name_en' => 'About Us',
                        'text' => 'We are awesome. Work with us.',
                        'published' => 1,
                        'published_at' => Date::dbNow(),
                    ]
                ),
                new Article(
                    [
                        'id' => 2,
                        'name_ru' => 'Иллидан Ярость Бури',
                        'name_en' => 'Illidan Stormrage',
                        'text' => 'Illidan is a bad boy. Once a night elf, now a demon. Booo.',
                        'published' => 0,
                        'published_at' => null,
                        'aliases' => 'Illidan',
                    ]
                ),
            ]
        );
    }

    public function getBySlugOrAlias(string $name, string $cat = null) : ?Article
    {
        return $this->articles
            ->where(
                function (Article $article) use ($name, $cat) {
                    $name = Strings::toSpaces($name);
                    $cat = Strings::toSpaces($cat);
            
                    $query = self::getProtected();
                    
                    // if (is_numeric($name)) {
                    //     return $query->find($name);
                    // }
                    
                    $query = $query->where('name_en', $name);
                
                    if ($cat) {
                        $category = ArticleCategory::getByName($cat);
                        
                        if ($category) {
                            return $query
                                ->whereRaw('(cat = ? or cat is null)', [ $category->id ])
                                ->orderByDesc('cat');
                        }
                    }
            
                    return $query->orderByAsc('cat');

                    $name = Strings::toSpaces($name);
                    $cat = Strings::toSpaces($cat);
            
                    $aliasParts[] = $name;
                    
                    if (strlen($cat) > 0) {
                        $aliasParts[] = $cat;
                    }
                    
                    $alias = Strings::joinTagParts($aliasParts);
            
                    return self::getProtected()
                        ->whereRaw('(aliases like ?)', ['%' . $alias . '%'])
                        ->one();
            
                    return $article->nameEn == $name
                }
            )
            ->first();
    }
}
