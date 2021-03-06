<?php

namespace Tests\Unit;

use App\Media;
use Tests\TestDataSetup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaTest extends TestDataSetup
{
    /**
     * Total Test Cases: 13
     */

    /**
     * Positive Test Cases: 9
     */
    
    public function test_index_returns_expected_structure()
    {
        $this->actingAs($this->admin1)
                ->get('api/media')
                ->assertStatus(200)
                ->assertJsonStructure([ 
                    'length',
                    'data' => [
                        '*' => [
                            'id',
                            'base_path',
                            'filename',
                            'name',
                            'type',
                            'size',
                            'optimized',
                            'created_at',
                            'updated_at',
                            'user' => [
                                'name',
                                'type',
                                'slug',
                                'avatar',
                                'created_at',
                                'updated_at',
                            ]
                        ]
                    ]
                ]);
    }

    public function test_index_returns_expected_length()
    {
        $response = $this->actingAs($this->admin1)->get('api/media')->decodeResponseJson();
        $this->assertEquals($response['length'], count(Media::all()));
    }

    public function test_show_returns_expected_structure()
    {
        $this->actingAs($this->admin1)
                ->get('api/media/' . $this->media1->id)
                ->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'base_path',
                    'filename',
                    'name',
                    'type',
                    'size',
                    'optimized',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'name',
                        'type',
                        'slug',
                        'avatar',
                        'created_at',
                        'updated_at',
                    ]
                ]);
    }

    public function test_store_can_persist_data()
    {
        $media = [
            'name' => 'TestMedia',
            'file' => UploadedFile::fake()->image('testImage.jpg')->size(100)
        ];

        $response = $this->actingAs($this->admin1)
                ->post('admin/media', $media);
        $data = $response->getData();
        Storage::disk('public')->assertExists($data->filename);
    }

    public function test_update_can_persist_data()
    {
        $media = [
            'base_path' => 'http://test.com',
            'filename' => '600x300',
            'name' => 'test filename update',
            'type' => 'bmp'
        ];

        $this->actingAs($this->admin1)
                ->patch('admin/media/' . $this->media1->id, $media)
                ->assertStatus(200)
                ->assertJsonFragment($media);
    }

    public function test_destroy_can_delete_data()
    {
        $this->actingAs($this->admin1)
                ->delete('admin/media/3')
                ->assertStatus(200)
                ->assertJsonFragment([$this->media3->name]);
    }
    
    public function test_absolute_returns_expected_structure()
    {
        $this->actingAs($this->admin1)
                ->get('api/media/' . $this->media1->id . '/absolute')
                ->assertStatus(200)
                ->assertJsonStructure([
                    'path'
                ]);
    }

    public function test_relative_returns_expected_structure()
    {
        $this->actingAs($this->admin1)
                ->get('api/media/' . $this->media1->id . '/relative')
                ->assertStatus(200)
                ->assertJsonStructure([
                    'path'
                ]);
    }

    public function test_optimize_returns_expected_structure()
    {
        $this->actingAs($this->admin1)
                ->get('api/media/' . $this->media1->id . '/optimize')
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status'
                ]);
    }


    /**
     * Negative Test Cases: 4
     */
    
    public function test_show_error_invalid_id()
    {
        $this->actingAs($this->admin1)
                ->get('api/media/108')
                ->assertStatus(404);
    }

    public function test_store_error_invalid_data()
    {
        $media = [
            'name' => 'Unsupported image',
            'file' => UploadedFile::fake()->image('testImage.xyz')->size(100)
        ];

        $this->actingAs($this->admin1)
                ->post('admin/media', $media)
                ->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Unallowed file type error'
                ]);
    }

    public function test_update_error_invalid_data()
    {
        $media = [
            'base_path' => 'http://test.com',
            'filename' => '600x300',
            'name' => 'test filename update'
        ];
        
        $this->actingAs($this->admin1)
                ->patch('admin/media/' . $this->media1->id, $media)
                ->assertStatus(302);
    }

    public function test_destroy_error_invalid_id()
    {
        $this->actingAs($this->admin1)
                ->delete('admin/media/108')
                ->assertStatus(404);
    }
}
