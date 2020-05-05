<?php

namespace App\Core\Interfaces;

use App\Models\Article;
use App\Models\ComicIssue;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Models\Skill;
use Plasticode\Core\Interfaces\LinkerInterface as PlasticodeLinkerInterface;

interface LinkerInterface extends PlasticodeLinkerInterface
{
    function game(?Game $game) : string;

    /**
     * Get article link.
     *
     * @param int|string|null $id
     */
    function article($id = null, ?string $cat = null) : string;

    function event(int $id = null) : string;
    function video(int $id = null) : string;
    function stream(string $alias = null) : string;

    function forumUser(int $id) : string;
    function forumTopic(int $id, bool $new = false) : string;

    function newsYear(int $year) : string;

    function recipe(int $id) : string;
    function recipes(?Skill $skill = null) : string;

    function galleryAuthor(GalleryAuthor $author) : string;
    function galleryPicture(GalleryPicture $picture) : string;
    function galleryPictureImg(GalleryPicture $picture) : string;
    function galleryThumbImg(GalleryPicture $picture) : string;

    function comicIssue(?ComicIssue $comic) : string;

    function disqusArticle(Article $article) : string;
    function disqusNews(int $id) : string;
    function disqusGalleryAuthor(GalleryAuthor $author) : string;
    function disqusRecipes(?Skill $skill) : string;
    function disqusRecipe(int $id) : string;
    
    function wowheadIcon(string $icon) : string;
    function wowheadUrl(string $params) : string;
    function wowheadUrlRu(string $params) : string;
    function wowheadSpellRu(int $id) : string;
    function wowheadItemRu(int $id) : string;
    function wowheadItem(int $id) : string;
    function wowheadItemXml(int $id) : string;
    function wowheadItemRuXml(int $id) : string;

    function hsCard(string $id) : string;
}
