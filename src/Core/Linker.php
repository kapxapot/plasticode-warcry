<?php

namespace App\Core;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Models\Skill;
use Plasticode\Core\Linker as LinkerBase;
use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Util\Strings;

class Linker extends LinkerBase implements LinkerInterface
{
    // urls
    private function forumUrl(string $url) : string
    {
        return $this->getSettings('forum.page') . '?' . $url;
    }

    // site

    /**
     * Get article link.
     *
     * @param int|string $id
     * @param string $cat
     * @return string
     */
    public function article($id = null, ?string $cat = null) : string
    {
        $params = ['id' => Strings::fromSpaces($id)];
        
        if (strlen($cat) > 0) {
            $params['cat'] = Strings::fromSpaces($cat);
        }

        return $this->router->pathFor('main.article', $params);
    }

    public function news(int $id = null) : string
    {
        return $this->router->pathFor('main.news', ['id' => $id]);
    }
    
    public function newsYear(int $year) : string
    {
        return $this->router->pathFor(
            'main.news.archive.year',
            ['year' => $year]
        );
    }

    public function event(int $id = null) : string
    {
        return $this->router->pathFor('main.event', ['id' => $id]);
    }

    public function video(int $id = null) : string
    {
        return $this->router->pathFor('main.video', ['id' => $id]);
    }

    public function n(int $id) : string
    {
        return $this->abs('n/' . $id);
    }

    public function game(?Game $game) : string
    {
        $params = [];
        
        if ($game && !$game->default()) {
            $params['game'] = $game->alias;
        }
        
        return $this->router->pathFor('main.index', $params);
    }
    
    // disqus
    public function disqusNews(int $id) : string
    {
        return $this->abs($this->news($id));
    }
    
    public function disqusArticle(Article $article) : string
    {
        $id = $article->nameEn;
        
        if ($article->category() != null) {
            $cat = $article->category()->nameEn;
        }

        return $this->abs($this->article($id, $cat));
    }
    
    public function disqusGalleryAuthor(GalleryAuthor $author) : string
    {
        return $this->abs($this->galleryAuthor($author));
    }

    public function disqusRecipes(?Skill $skill) : string
    {
        return $this->abs($this->recipes($skill));
    }

    public function disqusRecipe(int $id) : string
    {
        return $this->abs($this->recipe($id));
    }

    // forum
    public function forumTag(string $text) : string
    {
        return $this->forumUrl(
            'app=core&module=search&do=search&search_tags=' . urlencode($text) . '&search_app=forums'
        );
    }
    
    public function forumUser(int $id) : string
    {
        return $this->forumUrl('showuser=' . $id);
    }
    
    public function forumTopic(int $id, bool $new = false) : string
    {
        $appendix = $new ? '&view=getnewpost' : '';
        
        return $this->forumUrl('showtopic=' . $id . $appendix);
    }
    
    public function forumUpload(string $name) : string
    {
        return $this->getSettings('forum.index') . '/uploads/' . $name;
    }
    
    // gallery
    public function galleryAuthor(GalleryAuthor $author) : string
    {
        return $this->router->pathFor(
            'main.gallery.author',
            ['alias' => $author->alias]
        );
    }

    public function galleryPictureImg(GalleryPicture $picture)
    {
        return $this->gallery->getPictureUrl($picture->toArray());
    }
    
    public function galleryThumbImg(GalleryPicture $picture)
    {
        return $this->gallery->getThumbUrl($picture->toArray());
    }
    
    public function galleryPicture(string $alias, int $id) : string
    {
        return $this->router->pathFor(
            'main.gallery.picture',
            ['alias' => $alias, 'id' => $id]
        );
    }
    
    // streams
    public function stream(string $alias = null) : string
    {
        return $this->router->pathFor(
            'main.stream',
            ['alias' => $alias]
        );
    }
    
    // comics
    public function comicSeries($series) : string
    {
        return $this->router->pathFor(
            'main.comics.series',
            ['alias' => $series->alias]
        );
    }

    public function comicIssue($comic)
    {
        $series = $comic->series();
        
        if (is_null($series)) {
            throw new InvalidArgumentException("Comic issue {$comic} has no comic series.");
        }
        
        return $this->router->pathFor(
            'main.comics.issue',
            [
                'alias' => $series->alias,
                'number' => $comic->number,
            ]
        );
    }

    public function comicIssuePage($page)
    {
        $comic = $page->comic();
        
        if (is_null($comic)) {
            dd($page);
            
            throw new InvalidArgumentException("Comic issue page {$page} has no comic issue.");
        }
        
        $series = $comic->series();
        
        if (is_null($series)) {
            throw new InvalidArgumentException("Comic issue {$comic} has no comic series.");
        }
        
        return $this->router->pathFor(
            'main.comics.issue.page',
            [
                'alias' => $series->alias,
                'number' => $comic->number,
                'page' => $page->number,
            ]
        );
    }

    public function comicStandalone($comic)
    {
        return $this->router->pathFor(
            'main.comics.standalone',
            ['alias' => $comic->alias]
        );
    }

    public function comicStandalonePage($page)
    {
        $comic = $page->comic();
        
        if (is_null($comic)) {
            throw new InvalidArgumentException("Comic standalone page {$page} has no comic standalone.");
        }
        
        return $this->router->pathFor(
            'main.comics.standalone.page',
            [
                'alias' => $comic->alias,
                'page' => $page->number,
            ]
        );
    }

    public function comicPageImg($page)
    {
        $ext = $this->getExtension($page->type);
        return $this->getSettings('folders.comics_pages_public') . $page->id . '.' . $ext;
    }
    
    public function comicThumbImg($page)
    {
        $ext = $this->getExtension($page->type);
        return $this->getSettings('folders.comics_thumbs_public') . $page->id . '.' . $ext;
    }
    
    // recipes
    public function recipes(?Skill $skill = null)
    {
        $params = [];
        
        if ($skill) {
            $params['skill'] = $skill->alias;
        }
        
        return $this->router->pathFor('main.recipes', $params);
    }
    
    public function recipe(int $id) : string
    {
        return $this->router->pathFor('main.recipe', ['id' => $id]);
    }
    
    // wowhead
    public function wowheadIcon($icon)
    {
        $icon = strtolower($icon);
        return "//static.wowhead.com/images/wow/icons/medium/{$icon}.jpg";
    }
    
    private function wowheadUrl($params)
    {
        return $this->getSettings('webdb_link') . $params;
    }
    
    private function wowheadUrlRu($params)
    {
        return $this->getSettings('webdb_ru_link') . $params;
    }
    
    public function wowheadSpellRu($id)
    {
        return $this->wowheadUrlRu('spell=' . $id);
    }
    
    public function wowheadItemRu($id)
    {
        return $this->wowheadUrlRu('item=' . $id);
    }
    
    public function wowheadItem($id)
    {
        return $this->wowheadUrl('item=' . $id);
    }
    
    public function wowheadItemXml($id)
    {
        return $this->wowheadItem($id) . '&xml';
    }
    
    public function wowheadItemRuXml($id)
    {
        return $this->wowheadItemRu($id) . '&xml';
    }
    
    // hs
    public function hsCard(string $id) : string
    {
        return $this->getSettings('hsdb_ru_link') . 'cards/' . $id;
    }
}
