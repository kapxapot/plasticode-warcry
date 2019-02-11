<?php

namespace App\Data;

use Plasticode\Data\Db as DbBase;
use Plasticode\Util\Arrays;
use Plasticode\Util\Date;

class Db extends DbBase
{
    private $games;
    
	public function init()
	{
	    $this->initGames();
	}
	
	private function initGames()
	{
	    $games = Arrays::toAssocBy($this->getGames(true));

	    // roots
	    foreach ($games as &$game) {
	        $cur = $game;
	        
	        do {
	            $game['root_id'] = $cur['id'];
	            $cur = $games[$cur['parent_id']] ?? null;
	        } while ($cur);
	    }
	    
	    // subgames
	    $byRoot = Arrays::groupBy($games, 'root_id');
	    foreach ($byRoot as $rootId => $group) {
	        $games[$rootId]['subgames'] = array_column($group, 'id');
	    }
	    
	    $this->games = $games;
	}

	// ARTICLES

	public function getCat($id)
	{
		return $this->get(Tables::ARTICLE_CATEGORIES, $id);
	}
	
	/**
	 * Returns cat by cat name_en.
	 */
	public function getCatIdByName($name)
	{
        return $this->getIdByField(Tables::ARTICLE_CATEGORIES, 'name_en', $name);
	}

	/**
	 * Returns article by id (id - numeric, name_en - text) and cat id (optional).
	 */
	public function getArticle($id, $cat = null)
	{
		return $this->getProtected(Tables::ARTICLES, $id, function($q) use ($id, $cat) {
			if (is_numeric($id)) {
				$q = $q->where('id', $id);
			}
			else {
				$q = $q->where('name_en', $id);
	
				if ($cat) {
					$catId = $this->getCatIdByName($cat);
				}
	
				if ($catId) {
					$q = $q
						->whereRaw('(cat = ? or cat is null)', [ $catId ])
						->orderByDesc('cat');
				}
				else {
					$q = $q->orderByAsc('cat');
				}
			}
	
			return $q;
		});
	}

	/**
	 * Returns sub articles by article id (numeric strict).
	 */
	public function getSubArticles($parentId)
	{
		return $this->getMany(Tables::ARTICLES, function($q) use ($parentId) {
			if ($parentId > 0) {
				$q = $q->where('parent_id', $parentId);
			}
			else {
				$q = $q->whereRaw('(parent_id = 0 or parent_id is null)');
			}
			
			return $q
				->where('published', 1)
				->orderByAsc('name_ru');
		});
	}

	public function getItemId($name)
	{
		return $this->getIdByName(Tables::ITEMS, $name);
	}

	// to kill
	public function getNPCId($name)
	{
		return $this->getIdByName(Tables::NPC, $name);
	}

	public function getSpellId($name, $skill = null)
	{
		return $this->getIdByName(Tables::SPELLS, $name, function($q) use ($skill) {
			return $skill
				? $q->where('skill', $skill)
				: $q;
		});
	}

	// to kill
	public function getQuestId($name)
	{
		return $this->getIdByName(Tables::QUESTS, $name);
	}

	// to kill (eventually)
	public function getLocationId($name)
	{
		return $this->getIdByName(Tables::LOCATIONS, $name);
	}

	public function saveArticleCache($id, $cache)
	{
		$this->setFieldNoStamps(Tables::ARTICLES, $id, 'cache', $cache);
	}

	public function saveArticleContentsCache($id, $contentsCache)
	{
		$this->setFieldNoStamps(Tables::ARTICLES, $id, 'contents_cache', $contentsCache);
	}

	public function getArticlesByTag($tag)
	{
		return $this->getByTag(Tables::ARTICLES, Taggable::ARTICLES, $tag);
	}

	// RECIPES
	
	private function getRecipeQuery($skill = null, $q = null)
	{
		$query = $this
			->forTable(Tables::RECIPES);
			//->whereNotEqual('quality', '@');

		if ($skill) {
			$query = $query->where('skill', $skill);
		}

		if ($q) {
			$qParts = preg_split("/\s/", $q);
			foreach ($qParts as $qPart) {
				$decor = '%' . $qPart . '%';
				$query = $query
					->whereRaw('(name like ? or name_ru like ?)', [ $decor, $decor ]);
			}
		}

		return $query;
	}

	public function getRecipes($offset = 0, $limit = 0, $skill = null, $q = null)
	{
		$query = $this->getRecipeQuery($skill, $q)
			->orderByAsc('learnedat')
			->orderByAsc('lvl_orange')
			->orderByAsc('lvl_yellow')
			->orderByAsc('lvl_green')
			->orderByAsc('lvl_gray')
			->orderByAsc('name_ru');
		
		if ($limit > 0) {
			$query = $query
				->offset($offset)
				->limit($limit);
		}

		return $this->getArray($query);
	}

	function getRecipeCount($skill = null, $q = null)
	{
		$query = $this->getRecipeQuery($skill, $q);
		return $query->count();
	}

	public function getRecipeSources()
	{
		return $this->getMany(Tables::RECIPE_SOURCES);
	}

	public function getRecipeSource($id)
	{
		return $this->get(Tables::RECIPE_SOURCES, $id);
	}

	public function getSpellIcon($id)
	{
		return $this->get(Tables::SPELL_ICONS, $id);
	}

	public function setRecipeReagentIcons($id, $reagentIcons)
	{
		$this->setField(Tables::RECIPES, $id, 'reagent_icons', $reagentIcons);
	}

	public function getSkills() {
		return $this->getMany(Tables::SKILLS, function($q) {
			return $q->where('active', 1);
		});
	}

	public function getSkillByAlias($alias) {
		return $this->getByField(Tables::SKILLS, 'alias', $alias);
	}

	public function getSkill($id) {
		return $this->get(Tables::SKILLS, $id);
	}

	public function setRecipeIcon($id, $icon) {
		$this->setField(Tables::RECIPES, $id, 'icon', $icon);
	}

	public function setRecipeReagentCache($id, $reagentCache) {
		$this->setFieldNoStamps(Tables::RECIPES, $id, 'reagent_cache', $reagentCache);
	}

	public function setRecipeIconCache($id, $iconCache) {
		$this->setFieldNoStamps(Tables::RECIPES, $id, 'icon_cache', $iconCache);
	}

	public function getRecipe($id) {
		return $this->get(Tables::RECIPES, $id);
	}

	public function getRecipeByName($name) {
		return $this->getBy(Tables::RECIPES, function($q) use ($name) {
			return $q
				->whereRaw('(name like ? or name_ru like ?)', [ $name, $name ]);
		});
	}

	public function getRecipesByItemId($itemId) {
		return $this->getMany(Tables::RECIPES, function($q) use ($itemId) {
			return $q
				->where('creates_id', $itemId)
				->whereGt('creates_min', 0);
		});
	}

	/*public function getRecipeByItemId($itemId) {
		$sources = $this->getRecipesByItemId($itemId);
		
		if (is_array($sources)) {
			foreach ($sources as $source) {
				$srcId = $source['id'];
				$srcName = $source['name'];
				$srcNameRu = $source['name_ru'];
				
				// исключаем "трансмуты" кожи и  прочие циклы
				$forbidden = [ 50936, 64661, 32455, 32454, 22331, 20650, 20649, 20648, 2881, 12716, 13240 ];
				
				// разрешаем трансмуты, которые не зацикливаются и без которых не произвести тех или иных регов
				$allowed = [ 11480, 17187, 32765, 29688, 32766, 57427, 57425, 66658, 66662, 66664, 66660, 66663, 66659 ];
				
				if (in_array($srcId, $allowed) || (!in_array($srcId, $forbidden) && !preg_match('/^Transmute/', $srcName))) {
					$result = $source;
					break;
				}
			}
		}

		return $result;
	}*/

	public function getItem($id) {
		return $this->get(Tables::ITEMS, $id);
	}

	public function saveItem($id, $item) {
		$this->set(Tables::ITEMS, $id, $item);
	}

	public function getItems() {
		return $this->getMany(Tables::ITEMS);
	}

	// NEW STUFF

	private function getNewsForumIds($filterByGame = null) {
		$games = $this->getSubGames($filterByGame) ?? $this->games;

		return Arrays::extract($games, 'news_forum_id');
	}
	
	private function topicsWithPosts($topics) {
		return array_map(function($topic) {
			$post = $this->getForumTopicPost($topic['tid']);
			if ($post) {
				$topic['post'] = $post['post'];
			}

			return $topic;
		}, $topics);
	}

	public function getLatestForumNews($filterByGame = null, $offset = 0, $limit = 0, $exceptNewsId = null, $year = null) {
		$forumIds = $this->getNewsForumIds($filterByGame);

		$query = $this
			->forTable(Tables::FORUM_TOPICS)
			->whereIn('forum_id', $forumIds);

		if ($exceptNewsId) {
			$query = $query->whereNotEqual('tid', $exceptNewsId);
		}

		$query = $query->orderByDesc('start_date');
			
		if ($offset > 0 || $limit > 0) {
			$query = $query
				->offset($offset)
				->limit($limit);
		}
		
		if ($year > 0) {
			$query = $query->whereRaw('(year(from_unixtime(start_date)) = ?)', [ $year ]);
		}
        
		$topics = $this->getArray($query);

		return $this->topicsWithPosts($topics);
	}
	
	public function getForumNewsCount($filterByGame = null, $exceptNewsId = null, $year = null) {
		$news = $this->getLatestForumNews($filterByGame, 0, 0, $exceptNewsId, $year);
		
		return count($news);
	}

	public function getForumNewsByYear($year) {
		return $this->getLatestForumNews(null, 0, 0, null, $year);
	}
	
	public function getForumTopicPost($topicId) {
		return $this->getBy(Tables::FORUM_POSTS, function($q) use ($topicId) {
			return $q
				->where('topic_id', $topicId)
				->where('new_topic', 1);
		});
	}

	public function getForumNews($id) {
		$news = $this->getByField(Tables::FORUM_TOPICS, 'tid', $id);
		
		$post = $this->getForumTopicPost($id);
		if ($post) {
			$news['post'] = $post['post'];
		}
		
		return $news;
	}
	
	protected function byGame($query, $filterByGame)
	{
		$subGamesIds = $this->getSubGamesIds($filterByGame);
		if ($subGamesIds) {
			$query = $query->whereIn('game_id', $subGamesIds);
		}
		
		return $query;
	}

	public function getLatestNews($filterByGame = null, $offset = 0, $limit = 0, $exceptNewsId = null, $year = null)
	{
		return $this->getMany(Tables::NEWS, function($query) use ($filterByGame, $offset, $limit, $exceptNewsId, $year) {
			$query = $query
				->where('published', 1)
	   			->whereRaw('(published_at < now())');
	
			if ($exceptNewsId) {
				$query = $query->whereNotEqual('id', $exceptNewsId);
			}
			
			$query = $this->byGame($query, $filterByGame);
			$query = $query->orderByDesc('published_at');
			
			if ($offset > 0 || $limit > 0) {
				$query = $query
					->offset($offset)
					->limit($limit);
			}
			
			if ($year > 0) {
				$query = $query->whereRaw('(year(published_at) = ?)', [ $year ]);
			}
			
			return $query;
		});
	}
	
	public function getNewsCount($filterByGame = null, $exceptNewsId = null, $year = null)
	{
		$news = $this->getLatestNews($filterByGame, 0, 0, $exceptNewsId, $year);
		
		return count($news);
	}
	
	public function getAllNewsCount($filterByGame) {
		return $this->getNewsCount($filterByGame) + $this->getForumNewsCount($filterByGame);
	}

	public function getNewsByYear($year) {
		return $this->getLatestNews(null, 0, 0, null, $year);
	}

	private function getIdsByForumTag($app, $area, $tag) {
		$entities = $this->getMany(Tables::FORUM_TAGS, function($q) use ($app, $area, $tag) {
			return $q
				->where('tag_meta_app', $app)
				->where('tag_meta_area', $area)
				->whereRaw('(lcase(tag_text) = ?)', [ $tag ]);
		});
		
		return ($entities !== null)
		    ? array_column($entities, 'tag_meta_id')
		    : null;
	}
	
	public function getForumNewsByTag($tag)
	{
		$ids = $this->getIdsByForumTag('forums', 'topics', $tag);

		if (!$ids) {
			return null;
		}
		
		$forumIds = $this->getNewsForumIds();

		$query = $this
			->forTable(Tables::FORUM_TOPICS)
			->whereIn('forum_id', $forumIds)
			->whereIn('tid', $ids);

		$query = $query->orderByDesc('start_date');

		$topics = $this->getArray($query);

		return $this->topicsWithPosts($topics);
	}

	public function getNewsByTag($tag)
	{
		return $this->getByTag(Tables::NEWS, Taggable::NEWS, $tag);
	}

	public function getNews($id)
	{
		return $this->getProtected(Tables::NEWS, $id);
	}

	public function saveNewsCache($id, $cache)
	{
		$this->setFieldNoStamps(Tables::NEWS, $id, 'cache', $cache);
	}

	public function getForumTopicTags($topicId)
	{
		return $this->getMany(Tables::FORUM_TAGS, function($q) use ($topicId) {
			return $q
				->where('tag_meta_app', 'forums')
				->where('tag_meta_area', 'topics')
				->where('tag_meta_id', $topicId);
		});
	}

	public function getLatestArticles($filterByGame, $limit, $exceptArticleId)
	{
		$query = $this
			->forTable(Tables::ARTICLES)
			->where('published', 1)
			->where('announce', 1);
			
		if ($filterByGame) {
			//$query = $query->where('game_id', $filterByGame['id']);
			$query = $this->byGame($query, $filterByGame);
		}
		
		if ($exceptArticleId) {
			$query = $query->whereNotEqual('id', $exceptArticleId);
		}

		$query = $query
			->orderByDesc('published_at')
			->limit($limit);
		
		return $this->getArray($query);
	}
	
	public function getLatestForumTopics($filterByGame, $limit) {
		$query = $this
			->forTable(Tables::FORUM_TOPICS)
			->whereNotEqual('state', 'link')
			->whereGt('posts', 0);

		$hiddenIds = $this->getSettings('hidden_forum_ids');
		$query = $query->whereNotIn('forum_id', $hiddenIds);

		if ($filterByGame != null) {
			$forums = $this->getForumsByGameId($filterByGame['id']);
			if (count($forums) > 0) {
				$forumIds = array_column($forums, 'id');
				$query = $query->whereIn('forum_id', $forumIds);
			}
		}

		$result = $query
			->orderByDesc('last_post')
			->limit($limit)
			->findArray();
		
		return $result;
	}
	
	public function getDefaultGameId()
	{
		return $this->getSettings('default_game_id');
	}
	
	// games

	public function getGames($all = false)
	{
		return $this->getMany(Tables::GAMES, function($q) use ($all) {
		    if ($all !== true) {
		        $q = $q->where('published', 1);
		    }
		    
			return $q->orderByAsc('position');
		});
	}

	public function getGame($id)
	{
		return $this->games[$id] ?? null;
	}
	
	public function getGameByAlias($alias)
	{
		return Arrays::firstBy($this->games, 'alias', $alias);
	}

	public function getDefaultGame()
	{
		$id = $this->getDefaultGameId();
		return $this->getGame($id);
	}

	public function getRootGame($game)
	{
	    if (!is_array($game)) {
	        $game = $this->getGame($game);
	    }
	    
	    $rootId = $game['root_id'];
	    return $this->getGame($rootId);
	}
	
	public function getSubGamesIds($game)
	{
	    return $game['subgames'] ?? null;
	}
	
	public function getSubGames($game)
	{
	    $ids = $this->getSubGamesIds($game);
	    
	    if ($ids === null) {
	        return null;
	    }
	    
	    return Arrays::filter($this->games, function ($item) use ($ids) {
	        return in_array($item['id'], $ids);
	    });
	}
	
	// forums

	public function getForums()
	{
		return $this->getMany(Tables::FORUMS);
	}

	public function getForum($id)
	{
		return $this->get(Tables::FORUMS, $id);
	}

	public function getGameByForumId($forumId)
	{
		$path = 'gamesByForumId.' . $forumId;
		$games = $this->cache->get($path);
		
		if (!$games) {
			$games = $this->games;
			$foundGame = null;

			$curForumId = $forumId;
			
			while (!$foundGame && $curForumId != -1) {
				foreach ($games as $game) {
					if ($game['news_forum_id'] == $curForumId || $game['main_forum_id'] == $curForumId) {
						$foundGame = $game;
						break;
					}
				}

				if (!$foundGame) {
					$forum = $this->getForum($curForumId);
					$curForumId = $forum['parent_id'];
				}
			}

			$this->cache->set($path, $foundGame ?? $this->getDefaultGame());
		}

		return $this->cache->get($path);
	}
	
	public function getForumsByGameId($gameId)
	{
		$result = [];

		$forums = $this->getForums();

		foreach ($forums as $forum) {
			$game = $this->getGameByForumId($forum['id']);
			if ($game['id'] == $gameId) {
				$result[] = $forum;
			}
		}
		
		return $result;
	}

	public function getMenusByGame($gameId)
	{
		return $this->getMany(Tables::MENUS, function($q) use ($gameId) {
			return $q
				->where('game_id', $gameId)
				->orderByAsc('position');
		});
	}

	public function getMenuItems($menuId)
	{
		return $this->getMany(Tables::MENU_ITEMS, function($q) use ($menuId) {
			return $q
				->where('section_id', $menuId)
				->orderByAsc('position');
		});
	}
	
	public function getGalleryAuthorCategories()
	{
		return $this->getMany(Tables::GALLERY_AUTHOR_CATEGORIES, function($q) {
			return $q->orderByAsc('position');
		});
	}

	public function getGalleryAuthors()
	{
		return $this->getMany(Tables::GALLERY_AUTHORS, function($q) {
			return $q
				->where('published', 1);
		});
	}
	
	public function getGalleryAuthor($id, $all = false)
	{
		return $this->getBy(Tables::GALLERY_AUTHORS, function($q) use ($id, $all) {
			$q = $q->where('id', $id);
			
			if (!$all) {
				$q = $q->where('published', 1);
			}
			
			return $q;
		});
	}

	public function getGalleryAuthorByAlias($alias)
	{
		return $this->getBy(Tables::GALLERY_AUTHORS, function($q) use ($alias) {
			return $q
				->whereRaw('(alias = ? or id = ?)', [ $alias, $alias ])
				->where('published', 1);
		});
	}
	
	public function getGalleryPictures($authorId, $offset = 0, $limit = 0)
	{
		return $this->getMany(Tables::GALLERY_PICTURES, function($q) use ($authorId, $offset, $limit) {
			$q = $q
				->where('author_id', $authorId)
				->where('published', 1)
				->orderByDesc('published_at')
				->orderByDesc('id');
			
			if ($limit > 0) {
				$q = $q
					->offset($offset)
					->limit($limit);
			}
			
			return $q;
		});
	}
	
	public function getLatestGalleryPictures($filterByGame, $limit)
	{
		return $this->getMany(Tables::GALLERY_PICTURES, function($q) use ($filterByGame, $limit) {
			$q = $q
				->where('published', 1)
				->orderByDesc('published_at')
				->orderByDesc('id');
		    
			$q = $this->byGame($q, $filterByGame);

			if ($limit > 0) {
				$q = $q
					->offset(0)
					->limit($limit);
			}
			
			return $q;
		});
	}
	
	public function getGalleryPicture($id)
	{
		return $this->getBy(Tables::GALLERY_PICTURES, function($q) use ($id) {
			return $q
				->where('id', $id)
				->where('published', 1);
		});
	}
	
	public function getGalleryPicturesByTag($tag)
	{
		return $this->getByTag(Tables::GALLERY_PICTURES, Taggable::GALLERY_PICTURES, $tag);
	}
	
	public function getGalleryPicturePrev($pic)
	{
		return $this->getBy(Tables::GALLERY_PICTURES, function($q) use ($pic) {
			return $q
				->where('author_id', $pic['author_id'])
				->whereGt('published_at', $pic['published_at'])
				->where('published', 1)
				->orderByAsc('published_at')
				->orderByAsc('id');
		});
	}
	
	public function getGalleryPictureNext($pic)
	{
		return $this->getBy(Tables::GALLERY_PICTURES, function($q) use ($pic) {
			return $q
				->where('author_id', $pic['author_id'])
				->whereLt('published_at', $pic['published_at'])
				->where('published', 1)
				->orderByDesc('published_at')
				->orderByDesc('id');
		});
	}

	public function getForumMemberByUser($user)
	{
		return $this->getBy(Tables::FORUM_MEMBERS, function($q) use ($user) {
			return $q->where('name', $user['forum_name'] ?? $user['login']);
		});
	}

	public function getForumMemberByName($name)
	{
		return $this->getBy(Tables::FORUM_MEMBERS, function($q) use ($name) {
			return $q->where('name', $name);
		});
	}
	
	// COMICS
	
	public function getComicPublisher($id)
	{
		return $this->get(Tables::COMIC_PUBLISHERS, $id);
	}

	public function getComicSeries($id = null, $all = false)
	{
		return $id
			? $this->getBy(Tables::COMIC_SERIES, function($q) use ($id, $all) {
				$q = $q->where('id', $id);
				
				if (!$all) {
				    $q = $q->where('published', 1);
				}
				
				return $q;
			})
			: $this->getMany(Tables::COMIC_SERIES, function($q) {
				return $q->where('published', 1);
			});
	}

	public function getComicSeriesByAlias($alias)
	{
		return $this->getBy(Tables::COMIC_SERIES, function($q) use ($alias) {
			return $q
				->where('alias', $alias)
				->where('published', 1);
		});
	}
	
	public function getComicStandalones()
	{
		return $this->getMany(Tables::COMIC_STANDALONES, function($q) {
			return $q
				->where('published', 1)
				->orderByDesc('issued_on');
		});
	}
	
	public function getComicStandalone($id, $all = false)
	{
		return $this->getBy(Tables::COMIC_STANDALONES, function($q) use ($id, $all) {
			$q = $q->where('id', $id);
			
			if (!$all) {
			    $q = $q->where('published', 1);
			}
			
			return $q;
		});
	}
	
	public function getComicStandaloneByAlias($alias)
	{
		return $this->getBy(Tables::COMIC_STANDALONES, function($q) use ($alias) {
			return $q
				->where('alias', $alias)
				->where('published', 1);
		});
	}
	
	public function getComicIssues($seriesId)
	{
		return $this->getMany(Tables::COMIC_ISSUES, function($q) use ($seriesId) {
			return $q
				->where('series_id', $seriesId)
				->where('published', 1)
				->orderByAsc('number');
		});
	}
	
	public function getComicIssue($id, $seriesId = null, $number = null) {
		if ($id) {
			return $this->get(Tables::COMIC_ISSUES, $id);
		}
		else {
			return $this->getBy(Tables::COMIC_ISSUES, function($q) use ($seriesId, $number) {
				return $q
					->where('series_id', $seriesId)
					->where('number', $number)
					->where('published', 1);
			});
		}
	}

	public function getComicIssuePages($comicId)
	{
		return $this->getMany(Tables::COMIC_PAGES, function($q) use ($comicId) {
			return $q
				->where('comic_issue_id', $comicId)
				->where('published', 1)
				->orderByAsc('number');
		});
	}

	public function getComicIssuePage($comicId, $number)
	{
		return $this->getBy(Tables::COMIC_PAGES, function($q) use ($comicId, $number) {
			return $q
				->where('comic_issue_id', $comicId)
				->where('number', $number)
				->where('published', 1);
		});
	}
	
	public function getComicStandalonePages($comicId)
	{
		return $this->getMany(Tables::COMIC_PAGES, function($q) use ($comicId) {
			return $q
				->where('comic_standalone_id', $comicId)
				->where('published', 1)
				->orderByAsc('number');
		});
	}

	public function getComicStandalonePage($comicId, $number)
	{
		return $this->getBy(Tables::COMIC_PAGES, function($q) use ($comicId, $number) {
			return $q
				->where('comic_standalone_id', $comicId)
				->where('number', $number)
				->where('published', 1);
		});
	}

	// generic	
	private function getComicPagePrev($page, $filter)
	{
		return $this->getBy(Tables::COMIC_PAGES, function($q) use ($page, $filter) {
			return $q
				->where($filter, $page[$filter])
				->whereLt('number', $page['number'])
				->where('published', 1)
				->orderByDesc('number');
		});
	}
	
	public function getComicPageNext($page, $filter)
	{
		return $this->getBy(Tables::COMIC_PAGES, function($q) use ($page, $filter) {
			return $q
				->where($filter, $page[$filter])
				->whereGt('number', $page['number'])
				->where('published', 1)
				->orderByAsc('number');
		});
	}

	public function getComicStandalonePagePrev($page)
	{
		return $this->getComicPagePrev($page, 'comic_standalone_id');
	}
	
	public function getComicStandalonePageNext($page)
	{
		return $this->getComicPageNext($page, 'comic_standalone_id');
	}
	
	public function getComicIssuePagePrev($comic, $page)
	{
		$prevPage = $this->getComicPagePrev($page, 'comic_issue_id');
		if ($prevPage) {
			$prevPage['comic'] = $comic;
		}
		else {
			$prevComic = $this->getComicIssuePrev($comic);
			if ($prevComic) {
				$prevComicPages = $this->getComicIssuePages($prevComic['id']);
				$prevPage = array_values(array_slice($prevComicPages, -1))[0];
				$prevPage['comic'] = $prevComic;
			}
		}
		
		return $prevPage;
	}
	
	public function getComicIssuePageNext($comic, $page)
	{
		$nextPage = $this->getComicPageNext($page, 'comic_issue_id');
		if ($nextPage) {
			$nextPage['comic'] = $comic;
		}
		else {
			$nextComic = $this->getComicIssueNext($comic);
			if ($nextComic) {
				$nextComicPages = $this->getComicIssuePages($nextComic['id']);
				$nextPage = $nextComicPages[0];
				$nextPage['comic'] = $nextComic;
			}
		}
		
		return $nextPage;
	}
	
	public function getComicIssuePrev($comic)
	{
		return $this->getBy(Tables::COMIC_ISSUES, function($q) use ($comic) {
			return $q
				->where('series_id', $comic['series_id'])
				->whereLt('number', $comic['number'])
				->where('published', 1)
				->orderByDesc('number');
		});
	}
	
	public function getComicIssueNext($comic)
	{
		return $this->getBy(Tables::COMIC_ISSUES, function($q) use ($comic) {
			return $q
				->where('series_id', $comic['series_id'])
				->whereGt('number', $comic['number'])
				->where('published', 1)
				->orderByAsc('number');
		});
	}
	
	public function getComicSeriesByTag($tag)
	{
		return $this->getByTag(Tables::COMIC_SERIES, Taggable::COMIC_SERIES, $tag, function($q) {
			return $q->orderByAsc('name_ru');
		});
	}
	
	public function getComicIssuesByTag($tag)
	{
		return $this->getByTag(Tables::COMIC_ISSUES, Taggable::COMIC_ISSUES, $tag);
	}
	
	public function getComicStandalonesByTag($tag)
	{
		return $this->getByTag(Tables::COMIC_STANDALONES, Taggable::COMIC_STANDALONES, $tag, function($q) {
			return $q->orderByAsc('name_ru');
		});
	}
	
	public function getMaxComicIssueNumber($seriesId, $exceptId = null)
	{
        $comic = $this->getBy(Tables::COMIC_ISSUES, function($q) use ($seriesId, $exceptId) {
            $q = $q->where('series_id', $seriesId);
            
            if ($exceptId) {
                $q->whereNotEqual('id', $exceptId);
            }
            
            return $q->orderByDesc('number');
        });
        
        return $comic ? $comic['number'] : 0;
	}
	
	public function getMaxComicPageNumber($context, $exceptId = null)
	{
        $page = $this->getBy(Tables::COMIC_PAGES, function($q) use ($context, $exceptId) {
            foreach ($context as $key => $value) {
                $q = $q->where($key, $value);
            }
            
            if ($exceptId) {
                $q->whereNotEqual('id', $exceptId);
            }
            
            return $q->orderByDesc('number');
        });
        
        return $page ? $page['number'] : 0;
	}

	// STREAMS
	
	private function encodeStreamData($data)
	{
		if ($data) {
			$data['remote_status'] = urlencode($data['remote_status']);
		}
		
		return $data;
	}
	
	private function decodeStreamData($data)
	{
		if ($data) {
			$data['remote_status'] = urldecode($data['remote_status']);
		}
		
		return $data;
	}
	
	private function decodeManyStreamData($array)
	{
		return ($array !== null)
		    ? array_map(array($this, 'decodeStreamData'), $array)
		    : null;
	}
	
    public function getStreams()
    {
    	$streams = $this->getMany(Tables::STREAMS, function($q) {
    		return $q
    			->where('published', 1)
    			->orderByDesc('remote_viewers');
    	});

    	return $this->decodeManyStreamData($streams);
    }
    
    public function getStreamByAlias($alias)
    {
    	$stream = $this->getBy(Tables::STREAMS, function($q) use ($alias) {
    		return $q
    			->whereRaw('(stream_alias = ? or (stream_alias is null and stream_id = ?))', [ $alias, $alias ])
    			->where('published', 1);
    	});
    	
    	return $this->decodeStreamData($stream);
    }
	
	public function saveStream($data)
	{
		$data = $this->encodeStreamData($data);
		
		$stream = $this->getObj(Tables::STREAMS, $data['id']);

        $stream->remote_viewers = $data['remote_viewers'];
        $stream->remote_title = $data['remote_title'];
        $stream->remote_game = $data['remote_game'];
        $stream->remote_status = $data['remote_status'];
        $stream->remote_logo = $data['remote_logo'];
        $stream->remote_online = $data['remote_online'];
		$stream->setExpr('remote_updated_at', 'now()');

		if ($data['remote_online'] == 1) {
			$stream->setExpr('remote_online_at', 'now()');
		}

		$stream->save();
	}
	
	public function getLastStreamStats($streamId)
	{
		$stats = $this->getBy(Tables::STREAM_STATS, function($q) use ($streamId) {
			return $q
				->where('stream_id', $streamId)
				->orderByDesc('created_at');
		});
		
    	return $this->decodeStreamData($stats);
	}
	
	public function saveStreamStats($data)
	{
		$data = $this->encodeStreamData($data);

		$stats = $this->forTable(Tables::STREAM_STATS)->create();

		$stats->stream_id = $data['id'];
        $stats->remote_viewers = $data['remote_viewers'];
        $stats->remote_game = $data['remote_game'];
        $stats->remote_status = $data['remote_status'];

		$stats->save();
	}
	
	public function finishStreamStats($id)
	{
		$this->setField(Tables::STREAM_STATS, $id, 'finished_at', Date::dbNow());
	}
	
	public function getStreamGameStats($streamId, $days = 30)
	{
		$stats = $this->getMany(Tables::STREAM_STATS, function($q) use ($streamId, $days) {
			$table = $this->getTableName(Tables::STREAM_STATS);
			
			return $q->rawQuery(
				"select remote_game, count(*) count
				from {$table}
				where created_at >= date_sub(now(), interval {$days} day) and length(remote_game) > 0 and stream_id = :stream_id
				group by remote_game",
				[ 'stream_id' => intval($streamId) ]);
		});
		
    	return $this->decodeManyStreamData($stats);
	}
	
	public function getLatestStreamStats($streamId, $days = 1)
	{
		$stats = $this->getMany(Tables::STREAM_STATS, function($q) use ($streamId, $days) {
			$table = $this->getTableName(Tables::STREAM_STATS);
			
			return $q
				->rawQuery(
					"select *
					from {$table}
					where created_at >= date_sub(now(), interval {$days} day) and length(remote_game) > 0 and stream_id = :stream_id",
					[ 'stream_id' => intval($streamId) ])
				->orderByAsc('created_at');
		});
	
	   	return $this->decodeManyStreamData($stats);
	}
	
	public function getStreamStatsFrom($streamId, \DateTime $from)
	{
		$stats = $this->getMany(Tables::STREAM_STATS, function($q) use ($streamId, $from) {
			return $q
				->where('stream_id', $streamId)
				->whereGte('created_at', Date::formatDb($from))
				->orderByAsc('created_at');
		});

    	return $this->decodeManyStreamData($stats);
	}
	
	public function getStreamsByTag($tag)
	{
		$streams = $this->getByTag(Tables::STREAMS, Taggable::STREAMS, $tag);
		
    	return $this->decodeManyStreamData($streams);
	}
	
	// events
	
	public function getEvents()
	{
		return $this->getMany(Tables::EVENTS, function($q) {
			return $q
				->where('published', 1)
				->whereRaw('(published_at < now())')
				->orderByAsc('starts_at')
				->orderByAsc('ends_at');
		});
	}
	
	public function getEvent($id)
	{
		return $this->getProtected(Tables::EVENTS, $id);
	}
	
	public function getRegion($id)
	{
		return $this->get(Tables::REGIONS, $id);
	}
	
	public function getEventType($id)
	{
		return $this->get(Tables::EVENT_TYPES, $id);
	}
	
	public function getEventTypes()
	{
		return $this->getMany(Tables::EVENT_TYPES);
	}
	
	public function getEventsByTag($tag)
	{
		return $this->getByTag(Tables::EVENTS, Taggable::EVENTS, $tag, function($q) {
			return $q
				->orderByAsc('starts_at')
				->orderByAsc('ends_at');
		});
	}
	
	public function getCurrentEvents($filterByGame = null, $days = 7)
	{
		return $this->getMany(Tables::EVENTS, function($q) use ($filterByGame, $days) {
			$q = $q
				->where('published', 1)
				->whereRaw('(published_at < now())')
				->whereRaw('(coalesce(ends_at, date_add(date(starts_at), interval 24*60*60 - 1 second)) >= now() or unknown_end = 1)')
				->whereRaw("(starts_at < date_add(now(), interval {$days} day))");

			if ($filterByGame) {
				//$q = $q->where('game_id', $filterByGame['id']);
			    $q = $this->byGame($q, $filterByGame);
			}
			else {
				$q = $q->where('announce', 1);
			}
			
			return $q
				->orderByAsc('starts_at')
				->orderByAsc('ends_at');
		});
	}

	public function saveEventCache($id, $cache)
	{
		$this->setFieldNoStamps(Tables::EVENTS, $id, 'cache', $cache);
	}
	
	private function cleanCache()
	{
	    //'update enc_data set cache = null, contents_cache = null; update news set cache = null; update events set cache = null;';
	}
}
