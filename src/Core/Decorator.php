<?php

namespace App\Core;

use Plasticode\Core\Decorator as DecoratorBase;

class Decorator extends DecoratorBase
{
	private function articleBase($template)
	{
		return $template
			? "%article%"
			: $this->getSettings('legacy.articles.page');
	}

	public function articleUrlBare($name, $cat, $template = false)
	{
		if ($cat) {
			$cat = '/' . $cat;
		}

		$url = $this->articleBase($template);

		return $url . '/' . $name . $cat;
	}

	public function articleUrl($nameRu, $nameEn, $nameEsc, $cat, $catEsc, $template = false, $style = "nd_article")
	{
		if ($cat) {
			$cat = " ({$cat})";
		}

		$url = $this->articleUrlBare($nameEsc, $catEsc, $template);

		return $this->component('url', [
		    'url' => $url,
		    'text' => $nameRu,
		    'title' => $nameEn . $cat,
		    'style' => $style,
		]);
	}

	public function noArticleUrl($nameRu, $nameEn, $cat = null)
	{
		if ($cat) {
			$cat = " ({$cat})";
		}

		return "<font class=\"nd_noarticle\" data-toggle=\"tooltip\" title=\"{$nameEn}{$cat}\">{$nameRu}</font>";
	}
	
	public function entityUrl($url, $text, $title = null)
	{
		return $this->component('url', [
		    'url' => $url,
		    'text' => $text,
		    'title' => $title,
		    'style' => 'nd_article',
		]);
	}

	public function recipePageUrl($id, $title, $rel = null, $content = '[~]')
	{
		$url = $this->linker->recipe($id);
		
		if ($rel) {
			$rel = " rel=\"{$rel}\"";
		}

		return "<a href=\"{$url}\" data-toggle=\"tooltip\" title=\"{$title}\"{$rel}>{$content}</a>";
	}
}
