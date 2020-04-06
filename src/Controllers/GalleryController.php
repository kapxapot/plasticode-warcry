<?php

namespace App\Controllers;

use App\Handlers\NotFoundHandler;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Services\GalleryService;
use Plasticode\Exceptions\Http\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class GalleryController extends Controller
{
    private GalleryService $galleryService;
    private NotFoundHandler $notFoundHandler;

    /**
     * Gallery title for views
     */
    private string $galleryTitle;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->galleryService = $container->galleryService;
        $this->notFoundHandler = $container->notFoundHandler;

        $this->galleryTitle = $this->getSettings('gallery.title');
    }

    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $query = GalleryPicture::getPublished();
        $pictures = $this->galleryService->getPage($query)->all();
        
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

        $author = GalleryAuthor::getPublishedByAlias($alias);

        if (!$author) {
            return ($this->notFoundHandler)($request, $response);
        }
        
        $id = $author->id;

        $query = $author->pictures();
        $pictures = $this->galleryService->getPage($query)->all();
        
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
                    'disqus_id' => 'galleryauthor' . $id,
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

        /** @var GalleryPicture */
        $picture = GalleryPicture::getPublished()->find($id);
        
        if (!$picture) {
            return ($this->notFoundHandler)($request, $response);
        }

        $author = $picture->author();

        if ($alias) {
            $aliasAuthor = GalleryAuthor::getPublishedByAlias($alias);
        
            if (!$aliasAuthor || $author->getId() != $aliasAuthor->getId()) {
                return ($this->notFoundHandler)($request, $response);
            }
            
            $author = $aliasAuthor;
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

        $borderPic = GalleryPicture::get($borderId);
        
        if ($authorId > 0) {
            $query = GalleryPicture::getPublishedByAuthor($authorId);
        } elseif (strlen($tag) > 0) {
            $query = GalleryPicture::getByTag($tag);
        }
        
        $query = GalleryPicture::getBefore($borderPic, $query);

        $pictures = $this->galleryService->getPage($query)->all();
        
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
