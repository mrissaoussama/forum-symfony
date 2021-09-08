<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentType;

use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Repository\CategorieRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, CategorieRepository $categorieRepository): Response 
    {$this->denyAccessUnlessGranted("ROLE_ADMIN");
        if (isset($_GET["searchvalue"])) {
            return $this->render("post/index.html.twig", [
                "posts" => $postRepository->findBySearchCategorie(
                    $_GET["searchvalue"],
                    $_GET["categorie"]
                ),
                "categories" => $categorieRepository->findAll(),
                "search" => $_GET["searchvalue"],
            ]);
        } else {
            return $this->render("post/index.html.twig", [
                "posts" => $postRepository->findAll(),
                "categories" => $categorieRepository->findAll(),
            ]);
        }
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request,CategorieRepository $categorieRepository):Response
    {$this->denyAccessUnlessGranted("ROLE_USER");
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filetoupload = $form->get("file")->getData();
            if ($filetoupload) {
                $originalFilename = pathinfo(
                    $filetoupload->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $newFilename =
                    $originalFilename .
                    "-" .
                    uniqid() .
                    "." .
                    $filetoupload->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $filetoupload->move(
                        $this->getParameter("file_directory"),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $post->setFile($newFilename);
            }
            $post->setPostdate(new \DateTime());
            $post->setIsdeleted(false);
            $post->setIduser($this->getUser());
            $post->setLikes([]);
            $post->setViews(0);
            $post->setIdcategorie($categorieRepository->findBy(array('id' => $_POST['categorie']))[0]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute("post_show",['id'=>$post->getId()]);
        }

        return $this->render("post/new.html.twig", [
            "post" => $post,
            "form" => $form->createView(),
            'categories' => $categorieRepository->findAll(),

        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET","POST"})
     */
    public function show(
        Post $post,
        PostRepository $postRepository,
        Request $request
    ): Response {
        if ($this->getUser() != null) {
            $hasliked = in_array([$this->getUser()->getId()], $post->getLikes());
        }
        else ($hasliked=false);
        $entityManager = $this->getDoctrine()->getManager();
        if ($this->getUser() != $post->getIduser()) {
            $post->setViews($post->getViews() + 1);
        }
        //form creation
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        //form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addcomment($post, $comment, $form);
        } else {
            $entityManager->persist($post);
        }
        $entityManager->flush();
        $comments = $postRepository->findPostComments($post->getId());
        return $this->render("post/show.html.twig", [
            "post" => $post,
            "poster" => $post->getIduser(),
            "liked" => $hasliked,
            "comments" => $comments,
            "form" => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/like", name="post_like", methods={"GET","POST"})
     */
    public function likepost(
        Post $post,
        PostRepository $postRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");
        //form creation
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        //form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addcomment($post, $comment, $form);
        }
        $hasliked = in_array([$this->getUser()->getId()], $post->getLikes());
        if (!$hasliked) {
            //add like to post
            $x = $post->getLikes();
            $p = [$this->getUser()->getId()];
            array_push($x, $p);
            $post->setLikes($x);
            //add liked post to user
            $user = $this->getUser();
            $userlikes = $user->getLikedPosts();
            $p = [$post->getId()];
            array_push($userlikes, $p);
            $user->setLikedPosts($userlikes);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute("post_show", ["id" => $post->getId()]);

    }
    /**
     * @Route("/{id}/unlike", name="post_unlike", methods={"GET","POST"})
     */
    public function unlikepost(
        Post $post,
        PostRepository $postRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");
        //form creation
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        //form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addcomment($post, $comment, $form);
        }

        $hasliked = in_array([$this->getUser()->getId()], $post->getLikes());
        if ($hasliked) {
            // remove like
            $x = $post->getLikes();
            $p = [$this->getUser()->getId()];
            //put array with user to search in post likes
            unset($x[array_search($p, $x)]); //unset removes item but leaves null in its index
            $x = array_values($x); //array values returns the values of array so null is removed
            $post->setLikes($x);
            //same with user
            $user = $this->getUser();
            $userlikes = $user->getLikedPosts();
            $p = [$post->getId()];
            unset($userlikes[array_search($userlikes, $p)]);
            $userlikes = array_values($userlikes);
            $user->setLikedPosts($userlikes);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute("post_show", ["id" => $post->getId()]);

    }
    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if ($post->getIduser() == $this->getUser()) {
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $filetoupload = $form->get("file")->getData();
                if ($filetoupload) {
                    $originalFilename = pathinfo(
                        $filetoupload->getClientOriginalName(),
                        PATHINFO_FILENAME
                    );
                    $newFilename =
                        $originalFilename .
                        "-" .
                        uniqid() .
                        "." .
                        $filetoupload->guessExtension();

                    try {
                        $filetoupload->move(
                            $this->getParameter("file_directory"),
                            $newFilename
                        );
                    } catch (FileException $e) {
                    }

                    $post->setFile($newFilename);
                }
                $this->getDoctrine()
                    ->getManager()
                    ->flush();

                    return $this->redirectToRoute("post_show", [
                        "id" => $post->getId(),
                    ]);            }

            return $this->render("post/edit.html.twig", [
                "post" => $post,
                "form" => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute("post_show", [
                "id" => $post->getId(),
            ]);
        }
    }

    /**
     * @Route("/{id}/delete", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request,Post $post,PostRepository $postRepository): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if (
            $this->isCsrfTokenValid(
                "delete" . $post->getId(),
                $request->request->get("_token")
            )
        ) {
            $post->setIsdeleted(true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
        }
        return $this->redirectToRoute("post_show", ["id" => $post->getId()]);

    }
    public function addcomment($post, $comment, $form)
    {$this->denyAccessUnlessGranted("ROLE_USER");
        $this->get("security.csrf.token_manager")->refreshToken(
            "form_intention"
        );
        //file check
        $filetoupload = $form->get("file")->getData();
        if ($filetoupload) {
            $originalFilename = pathinfo(
                $filetoupload->getClientOriginalName(),
                PATHINFO_FILENAME
            );
            $newFilename =
                $originalFilename .
                "-" .
                uniqid() .
                "." .
                $filetoupload->guessExtension();
            try {
                $filetoupload->move(
                    $this->getParameter("file_directory"),
                    $newFilename
                );
            } catch (FileException $e) {
            }
            $comment->setFile($newFilename);
        }
        //end file check
        //set comment values
        $comment->setCommentdate(new \DateTime());
        $comment->setIsdeleted(false);
        $comment->setIduser($this->getUser());
        $comment->setIsaccepted(false);
        $comment->setIdpost($post);

        //save to db
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();
    }

    /**
     * @Route("/{id}/accepted", name="post_setacceptedanswer", methods={"GEt","POST"})
     */
    public function setacceptedcomment(
        Post $post,
        PostRepository $postRepository,
        Request $request,
        CommentRepository $commentRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $comment = $commentRepository->find($_GET["idcomment"]);
        dump($comment);
        $comment->setIsaccepted(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();
        return $this->redirectToRoute("post_show", ["id" => $post->getId()]);
    }
}
