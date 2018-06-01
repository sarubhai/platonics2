<?php

namespace Tests\Unit;

use Tests\TestDataSetup;

class CategoriesTest extends TestDataSetup
{
    /**
     * Total Test Cases: 10
     */

    /**
     * Positive Test Cases: 6
     */

    public function test_index_returns_expected_structure()
    {
        $this->get('/api/categories')
                ->assertStatus(200)
                ->assertJsonStructure([ 
                    'length',
                    'data' => [
                        '*' => [
                            'id',
                            'parent_id',
                            'name',
                            'description',
                            'access_level',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_index_returns_expected_length()
    {
        $content = $this->get('/api/categories')->decodeResponseJson();
        $this->assertEquals($content['length'], count(\App\Category::all()));
    }

    public function test_show_returns_expected_structure()
    {
        $this->get('/api/categories/' . $this->category1->id)
                ->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'parent_id',
                    'name',
                    'description',
                    'access_level',
                    'created_at',
                    'updated_at'
                ]);
    }

    public function test_store_can_persist_data()
    {
        $category = [
            'name' => 'test category',
            'description' => 'test category desc',
            'access_level' => 'F'
        ];

        $this->post('/api/categories', $category)
                ->assertStatus(201)
                ->assertJsonFragment($category);
    }

    public function test_update_can_persist_data()
    {
        $category = [
            'name' => 'test category',
            'description' => 'test category desc',
            'access_level' => 'F'
        ];
        $response = $this->post('/api/categories', $category)->decodeResponseJson();

        $payload = [
            'name' => 'test category update',
            'description' => 'test category desc update',
            'access_level' => 'F'
        ];
        $this->call('PUT', '/api/categories/' . $response['id'], $payload)
                ->assertStatus(200)
                ->assertJsonFragment($payload);;
    }

    public function test_destroy_can_delete_data()
    {
        $this->call('DELETE', '/api/categories/1')
                ->assertStatus(200);
    }

    /**
     * Negative Test Cases: 4
     */

    public function test_show_error_invalid_id()
    {
        $this->get('/api/categories/4')
                ->assertStatus(404);
    }

    public function test_store_error_invalid_data()
    {
        $category = [
            'name' => 'test category',
            'description' => 'test category desc',
            'access_level' => 'X'
        ];

        $this->post('/api/categories', $category)
                ->assertStatus(302);
                //->assertJsonFragment(['message' => 'The given data was invalid']);
    }

    public function test_update_error_invalid_data()
    {
        $category = [
            'name' => 'test category',
            'description' => 'test category desc',
            'access_level' => 'F'
        ];
        $response = $this->post('/api/categories', $category)->decodeResponseJson();

        $payload = [
            'name' => 'test category update',
            'description' => 'test category desc update',
            'access_level' => 'X'
        ];
        $this->call('PUT', '/api/categories/' . $response['id'], $payload)
                ->assertStatus(302);
    }

    public function test_destroy_error_invalid_id()
    {
        $this->call('DELETE', '/api/categories/4')
                ->assertStatus(404);
    }
}
