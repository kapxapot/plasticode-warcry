<?php

namespace App\Repositories\Interfaces;

use App\Collections\ArticleCollection;
use App\Models\Article;

interface ArticleRepositoryInterface extends SearchableNewsSourceRepositoryInterface
{
    function get(?int $id) : ?Article;
    function getProtected(?int $id) : ?Article;
    function getBySlugOrAlias(string $name, string $cat = null) : ?Article;
    function getBySlug(string $slug, string $cat = null) : ?Article;
    function getByAlias(string $name, string $cat = null) : ?Article;
    function getChildren(Article $parent) : ArticleCollection;
    function getAllPublishedOrphans() : ArticleCollection;
}
