<?php

namespace App\Core;

use Plasticode\Core\Decorator as DecoratorBase;

class Decorator extends DecoratorBase {
	private function articleBase($template) {
		return $template
			? "%article%"
			: $this->getSettings('legacy.articles.page');
	}

	public function articleUrlBare($name, $cat, $template = false) {
		if ($cat) {
			$cat = '/' . $cat;
		}

		$url = $this->articleBase($template);

		return $url . '/' . $name . $cat;
	}

	public function articleUrl($nameRu, $nameEn, $nameEsc, $cat, $catEsc, $template = false, $style = "nd_article") {
		if ($cat) {
			$cat = " ({$cat})";
		}

		$url = $this->articleUrlBare($nameEsc, $catEsc, $template);

		return $this->url($url, $nameRu, $nameEn . $cat, $style);
	}

	public function noArticleUrl($nameRu, $nameEn, $cat = null) {
		if ($cat) {
			$cat = " ({$cat})";
		}

		return "<font class=\"nd_noarticle\" title=\"{$nameEn}{$cat}\">{$nameRu}</font>";
	}
	
	public function entityUrl($url, $text, $title = null) {
		return $this->url($url, $text, $title, 'nd_article');
	}

	public function recipePageUrl($id, $title, $rel = null, $content = '[~]') {
		$url = $this->linker->recipe($id);
		
		if ($rel) {
			$rel = " rel=\"{$rel}\"";
		}

		return "<a href=\"{$url}\" title=\"{$title}\"{$rel}>{$content}</a>";
	}

	public function coordsBlock($x, $y) {
		return '[' . round($x) . ',&nbsp;' . round($y) . ']';
	}

	public function bluepostBlock($text, $author, $url = null, $date = null) {
		$author = $author ?? 'Blizzard';

		if ($url) {
			$author = $this->url($url, $author, null, 'blue');
		}
		
		if ($date) {
			$date = " [{$date}]";
		}

		$result = "<div class=\"quote bluepost\"><div class=\"quote-header\"><span class=\"quote-author\">{$author}</span>{$date}:</div><div class=\"quote-body\">{$text}</div></div>";

		return $result;
	}
}
