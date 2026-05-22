<?php

namespace Tests\Feature\Public;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_public_storage_files_are_served_without_signature(): void
    {
        Storage::disk('public')->put('test-assets/hello.txt', 'hello storage');

        $response = $this->get('/storage/test-assets/hello.txt');

        $response->assertOk();

        $this->assertSame('hello storage', $response->streamedContent());
    }
}
