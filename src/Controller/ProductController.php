<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages product related operations.
 */
final class ProductController extends AbstractController
{
    /**
     * Displays the product index page.
     * @return Response The rendered index template.
     */
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    /**
     * Creates a new product with a random name and value.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @returns Response The confirmation message.
     */
    #[Route('/product/create', name: 'product_create')]
    public function createProduct(
        ManagerRegistry $doctrine
    ): Response {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('Keyboard_num_' . rand(1, 9));
        $product->setValue(rand(100, 999));

        // tell Doctrine you want to (eventually) save the Product
        // (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    /**
     * Displays all products as JSON.
     * @param ProductRepository $productRepository The repository for product queries.
     * @return Response The JSON response with product data.
     */
    #[Route('/product/show', name: 'product_show_all')]
    public function showAllProduct(
        ProductRepository $productRepository
    ): Response {
        $products = $productRepository
            ->findAll();

        //return $this->json($products);
        $response = $this->json($products);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    /**
     * Displays a single product by ID as JSON.
     * @param ProductRepository $productRepository The repository for product queries.
     * @param int $id The ID of the product to display.
     * @return Response The JSON response with product data.
     */
    #[Route('/product/show/{id}', name: 'product_by_id')]
    public function showProductById(
        ProductRepository $productRepository,
        int $id
    ): Response {
        $product = $productRepository
            ->find($id);

        return $this->json($product);
    }

    /**
     * Deletes a product by ID.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @param int $id The ID of the product to delete.
     * @return Response The redirect response.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If product is not found.
     */
    #[Route('/product/delete/{id}', name: 'product_delete_by_id')]
    public function deleteProductById(
        ManagerRegistry $doctrine,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('product_show_all');
    }

    /**
     * Updates a product's value by ID.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @param int $id The ID of the product to update.
     * @param int $value The new value to set.
     * @return Response The redirect response.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If product is not found.
     */
    #[Route('/product/update/{id}/{value}', name: 'product_update')]
    public function updateProduct(
        ManagerRegistry $doctrine,
        int $id,
        int $value
    ): Response {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $product->setValue($value);
        $entityManager->flush();

        return $this->redirectToRoute('product_show_all');
    }

    /**
     * Displays all products in an HTML view.
     * @param ProductRepository $productRepository The repository for product queries.
     * @return Response The rendered product list template.
     */
    #[Route('/product/view', name: 'product_view_all')]
    public function viewAllProduct(
        ProductRepository $productRepository
    ): Response {
        $products = $productRepository->findAll();

        $data = [
            'products' => $products
        ];

        return $this->render('product/view.html.twig', $data);
    }

    /**
     * Displays products with a minimum value in an HTML view.
     * @param ProductRepository $productRepository The repository for product queries.
     * @param int $value The minimum value for filtering products.
     * @return Response The rendered product list template.
     */
    #[Route('/product/view/{value}', name: 'product_view_minimum_value')]
    public function viewProductWithMinimumValue(
        ProductRepository $productRepository,
        int $value
    ): Response {
        $products = $productRepository->findByMinimumValue($value);

        $data = [
            'products' => $products
        ];

        return $this->render('product/view.html.twig', $data);
    }

    /**
     * Displays products with a minimum value as JSON.
     * @param ProductRepository $productRepository The repository for product queries.
     * @param int $value The minimum value for filtering products.
     * @return Response The JSON response with product data.
     */
    #[Route('/product/show/min/{value}', name: 'product_by_min_value')]
    public function showProductByMinimumValue(
        ProductRepository $productRepository,
        int $value
    ): Response {
        $products = $productRepository->findByMinimumValue2($value);

        return $this->json($products);
    }
}
