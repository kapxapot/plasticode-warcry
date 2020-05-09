<?php

namespace App\Core;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Models\ComicIssue;
use App\Models\ComicIssuePage;
use App\Models\ComicPage;
use App\Models\ComicSeries;
use App\Models\ComicStandalone;
use App\Models\ComicStandalonePage;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Models\Skill;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Core\Linker as LinkerBase;
use Plasticode\Gallery\Gallery;
use Plasticode\IO\File;
use Plasticode\Util\Strings;
use Slim\Interfaces\RouterInterface;

class Linker extends LinkerBase implements LinkerInterface
{
    private Gallery $gallery;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        RouterInterface $router,
        Gallery $gallery,
        TagsConfigInterface $tagsConfig
    )
    {
        parent::__construct($settingsProvider, $router, $tagsConfig);

        $this->gallery = $gallery;
    }

    private function forumUrl(string $url) : string
    {
        return $this->settingsProvider->get('forum.page') . '?' . $url;
    }

    /**
     * Get article link.
     *
     * @param int|string|null $id
     */
    public function article($id = null, ?string $cat = null) : string
    {
        $params = ['id' => Strings::fromSpaces($id)];

        if (strlen($cat) > 0) {
            $params['cat'] = Strings::fromSpaces($cat);
        }

        return $this->router->pathFor('main.article', $params);
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

        if ($game && !$game->isDefault()) {
            $params['game'] = $game->alias;
        }

        return $this->router->pathFor('main.index', $params);
    }

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
        return $this->settingsProvider->get('forum.index') . '/uploads/' . $name;
    }

    public function galleryAuthor(GalleryAuthor $author) : string
    {
        return $this->router->pathFor(
            'main.gallery.author',
            ['alias' => $author->alias]
        );
    }

    public function galleryPictureImg(GalleryPicture $picture) : string
    {
        return $this->gallery->getPictureUrl($picture->toArray());
    }

    public function galleryThumbImg(GalleryPicture $picture) : string
    {
        return $this->gallery->getThumbUrl($picture->toArray());
    }

    public function galleryPicture(GalleryPicture $picture) : string
    {
        return $this->router->pathFor(
            'main.gallery.picture',
            [
                'alias' => $picture->author()->alias,
                'id' => $picture->getId()
            ]
        );
    }

    public function stream(string $alias = null) : string
    {
        return $this->router->pathFor(
            'main.stream',
            ['alias' => $alias]
        );
    }

    public function comicSeries(ComicSeries $series) : string
    {
        return $this->router->pathFor(
            'main.comics.series',
            ['alias' => $series->alias]
        );
    }

    public function comicIssue(ComicIssue $comic) : string
    {
        $series = $comic->series();

        return $this->router->pathFor(
            'main.comics.issue',
            [
                'alias' => $series->alias,
                'number' => $comic->number,
            ]
        );
    }

    public function comicIssuePage(ComicIssuePage $page) : string
    {
        $comic = $page->comic();
        $series = $comic->series();

        return $this->router->pathFor(
            'main.comics.issue.page',
            [
                'alias' => $series->alias,
                'number' => $comic->number,
                'page' => $page->number,
            ]
        );
    }

    public function comicStandalone(ComicStandalone $comic) : string
    {
        return $this->router->pathFor(
            'main.comics.standalone',
            ['alias' => $comic->alias]
        );
    }

    public function comicStandalonePage(ComicStandalonePage $page) : string
    {
        $comic = $page->comic();

        return $this->router->pathFor(
            'main.comics.standalone.page',
            [
                'alias' => $comic->alias,
                'page' => $page->number,
            ]
        );
    }

    public function comicPageImg(ComicPage $page) : string
    {
        $folder = $this->settingsProvider
            ->get('folders.comics_pages_public');

        return File::combine($folder, $page->fileName());
    }

    public function comicThumbImg(ComicPage $page) : string
    {
        $folder = $this->settingsProvider
            ->get('folders.comics_thumbs_public');

        return File::combine($folder, $page->fileName());
    }

    public function recipes(?Skill $skill = null) : string
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

    public function wowheadIcon(string $icon) : string
    {
        $icon = strtolower($icon);
        return '//static.wowhead.com/images/wow/icons/medium/' . $icon . '.jpg';
    }

    public function wowheadUrl(string $params) : string
    {
        return $this->settingsProvider->get('webdb_link') . $params;
    }

    public function wowheadUrlRu(string $params) : string
    {
        return $this->settingsProvider->get('webdb_ru_link') . $params;
    }

    public function wowheadSpellRu(int $id) : string
    {
        return $this->wowheadUrlRu('spell=' . $id);
    }

    public function wowheadItemRu(int $id) : string
    {
        return $this->wowheadUrlRu('item=' . $id);
    }

    public function wowheadItem(int $id) : string
    {
        return $this->wowheadUrl('item=' . $id);
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
        return $this->settingsProvider
            ->get('hsdb_ru_link') . 'cards/' . $id;
    }
}
