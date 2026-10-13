<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/
/** @var object $router **/

$router->get('/', 'Welcome::index');

/*
| -------------------------------------------------------------------
| Student Information Page routes (Laboratory Activity 3)
| -------------------------------------------------------------------
*/
$router->get('/student', 'StudentController::index');

$router->get('/student/profile', 'StudentController::profile')
       ->middleware('student_access');

/*
| -------------------------------------------------------------------
| Product Management API
| -------------------------------------------------------------------
*/
$router->post('/api/auth/login', 'ApiController::login');
$router->post('/api/auth/register', 'ApiController::register');
$router->post('/api/auth/refresh', 'ApiController::refresh');
$router->post('/api/auth/logout', 'ApiController::logout');

$router->get('/api/products', 'ApiController::products');
$router->post('/api/products', 'ApiController::create_product');
$router->get('/api/products/{id}', 'ApiController::product');
$router->put('/api/products/{id}', 'ApiController::update_product');
$router->patch('/api/products/{id}', 'ApiController::update_product');
$router->delete('/api/products/{id}', 'ApiController::delete_product');

$router->options('/api/auth/login', 'ApiController::options');
$router->options('/api/auth/register', 'ApiController::options');
$router->options('/api/auth/refresh', 'ApiController::options');
$router->options('/api/auth/logout', 'ApiController::options');
$router->options('/api/products', 'ApiController::options');
$router->options('/api/products/{id}', 'ApiController::options');