<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\PostRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user,PostRepository $postRepository,CommentRepository $commentRepository): Response
    {$this->denyAccessUnlessGranted("ROLE_USER", null,'Access Denied.');
        $userposts=$postRepository->findBy(array('iduser' => $user->getId()),array('postdate' => 'desc'));
        $postcomments=array();
        $likedposts=$user->getLikedposts();
        $likedpostscomments=array();
        $usercomments=$commentRepository->findBy(array('iduser' => $user->getId()),array('commentdate' => 'desc'));
        foreach($userposts as $post)
        {if (strlen($post->getContent())>50)
            {
                $result = substr($post->getContent(), 0, 50)."...";
                $post->setContent($result);
            }
            array_push($postcomments,$postRepository->findPostComments($post->getId()));

        }
        foreach($usercomments as $comment)
        {if (strlen($comment->getContent())>50)
            {
                $result = substr($post->getContent(), 0, 50)."...";
                $comment->setContent($result);
            }

        }
        
        $likedpostsobject=array();
        foreach($likedposts as $likedpost)
        {$post=$postRepository->findBy(array('id' => $likedpost))[0];
            if (strlen($post->getContent())>50)
            {
                $result = substr($post->getContent(), 0, 50)."...";
                $post->setContent($result);
            }
            array_push($likedpostsobject,$post);
            array_push($likedpostscomments,$postRepository->findPostComments($post->getId()));

        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'posts'=>$userposts,
            'postcomments'=>$postcomments,
            'likedposts'=>$likedpostsobject,
            'likedpostscomments'=>$likedpostscomments,
            'usercomments'=>$usercomments,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('index');
    }
}
