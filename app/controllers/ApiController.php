<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('api');
        $this->api = load_class('api', 'libraries');
        $this->call->database();
        $this->db = lava_instance()->db;
    }

    public function options()
    {
        $this->api->respond(null, 204);
    }

    public function login()
    {
        $input = $this->api->body();
        $identity = isset($input['identity']) && is_string($input['identity'])
            ? trim(htmlspecialchars_decode($input['identity'], ENT_QUOTES))
            : '';
        $password = isset($input['password']) && is_string($input['password'])
            ? htmlspecialchars_decode($input['password'], ENT_QUOTES)
            : '';

        if ($identity === '' || $password === '') {
            $this->api->respond_error('Identity and password are required.', 422);
        }

        $statement = $this->db->raw(
            'SELECT id, username, email, password, role, is_active
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1',
            [$identity, $identity]
        );
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user || (int) $user['is_active'] !== 1 || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid identity or password.', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id' => (int) $user['id'],
            'role' => $user['role'],
            'scopes' => ['read', 'write'],
        ]);

        unset($user['password'], $user['is_active']);

        $this->api->respond([
            'user' => $user,
            'tokens' => $tokens,
        ]);
    }

    public function register()
    {
        $input = $this->api->body();
        if (!is_array($input)) {
            $this->api->respond_error('A JSON object is required.', 400);
        }

        $username = isset($input['username']) && is_string($input['username'])
            ? trim(htmlspecialchars_decode($input['username'], ENT_QUOTES))
            : '';
        $email = isset($input['email']) && is_string($input['email'])
            ? strtolower(trim(htmlspecialchars_decode($input['email'], ENT_QUOTES)))
            : '';
        $password = isset($input['password']) && is_string($input['password'])
            ? htmlspecialchars_decode($input['password'], ENT_QUOTES)
            : '';

        if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
            $this->api->respond_error(
                'Username must be 3-100 characters and use only letters, numbers, dots, underscores, or hyphens.',
                422
            );
        }
        if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->api->respond_error('A valid email address is required.', 422);
        }
        if (strlen($password) < 8 || strlen($password) > 72) {
            $this->api->respond_error('Password must be between 8 and 72 bytes.', 422);
        }

        $duplicate = $this->db->raw(
            'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$username, $email]
        );
        if ($duplicate->fetch(PDO::FETCH_ASSOC)) {
            $this->api->respond_error('That username or email is already registered.', 409);
        }

        $this->db->table('users')->insert([
            'firstname' => $username,
            'lastname' => '',
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'is_active' => 1,
        ]);
        $userId = (int) $this->db->last_id();

        $statement = $this->db->raw(
            'SELECT id, username, email, role FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        $tokens = $this->api->issue_tokens([
            'id' => $userId,
            'role' => 'user',
            'scopes' => ['read', 'write'],
        ]);

        $this->api->respond([
            'user' => $user,
            'tokens' => $tokens,
        ], 201);
    }

    public function refresh()
    {
        $input = $this->api->body();
        $refreshToken = isset($input['refresh_token']) && is_string($input['refresh_token'])
            ? $input['refresh_token']
            : '';

        if ($refreshToken === '') {
            $this->api->respond_error('A refresh token is required.', 422);
        }

        $this->api->refresh_access_token($refreshToken);
    }

    public function logout()
    {
        $user = $this->authenticated_user();
        $input = $this->api->body();
        $refreshToken = isset($input['refresh_token']) && is_string($input['refresh_token'])
            ? $input['refresh_token']
            : '';

        if ($refreshToken !== '') {
            $payload = $this->api->validate_jwt($refreshToken);
            if (!$payload || ($payload['type'] ?? '') !== 'refresh' || (int) $payload['sub'] !== (int) $user['id']) {
                $this->api->respond_error('Invalid refresh token.', 422);
            }
            $this->api->revoke_refresh_token($refreshToken);
        }

        $this->api->respond(['message' => 'Signed out successfully.']);
    }

    public function products()
    {
        $this->authenticated_user();
        $statement = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at
             FROM products
             ORDER BY created_at DESC, id DESC'
        );

        $this->api->respond(['data' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function product($id)
    {
        $this->authenticated_user();
        $productId = $this->product_id($id);
        $statement = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at
             FROM products
             WHERE id = ?
             LIMIT 1',
            [$productId]
        );
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $this->api->respond_error('Product not found.', 404);
        }

        $this->api->respond(['data' => $product]);
    }

    public function create_product()
    {
        $this->authenticated_user();
        $fields = $this->validated_product_fields($this->api->body(), false);

        $this->db->table('products')->insert($fields);
        $productId = (int) $this->db->last_id();

        $statement = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at
             FROM products
             WHERE id = ?',
            [$productId]
        );

        $this->api->respond([
            'data' => $statement->fetch(PDO::FETCH_ASSOC),
            'message' => 'Product created.',
        ], 201);
    }

    public function update_product($id)
    {
        $this->authenticated_user();
        $productId = $this->product_id($id);
        $input = $this->api->body();
        $fields = $this->validated_product_fields(
            $input,
            strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'PATCH'
        );

        $statement = $this->db->raw(
            'SELECT id FROM products WHERE id = ? LIMIT 1',
            [$productId]
        );
        if (!$statement->fetch(PDO::FETCH_ASSOC)) {
            $this->api->respond_error('Product not found.', 404);
        }

        $columns = [];
        $values = [];
        foreach ($fields as $column => $value) {
            $columns[] = $column . ' = ?';
            $values[] = $value;
        }
        $values[] = $productId;

        $this->db->raw(
            'UPDATE products SET ' . implode(', ', $columns) . ' WHERE id = ?',
            $values
        );

        $updated = $this->db->raw(
            'SELECT id, product_name, description, price, quantity, created_at
             FROM products
             WHERE id = ?',
            [$productId]
        );

        $this->api->respond([
            'data' => $updated->fetch(PDO::FETCH_ASSOC),
            'message' => 'Product updated.',
        ]);
    }

    public function delete_product($id)
    {
        $this->authenticated_user();
        $productId = $this->product_id($id);
        $statement = $this->db->raw(
            'DELETE FROM products WHERE id = ?',
            [$productId]
        );

        if ($statement->rowCount() === 0) {
            $this->api->respond_error('Product not found.', 404);
        }

        $this->api->respond(['message' => 'Product deleted.']);
    }

    private function authenticated_user()
    {
        $payload = $this->api->require_jwt();
        if (!isset($payload['sub']) || !ctype_digit((string) $payload['sub'])) {
            $this->api->respond_error('Unauthorized.', 401);
        }

        $statement = $this->db->raw(
            'SELECT id, username, email, role, is_active
             FROM users
             WHERE id = ?
             LIMIT 1',
            [(int) $payload['sub']]
        );
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user || (int) $user['is_active'] !== 1) {
            $this->api->respond_error('Unauthorized.', 401);
        }

        return $user;
    }

    private function product_id($id)
    {
        if (!is_scalar($id) || !ctype_digit((string) $id) || (int) $id < 1) {
            $this->api->respond_error('A valid product ID is required.', 422);
        }

        return (int) $id;
    }

    private function validated_product_fields($input, $partial)
    {
        if (!is_array($input)) {
            $this->api->respond_error('A JSON object is required.', 400);
        }

        $allowed = ['product_name', 'description', 'price', 'quantity'];
        if (array_diff(array_keys($input), $allowed)) {
            $this->api->respond_error('Only product_name, description, price, and quantity may be changed.', 422);
        }
        if (!$partial) {
            foreach ($allowed as $field) {
                if (!array_key_exists($field, $input)) {
                    if ($field === 'description') {
                        $input[$field] = '';
                    } else {
                        $this->api->respond_error("The {$field} field is required.", 422);
                    }
                }
            }
        } elseif (!$input) {
            $this->api->respond_error('Provide at least one valid product field.', 422);
        }

        $fields = [];
        if (array_key_exists('product_name', $input)) {
            if (!is_string($input['product_name'])) {
                $this->api->respond_error('Product name must be text.', 422);
            }
            $name = trim(htmlspecialchars_decode($input['product_name'], ENT_QUOTES));
            preg_match_all('/./us', $name, $characters);
            if ($name === '' || count($characters[0]) > 100) {
                $this->api->respond_error('Product name must be between 1 and 100 characters.', 422);
            }
            $fields['product_name'] = $name;
        } elseif (!$partial) {
            $this->api->respond_error('The product_name field is required.', 422);
        }

        if (array_key_exists('description', $input)) {
            if (!is_string($input['description'])) {
                $this->api->respond_error('Description must be text.', 422);
            }
            $fields['description'] = htmlspecialchars_decode($input['description'], ENT_QUOTES);
        } elseif (!$partial) {
            $fields['description'] = '';
        }

        if (array_key_exists('price', $input)) {
            if (!is_string($input['price']) && !is_int($input['price']) && !is_float($input['price'])) {
                $this->api->respond_error('Price must be a non-negative amount with up to two decimal places.', 422);
            }
            $price = (string) $input['price'];
            if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price)) {
                $this->api->respond_error('Price must be a non-negative amount with up to two decimal places.', 422);
            }
            $fields['price'] = number_format((float) $price, 2, '.', '');
        } elseif (!$partial) {
            $this->api->respond_error('The price field is required.', 422);
        }

        if (array_key_exists('quantity', $input)) {
            if ((!is_string($input['quantity']) && !is_int($input['quantity'])) ||
                !preg_match('/^\d{1,10}$/', (string) $input['quantity']) ||
                (int) $input['quantity'] > 2147483647) {
                $this->api->respond_error('Quantity must be a non-negative whole number.', 422);
            }
            $fields['quantity'] = (int) $input['quantity'];
        } elseif (!$partial) {
            $this->api->respond_error('The quantity field is required.', 422);
        }

        return $fields;
    }
}
