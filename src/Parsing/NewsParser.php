<?php

namespace App\Parsing;

use App\Core\Interfaces\LinkerInterface;
use App\Core\Interfaces\RendererInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Numbers;
use Plasticode\Util\Text;

class NewsParser
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var RendererInterface */
    private $renderer;

    /** @var LinkerInterface */
    private $linker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        RendererInterface $renderer,
        LinkerInterface $linker
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->renderer = $renderer;
        $this->linker = $linker;
    }

    private function decodeHtmlSpecialChars($str)
    {
        return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
    }
    
    public function beforeParsePost($post, $tid)
    {
        $post = str_replace(" target='_blank'", "", $post);
        $post = str_replace(" target=\"_blank\"", "", $post);
        //$post = str_replace("<a", "<noindex><a", $post);
        //$post = str_replace("</a>", "</a></noindex>", $post);
        $post = str_replace("<p>", "", $post);
        $post = str_replace("</p>", "", $post);
        $post = $this->decodeHtmlSpecialChars($post);
    
        if ($tid >= 6922 || $tid == 6902) {
            $post = str_replace("[sup]", "<sup>", $post);
            $post = str_replace("[/sup]", "</sup>", $post);
            $post = str_replace("[center]", "<div class='center'>", $post);
            $post = str_replace("[/center]", "</div>", $post);
            $post = str_replace("<br />", "", $post);
            $post = str_replace("[url=\"", "[url=", $post);
            $post = str_replace("\"]", "]", $post);
            //$post = str_replace("[url", "<noindex>[url", $post);
            //$post = str_replace("[/url]", "[/url]</noindex>", $post);
    
            $url = $this->linker->news($tid);
            $url = $this->linker->abs($url);
        }
    
        return $post;
    }

    public function decodeTopicTitle($tt)
    {
        $tt = $this->decodeHtmlSpecialChars($tt);
        return str_replace('&#33;', '!', $tt);
    }

    public function afterParsePost($str)
    {
        $siteUrl = $this->settingsProvider->get('view_globals.site_url');

        // fking smileys
        $str = str_replace("<img src=\"style_emoticons/", "<imgr src=\"/forum/public/style_emoticons/", $str);
        $str = str_replace("<img src='{$siteUrl}/forum/public/style_emoticons", "<imgr src='/forum/public/style_emoticons", $str);
        $str = str_replace("<img src=\"{$siteUrl}/forum/style_emoticons", "<imgr src=\"/forum/style_emoticons", $str);

        $str = str_replace("<#EMO_DIR#>", "default", $str);
        
        $str = Text::toAbsoluteUrls($str, $this->linker->abs());

        while (preg_match("/(<a href='[^']*\.(jpg|gif|jpeg|png)')>/i", $str, $matches)) {
            $str = str_replace($matches[0], $matches[1]." rel=\"colorbox\">", $str);
        }
        
        $str = preg_replace("/(<img [^>]*>)/", "<div class=\"img\">$0</div>", $str);
        $str = str_replace("<img ", "<img class=\"img-responsive center\" ", $str);
    
        // smileys?..
        $str = str_replace("<imgr", "<img", $str);
        
        $str = str_replace(" border='0' alt='user posted image'", " alt=\"\"", $str);
    
        $str = $this->parseTopic($str);
        $str = $this->parseYoutube($str);
        $str = $this->parseLeftImg($str);
        $str = $this->parseRightImg($str);
        $str = $this->parseCoolquote($str);
        $str = $this->parseBluepost($str);
        $str = $this->parseSpoiler($str);
    
        return $str;
    }

    private function parseTopic($str)
    {
        $newstr = "";
        
        $parts = preg_split("/(\[topic[^\[]*\].*\[\/topic\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[topic([^\[]*)\](.*)\[\/topic\]/", $part, $matches)) {
                $attrs = trim($matches[1]);
                $content = $matches[2];
                
                $id = 0;
                if (preg_match("/=(.*)/", $attrs, $matches)) {
                    $id = $matches[1];
                }
    
                $newstr .= "<a href=\"http://warcry.ru/news/$id\">$content</a>";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
    
    private function parseYoutube($str)
    {
        $newstr = "";
        
        $parts = preg_split("/(\[youtube\].*\[\/youtube\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[youtube\](.*)\[\/youtube\]/", $part, $matches)) {
                $code = $matches[1];
                $newstr .= "<div class=\"embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" width=\"640\" height=\"360\" src=\"https://www.youtube.com/embed/{$code}\" allowfullscreen></iframe></div>";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
    
    private function parseLeftImg($str)
    {
        $newstr = "";
        
        $parts = preg_split("/(\[leftimg\].*\[\/leftimg\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[leftimg\](.*)\[\/leftimg\]/", $part, $matches)) {
                $code = $matches[1];
                $newstr .= "<img src=\"{$code}\" class=\"img-left\" />";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
    
    private function parseRightImg($str)
    {
        $newstr = "";
        
        $parts = preg_split("/(\[rightimg\].*\[\/rightimg\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[rightimg\](.*)\[\/rightimg\]/", $part, $matches)) {
                $code = $matches[1];
                $newstr .= "<img src=\"{$code}\" class=\"img-right\" />";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
    
    private function parseBluepost($str)
    {
        $newstr = "";
    
        //while (preg_match("/(<noindex><a href='[^']*'>)\[bluepost=([^\]]*)\](<\/a><\/noindex>)/", $str, $matches)) {
        while (preg_match("/(<a href='[^']*'>)\[bluepost=([^\]]*)\](<\/a>)/", $str, $matches)) {
            $str = str_replace($matches[0], "[bluepost=".$matches[1].$matches[2].$matches[3]."]", $str);
        }
        
        $parts = preg_split("/(\[bluepost[^\[]*\].*\[\/bluepost\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[bluepost([^\[]*)\](.*)\[\/bluepost\]/", $part, $matches)) {
                $attrs = trim($matches[1]);
                $content = $matches[2];
                
                $author = "Blizzard";

                if (preg_match("/=(.*)/", $attrs, $matches)) {
                    $author = $matches[1];
                }
    
                $newstr .= "<div class=\"quote bluepost\"><div class=\"quote-header\"><b>{$author}</b>:</div><div class=\"quote-body\">{$content}</div></div>";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
    
    private function parseCoolquote($str)
    {
        $newstr = "";
    
        //while (preg_match("/(<noindex><a href='[^']*'>)\[coolquote=([^\]]*)\](<\/a><\/noindex>)/", $str, $matches)) {
        while (preg_match("/(<a href='[^']*'>)\[coolquote=([^\]]*)\](<\/a>)/", $str, $matches)) {
            $str = str_replace($matches[0], "[coolquote=".$matches[1].$matches[2].$matches[3]."]", $str);
        }
            
        $parts = preg_split("/(\[coolquote[^\[]*\].*\[\/coolquote\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[coolquote([^\[]*)\](.*)\[\/coolquote\]/", $part, $matches)) {
                $attrs = trim($matches[1]);
                $content = $matches[2];
                
                $author = "Цитата";
                if (preg_match("/=(.*)/", $attrs, $matches)) {
                    $author = $matches[1];
                }
    
                $newstr .= "<div class=\"quote\"><div class=\"quote-header\"><b>$author</b>:</div><div class=\"quote-body\" style=\"padding-top: 5px;\">$content</div></div>";
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }

    private function parseSpoiler($str)
    {
        $newstr = "";
    
        $parts = preg_split("/(\[spoiler.*\].*\[\/spoiler\])/U", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match("/\[spoiler(.*)\](.*)\[\/spoiler\]/", $part, $matches)) {
                $attrs = trim($matches[1]);
                $content = $matches[2];
                
                $label = "Спойлер";
                if (preg_match("/=(.*)/", $attrs, $matches)) {
                    $label = $matches[1];
                }
                
                $newstr .= $this->renderer->component(
                    'spoiler',
                    [
                        'id' => Numbers::generate(10),
                        'title' => $label,
                        'body' => $content,
                    ]
                );
            } else {
                $newstr .= $part;
            }
        }
    
        return $newstr;
    }
}
