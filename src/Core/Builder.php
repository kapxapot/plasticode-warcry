<?php

namespace App\Core;

use Plasticode\Core\Builder as BuilderBase;
use Plasticode\Util\Cases;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

use App\Data\Taggable;

class Builder extends BuilderBase {
	public function buildGame($row) {
		$game = $row;
		
		$game['default'] = ($game['id'] == $this->db->getDefaultGameId());
		$game['url'] = $this->linker->game($game);

		return $game;
	}
	
	public function buildGames($rows) {
		return array_map(function($row) {
			return $this->buildGame($row);
		}, $rows);
	}

	public function buildForumNews($news, $full = false) {
		$id = $news['tid'];
		
		$news['id'] = $id;
		$news['title'] = $this->newsParser->decodeTopicTitle($news['title']);

		$post = $this->newsParser->beforeParsePost($news['post'], $id, $full);
		$post = $this->forumParser->convert([ 'TEXT' => $post, 'CODE' => 1 ]);
		$news['text'] = $this->newsParser->afterParsePost($post);

		$game = $this->db->getGameByForumId($news['forum_id']);
		$news['game'] = $this->buildGame($game);

		$tagRows = $this->db->getForumTopicTags($id);

		if ($tagRows) {
			foreach ($tagRows as $tagRow) {
				$text = $tagRow['tag_text'];
				$tags[] = [ 'text' => $text, 'url' => $this->linker->tag($text, Taggable::NEWS) ];
			}
		}

		$news['pub_date'] = $news['start_date'];
		$news['start_date'] = Date::formatUi($this->formatDate($news['start_date']));
		$news['starter_url'] = $this->linker->forumUser($news['starter_id']);
		$news['tags'] = $tags;
		$news['url'] = $this->linker->news($id);
		$news['forum_url'] = $this->linker->forumTopic($id);
		
		$news['description'] = Strings::trunc($news['text'], 1000);
		
		return $news;
	}

	public function buildNews($news, $full = false, $rebuild = false) {
		$id = $news['id'];
		
		$game = $this->db->getGame($news['game_id']);
		$news['game'] = $this->buildGame($game);

		if (!$rebuild && strlen($news['cache']) > 0) {
			$text = $news['cache'];
		}
		else {
			$parsed = $this->parser->parse($news['text']);
			$text = $parsed['text'];

			$this->db->saveNewsCache($id, $text);
		}

		$url = $this->linker->news($id);
		$url = $this->linker->abs($url);
		$text = $this->parser->parseCut($text, $url, $full);

		$text = $this->parser->renderLinks($text);
		$text = $this->parser->makeAbsolute($text);

		$news['text'] = $text;
		$news['description'] = Strings::trunc($text, 1000);
		$news['tags'] = $this->tags($news['tags'], Taggable::NEWS);
		$news['pub_date'] = strtotime($news['published_at']);

		$news = $this->stamps($news);

		$news['start_date'] = Date::formatUi($news['published_at']);
		$news['starter_name'] = $news['author']['name'];
		$news['starter_url'] = $news['author']['member_url'];
		$news['url'] = $this->linker->news($id);

		return $news;
	}

	public function buildForumNewsLink($news) {
		$id = $news['tid'];
		
		$news['id'] = $id;
		$news['title'] = $this->newsParser->decodeTopicTitle($news['title']);

		$game = $this->db->getGameByForumId($news['forum_id']);
		$news['game'] = $this->buildGame($game);

		$news['pub_date'] = $news['start_date'];
		$news['start_date'] = Date::formatUi($this->formatDate($news['start_date']));
		$news['url'] = $this->linker->news($id);
		$news['subtitle'] = $news['start_date'];
		$news['forum_url'] = $this->linker->forumTopic($id);

		return $news;
	}

	public function buildNewsLink($news) {
		$id = $news['id'];
		
		$game = $this->db->getGame($news['game_id']);
		$news['game'] = $this->buildGame($game);

		$news['pub_date'] = strtotime($news['published_at']);

		$news = $this->stamps($news, true);

		$news['start_date'] = Date::formatUi($news['published_at']);
		$news['subtitle'] = $news['start_date'];
		$news['url'] = $this->linker->news($id);

		return $news;
	}

	public function buildForumTopic($filterByGame, $row) {
		$title = $this->newsParser->decodeTopicTitle($row['title']);

		return [
			'title' => $this->newsParser->decodeTopicTitle($row['title']),
			'url' => $this->linker->forumTopic($row['tid'], true),
			'game' => $filterByGame ?? $this->db->getGameByForumId($row['forum_id']),
			'addendum' => $row['posts'],
		];
	}
	
	public function buildForumTopics($filterByGame, $limit) {
		$rows = $this->db->getLatestForumTopics($filterByGame, $limit);

		return array_map(function($row) {
			return $this->buildForumTopic($filterByGame, $row);
		}, $rows);
	}
	
	public function buildLatestNews($filterByGame, $limit, $exceptNewsId) {
		return $this->buildAllNews($filterByGame, 1, $limit, $exceptNewsId);
	}

	public function buildAllNews($filterByGame = null, $page = 1, $pageSize = 7, $exceptNewsId = null) {
		$offset = ($page - 1) * $pageSize;
		$goal = $pageSize;

		$news = $this->db->getLatestNews($filterByGame, $offset, $goal, $exceptNewsId);
		$newsCount = count($news);
		
		$goal -= $newsCount;
		
		if ($goal > 0) {
			if ($newsCount > 0) {
				$offset = 0;
			}
			else {
				$offset -= $this->db->getNewsCount($filterByGame, $exceptNewsId);
			}

			$forumNews = $this->db->getLatestForumNews($filterByGame, $offset, $goal, $exceptNewsId);
		}

		$merged = array_merge(
			array_map(function($fn) {
				return $this->buildForumNews($fn);
			}, $forumNews ?? []),
			array_map(function($n) {
				return $this->buildNews($n);
			}, $news ?? [])
		);
		
		$sorted = $this->sortByDate($merged);

		return $sorted;
	}
	
	public function buildNewsYears() {
		$forumNews = $this->db->getLatestForumNews() ?? [];
		$news = $this->db->getLatestNews() ?? [];
		
		$years = array_merge(
			array_map(function($fn) {
				return $this->year($fn['start_date']);
			}, $forumNews),
			array_map(function($n) {
				return $this->year(strtotime($n['published_at']));
			}, $news)
		);
		
		$years = array_unique($years);
		rsort($years);
		
		return $years;
	}
	
	public function buildNewsArchive($year) {
		$forumNews = $this->db->getForumNewsByYear($year) ?? [];
		$news = $this->db->getNewsByYear($year) ?? [];

		$merged = array_merge(
			array_map(function($fn) {
				return $this->buildForumNewsLink($fn);
			}, $forumNews),
			array_map(function($n) {
				return $this->buildNewsLink($n);
			}, $news)
		);
		
		$sorted = $this->sortByDate($merged);
		
		$monthly = [];
		
		foreach ($sorted as $s) {
			$month = intval($this->month($s['pub_date']));
			if (!array_key_exists($month, $monthly)) {
				$monthly[$month] = [
					'label' => Date::SHORT_MONTHS[$month],
					'full_label' => Date::MONTHS[$month],
					'news' => [],
				];
			}
			
			$monthly[$month]['news'][] = $s;
		}

		ksort($monthly);

		return $monthly;
	}
	
	public function buildNewsByTag($tag) {
		$tag = Strings::normalize($tag);
		
		$forumNews = $this->db->getForumNewsByTag($tag) ?? [];
		$news = $this->db->getNewsByTag($tag) ?? [];

		$merged = array_merge(
			array_map(function($fn) {
				return $this->buildForumNewsLink($fn);
			}, $forumNews),
			array_map(function($n) {
				return $this->buildNewsLink($n);
			}, $news)
		);
		
		return $this->sortByDate($merged);
	}

	public function buildArticle($article) {
		$result = $article->data;

		if ($result['cat']) {
			$result['cat'] = $this->db->getCat($result['cat']);
		}
		
		$result['game'] = $this->db->getGame($result['game_id']);

		$text = $this->parser->renderLinks($article->text);

		if (strlen($text) > 0) {
			$result['description'] = Strings::trunc($text, 1000);
		}

		$subArticleRows = $this->db->getSubArticles($article->id);
		if ($subArticleRows) {
			foreach ($subArticleRows as $saRow) {
				$subList[] = $this->buildArticleLink($saRow);
			}
		
			$result['sub_articles'] = $subList;
		}

		$result['breadcrumbs'] = $article->breadcrumbs;
		$result['contents'] = $article->contents;

		$link = $this->buildArticleLink($result);
		$result['title'] = $link['title_full'];

		$result['text'] = $text;

		$result = $this->stamps($result);
		
		if ($result['published_at']) {
			$result['published_at'] = Date::formatUi($result['published_at']);
		}
		
		$result['updated_at'] = Date::formatUi($result['updated_at']);
		
		return $result;
	}

	private function getItem($id) {
		$item = $this->db->getItem($id);
		
		if (!$item || !isset($item['name_ru'])) {
			$item = $this->getRemoteItem($id);
		}

		return $item;
	}

	private function getRemoteItem($id) {
		$url = $this->linker->wowheadItemXml($id);
		$urlRu = $this->linker->wowheadItemRuXml($id);
		
		$xml = simplexml_load_file($url, null, LIBXML_NOCDATA);
		$xmlRu = simplexml_load_file($urlRu, null, LIBXML_NOCDATA);
		
		if ($xml !== false) {
			$name = (string)$xml->item->name;
			
			$item = [
				'icon' => (string)$xml->item->icon,
				'name' => $name,
				'quality' => (string)$xml->item->quality['id'],
			];
		
			if ($xmlRu !== false) {
				$nameRu = (string)$xmlRu->item->name;
				
				if ($nameRu !== $name) {
					$item['name_ru'] = $nameRu;
				}
			}

			$this->db->saveItem($id, $item);
		}
		
		return $item;
	}

	protected function getSpellIcon($id) {
		$icon = $this->db->getSpellIcon($id);
		return ($icon != null) ? $icon['icon'] : null;
	}
	
	protected function invertQuality($q) {
		return 8 - $q;
	}
	
	private function extractRecipeReagents($reagentsString) {
		$reagents = [];
		
		if (strlen($reagentsString) > 0) {
			$reagentsRaw = explode(',', $reagentsString);
			
			foreach ($reagentsRaw as $reagent) {
				$parts = explode('x', $reagent);
				list($id, $count) = $parts;
				
				$reagents[$id] = $count;
			}
		}
		
		return $reagents;
	}
	
	private function getRecipeBaseReagents($recipe, $baseReagents = []) {
		foreach ($recipe['reagents'] as $reagent) {
			if (isset($reagent['recipe'])) {
				$baseReagents = $this->getRecipeBaseReagents($reagent['recipe'], $baseReagents);
			}
			else {
				$id = $reagent['item_id'];
			
				if (!isset($baseReagents[$id])) {
					$baseReagents[$id] = $reagent;
				}
				else {
					$baseReagents[$id]['total_min'] += $reagent['total_min'];
					$baseReagents[$id]['total_max'] += $reagent['total_max'];
				}
			}
		}
		
		return $baseReagents;
	}
	
	private function addNodeIds($recipe, $label = '1') {
		$recipe['node_id'] = $label;

		$count = 1;
		foreach ($recipe['reagents'] as &$reagent) {
			if (isset($reagent['recipe'])) {
				$reagent['recipe'] = $this->addNodeIds($reagent['recipe'], $label . '_' . $count++);
			}
		}
		
		return $recipe;
	}
	
	private function addTotals($recipe, $countMin = 1, $countMax = 1) {
		$createsMin = $recipe['creates_min'];
		$createsMax = $recipe['creates_max'];
		
		$neededMin = ($createsMax > 0) ? ceil($countMin / $createsMax) : 0;
		$neededMax = ($createsMin > 0) ? ceil($countMax / $createsMin) : 0;

		$recipe['total_min'] = $neededMin;
		$recipe['total_max'] = $neededMax;

		foreach ($recipe['reagents'] as &$reagent) {
			$count = $reagent['count'];

			$totalMin = ($neededMin > 0) ? $neededMin * $count : $count;
			$totalMax = ($neededMax > 0) ? $neededMax * $count : $count;

			$reagent['total_min'] = $totalMin;
			$reagent['total_max'] = $totalMax;

			if (isset($reagent['recipe'])) {
				$reagent['recipe'] = $this->addTotals($reagent['recipe'], $totalMin, $totalMax);
			}
		}
		
		return $recipe;
	}
	
	public function buildRecipe($recipe, $cacheEnabled = true, &$requiredSkills = [], $trunk = []) {
		$topLevel = empty($trunk);

		// на всякий -__-
		if (count($trunk) > 20) {
			return;
		}

		$trunk[] = $recipe['creates_id'];

		/*$createsMin = $recipe['creates_min'];
		$createsMax = $recipe['creates_max'];
		
		$neededMin = ($createsMax > 0) ? ceil($countMin / $createsMax) : 0;
		$neededMax = ($createsMin > 0) ? ceil($countMax / $createsMin) : 0;

		$recipe['total_min'] = $neededMin;
		$recipe['total_max'] = $neededMax;*/
		
		// title
		$result['title'] = $result['name_ru'];
		if ($result['name'] && $result['name'] != $result['name_ru']) {
			$result['title'] .= ' (' . $result['name'] . ')';
		}

		// learned at
		if ($recipe['learnedat'] == 9999) {
			$recipe['learnedat'] = '??';
		}

		// lvls
		$recipe['levels'] = [
			'orange' => $recipe['lvl_orange'],
			'yellow' => $recipe['lvl_yellow'],
			'green' => $recipe['lvl_green'],
			'gray' => $recipe['lvl_gray'],
		];

		// skill
		$skillId = $recipe['skill'];
		$recipe['skill_id'] = $skillId;
		$recipe['skill'] = $this->db->getSkill($skillId);
		
		if (!isset($requiredSkills[$skillId])) {
			$requiredSkills[$skillId] = [
				'skill' => $recipe['skill'],
				'max' => $recipe['learnedat'],
			];
		}
		else {
			$curMax = $requiredSkills[$skillId]['max'];
			
			if ($recipe['learnedat'] > $curMax) {
				$requiredSkills[$skillId]['max'] = $recipe['learnedat'];
			}
		}

		// source
		$srcIds = explode(',', $recipe['source']);
		$sources = array_map(function($srcId) {
			$src = $this->db->getRecipeSource($srcId);
			return $src ? $src['name_ru'] : $srcId;
		}, $srcIds);

		// reagents
		if ($cacheEnabled && strlen($recipe['reagent_cache']) > 0) {
			$reagents = json_decode($recipe['reagent_cache'], true);
		}
		else {
			$reagents = [];

			$extRegs = $this->extractRecipeReagents($recipe['reagents']);
			
			foreach ($extRegs as $id => $count) {
				//$totalMin = ($neededMin > 0) ? $neededMin * $count : $count;
				//$totalMax = ($neededMax > 0) ? $neededMax * $count : $count;

				$item = $this->getItem($id);

				$reagent = [
					'icon' => ($item != null) ? $item['icon'] : null,
					'item_id' => $id,
					'count' => $count,
					'item' => $this->buildItem($item),
					//'total_min' => $totalMin,
					//'total_max' => $totalMax,
				];

				// going deeper?
				$foundRecipe = null;
				
				if (!in_array($id, $trunk)) {
					$srcRecipes = $this->db->getRecipesByItemId($id);

					if (!empty($srcRecipes)) {
						foreach ($srcRecipes as $srcRecipe) {
							// skipping transmutes
							if (preg_match('/^Transmute/', $srcRecipe['name'])) {
								continue;
							}
							
							$srcRegs = $this->extractRecipeReagents($srcRecipe['reagents']);
							
							$badReagents = array_filter(array_keys($srcRegs), function($srcRegId) use ($trunk) {
								return in_array($srcRegId, $trunk);
							});
							
							if (empty($badReagents)) {
								$foundRecipe = $this->buildRecipe($srcRecipe, $cacheEnabled, $requiredSkills, $trunk);
								break;
							}
						}
					}
				}
				
				$reagent['recipe'] = $foundRecipe;

				$reagents[] = $reagent;
			}

			if ($cacheEnabled) {
				$this->db->setRecipeReagentCache($recipe['id'], json_encode($reagents));
			}
		}

		// link
		if ($cacheEnabled && strlen($recipe['icon_cache']) > 0) {
			$link = json_decode($recipe['icon_cache'], true);
		}
		else {
			if ($recipe['creates_id'] != 0) {
				$item = $this->getItem($recipe['creates_id']);
				
				$link = [
					'icon' => ($item != null) ? $item['icon'] : null,
					'item_id' => $recipe['creates_id'],
					'count' => $createsMin,
					'max_count' => $createsMax,
					'spell_id' => $recipe['id'],
				];
			}
			else {
				$icon = $this->getSpellIcon($recipe['id']);
				$link =	[
					'icon' => $icon,
					'spell_id' => $recipe['id'],
				];
			}

			if ($cacheEnabled) {
				$this->db->setRecipeIconCache($recipe['id'], json_encode($link));
			}
		}

		$recipe['inv_quality'] = $this->invertQuality($recipe['quality']);
		$recipe['sources'] = $sources;
		$recipe['url'] = $this->linker->recipe($recipe['id']);
		$recipe['link'] = $this->buildRecipeLink($link);

		$recipe['reagents'] = array_map(function($r) {
			return $this->buildRecipeLink($r);
		}, $reagents);

		$recipe = $this->addNodeIds($recipe);
		$recipe = $this->addTotals($recipe);

		if ($topLevel) {
			$baseReagents = $this->getRecipeBaseReagents($recipe);
			
			$recipe['base_reagents'] = array_map(function($r) {
				return $this->buildRecipeLink($r);
			}, array_values($baseReagents));
			
			$recipe['required_skills'] = $requiredSkills;
		}

		return $recipe;
	}
	
	private function defaultIcon() {
		return $this->getSettings('recipes.default_icon');
	}
	
	private function buildRecipeLink($link) {
		$link['icon_url'] = $this->linker->wowheadIcon($link['icon'] ?? $this->defaultIcon());

		if (isset($link['item_id'])) {
			$link['item_url'] = $this->linker->wowheadItemRu($link['item_id']);
		}
		
		if (isset($link['spell_id'])) {
			$link['spell_url'] = $this->linker->wowheadSpellRu($link['spell_id']);
		}
		
		$link['url'] = $link['item_url'] ?? $link['spell_url'];

		return $link;
	}
	
	private function buildItem($item) {
		$item['name_ru'] = $item['name_ru'] ?? $item['name'];
		
		$item['url'] = $this->linker->wowheadItemRu($item['id']);

		return $item;
	}
	
	public function buildSkill($skill) {
		$skill['icon_url'] = $this->linker->wowheadIcon($skill['icon'] ?? $this->defaultIcon());

		return $skill;
	}

	public function buildArticleLink($row) {
		$cat = is_array($row['cat'])
			? $row['cat']
			: $this->db->getCat($row['cat']);

		$ru = $row['name_ru'];
		$en = $row['name_en'];

		return [
			'cat' => $cat,
			'url' => $this->linker->article($en, $cat['name_en']),
			'title' => $ru,
			'title_en' => $row['hideeng'] ? $ru : $en,
			'title_full' => $ru . (!$row['hideeng'] ? " ({$en})" : ''),
			'game' => $row['game'] ?? $this->db->getGame($row['game_id']),
		];
	}
	
	public function buildLatestArticles($filterByGame, $limit, $exceptArticleId) {
		$rows = $this->db->getLatestArticles($filterByGame, $limit, $exceptArticleId);
		
		if ($rows === null) {
			return null;
		}

		return array_map(function($row) {
			return $this->buildArticleLink($row);
		}, $rows);
	}
	
	public function buildMenuByGame($game) {
		if (!$game) {
			throw new \Exception('Game cannot be null.');
		}
		
		$menus = $this->db->getMenusByGame($game['id']);
		
		return $this->buildSubMenus($menus);
	}
	
	public function buildSortedGalleryAuthors() {
		$rows = $this->db->getGalleryAuthors();
		
		$authors = [];
		
		foreach ($rows as $row) {
			$authors[] = $this->buildGalleryAuthor($row);
		}
			
		$sorts = [
			'count' => [ 'dir' => 'desc' ],
			'name' => [ 'dir' => 'asc', 'type' => 'string' ],
		];

		$authors = $this->sort->multiSort($authors, $sorts);
		
		return $authors;
	}

	public function buildGalleryAuthor($row, $short = false) {
		$author = $row;

		$author['page_url'] = $this->linker->galleryAuthor($author['alias']);

		if (!$short) {
			$picRows = $this->db->getGalleryPictures($author['id']);
			
			$author['count'] = count($picRows);
			
			if ($author['count'] > 0) {
				$last = $picRows[0];

				$author['last_picture_id'] = $last['id'];
				$author['last_thumb_url'] = $this->linker->galleryThumbImg($last);
			}
	
			$author['pictures_str'] = $this->cases->caseForNumber('картинка', $author['count']);
			
			$forumMember = $this->db->getForumMemberByName($author['name']);
			
			if ($forumMember) {
				$author['member_id'] = $forumMember['member_id'];
				$author['member_url'] = $this->linker->forumUser($author['member_id']);
			}
		}
		
		return $author;
	}
	
	public function buildGalleryPicture($row, $author = null) {
		$picture = $row;

		$id = $picture['id'];
		
		$picture['ext'] = $this->linker->getExtension($picture['picture_type']);
		$picture['url'] = $this->linker->galleryPictureImg($picture);
		$picture['thumb'] = $this->linker->galleryThumbImg($picture);

		if ($author == null) {
			$authorRow = $this->db->getGalleryAuthor($picture['author_id']);
			$author = $this->builder->buildGalleryAuthor($authorRow, true);
		}

		if ($author != null) {
			$picture['author'] = $author;
			$picture['page_url'] = $this->linker->galleryPicture($author['alias'], $id);
		}
		
		$prev = $this->db->getGalleryPicturePrev($picture);
		$next = $this->db->getGalleryPictureNext($picture);

		if ($prev != null) {
			$prev['page_url'] = $this->linker->galleryPicture($author['alias'], $prev['id']);
			$picture['prev'] = $prev;
		}
		
		if ($next != null) {
			$next['page_url'] = $this->linker->galleryPicture($author['alias'], $next['id']);
			$picture['next'] = $next;
		}
		
		$picture['created_ui'] = Date::formatUi($picture['created_at']);

		return $this->stamps($picture, true);
	}

	/**
	 * Override.
	 */
	public function buildUser($row) {
		$user = parent::buildUser($row);

		$forumMember = $this->db->getForumMemberByUser($user);
		
		if ($forumMember) {
			$user['member_url'] = $this->linker->forumUser($forumMember['member_id']);
		}

		return $user;
	}
	
	private function isPriorityGame($game) {
		$game = strtolower($game);
		$priorityGames = $this->getSettings('streams.priority_games');

		return in_array($game, $priorityGames);
	}
	
	public function buildStream($row) {
		$stream = $row;

		$stream['priority_game'] = false;

		if ($stream['remote_online_at']) {
			$streamTimeToLive = $this->getSettings('streams.ttl');
			$age = Date::age($stream['remote_online_at']);
			
			$stream['alive'] = ($age->days < $streamTimeToLive);

			if ($stream['alive']) {
				$stream['priority_game'] = $this->isPriorityGame($stream['remote_game']);
				/*$game = strtolower($stream['remote_game']) ?? '';
				$priorityGames = $this->getSettings('streams.priority_games');
				$stream['priority_game'] = in_array($game, $priorityGames);*/
			}
		}

		$id = $stream['stream_id'];
		
		$stream['stream_alias'] = $stream['stream_alias'] ?? $id;
		$stream['page_url'] = $this->linker->stream($stream['stream_alias']);

		// only Twitch for now
		switch ($stream['type']) {
			// Twitch
			case 1:
				$stream['img_url'] = $this->linker->twitchImg($id);
				$stream['large_img_url'] = $this->linker->twitchLargeImg($id);
				
				$stream['twitch'] = true;
				$stream['stream_url'] = $this->linker->twitch($id);
				break;

			default:
				throw new \Exception('Unsupported stream type: ' . $stream['type']);
		}
		
		$onlineAt = $stream['remote_online_at'];
		
		if ($onlineAt) {
			$stream['remote_online_at'] = $this->formatDate(strtotime($onlineAt));
		}
		
		$stream['remote_online_ago'] = Date::toAgo($onlineAt);
		
		$form = [
			'time' => Cases::PAST,
			'person' => Cases::FIRST,
			'number' => Cases::SINGLE,
			'gender' => $stream['gender_id'],
		];
		
		$stream['played'] = $this->cases->conjugation('играть', $form);
		$stream['broadcasted'] = $this->cases->conjugation('транслировать', $form);
		$stream['held'] = $this->cases->conjugation('вести', $form);

		return $stream;
	}
	
	public function buildStreamStats($stream) {
		$stats = [];
		
		$games = $this->db->getStreamGameStats($stream['id']);
		
		if (!empty($games)) {
			$total = 0;
			foreach ($games as $game) {
				$total += $game['count'];
			}
			
			$games = array_map(function($game) use ($total) {
				$game['percent'] = ($total > 0)
					? round($game['count'] * 100 / $total, 1)
					: 0;

				$game['priority'] = $this->isPriorityGame($game['remote_game']);
				
				return $game;
			}, $games);

			$sorts = [
				'priority' => [ 'dir' => 'desc' ],
				'percent' => [ 'dir' => 'desc' ],
			];
		
			$games = $this->sort->multiSort($games, $sorts);
			
			$blizzardTotal = 0;
			foreach ($games as $game) {
				if ($game['priority']) {
					$blizzardTotal += $game['percent'];
				}
			}

			$stats['games'] = $games;
			$stats['blizzard_total'] = $blizzardTotal;
			
			$stats['blizzard'] = [
				[ 'value' => $blizzardTotal, 'label' => 'Игры Blizzard' ],
				[ 'value' => 100 - $blizzardTotal, 'label' => 'Другие игры' ]
			];
		}

		$now = new \DateTime;
		$start = Date::startOfHour($now)->modify('-23 hour');

		$latest = $this->db->getStreamStatsFrom($stream['id'], $start);

		if (!empty($latest)) {
			$latest = array_map(function($s) {
				$cr = strtotime($s['created_at']);
				
				$s['stamp'] = strftime('%d-%H', $cr);
				$s['iso'] = Date::formatIso($cr);
				
				return $s;
			}, $latest);

			//$stats['bars'] = $this->buildHourlyStreamStats($latest, $start, $now);
			$stats['viewers'] = $this->buildGamelyStreamStats($latest, $start, $now);
		}

		return $stats;
	}
	
	private function buildHourlyStreamStats($latest, \DateTime $start, \DateTime $now) {
		$hourly = [];
		
		$cur = clone $start;

		while ($cur < $now) {
			$stamp = $cur->format('d-H');

			$slice = array_filter($latest, function($s) use ($stamp) {
				return $s['stamp'] == $stamp;
			});

			$avg = 0;
			
			if (!empty($slice)) {
				$sum = 0;
				foreach ($slice as $stat) {
					$sum += $stat['remote_viewers'];
				}
				
				$avg = $sum / count($slice);
			}
			
			$hourly[] = [
				'hour' => $cur->format('G'),
				'viewers' => floor($avg),
			];
			
			$cur->modify('+1 hour');
		}
		
		return $hourly;
	}
	
	private function buildGamelyStreamStats($latest, \DateTime $start, \DateTime $end) {
		$gamely = [];
		
		$prev = null;
		$prevGame = null;
		
		$set = [];
		
		$closeSet = function($game) use (&$gamely, &$set) {
			if (!empty($set)) {
				if (!array_key_exists($game, $gamely)) {
					$gamely[$game] = [];
				}

				$gamely[$game][] = $set;
				$set = [];
			}
		};
		
		foreach ($latest as $s) {
			$game = $s['remote_game'];
			
			if ($prev) {
				$exceeds = Date::exceedsInterval($prev['created_at'], $s['created_at'], 'PT30M'); // 30 minutes

				if ($exceeds) {
					$closeSet($prevGame);
				}
				elseif ($prevGame != $game) {
					$closeSet($prevGame);

					$prev['remote_game'] = $game;
					$set[] = $prev;
				}
			}

			$set[] = $s;
			
			$prev = $s;
			$prevGame = $game;
		}
		
		$closeSet($prevGame);

		return [
			'data' => $gamely,
			'min_date' => Date::formatIso($start),
			'max_date' => Date::formatIso($end),
		];
	}
	
	public function updateStreamData($row, $notify = false) {
		$stream = $row;
		
		$id = $stream['stream_id'];
		
		switch ($stream['type']) {
			// Twitch
			case 1:
				$data = $this->twitch->getStreamData($id);

				if (isset($data['streams'][0])) {
					$s = $data['streams'][0];

					$streamStarted = ($stream['remote_online'] == 0);

					$stream['remote_online'] = 1;
					$stream['remote_game'] = $s['game'];
					$stream['remote_viewers'] = $s['viewers'];
					
					if (isset($s['channel'])) {
						$ch = $s['channel'];

						$stream['remote_title'] = $ch['display_name'];
						$stream['remote_status'] = $ch['status'];
						$stream['remote_logo'] = $ch['logo'];
					}
					
					if ($notify && $streamStarted) {
						$message = $this->sendStreamNotifications($stream);
					}
				}
				else {
					$stream['remote_online'] = 0;
					$stream['remote_viewers'] = 0;
				}
				
				break;
			
			default:
				throw new \Exception('Unsupported stream type: ' . $stream['type']);
		}

		// save
		$this->db->saveStream($stream);
		
		// stats
		$this->updateStreamStats($stream);

		if ($s) {
			$stream['json'] = $data;
			$stream['message'] = $message;
		}

		return $stream;
	}
	
	private function updateStreamStats($stream) {
		$online = ($stream['remote_online'] == 1);
		$refresh = $online;
		
		$stats = $this->db->getLastStreamStats($stream['id']);
		
		if ($stats) {
			if ($online) {
				$statsTTL = $this->getSettings('streams.stats_ttl');

				$exceeds = Date::exceedsInterval($stats['created_at'], null, "PT{$statsTTL}M");
	
				if (!$exceeds && ($stream['remote_game'] == $stats['remote_game'])) {
					$refresh = false;
				}
			}

			if (!$stats['finished_at'] && (!$online || $refresh)) {
				$this->db->finishStreamStats($stats['id']);
			}
		}
		
		if ($refresh) {
			$this->db->saveStreamStats($stream);
		}
	}
	
	private function sendStreamNotifications($s) {
		$verb = ($s['channel'] == 1)
			? ($s['remote_status']
				? "транслирует <b>{$s['remote_status']}</b>"
				: 'ведет трансляцию')
			: "играет в <b>{$s['remote_game']}</b>
{$s['remote_status']}";

		$verbEn = ($s['channel'] == 1)
			? ($s['remote_status']
				? "is streaming <b>{$s['remote_status']}</b>"
				: 'started streaming')
			: "is playing <b>{$s['remote_game']}</b>
{$s['remote_status']}";
		
		$url = $this->linker->twitch($s['stream_id']);
		$source = "<a href=\"{$url}\">{$s['title']}</a>";
		
		$message = $source . ' ' . $verb;
		$messageEn = $source . ' ' . $verbEn;

		$settings = [
			[
				'channel' => 'warcry',
				'condition' => $s['priority'] == 1 || $s['official'] == 1 || $s['official_ru'] == 1,
				'message' => $message,
			],
			[
				'channel' => 'warcry_streams',
				'condition' => true,
				'message' => $message,
			],
			[
				'channel' => 'blizzard_streams',
				'condition' => $s['official'] == 1,
				'message' => $messageEn,
			],
			[
				'channel' => 'blizzard_streams_ru',
				'condition' => $s['official_ru'] == 1,
				'message' => $message,
			],
		];

		foreach ($settings as $setting) {
			if ($setting['condition']) {
				$this->telegram->sendMessage($setting['channel'], $setting['message']);
			}
		}

		return $message . ' ' . $messageEn;
	}

	public function buildSortedStreams() {
		$streams = array_map(function($s) {
			return $this->buildStream($s);
		}, $this->db->getStreams());

		$sorts = [
			'remote_online' => [ 'dir' => 'desc' ],
			'official_ru' => [ 'dir' => 'desc' ],
			'official' => [ 'dir' => 'desc' ],
			'priority' => [ 'dir' => 'desc' ],
			'priority_game' => [ 'dir' => 'desc' ],
			'remote_viewers' => [ 'dir' => 'desc' ],
			'title' => [ 'dir' => 'asc', 'type' => 'string' ],
		];
		
		$streams = $this->sort->multiSort($streams, $sorts);
		
		return $streams;
	}
	
	public function buildStreamGroups($streams) {
		$groupSettings = [
			[
				'id' => 'online',
				'label' => 'Онлайн',
				'telegram' => 'warcry_streams',
				'condition' => function($s) {
					return $s['remote_online'];
				},
			],
			[
				'id' => 'offline',
				'label' => 'Офлайн',
				'telegram' => 'warcry_streams',
				'condition' => function($s) {
					return $s['alive'] && !$s['remote_online'];
				},
			],
			[
				'id' => 'blizzard',
				'label' => 'Blizzard EN',
				'telegram' => 'blizzard_streams',
				'telegram_label' => 'официальных трансляций (англ.)',
				'condition' => function($s) {
					return $s['official'];
				},
			],
			[
				'id' => 'blizzard_ru',
				'label' => 'Blizzard РУ',
				'telegram' => 'blizzard_streams_ru',
				'telegram_label' => 'официальных трансляций (рус.)',
				'condition' => function($s) {
					return $s['official_ru'];
				},
			],
		];
		
		$groups = [];
		
		foreach ($groupSettings as $gs) {
			$gs['streams'] = array_filter($streams, $gs['condition']);
			$groups[] = $gs;
		}
		
		return $groups;
	}
	
	public function buildTagParts($tag) {
		$parts = [];
		
		$news = $this->builder->buildNewsByTag($tag);
		
		if ($news) {
			$parts[] = [
				'id' => 'news',
				'label' => 'Новости',
				'values' => $news,
			];
		}
		
		$events = $this->builder->buildEventsByTag($tag);
		
		if ($events) {
			$parts[] = [
				'id' => 'events',
				'label' => 'События',
				'values' => $events,
			];
		}
		
		return $parts;
	}
	
	public function buildOnlineStream($filterByGame) {
		$streams = $this->buildSortedStreams();
	
		$onlineStreams = array_filter($streams, function($stream) {
			return $stream['remote_online'] == 1;
		});
		
		$totalOnline = count($onlineStreams);
	
		if ($totalOnline > 0) {
			$onlineStream = $onlineStreams[0];
			$onlineStream['total_streams_online'] = $totalOnline . ' ' . $this->cases->caseForNumber('стрим', $totalOnline);
		}
		
		return $onlineStream;
	}
	
	// COMICS

	public function buildSortedComicSeries() {
		$rows = $this->db->getComicSeries();

		$series = [];
		
		foreach ($rows as $row) {
			$series[] = $this->buildComicSeries($row);
		}
			
		$sorts = [
			'last_issued_on' => [ 'dir' => 'desc', 'type' => 'string' ],
		];

		$series = $this->sort->multiSort($series, $sorts);
		
		return $series;
	}

	public function buildComicSeries($row) {
		$series = $row;
		
		$series['game'] = $this->db->getGame($series['game_id']);

		$series['page_url'] = $this->linker->comicSeries($series['alias']);

		$comicRows = $this->db->getComicIssues($series['id']);
		$comicCount = count($comicRows);
		
		if ($comicCount > 0) {
			$series['cover_url'] = $this->getComicIssueCover($comicRows[0]['id']);
			$series['last_issued_on'] = $comicRows[$comicCount - 1]['issued_on'];
		}
		
		$series['comic_count'] = $comicCount;
		$series['comic_count_str'] = $this->cases->caseForNumber('выпуск', $comicCount);

		$series['publisher'] = $this->db->getComicPublisher($series['publisher_id']);
		
		if ($series['name_ru'] == $series['name_en']) {
			$series['name_en'] = null;
		}

		return $series;
	}

	public function buildSortedComicStandalones() {
		$rows = $this->db->getComicStandalones();

		$comics = [];
		
		foreach ($rows as $row) {
			$comics[] = $this->buildComicStandalone($row);
		}

		return $comics;
	}

	public function buildComicStandalone($row) {
		$comic = $row;
		
		$comic['game'] = $this->db->getGame($comic['game_id']);

		$comic['page_url'] = $this->linker->comicStandalone($comic['alias']);

		$pageRows = $this->db->getComicStandalonePages($comic['id']);
		
		if (count($pageRows) > 0) {
			$pageRow = $pageRows[0];
			$comic['cover_url'] = $this->linker->comicThumbImg($pageRow);
		}

		$comic['publisher'] = $this->db->getComicPublisher($comic['publisher_id']);
		$comic['issued_ui'] = Date::formatUi($comic['issued_on']);

		if ($comic['name_ru'] == $comic['name_en']) {
			$comic['name_en'] = null;
		}

		return $comic;
	}
	
	private function padNum($num) {
		return str_pad($num, 2, '0', STR_PAD_LEFT);
	}
	
	private function comicNum($comic) {
		$numStr = '#' . $comic['number'];
		
		if ($comic['name_ru']) {
			$numStr .= ': ' . $comic['name_ru'];
		}

		return $numStr;
	}
	
	private function pageNum($num) {
		return $this->padNum($num);
	}
	
	private function getComicIssueCover($comicId) {
		$pageRows = $this->db->getComicIssuePages($comicId);
		
		if (count($pageRows) > 0) {
			$cover = $this->linker->comicThumbImg($pageRows[0]);
		}
		
		return $cover;
	}

	public function buildComicIssue($row, $series) {
		$comic = $row;

		$comic['page_url'] = $this->linker->comicIssue($series['alias'], $comic['number']);
		$comic['cover_url'] = $this->getComicIssueCover($comic['id']);
		$comic['number_str'] = $this->comicNum($comic);
		$comic['issued_ui'] = Date::formatUi($comic['issued_on']);

		$prev = $this->db->getComicIssuePrev($comic);
		$next = $this->db->getComicIssueNext($comic);
		
		if ($prev != null) {
			$prev['page_url'] = $this->linker->comicIssue($series['alias'], $prev['number']);
			$prev['number_str'] = $this->comicNum($prev);
			$comic['prev'] = $prev;
		}
		
		if ($next != null) {
			$next['page_url'] = $this->linker->comicIssue($series['alias'], $next['number']);
			$next['number_str'] = $this->comicNum($next);
			$comic['next'] = $next;
		}

		return $comic;
	}
	
	public function buildComicIssuePage($row, $series, $comic) {
		$page = $row;

		$id = $page['id'];
		
		$page['url'] = $this->linker->comicPageImg($page);
		$page['thumb'] = $this->linker->comicThumbImg($page);
		$page['page_url'] = $this->linker->comicIssuePage($series['alias'], $comic['number'], $page['number']);
		$page['number_str'] = $this->pageNum($page['number']);

		$prev = $this->db->getComicIssuePagePrev($comic, $page);
		$next = $this->db->getComicIssuePageNext($comic, $page);
		
		if ($prev != null) {
			$prev['page_url'] = $this->linker->comicIssuePage($series['alias'], $prev['comic']['number'], $prev['number']);
			$prev['comic_number_str'] = $this->comicNum($prev['comic']);
			$prev['number_str'] = $this->pageNum($prev['number']);
			$page['prev'] = $prev;
		}
		
		if ($next != null) {
			$next['page_url'] = $this->linker->comicIssuePage($series['alias'], $next['comic']['number'], $next['number']);
			$next['comic_number_str'] = $this->comicNum($next['comic']);
			$next['number_str'] = $this->pageNum($next['number']);
			$page['next'] = $next;
		}
		
		$page['ext'] = 'jpg';

		return $page;
	}
	
	public function buildComicStandalonePage($row, $comic) {
		$page = $row;

		$id = $page['id'];
		
		$page['url'] = $this->linker->comicPageImg($page);
		$page['thumb'] = $this->linker->comicThumbImg($page);
		$page['page_url'] = $this->linker->comicStandalonePage($comic['alias'], $page['number']);
		$page['number_str'] = $this->pageNum($page['number']);

		$prev = $this->db->getComicStandalonePagePrev($page);
		$next = $this->db->getComicStandalonePageNext($page);
		
		if ($prev != null) {
			$prev['page_url'] = $this->linker->comicStandalonePage($comic['alias'], $prev['number']);
			$prev['number_str'] = $this->pageNum($prev['number']);
			$page['prev'] = $prev;
		}
		
		if ($next != null) {
			$next['page_url'] = $this->linker->comicStandalonePage($comic['alias'], $next['number']);
			$next['number_str'] = $this->pageNum($next['number']);
			$page['next'] = $next;
		}
		
		$page['ext'] = 'jpg';

		return $page;
	}

	private function getSubArticles($parentId, $recursive = false) {
		$rows = $this->db->getSubArticles($parentId);
		if ($rows) {
			foreach ($rows as $row) {
				$item = $this->buildArticleLink($row);
				
				if ($recursive) {
					$item['items'] = $this->getSubArticles($row['id'], true);
					$items[] = $item;
				}
			}
		}

		return $items;
	}

	public function buildMap() {
		$rootId = $this->getSettings('articles.root_id');
		
		return $this->getSubArticles($rootId, true);
	}
	
	public function buildCurrentEvents($game, $days) {
		$rows = $this->db->getCurrentEvents($game, $days);
		
		if ($rows === null) {
			return null;
		}
		
		$events = array_map(function($row) {
			$event = $this->buildEvent($row);
			return $this->buildEventLink($event);
		}, $rows);

		return $events;
	}

	public function buildEventLink($event) {
		if (!$event['started']) {
			$event['addendum'] = Date::to($event['starts_at']);
		}
		
		return $event;
	}
	
	public function buildEvents($rows) {
		$events = array_map(function($row) {
			return $this->buildEvent($row);
		}, $rows);
		
		$groups = [
			[
				'id' => 'current',
				'label' => 'Текущие',
				'items' => array_filter($events, function($e) {
					return $e['started'] && !$e['ended'];
				}),
			],
			[
				'id' => 'future',
				'label' => 'Будущие',
				'items' => array_filter($events, function($e) {
					return !$e['started'];
				}),
			],
			[
				'id' => 'past',
				'label' => 'Прошедшие',
				'items' => array_filter($events, function($e) {
					return $e['ended'];
				}),
			]
		];
		
		return $groups;
	}
	
	private function buildRegion($region) {
		$ru = [ $region['name_ru'] ];
		$en = [ $region['name_en'] ];

		if ($region['parent_id'] && !$region['terminal']) {
			$parent = $this->db->getRegion($region['parent_id']);
			
			if ($parent) {
				$region['parent'] = $parent;
				
				$ru[] = $parent['name_ru'];
				$en[] = $parent['name_en'];
			}
		}
		
		$notNull = function($v) { return strlen($v) > 0; };
		
		$ru = implode(', ', array_filter($ru, $notNull));
		$en = implode(', ', array_filter($en, $notNull));
		
		if ($en) {
			$en = " ({$en})";
		}
		
		$region['title'] = $ru . $en;

		return $region;
	}
	
	public function buildEvent($event, $rebuild = false) {
		$id = $event['id'];
		
		$event['game'] = $event['game_id']
			? $this->db->getGame($event['game_id'])
			: $this->db->getDefaultGame();

		$event['pub_date'] = strtotime($event['published_at']);

		$event = $this->stamps($event);

		$start = $event['starts_at'];
		$end = $event['ends_at'];

		$event['start_date'] = $start;
		$event['end_date'] = $end;

		$event['url'] = $this->linker->event($event['id']);
		$event['title'] = $event['name'];

		$regionRow = $this->db->getRegion($event['region_id']);
		if ($regionRow) {
			$event['region'] = $this->buildRegion($regionRow);
		}
		
		$event['type'] = $this->db->getEventType($event['type_id']);

		$event['tags'] = $this->tags($event['tags'], Taggable::EVENTS);

		// ui
		$event['interval_ui'] = Date::formatIntervalUi($start, $end);
		$event['subtitle'] = $event['type']['name'] . ', ' . $event['interval_ui'];

		$event['start_ui'] = Date::formatUi($start);
		$event['end_ui'] = Date::formatUi($end);
		
		// started? ended?
		$event['started'] = Date::happened($start);

		$end = $end ?? Date::endOfDay($start);
		$event['ended'] = Date::happened($end);
		
		// description
		if (!$rebuild && strlen($event['cache']) > 0) {
			$text = $event['cache'];
		}
		else {
			$parsed = $this->parser->parse($event['description']);
			$text = $parsed['text'];

			$this->db->saveEventCache($id, $text);
		}
		
		$text = $this->parser->renderLinks($text);
		
		$event['description'] = $text;

		return $event;
	}
	
	public function buildEventsByTag($tag) {
		$tag = Strings::normalize($tag);
		
		$events = $this->db->getEventsByTag($tag);

		$events = array_map(function($e) {
			return $this->buildEvent($e);
		}, $events);
		
		return $events;
	}
	
	protected function tags($tags, $tab = null) {
		return array_map(function($t) use ($tab) {
			$tag = trim($t);
			
			return [
				'text' => $tag,
				'url' => $this->linker->tag($tag, $tab),
			];
		}, explode(',', $tags));
	}
}
