<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seller;
use AppBundle\Form\Type\SellerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class sellerController.
 *
 * @Route("/seller")
 */
class SellerController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="seller_index")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('seller/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()",name="seller_index_ajax")
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Seller::class, true, true);

        return $this->render('seller/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="seller_add")
     * @Security("user.isTeamAdministrator()")
     */
    public function addAction(Request $request)
    {
        $seller = new Seller();
        $form = $this->createForm(SellerType::class, $seller)
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Save & Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($seller);
            $em->flush();

            $this->addFlash('success', 'The seller has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'seller_add'
                : 'seller_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('seller/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="seller_edit")
     * @Security("user.isTeamAdministrator()")
     */
    public function editAction(Seller $seller, Request $request)
    {
        $form = $this->createForm(SellerType::class, $seller);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The seller has been edited successfully.');

            return $this->redirectToRoute('seller_index');
        }

        return $this->render('seller/edit.html.twig', [
            'seller' => $seller,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="seller_delete")
     * @Method("POST")
     * @Security("user.isTeamAdministrator()")
     */
    public function deleteAction(Seller $seller, Request $request)
    {
        // If the seller is used by a product, redirect user
        if (!$seller->getProducts()->isEmpty()) {
            $this->addFlash('warning', 'The seller cannot be deleted, it\'s used by product(s).');

            return $this->redirectToRoute('seller_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('seller_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('seller_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($seller);
        $em->flush();

        $this->addFlash('success', 'The seller has been deleted successfully.');

        return $this->redirectToRoute('seller_index');
    }
}
