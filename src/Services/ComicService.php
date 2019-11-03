<?php

namespace App\Services;

use App\Models\ComicIssue;
use App\Models\ComicStandalone;
use Plasticode\Exceptions\InvalidArgumentException;

class ComicService
{
    public function getComicByContext(array $data)
    {
        if (isset($data['comic_issue_id'])) {
            return ComicIssue::get($data['comic_issue_id']);
        }
        
        if (isset($data['comic_standalone_id'])) {
            return ComicStandalone::get($data['comic_standalone_id']);
        }
        
        throw new InvalidArgumentException(
            'Either comic_issue_id or comic_standalone_id must be provided.'
        );
    }
}
