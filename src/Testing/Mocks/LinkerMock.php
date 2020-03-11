<?php

namespace App\Testing\Mocks;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Models\ComicIssue;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Models\Skill;
use Plasticode\Testing\Mocks\LinkerMock as LinkerMockBase;
use Plasticode\Util\Strings;

class LinkerMock extends LinkerMockBase implements LinkerInterface
{
    public function game(?Game $game) : string
    {
        return $this->abs($game->alias);
    }

    public function article($id = null, ?string $cat = null) : string
    {
        $id = Strings::fromSpaces($id);
        
        if (strlen($cat) > 0) {
            $cat = Strings::fromSpaces($cat);
        }

        return $this->abs('/articles/') . $id . ($cat ? '/' . $cat : '');
    }

    public function event(int $id = null) : string
    {
        return $this->abs('/events/') . $id;
    }

    public function video(int $id = null) : string
    {
        return $this->abs('/videos/') . $id;
    }

    public function stream(string $alias = null) : string
    {
        return $this->abs('/streams/') . $alias;
    }

    public function recipe(int $id) : string
    {
        return $this->abs('/recipes/') . $id;
    }

    public function recipes(?Skill $skill = null) : string
    {
        return $this->abs('/recipes/') . $skill->alias;
    }

    public function galleryPictureImg(GalleryPicture $picture) : string
    {
        return $this->abs('/gallery/picture/') . $picture->getId();
    }
    
    public function galleryThumbImg(GalleryPicture $picture) : string
    {
        return $this->abs('/gallery/picture/thumb/') . $picture->getId();
    }

    public function comicIssue(?ComicIssue $comic) : string
    {
        return $this->abs('/comic_issues/') . $comic->getId();
    }

    public function disqusArticle(Article $article) : string
    {
        return 'disqus/article/' . $article->getId();
    }

    public function disqusNews(int $id) : string
    {
        return 'disqus/news/' . $id;
    }

    function disqusGalleryAuthor(GalleryAuthor $author) : string
    {
        return 'disqus/gallery_author/' . $author->getId();
    }

    function disqusRecipes(?Skill $skill) : string
    {
        return 'disqus/recipes/' . $skill->alias;
    }

    function disqusRecipe(int $id) : string
    {
        return 'disqus/recipes/' . $id;
    }

    public function wowheadIcon(string $icon) : string
    {
        return 'wowhead/icons/' . $icon;
    }

    public function wowheadUrl(string $params) : string
    {
        return 'wowhead/' . $params;
    }

    public function wowheadUrlRu(string $params) : string
    {
        return 'wowhead.ru/' . $params;
    }

    public function wowheadSpellRu(int $id) : string
    {
        return 'wowhead.ru/spells/' . $id;
    }

    public function wowheadItemRu(int $id) : string
    {
        return 'wowhead.ru/items/' . $id;
    }

    public function wowheadItem(int $id) : string
    {
        return 'wowhead/items/' . $id;
    }

    public function wowheadItemXml(int $id) : string
    {
        return $this->wowheadItem($id) . '&xml';
    }

    public function wowheadItemRuXml(int $id) : string
    {
        return $this->wowheadItemRu($id) . '&xml';
    }

    public function hsCard(string $id) : string
    {
        return 'http://hscards.com/' . $id;
    }
}
