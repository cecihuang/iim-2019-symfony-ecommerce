<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrderProduct;
use App\Form\CardType;
use App\Model\Card;
use App\Entity\Cart;
use App\Entity\CartProduct;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/checkout")
 */
class CheckoutController extends AbstractController
{
    /**
     * @Route("/payment", name="checkout_payment", methods={"GET","POST"})
     */
    public function payment(Request $request, SessionInterface $session)
    {
        $objectManager = $this->getDoctrine()->getManager();
        $card = new Card();
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = new Orders();
            $objectManager->persist($order);
            $objectManager->flush();
            $cartId = $session->get('cart');
            $repositoryCart = $this->getDoctrine()->getRepository(Cart::class);
            $cart = $repositoryCart->find($cartId);
            $repositoryCartProducts = $this->getDoctrine()->getRepository(CartProduct::class);
            foreach($cart->getCartProducts() as $cartProduct){
                $orderProduct = new OrderProduct();
                $orderProduct->setProductId($cartProduct->getProduct()->getId());
                $orderProduct->setOrderId($order->getId());
                $orderProduct->setQuantity($cartProduct->getQuantity());
                $objectManager->persist($orderProduct);
                $objectManager->flush();
                $objectManager->remove($cartProduct);
                $objectManager->persist($cart);
                $objectManager->flush();

            }
            //header('Location:index');
        }

        return $this->render('checkout/payment.html.twig', [
            'card' => $card,
            'form' => $form->createView(),
        ]);
    }
}
