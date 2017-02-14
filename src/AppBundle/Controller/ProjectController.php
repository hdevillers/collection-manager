<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Form\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProjectController.
 *
 * @Route("/project")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ProjectController extends Controller
{
    /**
     * @Route("/",
     *     options={"expose"=true},
     *     name="project_index"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('project/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="project_index_ajax"
     * )
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Project');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $this->getUser());
        $nbResults = $this->get('fos_elastica.index.app.project')->count($elasticQuery);
        $finder = $this->get('fos_elastica.finder.app.project');
        $projectList = $finder->find($elasticQuery);

        $nbPages = ceil($nbResults / Project::NUM_ITEMS);

        return $this->render('project/_list.html.twig', [
            'projectList' => $projectList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/{id}", name="project_view", requirements={"id": "\d+"})
     * @ParamConverter("project", class="AppBundle:Project", options={
     *     "repository_method" = "findOneWithAdminsMembers"
     * })
     * @Security("is_granted('PROJECT_VIEW', project)")
     */
    public function viewAction(Project $project)
    {
        return $this->render('project/view.html.twig', array(
            'project' => $project,
        ));
    }

    /**
     * @Route("/add", name="project_add")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function addAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project)
            ->add('save', SubmitType::class, [
                'label' => 'Create',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Create and Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                ],
            ])
            ->add('saveAndAddBox', SubmitType::class, [
                'label' => 'Create and Add a box',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            if ($project->isValid()) {
                $this->addFlash('success', 'The project has been added successfully. Now you may create one or more boxe(s).');
            } else {
                $this->addFlash('warning', 'The project has been added successfully, but a team admin must valid it.');
            }

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('project_add');
            } elseif ($form->get('saveAndAddBox')->isClicked() && $project->isValid()) {
                return $this->redirectToRoute('box_add_4_project', ['id' => $project->getId()]);
            } else {
                return $this->redirectToRoute('project_index');
            }
        }

        return $this->render('project/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/validate", name="project_validate")
     * @Security("is_granted('PROJECT_VALIDATE', project)")
     */
    public function validateAction(Project $project, Request $request)
    {
        if ($project->isValid()) {
            $this->addFlash('warning', 'The project is already valid !');

            return $this->redirectToRoute('project_index');
        }

        if (!$this->isCsrfTokenValid('project_validate', $request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid !');

            return $this->redirectToRoute('project_view', ['id' => $project->getId()]);
        }

        $em = $this->getDoctrine()->getManager();
        $project->setValid(true);
        $em->flush();

        $this->addFlash('success', 'The project has been successfully validated.');

        return $this->redirectToRoute('project_index');
    }

    /**
     * @Route("/{id}/edit", name="project_edit")
     * @Security("is_granted('PROJECT_EDIT', project)")
     */
    public function editAction(Project $project, Request $request)
    {
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The project has been edited unsuccessfully.');

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="project_delete")
     * @Method("POST")
     * @Security("is_granted('PROJECT_DELETE', project)")
     */
    public function deleteAction(Project $project, Request $request)
    {
        // If the project is not empty, else redirect user
        if (!$project->getBoxes()->isEmpty()) {
            $this->addFlash('warning', 'The project cannot be deleted, there are boxes attached.');

            return $this->redirectToRoute('type_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('project_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('project_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($project);
        $entityManager->flush();

        $this->addFlash('success', 'The project has been deleted successfully.');

        return $this->redirectToRoute('project_index');
    }
}
