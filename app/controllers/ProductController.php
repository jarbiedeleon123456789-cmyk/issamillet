<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('ProductModel');
        $this->call->library('session');
    }

    /**
     * READ - Display all products
     */
    public function index()
    {
        $data['products'] = $this->ProductModel->all_products();
        $data['success']  = $this->session->flashdata('success');
        $data['error']    = $this->session->flashdata('error');
        $data['username'] = $this->session->userdata('username');
        $data['role']     = $this->session->userdata('role') ?: 'user';

        $this->call->view('products/index', $data);
    }

    /**
     * CREATE - Show the add product form
     */
    public function create()
    {
        $data['product']  = [
            'product_name' => '',
            'description'  => '',
            'price'        => '',
            'quantity'     => '',
        ];
        $data['mode']     = 'create';
        $data['error']    = $this->session->flashdata('error');
        $data['old']      = $this->session->flashdata('old') ?: [];
        $data['username'] = $this->session->userdata('username');
        $data['role']     = $this->session->userdata('role') ?: 'user';

        if (!empty($data['old'])) {
            $data['product'] = array_merge($data['product'], $data['old']);
        }

        $this->call->view('products/form', $data);
    }

    /**
     * CREATE - Handle the add product submission
     */
    public function store()
    {
        if (!$this->request->is_post()) {
            redirect(site_url('products/create'));
        }

        $errors = $this->validate_product();

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            $this->session->set_flashdata('old', $this->request->post());
            redirect(site_url('products/create'));
        }

        $this->ProductModel->insert([
            'product_name' => trim((string) $this->request->post('product_name')),
            'description'  => trim((string) $this->request->post('description')),
            'price'        => (float) $this->request->post('price'),
            'quantity'     => (int) $this->request->post('quantity'),
        ]);

        $this->session->set_flashdata('success', 'Product added to the lineup.');
        redirect(site_url('products'));
    }

    /**
     * UPDATE - Show the edit product form
     *
     * @param int $id
     */
    public function edit($id)
    {
        $product = $this->ProductModel->find((int) $id);

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect(site_url('products'));
        }

        $data['product']  = $product;
        $data['mode']     = 'edit';
        $data['error']    = $this->session->flashdata('error');
        $data['username'] = $this->session->userdata('username');
        $data['role']     = $this->session->userdata('role') ?: 'user';

        $this->call->view('products/form', $data);
    }

    /**
     * UPDATE - Handle the edit product submission
     *
     * @param int $id
     */
    public function update($id)
    {
        if (!$this->request->is_post()) {
            redirect(site_url('products'));
        }

        $product = $this->ProductModel->find((int) $id);

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect(site_url('products'));
        }

        $errors = $this->validate_product();

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect(site_url('products/edit/' . $id));
        }

        $this->ProductModel->update((int) $id, [
            'product_name' => trim((string) $this->request->post('product_name')),
            'description'  => trim((string) $this->request->post('description')),
            'price'        => (float) $this->request->post('price'),
            'quantity'     => (int) $this->request->post('quantity'),
        ]);

        $this->session->set_flashdata('success', 'Product updated.');
        redirect(site_url('products'));
    }

    /**
     * DELETE - Remove a product
     *
     * @param int $id
     */
    public function delete($id)
    {
        $product = $this->ProductModel->find((int) $id);

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect(site_url('products'));
        }

        $this->ProductModel->delete((int) $id);

        $this->session->set_flashdata('success', 'Product removed.');
        redirect(site_url('products'));
    }

    /**
     * Shared validation for create/update
     *
     * @return array List of error messages (empty when valid)
     */
    private function validate_product()
    {
        $errors = [];

        $name     = trim((string) $this->request->post('product_name'));
        $price    = $this->request->post('price');
        $quantity = $this->request->post('quantity');

        if ($name === '') {
            $errors[] = 'Product name is required.';
        }

        if ($price === null || $price === '' || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Price must be a valid, non-negative number.';
        }

        if ($quantity === null || $quantity === '' || !is_numeric($quantity) || (int) $quantity < 0) {
            $errors[] = 'Quantity must be a valid, non-negative whole number.';
        }

        return $errors;
    }
}