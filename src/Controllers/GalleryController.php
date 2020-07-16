<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Services\GalleryService;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class GalleryController extends Controller
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;
    private GalleryPictureRepositoryInterface $galleryPictureRepository;

    private GalleryService $galleryService;
    private NotFoundHandler $notFoundHandler;

    /**
     * Gallery title for views.
     */
    private string $galleryTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->galleryAuthorRepository = $container->galleryAuthorRepository;
        $this->galleryPictureRepository = $container->galleryPictureRepository;

        $this->galleryService = $container->galleryService;
        $this->notFoundHandler = $container->notFoundHandler;

        $this->galleryTitle = $this->getSettings('gallery.title');
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $pictures = $this->galleryService->getChunk();

        $lastPic = $pictures->last();

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'news'],
                'params' => [
                    'title' => $this->galleryTitle,
                    'pictures' => $pictures,
                    'border_id' => $lastPic ? $lastPic->getId() : null,
                ],
            ]
        );

        return $this->render($response, 'main/gallery/index.twig', $params);
    }

    public function author(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $alias = $args['alias'];

        $author = $this->galleryAuthorRepository->getPublishedByAlias($alias);

        if (!$author) {
            return ($this->notFoundHandler)($request, $response);
        }

        $pictures = $this->galleryService->getChunk(null, $author);

        $lastPic = $pictures->last();

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery', 'news'],
                'large_image' => $author->displayPicture()
                    ? $this->linker->abs($author->displayPicture()->url())
                    : null,
                'params' => [
                    'author' => $author,
                    'pictures' => $pictures,
                    'border_id' => $lastPic ? $lastPic->getId() : null,
                    'title' => $author->fullName(),
                    'gallery_title' => $this->galleryTitle,
                    'disqus_url' => $this->linker->disqusGalleryAuthor($author),
                    'disqus_id' => 'galleryauthor' . $author->getId(),
                    'rel_prev' => $author->prev() ? $author->prev()->url() : null,
                    'rel_next' => $author->next() ? $author->next()->url() : null,
                ],
            ]
        );

        return $this->render($response, 'main/gallery/author.twig', $params);
    }

    public function picture(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];
        $alias = $args['alias'] ?? null;

        $picture = $this->galleryPictureRepository->get($id);

        if (!$picture) {
            return ($this->notFoundHandler)($request, $response);
        }

        $author = $picture->author();

        // Todo: do we need alias here at all?
        if ($alias) {
            $aliasAuthor = $this
                ->galleryAuthorRepository
                ->getPublishedByAlias($alias);

            if (!$author->equals($aliasAuthor)) {
                return ($this->notFoundHandler)($request, $response);
            }
        }

        $fullscreen = $request->getQueryParam('full', null);

        $params = $this->buildParams(
            [
                'game' => $picture->game(),
                'global_context' => true,
                'sidebar' => ['stream', 'gallery', 'news'],
                'large_image' => $this->linker->abs($picture->url()),
                'description' => $author->fullName(),
                'params' => [
                    'author' => $author,
                    'picture' => $picture,
                    'title' => $picture->comment,
                    'gallery_title' => $this->galleryTitle,
                    'rel_next' => $picture->next()
                        ? $picture->next()->pageUrl()
                        : null,
                    'rel_prev' => $picture->prev()
                        ? $picture->prev()->pageUrl()
                        : null,
                    'fullscreen' => $fullscreen !== null,
                ],
            ]
        );

        return $this->render($response, 'main/gallery/picture.twig', $params);
    }

    public function chunk(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $borderId = $args['border_id'];

        $authorId = $request->getQueryParam('author_id', null);
        $tag = $request->getQueryParam('tag', null);
        $showAuthor = $request->getQueryParam('show_author', false);

        $borderPic = $borderId > 0
            ? $this->galleryPictureRepository->get($borderId)
            : null;

        $author = $authorId > 0
            ? $this->galleryAuthorRepository->get($authorId)
            : null;

        $pictures = $this
            ->galleryService
            ->getChunk($borderPic, $author, $tag);

        if ($pictures->isEmpty()) {
            throw new NotFoundException();
        }

        return $this->render(
            $response,
            'components/gallery_chunk.twig',
            [
                'pictures' => $pictures,
                'show_author' => $showAuthor !== false,
            ]
        );
    }
}
