<?php

namespace App\Generators;

use App\Core\Interfaces\LinkerInterface;
use App\Repositories\Interfaces\ArticleCategoryRepositoryInterface;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator;

class ArticlesGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private ArticleCategoryRepositoryInterface $articleCategoryRepository;
    private ArticleRepositoryInterface $articleRepository;
    private LinkerInterface $linker;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->articleCategoryRepository = $container->articleCategoryRepository;
        $this->articleRepository = $container->articleRepository;
        $this->linker = $container->linker;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['name_ru'] = $this->rule('text');
        $rules['parent_id'] = Validator::nonRecursiveParent($this->entity, $id);

        if (array_key_exists('name_en', $data) && array_key_exists('cat', $data)) {
            $rules['name_en'] = $this->rule('text')
                ->articleNameCatAvailable($data['cat'], $id);
        }

        return $rules;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['exclude'] = ['text'];
        $options['admin_template'] = 'articles';

        return $options;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $article = $this->articleRepository->get($id);

        $item['name_en_esc'] = Strings::fromSpaces($article->nameEn);

        $cat = $article->category();

        if ($cat) {
            $item['cat_ru'] = $cat->nameRu;
            $item['cat_en'] = $cat->nameEn;
            $item['cat_en_esc'] = Strings::fromSpaces($cat->nameEn);
        }

        $game = $article->game();
        $parts = [$game->name];

        $parent = $article->parent();

        if ($parent && $parent->noBreadcrumb != 1) {
            $parentParent = $parent->parent();

            if ($parentParent && $parentParent->noBreadcrumb != 1) {
                $parts[] = '...';
            }

            $parts[] = $parent->nameRu;
        }
        
        $parts[] = $article->nameRu;
        $partsStr = implode(' » ', $parts);

        $item['select_title'] = '[' . $article->getId() . '] ' . $partsStr;
        $item['tokens'] = $game->name . ' ' . $article->nameRu;
        $item['url'] = $article->url();

        return $item;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);

        $nameEn = $data['name_en'] ?? null;

        if (!$nameEn) {
            $data['name_en'] = $data['name_ru'];
        }

        $data['cache'] = null;

        return $data;
    }

    public function afterSave(array $item, array $data) : void
    {
        parent::afterSave($item, $data);

        $this->notify($item, $data);
    }

    /**
     * Disabled currently.
     */
    private function notify(array $item, array $data) : void
    {
        if ($this->isJustPublished($item, $data) && $item['announce'] == 1) {
            $cat = $item['cat'] ?? null;

            if ($cat) {
                $category = $this->articleCategoryRepository->get($cat);

                if ($category) {
                    $catName = $category->nameEn;
                }
            }

            $url = $this->linker->article($item['name_en'], $catName);
            $url = $this->linker->abs($url);

            // $this->telegram->sendMessage(
            //     'warcry',
            //     '[Статья] <a href="' . $url . '">' . $item['name_ru'] . '</a>'
            // );
        }
    }
}
