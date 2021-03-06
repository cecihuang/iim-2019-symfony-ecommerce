<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart", methods={"GET"})
     */
    public function cart(SessionInterface $session)
    {
        $repositoryP = $this->getDoctrine()->getRepository(Product::class);

        $objectManager = $this->getDoctrine()->getManager();

                $cartId = $session->get('cart');

                if (!$cartId) {
                    $cart = new Cart();

                    $objectManager->persist($cart);
                    $objectManager->flush();

                    $session->set('cart', $cartId = $cart->getId());
                } else {
                    $repositoryCart = $this->getDoctrine()->getRepository(Cart::class);
                    /** @var Cart $cart */
                    $cart = $repositoryCart->find($cartId);
                }


        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/cart.json", name="cart_json", methods={"GET"})
     */
    public function cartJson()
    {
        $cart = [
            'products' => [
                'id'       => 1,
                'quantity' => 2
            ]
        ];

        return new JsonResponse($cart);
    }

    /**
     * @Route("/cart/add.json", name="add_cart_json", methods={"POST"})
     */
    public function addToCartJson(Request $request, SessionInterface $session)
    {
        $repositoryP = $this->getDoctrine()->getRepository(Product::class);
        $product     = $repositoryP->find($request->request->get('product_id'));

        $objectManager = $this->getDoctrine()->getManager();

        if (!$product instanceof Product) {
            $status  = 'ko';
            $message = 'Product not found';
        } else {
            if ($product->getStock() < $request->request->get('quantity')) {
                $status  = 'ko';
                $message = 'Missing quantity for product';
            } else {
                $cartId = $session->get('cart');

                if (!$cartId) {
                    $cart = new Cart();
                    $objectManager->persist($cart);
                    $objectManager->flush();
                    $session->set('cart', $cartId = $cart->getId());
                } else {
                    $repositoryCart = $this->getDoctrine()->getRepository(Cart::class);
                    /** @var Cart $cart */
                    $cart = $repositoryCart->find($cartId);
                }
                foreach($cart->getCartProducts() as $cartProduct){
                    if($cartProduct->getProduct()->getId() == $product->getId()){
                        $cartProduct->setQuantity($cartProduct->getQuantity()+1);
                        $status  = 'ok';
                        $message = 'Added to cart';
                        $objectManager->persist($cart);
                        $objectManager->persist($cartProduct);
                        $objectManager->flush();
                        $page = $this->render('partials/cart.html.twig',['cart'=>$cart])->getContent();
                        return new JsonResponse([
                            'result'    => $status,
                            'message'   => $message,
                            'page'      => substr($page,42,strlen($page)-6)
                        ]);
                    }
                }
                $cartProduct = new CartProduct();
                $cartProduct->setCart($cart);
                $cartProduct->setProduct($product);
                $cartProduct->setQuantity((int)$request->request->get('quantity'));
                $status  = 'ok';
                $message = 'Added to cart';
                $cart->addCartProduct($cartProduct);
                $objectManager->persist($cart);
                $objectManager->persist($cartProduct);
                $objectManager->flush();
                $page = $this->render('partials/cart.html.twig',['cart'=>$cart])->getContent();
                return new JsonResponse([
                    'result'    => $status,
                    'message'   => $message,
                    'page'      => substr($page,42,strlen($page)-6)
                ]);

            }
        }
        return new JsonResponse([
            'result'    => $status,
            'message'   => $message,
        ]);
    }

    /**
     * @Route("/cart/delete.json", name="delete_cart_json", methods={"POST"})
     */
    public function deleteToCartJson(Request $request, SessionInterface $session)
    {
        $repositoryP = $this->getDoctrine()->getRepository(Product::class);
        $product     = $repositoryP->find($request->request->get('product_id'));

        $objectManager = $this->getDoctrine()->getManager();

        if (!$product instanceof Product) {
            $status  = 'ko';
            $message = 'Product not found';
        } else {
            $cartId = $session->get('cart');

            if ($cartId) {
                $repositoryCart = $this->getDoctrine()->getRepository(Cart::class);
                $cart = $repositoryCart->find($cartId);
                $repositoryCartProduct = $this->getDoctrine()->getRepository(CartProduct::class);
                $cartProduct = $repositoryCartProduct->findOneBy(['product'=>$product]);

                $cart->removeCartProduct($cartProduct);
            } else {
                $status  = 'ko';
                $message = 'Error, we couldn\'t delete the product from the cart.';
                return new JsonResponse([
                    'result'    => $status,
                    'message'   => $message,
                ]);
            }

            $status  = 'ok';
            $message = 'Successfuly deleted';
            $objectManager->persist($cart);
            $objectManager->flush();
            $page = $this->render('partials/cart.html.twig',['cart'=>$cart])->getContent();

            return new JsonResponse([
                'result'    => $status,
                'message'   => $message,
                'page'      => substr($page,42,strlen($page)-6)
            ]);

        }
        return new JsonResponse([
            'result'    => $status,
            'message'   => $message,
        ]);

    }

    public function partial(SessionInterface $session)
    {
        $cartId = $session->get('cart');

        $repositoryCart = $this->getDoctrine()->getRepository(Cart::class);

        /** @var Cart $cart */
        $cart = $cartId ? $repositoryCart->find($cartId) : new Cart();

        return $this->render('partials/cart.html.twig', [
            'cart' => $cart
        ]);
    }
}
