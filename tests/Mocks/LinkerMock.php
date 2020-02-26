<?php

namespace App\Tests\Mocks;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Game;
use Plasticode\Tests\Mocks\LinkerMock as LinkerMockBase;
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

    public function hsCard(string $id) : string
    {
        return 'http://hscards.com/' . $id;
    }

    public function disqusNews(int $id) : string
    {
        return 'disqus/news/' . $id;
    }
}
