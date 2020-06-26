<?php

namespace App\Validation\Rules;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class ArticleNameCatAvailable extends AbstractRule
{
    private ArticleRepositoryInterface $articleRepository;

    private ?int $catId = null;
    private ?int $exceptId = null;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        ?int $catId = null,
        ?int $exceptId = null
    )
    {
        $this->articleRepository = $articleRepository;

        $this->catId = $catId ?? 0;
        $this->exceptId = $exceptId ?? 0;
    }

    /**
     * @param string $input
     */
    public function validate($input)
    {
        return $this
            ->articleRepository
            ->lookup($input, $this->catId, $this->exceptId)
            ->isEmpty();
    }
}
