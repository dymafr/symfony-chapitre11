<?php

namespace App\Controller;

use DateTime;
use App\Entity\Todo;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DefaultController extends AbstractController
{
  function __construct()
  {
  }

  #[Route('/', name: 'home')]
  public function index(TodoRepository $repo)
  {
    $todos = $repo->findAll();

    if (!$todos) {
      throw $this->createNotFoundException(
        'Pas de todos'
      );
    }

    return $this->render('home.html.twig', [
      'todos' => $todos
    ]);
  }

  #[Route('/form', name: 'todo_form')]
  #[Route('/edit/{id}', name: 'todo_edit')]
  public function form(Request $request, EntityManagerInterface $entityManager, ?Todo $todo)
  {

    $edit = $todo ? true : false;
    if (!$edit) {
      if ($request->get('_route') === 'todo_edit') {
        return $this->redirectToRoute('home');
      }
      $todo = new Todo();
    }

    $form = $this->createForm(TodoType::class, $todo);
    if ($edit) {
      $form->add('done', CheckboxType::class, ['required' => false]);
    }

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      if (!$edit) {
        $todo->setCreatedAt(new DateTime());
        $entityManager->persist($todo);
      }
      $entityManager->flush();
      return $this->redirectToRoute('home');
    }

    return $this->render('todo_form.html.twig', [
      'form' => $form->createView()
    ]);
  }

  #[Route('/remove/{id}', name: 'todo_remove')]
  public function remove(EntityManagerInterface $entityManager, Todo $todo)
  {
    $entityManager->remove($todo);
    $entityManager->flush();
    return $this->redirectToRoute('home');
  }
}