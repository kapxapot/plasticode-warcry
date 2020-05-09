<?php

namespace App\Repositories\Interfaces;

use App\Collections\ForumTopicCollection;
use App\Models\ForumTopic;
use App\Models\Game;

interface ForumTopicRepositoryInterface extends NewsSourceRepositoryInterface
{
    function get(?int $id) : ?ForumTopic;
    function getAllByTag(string $tag, int $limit = 0) : ForumTopicCollection;
    function getNewsByTag(string $tag, int $limit = 0) : ForumTopicCollection;

    function getLatestNews(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : ForumTopicCollection;

    function getNewsBefore(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : ForumTopicCollection;

    function getNewsAfter(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : ForumTopicCollection;

    function getNewsByYear(int $year) : ForumTopicCollection;

    function getNews(?int $id) : ?ForumTopic;
}
